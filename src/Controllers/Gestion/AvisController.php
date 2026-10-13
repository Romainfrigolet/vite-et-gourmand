<?php

declare(strict_types=1);

namespace App\Controllers\Gestion;

use App\Core\Session;
use App\Repositories\AvisRepository;

/**
 * Modération des avis clients : seuls les avis validés apparaissent sur la page d'accueil.
 */
final class AvisController
{
    public function index(): void
    {
        $statut = (string) ($_GET['statut'] ?? 'en_attente');
        $statut = isset(AvisRepository::STATUTS[$statut]) ? $statut : null;
        view('gestion/avis', [
            'title'  => 'Avis clients',
            'avis'   => AvisRepository::forStaff($statut),
            'statut' => $statut,
        ]);
    }

    public function validate(int $id): void
    {
        AvisRepository::setStatus($id, 'valide');
        Session::flash('success', 'Avis validé : il est maintenant visible sur la page d\'accueil.');
        back('/gestion/avis');
    }

    public function refuse(int $id): void
    {
        AvisRepository::setStatus($id, 'refuse');
        Session::flash('success', 'Avis refusé : il ne sera pas publié.');
        back('/gestion/avis');
    }
}
