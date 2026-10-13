<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

/**
 * Accès aux plats et à leurs allergènes.
 */
final class PlatRepository
{
    public const TYPES = ['entree' => 'Entrée', 'plat' => 'Plat', 'dessert' => 'Dessert'];

    public static function all(): array
    {
        return Database::fetchAll(
            "SELECT p.*,
                    GROUP_CONCAT(DISTINCT a.libelle ORDER BY a.libelle SEPARATOR ', ') AS allergenes,
                    COUNT(DISTINCT mp.menu_id) AS nb_menus
             FROM plat p
             LEFT JOIN plat_allergene pa ON pa.plat_id = p.plat_id
             LEFT JOIN allergene a ON a.allergene_id = pa.allergene_id
             LEFT JOIN menu_plat mp ON mp.plat_id = p.plat_id
             GROUP BY p.plat_id
             ORDER BY FIELD(p.type_plat, 'entree', 'plat', 'dessert'), p.titre_plat"
        );
    }

    public static function find(int $id): ?array
    {
        return Database::fetch('SELECT * FROM plat WHERE plat_id = :id', ['id' => $id]);
    }

    public static function allergenIds(int $platId): array
    {
        return array_map('intval', array_column(
            Database::fetchAll('SELECT allergene_id FROM plat_allergene WHERE plat_id = :id', ['id' => $platId]),
            'allergene_id'
        ));
    }

    public static function existingIds(array $ids): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if ($ids === []) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        return array_map('intval', array_column(
            Database::fetchAll("SELECT plat_id FROM plat WHERE plat_id IN ($placeholders)", $ids),
            'plat_id'
        ));
    }

    public static function save(?int $id, array $data, array $allergenIds): int
    {
        return Database::transaction(function () use ($id, $data, $allergenIds): int {
            $params = [
                'titre'       => $data['titre_plat'],
                'type'        => $data['type_plat'],
                'description' => $data['description'] !== '' ? $data['description'] : null,
            ];
            if ($id === null) {
                Database::run(
                    'INSERT INTO plat (titre_plat, type_plat, description) VALUES (:titre, :type, :description)',
                    $params
                );
                $id = Database::lastInsertId();
            } else {
                Database::run(
                    'UPDATE plat SET titre_plat = :titre, type_plat = :type, description = :description WHERE plat_id = :id',
                    $params + ['id' => $id]
                );
                Database::run('DELETE FROM plat_allergene WHERE plat_id = :id', ['id' => $id]);
            }
            $stmt = Database::pdo()->prepare(
                'INSERT INTO plat_allergene (plat_id, allergene_id)
                 SELECT :plat, allergene_id FROM allergene WHERE allergene_id = :allergene'
            );
            foreach (array_unique($allergenIds) as $allergenId) {
                $stmt->execute(['plat' => $id, 'allergene' => (int) $allergenId]);
            }
            return $id;
        });
    }

    public static function delete(int $id): void
    {
        Database::run('DELETE FROM plat WHERE plat_id = :id', ['id' => $id]);
    }
}
