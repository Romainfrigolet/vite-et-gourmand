<?php

declare(strict_types=1);

namespace App\Controllers\Gestion;

use App\Core\Session;
use App\Repositories\HoraireRepository;

final class HoraireController
{
    public function edit(): void
    {
        view('gestion/horaires', ['title' => 'Horaires', 'horaires' => HoraireRepository::all()]);
    }

    public function update(): void
    {
        $rows = (array) ($_POST['horaires'] ?? []);
        $errors = [];
        foreach (HoraireRepository::all() as $h) {
            $id = (int) $h['horaire_id'];
            $row = (array) ($rows[$id] ?? []);
            $closed = !empty($row['ferme']);
            $open = (string) ($row['ouverture'] ?? '');
            $close = (string) ($row['fermeture'] ?? '');
            $valid = static fn (string $t): bool => (bool) preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $t);

            if (!$closed && (!$valid($open) || !$valid($close) || $open >= $close)) {
                $errors[] = $h['jour'];
                continue;
            }
            HoraireRepository::update($id, $closed ? null : $open, $closed ? null : $close, $closed);
        }

        if ($errors) {
            Session::flash('error', 'Horaires invalides pour : ' . implode(', ', $errors) . ' (l\'ouverture doit précéder la fermeture).');
        } else {
            Session::flash('success', 'Les horaires ont été mis à jour.');
        }
        redirect('/gestion/horaires');
    }
}
