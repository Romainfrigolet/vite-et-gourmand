<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Repositories\MenuRepository;
use App\Repositories\ReferenceRepository;

final class MenuController
{
    /** Vue globale de tous les menus (accessible à tous). */
    public function index(): void
    {
        $filters = $this->filters();
        view('menus/index', [
            'title'   => 'Nos menus',
            'menus'   => MenuRepository::search($filters),
            'themes'  => ReferenceRepository::themes(),
            'regimes' => ReferenceRepository::regimes(),
            'filters' => $filters,
        ]);
    }

    /**
     * API utilisée par le JavaScript des filtres : renvoie le HTML des cartes
     * et le nombre de résultats, sans rechargement de la page.
     */
    public function filter(): void
    {
        $menus = MenuRepository::search($this->filters());
        json_response([
            'count' => count($menus),
            'html'  => View::capture('menus/_list', ['menus' => $menus]),
        ]);
    }

    public function show(int $id): void
    {
        $menu = MenuRepository::find($id) ?? abort(404, "Ce menu n'existe pas ou n'est plus proposé.");
        view('menus/show', [
            'title'  => $menu['titre'],
            'menu'   => $menu,
            'images' => MenuRepository::images($id),
            'plats'  => MenuRepository::dishes($id),
        ]);
    }

    /** Lecture et nettoyage des filtres (seules des valeurs numériques sont acceptées). */
    private function filters(): array
    {
        $num = static function (string $key): string {
            $v = str_replace(',', '.', trim((string) ($_GET[$key] ?? '')));
            return is_numeric($v) && (float) $v >= 0 ? $v : '';
        };
        return [
            'prix_max'  => $num('prix_max'),
            'prix_min'  => $num('prix_min'),
            'theme'     => ctype_digit((string) ($_GET['theme'] ?? '')) ? $_GET['theme'] : '',
            'regime'    => ctype_digit((string) ($_GET['regime'] ?? '')) ? $_GET['regime'] : '',
            'personnes' => ctype_digit((string) ($_GET['personnes'] ?? '')) ? $_GET['personnes'] : '',
        ];
    }
}
