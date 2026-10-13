<?php

declare(strict_types=1);

namespace App\Controllers\Gestion;

use App\Core\Auth;
use App\Core\Mailer;
use App\Core\Session;
use App\Core\Validator;
use App\Repositories\CommandeRepository;
use App\Repositories\MenuRepository;
use App\Services\OrderForm;
use App\Services\OrderStatus;

/**
 * Gestion des commandes par l'équipe (employés et administrateur).
 * Règle métier : une commande ne peut être modifiée ou annulée par l'équipe
 * qu'après avoir contacté le client (mode de contact + motif obligatoires).
 */
final class CommandeController
{
    public function index(): void
    {
        $statut = (string) ($_GET['statut'] ?? '');
        if ($statut !== '' && !isset(OrderStatus::LABELS[$statut])) {
            $statut = '';
        }
        $client = mb_substr(trim((string) ($_GET['client'] ?? '')), 0, 100);

        view('gestion/commandes/index', [
            'title'     => 'Commandes',
            'commandes' => CommandeRepository::search($statut, $client),
            'statut'    => $statut,
            'client'    => $client,
        ]);
    }

    public function show(int $id): void
    {
        $commande = CommandeRepository::find($id) ?? abort(404, 'Commande introuvable.');
        view('gestion/commandes/show', [
            'title'    => 'Commande ' . $commande['numero_commande'],
            'commande' => $commande,
            'history'  => CommandeRepository::history($id),
            'next'     => OrderStatus::next($commande['statut'], (int) $commande['pret_materiel'] === 1),
        ]);
    }

    /** Passage au statut suivant (avec e-mails automatiques pour le retour de matériel et la fin de commande). */
    public function status(int $id): void
    {
        $commande = CommandeRepository::find($id) ?? abort(404);
        $statut = (string) ($_POST['statut'] ?? '');

        if (!OrderStatus::canTransition($commande['statut'], $statut, (int) $commande['pret_materiel'] === 1)) {
            Session::flash('error', 'Ce changement de statut n\'est pas autorisé.');
            redirect("/gestion/commandes/$id");
        }

        $comment = mb_substr(trim((string) ($_POST['commentaire'] ?? '')), 0, 500) ?: null;
        if ($commande['statut'] === 'attente_retour_materiel' && $statut === 'terminee') {
            $comment = $comment ?? 'Matériel restitué';
        }
        CommandeRepository::setStatus($id, $statut, (int) Auth::id(), $comment);

        $commande = CommandeRepository::find($id);
        if ($statut === 'attente_retour_materiel') {
            Mailer::send($commande['client_email'], 'Restitution du matériel prêté — commande ' . $commande['numero_commande'], 'material-return', ['commande' => $commande]);
        } elseif ($statut === 'terminee') {
            Mailer::send($commande['client_email'], 'Votre avis nous intéresse — commande ' . $commande['numero_commande'], 'order-finished', ['commande' => $commande]);
        }

        Session::flash('success', 'Statut mis à jour : ' . OrderStatus::label($statut) . '.');
        redirect("/gestion/commandes/$id");
    }

    public function material(int $id): void
    {
        $commande = CommandeRepository::find($id) ?? abort(404);
        if (in_array($commande['statut'], ['attente_retour_materiel', 'terminee', 'annulee'], true)) {
            Session::flash('error', 'Le prêt de matériel ne peut plus être modifié pour cette commande.');
            redirect("/gestion/commandes/$id");
        }
        CommandeRepository::setMaterialLent($id, !empty($_POST['pret_materiel']));
        Session::flash('success', 'Information sur le prêt de matériel enregistrée.');
        redirect("/gestion/commandes/$id");
    }

    public function editForm(int $id): void
    {
        $commande = CommandeRepository::find($id) ?? abort(404);
        $this->ensureStaffEditable($commande, 'modifiée');
        view('gestion/commandes/edit', ['title' => 'Modifier la commande', 'commande' => $commande]);
    }

    public function update(int $id): void
    {
        $commande = CommandeRepository::find($id) ?? abort(404);
        $this->ensureStaffEditable($commande, 'modifiée');

        $contact = $this->contactInput();
        $menu = MenuRepository::find((int) $commande['menu_id'], false);
        // L'équipe peut déroger au délai de commande après accord avec le client
        $result = OrderForm::process(OrderForm::input(), $menu, false);
        $errors = $result['errors'] + $contact['errors'];
        if ($errors) {
            Session::withInput($result['data'] + $contact['data'], $errors);
            redirect("/gestion/commandes/$id/modifier");
        }

        $comment = sprintf('Modifiée par l\'équipe après contact par %s : %s', $contact['label'], $contact['data']['motif']);
        CommandeRepository::updateDetails($id, $result['data'], $result['prices'], (int) Auth::id(), $comment);

        $commande = CommandeRepository::find($id);
        Mailer::send($commande['client_email'], 'Modification de votre commande ' . $commande['numero_commande'], 'order-updated', [
            'commande' => $commande,
            'motif'    => $contact['data']['motif'],
        ]);
        Session::flash('success', 'Commande modifiée. Nouveau total : ' . price($result['prices']['prix_total']) . '.');
        redirect("/gestion/commandes/$id");
    }

    public function cancel(int $id): void
    {
        $commande = CommandeRepository::find($id) ?? abort(404);
        $this->ensureStaffEditable($commande, 'annulée');

        $contact = $this->contactInput();
        if ($contact['errors']) {
            Session::withInput($contact['data'], $contact['errors']);
            redirect("/gestion/commandes/$id#annulation");
        }

        CommandeRepository::cancel($id, (int) Auth::id(), $contact['data']['motif'], $contact['data']['mode_contact']);
        $commande = CommandeRepository::find($id);
        Mailer::send($commande['client_email'], 'Annulation de votre commande ' . $commande['numero_commande'], 'order-cancelled', ['commande' => $commande]);

        Session::flash('success', 'La commande ' . $commande['numero_commande'] . ' a été annulée.');
        redirect("/gestion/commandes/$id");
    }

    /** Mode de contact et motif, obligatoires avant toute modification ou annulation par l'équipe. */
    private function contactInput(): array
    {
        $data = [
            'mode_contact' => (string) ($_POST['mode_contact'] ?? ''),
            'motif'        => trim((string) ($_POST['motif'] ?? '')),
        ];
        $v = Validator::make($data)
            ->required('mode_contact', 'Mode de contact du client')->in('mode_contact', ['telephone', 'email'], 'Mode de contact')
            ->required('motif', 'Motif')->max('motif', 450, 'Motif');
        return [
            'data'   => $data,
            'errors' => $v->errors(),
            'label'  => $data['mode_contact'] === 'telephone' ? 'téléphone (GSM)' : 'e-mail',
        ];
    }

    private function ensureStaffEditable(array $commande, string $action): void
    {
        $allowed = $action === 'annulée'
            ? OrderStatus::cancellableByStaff($commande['statut'])
            : OrderStatus::editableByStaff($commande['statut']);
        if (!$allowed) {
            Session::flash('error', "Cette commande ne peut plus être $action à ce stade.");
            redirect('/gestion/commandes/' . (int) $commande['commande_id']);
        }
    }
}
