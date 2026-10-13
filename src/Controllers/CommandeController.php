<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Mailer;
use App\Core\Session;
use App\Repositories\CommandeRepository;
use App\Repositories\MenuRepository;
use App\Services\DeliveryService;
use App\Services\OrderForm;
use App\Services\PricingService;

/**
 * Commande d'un menu : formulaire → récapitulatif détaillé du prix → validation.
 */
final class CommandeController
{
    /** Formulaire de commande, menu pré-rempli si l'on vient de la fiche d'un menu. */
    public function form(int $id = 0): void
    {
        $menuId = $id ?: (int) ($_GET['menu'] ?? 0);
        $menu = $menuId ? MenuRepository::find($menuId) : null;
        if ($menu && (int) $menu['quantite_restante'] <= 0) {
            Session::flash('error', 'Ce menu est épuisé et ne peut plus être commandé.');
            redirect('/menus/' . (int) $menu['menu_id']);
        }
        $pending = Session::get('pending_order');
        if (!$menu && $pending) {
            $menu = MenuRepository::find((int) $pending['menu_id']);
        }

        view('commande/form', [
            'title'   => 'Commander un menu',
            'menu'    => $menu,
            'menus'   => array_filter(MenuRepository::search(), static fn ($m) => (int) $m['quantite_restante'] > 0),
            'user'    => Auth::user(),
            'pending' => $pending && $menu && (int) $pending['menu_id'] === (int) $menu['menu_id'] ? $pending : null,
        ]);
    }

    /** Étape 2 : validation serveur et affichage du récapitulatif détaillé avant validation définitive. */
    public function recap(): void
    {
        $menuId = (int) ($_POST['menu_id'] ?? 0);
        $menu = MenuRepository::find($menuId);
        if (!$menu) {
            Session::flash('error', 'Veuillez choisir un menu.');
            redirect('/commander');
        }
        if ((int) $menu['quantite_restante'] <= 0) {
            Session::flash('error', 'Ce menu est épuisé.');
            redirect('/menus/' . $menuId);
        }

        $result = OrderForm::process(OrderForm::input(), $menu);
        if ($result['errors']) {
            Session::withInput($result['data'], $result['errors']);
            redirect('/commander/' . $menuId);
        }

        $order = $result['data'] + ['menu_id' => $menuId];
        Session::set('pending_order', $order);

        view('commande/recap', [
            'title'  => 'Récapitulatif de votre commande',
            'menu'   => $menu,
            'order'  => $order,
            'prices' => $result['prices'],
            'user'   => Auth::user(),
        ]);
    }

    /** Étape 3 : création de la commande (le prix est recalculé, jamais repris du navigateur). */
    public function confirm(): void
    {
        $order = Session::get('pending_order');
        if (!$order) {
            Session::flash('error', 'Votre commande a expiré, veuillez la saisir à nouveau.');
            redirect('/menus');
        }
        if (empty($_POST['accept_conditions'])) {
            Session::flash('error', 'Vous devez confirmer avoir lu les conditions du menu.');
            redirect('/commander/' . (int) $order['menu_id']);
        }

        $menu = MenuRepository::find((int) $order['menu_id']);
        $result = $menu ? OrderForm::process($order, $menu) : ['errors' => ['menu' => 'Menu indisponible']];
        if ($result['errors']) {
            Session::withInput($order, $result['errors']);
            redirect('/commander/' . (int) $order['menu_id']);
        }

        try {
            $id = CommandeRepository::create($result['data'] + ['menu_id' => $menu['menu_id']], $result['prices'], (int) Auth::id());
        } catch (\DomainException $e) {
            Session::flash('error', $e->getMessage());
            redirect('/menus/' . (int) $menu['menu_id']);
        }

        Session::forget('pending_order');
        $commande = CommandeRepository::find($id);
        Mailer::send($commande['client_email'], 'Confirmation de votre commande ' . $commande['numero_commande'], 'order-confirmed', [
            'commande' => $commande,
        ]);

        Session::flash('success', 'Merci ! Votre commande ' . $commande['numero_commande'] . ' est enregistrée. Un e-mail de confirmation vous a été envoyé.');
        redirect('/mon-compte/commandes/' . $id);
    }

    /** API : estimation des frais de livraison pour le récapitulatif dynamique (JavaScript). */
    public function delivery(): void
    {
        $address = trim((string) ($_GET['adresse'] ?? ''));
        $postcode = trim((string) ($_GET['code_postal'] ?? ''));
        $city = trim((string) ($_GET['ville'] ?? ''));
        if (!preg_match('/^\d{5}$/', $postcode) || $city === '' || mb_strlen($city) > 100 || mb_strlen($address) > 255) {
            json_response(['ok' => false, 'error' => 'Renseignez le code postal et la ville.'], 422);
        }
        $location = DeliveryService::locate($address, $postcode, $city);
        if (!$location['ok']) {
            json_response($location, 422);
        }
        json_response([
            'ok'             => true,
            'bordeaux'       => $location['bordeaux'],
            'distance_km'    => $location['distance_km'],
            'prix_livraison' => PricingService::deliveryPrice($location['bordeaux'], $location['distance_km']),
        ]);
    }
}
