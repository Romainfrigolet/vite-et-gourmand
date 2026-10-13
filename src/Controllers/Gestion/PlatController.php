<?php

declare(strict_types=1);

namespace App\Controllers\Gestion;

use App\Core\Session;
use App\Core\Validator;
use App\Repositories\PlatRepository;
use App\Repositories\ReferenceRepository;

/**
 * Gestion des plats et de leurs allergènes.
 */
final class PlatController
{
    public function index(): void
    {
        view('gestion/plats/index', ['title' => 'Plats', 'plats' => PlatRepository::all()]);
    }

    public function create(): void
    {
        $this->form(null);
    }

    public function edit(int $id): void
    {
        $this->form(PlatRepository::find($id) ?? abort(404));
    }

    private function form(?array $plat): void
    {
        view('gestion/plats/form', [
            'title'      => $plat ? 'Modifier le plat' : 'Nouveau plat',
            'plat'       => $plat,
            'allergenes' => ReferenceRepository::allergenes(),
            'selected'   => $plat ? PlatRepository::allergenIds((int) $plat['plat_id']) : [],
        ]);
    }

    public function store(): void
    {
        $this->save(null);
        Session::flash('success', 'Le plat a été créé.');
        redirect('/gestion/plats');
    }

    public function update(int $id): void
    {
        PlatRepository::find($id) ?? abort(404);
        $this->save($id);
        Session::flash('success', 'Le plat a été mis à jour.');
        redirect('/gestion/plats');
    }

    private function save(?int $id): void
    {
        $data = [
            'titre_plat'  => (string) input('titre_plat', ''),
            'type_plat'   => (string) input('type_plat', ''),
            'description' => (string) input('description', ''),
        ];
        $allergenIds = array_map('intval', (array) ($_POST['allergenes'] ?? []));

        $v = Validator::make($data)
            ->required('titre_plat', 'Nom du plat')->max('titre_plat', 120, 'Nom du plat')
            ->required('type_plat', 'Type')->in('type_plat', array_keys(PlatRepository::TYPES), 'Type')
            ->max('description', 500, 'Description');

        if ($v->fails()) {
            Session::withInput($data + ['allergenes' => $allergenIds], $v->errors());
            redirect($id ? "/gestion/plats/$id/modifier" : '/gestion/plats/nouveau');
        }
        PlatRepository::save($id, $data, $allergenIds);
    }

    public function delete(int $id): void
    {
        PlatRepository::find($id) ?? abort(404);
        PlatRepository::delete($id);
        Session::flash('success', 'Le plat a été supprimé (et retiré des menus qui le contenaient).');
        redirect('/gestion/plats');
    }
}
