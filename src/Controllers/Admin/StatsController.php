<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Repositories\MenuRepository;
use App\Repositories\StatsRepository;

/**
 * Tableau de bord administrateur : nombre de commandes par menu (graphique)
 * et chiffre d'affaires par menu filtrable par menu et par période.
 * Toutes les données proviennent de MongoDB (base non relationnelle).
 */
final class StatsController
{
    public function index(): void
    {
        $filters = $this->filters();
        $error = null;
        try {
            $stats = StatsRepository::byMenu($filters['menu'], $filters['du'], $filters['au']);
        } catch (\Throwable $e) {
            log_error('Statistiques MongoDB indisponibles : ' . $e->getMessage());
            $stats = [];
            $error = 'La base de statistiques (MongoDB) est momentanément indisponible.';
        }

        \App\Core\View::script('vendor/chartjs/chart.umd.min.js');
        \App\Core\View::script('admin-stats.js');

        view('admin/stats', [
            'title'   => 'Statistiques',
            'stats'   => $stats,
            'menus'   => MenuRepository::options(),
            'filters' => $filters,
            'error'   => $error,
            'totals'  => [
                'commandes' => array_sum(array_column($stats, 'nb_commandes')),
                'ca'        => array_sum(array_column($stats, 'chiffre_affaires')),
            ],
        ]);
    }

    /** API JSON utilisée par le graphique (actualisation sans rechargement). */
    public function data(): void
    {
        $f = $this->filters();
        json_response(StatsRepository::byMenu($f['menu'], $f['du'], $f['au']));
    }

    private function filters(): array
    {
        $date = static function (string $key): ?string {
            $v = (string) ($_GET[$key] ?? '');
            $d = \DateTime::createFromFormat('Y-m-d', $v);
            return $d && $d->format('Y-m-d') === $v ? $v : null;
        };
        $menu = ctype_digit((string) ($_GET['menu'] ?? '')) ? (int) $_GET['menu'] : null;
        return ['menu' => $menu ?: null, 'du' => $date('du'), 'au' => $date('au')];
    }
}
