<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Session;
use App\Core\Validator;
use App\Repositories\AvisRepository;
use App\Repositories\CommandeRepository;
use App\Repositories\MenuRepository;
use App\Repositories\UserRepository;
use App\Services\OrderForm;
use App\Services\OrderStatus;

/**
 * Espace utilisateur : commandes, suivi, modification/annulation, avis et informations personnelles.
 * Chaque accès à une commande vérifie qu'elle appartient bien à l'utilisateur connecté.
 */
final class AccountController
{
    public function index(): void
    {
        view('account/index', [
            'title'     => 'Mon espace',
            'user'      => Auth::user(),
            'commandes' => CommandeRepository::forUser((int) Auth::id()),
        ]);
    }

    public function show(int $id): void
    {
        $commande = $this->ownedOrder($id);
        view('account/order', [
            'title'    => 'Commande ' . $commande['numero_commande'],
            'commande' => $commande,
            'history'  => CommandeRepository::history($id),
            'avis'     => AvisRepository::forOrder($id),
        ]);
    }

    public function editForm(int $id): void
    {
        $commande = $this->ownedOrder($id);
        $this->ensureEditable($commande);
        view('account/order-edit', ['title' => 'Modifier ma commande', 'commande' => $commande]);
    }

    public function update(int $id): void
    {
        $commande = $this->ownedOrder($id);
        $this->ensureEditable($commande);

        $menu = MenuRepository::find((int) $commande['menu_id'], false);
        $result = OrderForm::process(OrderForm::input(), $menu);
        if ($result['errors']) {
            Session::withInput($result['data'], $result['errors']);
            redirect("/mon-compte/commandes/$id/modifier");
        }

        CommandeRepository::updateDetails($id, $result['data'], $result['prices'], (int) Auth::id(), 'Commande modifiée par le client');
        Session::flash('success', 'Votre commande a été modifiée. Nouveau total : ' . price($result['prices']['prix_total']) . '.');
        redirect("/mon-compte/commandes/$id");
    }

    public function cancel(int $id): void
    {
        $commande = $this->ownedOrder($id);
        $this->ensureEditable($commande);
        CommandeRepository::cancel($id, (int) Auth::id(), 'Annulée par le client', null);
        Session::flash('success', 'Votre commande ' . $commande['numero_commande'] . ' a été annulée.');
        redirect('/mon-compte');
    }

    public function review(int $id): void
    {
        $commande = $this->ownedOrder($id);
        if ($commande['statut'] !== 'terminee' || AvisRepository::forOrder($id)) {
            Session::flash('error', 'Vous ne pouvez pas donner d\'avis sur cette commande.');
            redirect("/mon-compte/commandes/$id");
        }

        $data = ['note' => (string) input('note', ''), 'commentaire' => (string) input('commentaire', '')];
        $v = Validator::make($data)
            ->required('note', 'Note')->integer('note', 'Note', 1, 5)
            ->required('commentaire', 'Commentaire')->max('commentaire', 1000, 'Commentaire');
        if ($v->fails()) {
            Session::withInput($data, $v->errors());
            redirect("/mon-compte/commandes/$id#avis");
        }

        AvisRepository::create($id, (int) Auth::id(), (int) $data['note'], $data['commentaire']);
        Session::flash('success', 'Merci pour votre avis ! Il sera publié après validation par notre équipe.');
        redirect("/mon-compte/commandes/$id");
    }

    // --- Informations personnelles ------------------------------------------------------------

    public function profileForm(): void
    {
        view('account/profile', ['title' => 'Mes informations', 'profile' => Auth::user()]);
    }

    public function updateProfile(): void
    {
        $user = Auth::user();
        $data = [];
        foreach (AuthController::profileFields() as $field) {
            $data[$field] = (string) input($field, '');
        }
        $passwords = [
            'current_password'      => (string) ($_POST['current_password'] ?? ''),
            'password'              => (string) ($_POST['password'] ?? ''),
            'password_confirmation' => (string) ($_POST['password_confirmation'] ?? ''),
        ];

        $v = AuthController::profileValidator($data + $passwords);
        if (UserRepository::emailExists($data['email'], (int) $user['utilisateur_id'])) {
            $v->addError('email', 'Cette adresse e-mail est déjà utilisée par un autre compte.');
        }
        $changePassword = $passwords['password'] !== '';
        if ($changePassword) {
            if (!password_verify($passwords['current_password'], $user['password'])) {
                $v->addError('current_password', 'Le mot de passe actuel est incorrect.');
            }
            $v->strongPassword('password')->confirmed('password', 'password_confirmation');
        }

        if ($v->fails()) {
            Session::withInput($data, $v->errors());
            redirect('/mon-compte/informations');
        }

        UserRepository::updateProfile((int) $user['utilisateur_id'], $data);
        if ($changePassword) {
            UserRepository::updatePassword((int) $user['utilisateur_id'], $passwords['password']);
            Session::regenerate();
        }
        Session::flash('success', 'Vos informations ont été mises à jour.');
        redirect('/mon-compte/informations');
    }

    /** Droit à l'effacement (RGPD). */
    public function delete(): void
    {
        $user = Auth::user();
        if (!password_verify((string) ($_POST['password_delete'] ?? ''), $user['password'])) {
            Session::flash('error', 'Mot de passe incorrect : votre compte n\'a pas été supprimé.');
            redirect('/mon-compte/informations');
        }
        if (CommandeRepository::hasActiveOrders((int) $user['utilisateur_id'])) {
            Session::flash('error', 'Vous avez une commande en cours : votre compte pourra être supprimé une fois celle-ci terminée ou annulée.');
            redirect('/mon-compte/informations');
        }
        UserRepository::anonymize((int) $user['utilisateur_id']);
        Auth::logout();
        Session::start();
        Session::flash('success', 'Votre compte a été supprimé et vos données personnelles effacées.');
        redirect('/');
    }

    // --- Contrôles d'accès ---------------------------------------------------------------------

    private function ownedOrder(int $id): array
    {
        return CommandeRepository::findForUser($id, (int) Auth::id()) ?? abort(404, 'Commande introuvable.');
    }

    private function ensureEditable(array $commande): void
    {
        if (!OrderStatus::editableByCustomer($commande['statut'])) {
            Session::flash('error', 'Cette commande a déjà été acceptée par notre équipe : contactez-nous pour toute modification.');
            redirect('/mon-compte/commandes/' . (int) $commande['commande_id']);
        }
    }
}
