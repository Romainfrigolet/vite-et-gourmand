<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

/**
 * Accès aux menus, à leur galerie et à leurs plats.
 */
final class MenuRepository
{
    private const SELECT = 'SELECT m.*, t.libelle AS theme, r.libelle AS regime,
                                   (m.prix_par_personne * m.nombre_personne_minimum) AS prix_minimum,
                                   (SELECT mi.chemin FROM menu_image mi WHERE mi.menu_id = m.menu_id ORDER BY mi.position, mi.image_id LIMIT 1) AS image,
                                   (SELECT mi.alt FROM menu_image mi WHERE mi.menu_id = m.menu_id ORDER BY mi.position, mi.image_id LIMIT 1) AS image_alt
                            FROM menu m
                            JOIN theme t ON t.theme_id = m.theme_id
                            JOIN regime r ON r.regime_id = m.regime_id';

    /**
     * Recherche filtrée des menus actifs.
     * Les filtres sont construits dynamiquement mais toujours avec des paramètres liés.
     */
    public static function search(array $filters = []): array
    {
        $where = ['m.actif = 1'];
        $params = [];

        if (isset($filters['prix_max']) && $filters['prix_max'] !== '') {
            $where[] = '(m.prix_par_personne * m.nombre_personne_minimum) <= :prix_max';
            $params['prix_max'] = (float) $filters['prix_max'];
        }
        if (isset($filters['prix_min']) && $filters['prix_min'] !== '') {
            $where[] = '(m.prix_par_personne * m.nombre_personne_minimum) >= :prix_min';
            $params['prix_min'] = (float) $filters['prix_min'];
        }
        if (!empty($filters['theme'])) {
            $where[] = 'm.theme_id = :theme';
            $params['theme'] = (int) $filters['theme'];
        }
        if (!empty($filters['regime'])) {
            $where[] = 'm.regime_id = :regime';
            $params['regime'] = (int) $filters['regime'];
        }
        if (isset($filters['personnes']) && $filters['personnes'] !== '') {
            // Menus commandables pour ce nombre de convives (minimum requis inférieur ou égal)
            $where[] = 'm.nombre_personne_minimum <= :personnes';
            $params['personnes'] = (int) $filters['personnes'];
        }

        $sql = self::SELECT . ' WHERE ' . implode(' AND ', $where)
             . ' ORDER BY (m.quantite_restante = 0), m.theme_id, prix_minimum';
        return Database::fetchAll($sql, $params);
    }

    public static function all(): array
    {
        return Database::fetchAll(self::SELECT . ' ORDER BY m.actif DESC, m.titre');
    }

    public static function find(int $id, bool $onlyActive = true): ?array
    {
        return Database::fetch(
            self::SELECT . ' WHERE m.menu_id = :id' . ($onlyActive ? ' AND m.actif = 1' : ''),
            ['id' => $id]
        );
    }

    public static function options(): array
    {
        return Database::fetchAll('SELECT menu_id, titre FROM menu ORDER BY titre');
    }

    public static function images(int $menuId): array
    {
        return Database::fetchAll(
            'SELECT * FROM menu_image WHERE menu_id = :id ORDER BY position, image_id',
            ['id' => $menuId]
        );
    }

    /** Plats d'un menu avec leurs allergènes, regroupés par type (entrée / plat / dessert). */
    public static function dishes(int $menuId): array
    {
        $rows = Database::fetchAll(
            "SELECT p.plat_id, p.titre_plat, p.type_plat, p.description,
                    GROUP_CONCAT(a.libelle ORDER BY a.libelle SEPARATOR ', ') AS allergenes
             FROM menu_plat mp
             JOIN plat p ON p.plat_id = mp.plat_id
             LEFT JOIN plat_allergene pa ON pa.plat_id = p.plat_id
             LEFT JOIN allergene a ON a.allergene_id = pa.allergene_id
             WHERE mp.menu_id = :id
             GROUP BY p.plat_id, p.titre_plat, p.type_plat, p.description
             ORDER BY FIELD(p.type_plat, 'entree', 'plat', 'dessert'), p.titre_plat",
            ['id' => $menuId]
        );
        $grouped = ['entree' => [], 'plat' => [], 'dessert' => []];
        foreach ($rows as $row) {
            $grouped[$row['type_plat']][] = $row;
        }
        return $grouped;
    }

    public static function dishIds(int $menuId): array
    {
        return array_map('intval', array_column(
            Database::fetchAll('SELECT plat_id FROM menu_plat WHERE menu_id = :id', ['id' => $menuId]),
            'plat_id'
        ));
    }

    public static function save(?int $id, array $data, array $platIds): int
    {
        return Database::transaction(function () use ($id, $data, $platIds): int {
            $params = [
                'titre'       => $data['titre'],
                'description' => $data['description'],
                'min'         => (int) $data['nombre_personne_minimum'],
                'prix'        => (float) str_replace(',', '.', (string) $data['prix_par_personne']),
                'conditions'  => $data['conditions'],
                'delai'       => (int) $data['delai_commande_jours'],
                'stock'       => (int) $data['quantite_restante'],
                'theme'       => (int) $data['theme_id'],
                'regime'      => (int) $data['regime_id'],
                'actif'       => !empty($data['actif']) ? 1 : 0,
            ];

            if ($id === null) {
                Database::run(
                    'INSERT INTO menu (titre, description, nombre_personne_minimum, prix_par_personne, conditions,
                                       delai_commande_jours, quantite_restante, theme_id, regime_id, actif)
                     VALUES (:titre, :description, :min, :prix, :conditions, :delai, :stock, :theme, :regime, :actif)',
                    $params
                );
                $id = Database::lastInsertId();
            } else {
                Database::run(
                    'UPDATE menu SET titre = :titre, description = :description, nombre_personne_minimum = :min,
                            prix_par_personne = :prix, conditions = :conditions, delai_commande_jours = :delai,
                            quantite_restante = :stock, theme_id = :theme, regime_id = :regime, actif = :actif
                     WHERE menu_id = :id',
                    $params + ['id' => $id]
                );
                Database::run('DELETE FROM menu_plat WHERE menu_id = :id', ['id' => $id]);
            }

            $stmt = Database::pdo()->prepare('INSERT INTO menu_plat (menu_id, plat_id) VALUES (:menu, :plat)');
            foreach (array_unique($platIds) as $platId) {
                $stmt->execute(['menu' => $id, 'plat' => $platId]);
            }
            return $id;
        });
    }

    public static function addImage(int $menuId, string $path, string $alt): void
    {
        $pos = (int) (Database::fetch(
            'SELECT COALESCE(MAX(position), -1) + 1 AS p FROM menu_image WHERE menu_id = :id',
            ['id' => $menuId]
        )['p'] ?? 0);
        Database::run(
            'INSERT INTO menu_image (menu_id, chemin, alt, position) VALUES (:id, :chemin, :alt, :pos)',
            ['id' => $menuId, 'chemin' => $path, 'alt' => $alt, 'pos' => $pos]
        );
    }

    public static function deleteImage(int $imageId): ?array
    {
        $image = Database::fetch('SELECT * FROM menu_image WHERE image_id = :id', ['id' => $imageId]);
        if ($image) {
            Database::run('DELETE FROM menu_image WHERE image_id = :id', ['id' => $imageId]);
        }
        return $image;
    }

    public static function hasOrders(int $menuId): bool
    {
        return Database::fetch('SELECT 1 FROM commande WHERE menu_id = :id LIMIT 1', ['id' => $menuId]) !== null;
    }

    /**
     * Suppression d'un menu. S'il a déjà été commandé, il est seulement désactivé
     * pour conserver l'historique des commandes.
     */
    public static function delete(int $menuId): bool
    {
        if (self::hasOrders($menuId)) {
            Database::run('UPDATE menu SET actif = 0 WHERE menu_id = :id', ['id' => $menuId]);
            return false;
        }
        Database::run('DELETE FROM menu WHERE menu_id = :id', ['id' => $menuId]);
        return true;
    }

    /** Décrémente le stock de façon atomique : échoue s'il n'en reste plus. */
    public static function decrementStock(int $menuId): bool
    {
        return Database::run(
            'UPDATE menu SET quantite_restante = quantite_restante - 1
             WHERE menu_id = :id AND quantite_restante > 0',
            ['id' => $menuId]
        )->rowCount() === 1;
    }

    public static function incrementStock(int $menuId): void
    {
        Database::run('UPDATE menu SET quantite_restante = quantite_restante + 1 WHERE menu_id = :id', ['id' => $menuId]);
    }
}
