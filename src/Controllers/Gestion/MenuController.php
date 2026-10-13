<?php

declare(strict_types=1);

namespace App\Controllers\Gestion;

use App\Core\Session;
use App\Core\Upload;
use App\Core\Validator;
use App\Repositories\MenuRepository;
use App\Repositories\PlatRepository;
use App\Repositories\ReferenceRepository;

/**
 * Gestion des menus (création, modification, suppression, galerie d'images).
 */
final class MenuController
{
    private const FIELDS = ['titre', 'description', 'nombre_personne_minimum', 'prix_par_personne', 'conditions',
                            'delai_commande_jours', 'quantite_restante', 'theme_id', 'regime_id'];

    public function index(): void
    {
        view('gestion/menus/index', ['title' => 'Menus', 'menus' => MenuRepository::all()]);
    }

    public function create(): void
    {
        $this->form(null);
    }

    public function edit(int $id): void
    {
        $this->form(MenuRepository::find($id, false) ?? abort(404));
    }

    private function form(?array $menu): void
    {
        view('gestion/menus/form', [
            'title'    => $menu ? 'Modifier le menu' : 'Nouveau menu',
            'menu'     => $menu,
            'themes'   => ReferenceRepository::themes(),
            'regimes'  => ReferenceRepository::regimes(),
            'plats'    => PlatRepository::all(),
            'selected' => $menu ? MenuRepository::dishIds((int) $menu['menu_id']) : [],
            'images'   => $menu ? MenuRepository::images((int) $menu['menu_id']) : [],
        ]);
    }

    public function store(): void
    {
        $id = $this->save(null);
        Session::flash('success', 'Le menu a été créé. Vous pouvez maintenant ajouter des images à sa galerie.');
        redirect("/gestion/menus/$id/modifier");
    }

    public function update(int $id): void
    {
        MenuRepository::find($id, false) ?? abort(404);
        $this->save($id);
        Session::flash('success', 'Le menu a été mis à jour.');
        redirect("/gestion/menus/$id/modifier");
    }

    private function save(?int $id): int
    {
        $data = [];
        foreach (self::FIELDS as $field) {
            $data[$field] = (string) input($field, '');
        }
        $data['actif'] = !empty($_POST['actif']);
        $platIds = PlatRepository::existingIds((array) ($_POST['plats'] ?? []));

        $v = Validator::make($data)
            ->required('titre', 'Titre')->max('titre', 120, 'Titre')
            ->required('description', 'Description')->max('description', 3000, 'Description')
            ->required('nombre_personne_minimum', 'Nombre de personnes minimum')->integer('nombre_personne_minimum', 'Nombre de personnes minimum', 1, 1000)
            ->required('prix_par_personne', 'Prix par personne')->decimal('prix_par_personne', 'Prix par personne')
            ->max('conditions', 3000, 'Conditions')
            ->required('delai_commande_jours', 'Délai de commande')->integer('delai_commande_jours', 'Délai de commande', 0, 365)
            ->required('quantite_restante', 'Stock disponible')->integer('quantite_restante', 'Stock disponible', 0, 10000)
            ->required('theme_id', 'Thème')->required('regime_id', 'Régime');

        if ($data['theme_id'] !== '' && !ReferenceRepository::themeExists((int) $data['theme_id'])) {
            $v->addError('theme_id', 'Thème inconnu.');
        }
        if ($data['regime_id'] !== '' && !ReferenceRepository::regimeExists((int) $data['regime_id'])) {
            $v->addError('regime_id', 'Régime inconnu.');
        }
        if ($platIds === []) {
            $v->addError('plats', 'Sélectionnez au moins un plat pour composer le menu.');
        }

        if ($v->fails()) {
            Session::withInput($data + ['plats' => $platIds], $v->errors());
            redirect($id ? "/gestion/menus/$id/modifier" : '/gestion/menus/nouveau');
        }

        return MenuRepository::save($id, $data, $platIds);
    }

    public function delete(int $id): void
    {
        MenuRepository::find($id, false) ?? abort(404);
        $deleted = MenuRepository::delete($id);
        Session::flash('success', $deleted
            ? 'Le menu a été supprimé.'
            : 'Ce menu a déjà été commandé : il a été désactivé (masqué du site) pour conserver l\'historique des commandes.');
        redirect('/gestion/menus');
    }

    public function addImage(int $id): void
    {
        MenuRepository::find($id, false) ?? abort(404);
        $alt = trim((string) ($_POST['alt'] ?? ''));
        if ($alt === '' || mb_strlen($alt) > 255) {
            Session::flash('error', 'Décrivez l\'image (texte alternatif obligatoire pour l\'accessibilité, 255 caractères max.).');
            redirect("/gestion/menus/$id/modifier#galerie");
        }
        $upload = Upload::image($_FILES['image'] ?? []);
        if (!$upload['ok']) {
            Session::flash('error', $upload['error']);
            redirect("/gestion/menus/$id/modifier#galerie");
        }
        MenuRepository::addImage($id, $upload['path'], $alt);
        Session::flash('success', 'Image ajoutée à la galerie.');
        redirect("/gestion/menus/$id/modifier#galerie");
    }

    public function deleteImage(int $id): void
    {
        $image = MenuRepository::deleteImage($id) ?? abort(404);
        // Seuls les fichiers téléversés sont supprimés du disque (pas les visuels fournis avec le projet)
        if (str_starts_with($image['chemin'], 'uploads/')) {
            $file = BASE_PATH . '/public/' . $image['chemin'];
            if (is_file($file) && str_starts_with((string) realpath($file), (string) realpath(BASE_PATH . '/public/uploads'))) {
                unlink($file);
            }
        }
        Session::flash('success', 'Image supprimée.');
        redirect('/gestion/menus/' . (int) $image['menu_id'] . '/modifier#galerie');
    }
}
