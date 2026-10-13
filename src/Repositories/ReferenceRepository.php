<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

/**
 * Référentiels : thèmes, régimes, allergènes.
 */
final class ReferenceRepository
{
    public static function themes(): array
    {
        return Database::fetchAll('SELECT theme_id, libelle FROM theme ORDER BY theme_id');
    }

    public static function regimes(): array
    {
        return Database::fetchAll('SELECT regime_id, libelle FROM regime ORDER BY regime_id');
    }

    public static function allergenes(): array
    {
        return Database::fetchAll('SELECT allergene_id, libelle FROM allergene ORDER BY libelle');
    }

    public static function addRegime(string $libelle): void
    {
        Database::run('INSERT IGNORE INTO regime (libelle) VALUES (:l)', ['l' => $libelle]);
    }

    public static function themeExists(int $id): bool
    {
        return Database::fetch('SELECT 1 FROM theme WHERE theme_id = :id', ['id' => $id]) !== null;
    }

    public static function regimeExists(int $id): bool
    {
        return Database::fetch('SELECT 1 FROM regime WHERE regime_id = :id', ['id' => $id]) !== null;
    }
}
