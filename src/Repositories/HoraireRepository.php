<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

/**
 * Horaires d'ouverture affichés dans le pied de page.
 */
final class HoraireRepository
{
    private static ?array $cache = null;

    public static function all(): array
    {
        if (self::$cache === null) {
            try {
                self::$cache = Database::fetchAll(
                    "SELECT * FROM horaire
                     ORDER BY FIELD(jour, 'Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche')"
                );
            } catch (\Throwable $e) {
                // Le pied de page ne doit jamais empêcher l'affichage d'une page
                log_error('Horaires indisponibles : ' . $e->getMessage());
                self::$cache = [];
            }
        }
        return self::$cache;
    }

    public static function update(int $id, ?string $opening, ?string $closing, bool $closed): void
    {
        Database::run(
            'UPDATE horaire SET heure_ouverture = :o, heure_fermeture = :f, ferme = :ferme WHERE horaire_id = :id',
            [
                'o'     => $closed ? null : $opening,
                'f'     => $closed ? null : $closing,
                'ferme' => $closed ? 1 : 0,
                'id'    => $id,
            ]
        );
        self::$cache = null;
    }
}
