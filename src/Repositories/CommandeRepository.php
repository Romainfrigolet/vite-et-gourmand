<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

/**
 * Accès aux commandes et à leur historique de statuts.
 * Chaque modification est répercutée dans MongoDB (StatsRepository) pour les statistiques.
 */
final class CommandeRepository
{
    private const SELECT = 'SELECT c.*, m.titre AS menu_titre, m.nombre_personne_minimum, m.prix_par_personne,
                                   m.conditions AS menu_conditions, m.delai_commande_jours,
                                   u.nom AS client_nom, u.prenom AS client_prenom, u.email AS client_email,
                                   u.telephone AS client_telephone
                            FROM commande c
                            JOIN menu m ON m.menu_id = c.menu_id
                            JOIN utilisateur u ON u.utilisateur_id = c.utilisateur_id';

    public static function find(int $id): ?array
    {
        return Database::fetch(self::SELECT . ' WHERE c.commande_id = :id', ['id' => $id]);
    }

    /** Commande appartenant à l'utilisateur (contrôle d'accès : empêche de consulter la commande d'un autre client). */
    public static function findForUser(int $id, int $userId): ?array
    {
        return Database::fetch(
            self::SELECT . ' WHERE c.commande_id = :id AND c.utilisateur_id = :user',
            ['id' => $id, 'user' => $userId]
        );
    }

    public static function forUser(int $userId): array
    {
        return Database::fetchAll(
            self::SELECT . ' WHERE c.utilisateur_id = :user ORDER BY c.date_commande DESC',
            ['user' => $userId]
        );
    }

    public static function hasActiveOrders(int $userId): bool
    {
        return Database::fetch(
            "SELECT 1 FROM commande WHERE utilisateur_id = :u AND statut NOT IN ('terminee', 'annulee') LIMIT 1",
            ['u' => $userId]
        ) !== null;
    }

    /** Liste filtrable pour l'espace employé : par statut et/ou par client (nom, prénom, e-mail ou n° de commande). */
    public static function search(?string $statut, ?string $client): array
    {
        $where = [];
        $params = [];
        if ($statut !== null && $statut !== '') {
            $where[] = 'c.statut = :statut';
            $params['statut'] = $statut;
        }
        if ($client !== null && $client !== '') {
            $where[] = "(u.nom LIKE :q1 OR u.prenom LIKE :q2 OR u.email LIKE :q3 OR c.numero_commande LIKE :q4
                         OR CONCAT(u.prenom, ' ', u.nom) LIKE :q5)";
            $like = '%' . addcslashes($client, '%_\\') . '%';
            $params += ['q1' => $like, 'q2' => $like, 'q3' => $like, 'q4' => $like, 'q5' => $like];
        }
        $sql = self::SELECT . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
             . " ORDER BY FIELD(c.statut, 'en_attente', 'accepte', 'en_preparation', 'en_livraison', 'livre',
                                'attente_retour_materiel', 'terminee', 'annulee'), c.date_prestation";
        return Database::fetchAll($sql, $params);
    }

    public static function countByStatus(): array
    {
        $rows = Database::fetchAll('SELECT statut, COUNT(*) AS nb FROM commande GROUP BY statut');
        return array_column($rows, 'nb', 'statut');
    }

    public static function history(int $id): array
    {
        return Database::fetchAll(
            'SELECT h.*, u.prenom, u.nom, r.libelle AS role
             FROM commande_historique h
             LEFT JOIN utilisateur u ON u.utilisateur_id = h.modifie_par
             LEFT JOIN role r ON r.role_id = u.role_id
             WHERE h.commande_id = :id
             ORDER BY h.date_modification, h.historique_id',
            ['id' => $id]
        );
    }

    /**
     * Création d'une commande dans une transaction :
     * le stock du menu est décrémenté de façon atomique (pas de survente).
     */
    public static function create(array $data, array $prices, int $userId): int
    {
        $id = Database::transaction(function () use ($data, $prices, $userId): int {
            if (!MenuRepository::decrementStock((int) $data['menu_id'])) {
                throw new \DomainException('Ce menu est épuisé : il ne peut plus être commandé.');
            }

            Database::run(
                'INSERT INTO commande (numero_commande, utilisateur_id, menu_id, date_prestation, heure_livraison,
                                       adresse_prestation, code_postal_prestation, ville_prestation, distance_km,
                                       nombre_personne, prix_menu, remise, prix_livraison, prix_total)
                 VALUES (:numero, :user, :menu, :date, :heure, :adresse, :cp, :ville, :km, :nb, :pm, :remise, :pl, :total)',
                [
                    'numero'  => 'TMP-' . bin2hex(random_bytes(6)),
                    'user'    => $userId,
                    'menu'    => (int) $data['menu_id'],
                    'date'    => $data['date_prestation'],
                    'heure'   => $data['heure_livraison'],
                    'adresse' => $data['adresse_prestation'],
                    'cp'      => $data['code_postal_prestation'],
                    'ville'   => $data['ville_prestation'],
                    'km'      => $data['distance_km'],
                    'nb'      => (int) $data['nombre_personne'],
                    'pm'      => $prices['prix_menu'],
                    'remise'  => $prices['remise'],
                    'pl'      => $prices['prix_livraison'],
                    'total'   => $prices['prix_total'],
                ]
            );
            $id = Database::lastInsertId();
            Database::run(
                'UPDATE commande SET numero_commande = :n WHERE commande_id = :id',
                ['n' => sprintf('VG-%s-%04d', date('Y'), $id), 'id' => $id]
            );
            self::addHistory($id, 'en_attente', $userId, 'Commande passée par le client');
            return $id;
        });

        StatsRepository::sync($id);
        return $id;
    }

    /** Modification de la prestation (tout sauf le menu). */
    public static function updateDetails(int $id, array $data, array $prices, int $byUser, string $comment): void
    {
        Database::transaction(function () use ($id, $data, $prices, $byUser, $comment): void {
            Database::run(
                'UPDATE commande SET date_prestation = :date, heure_livraison = :heure, adresse_prestation = :adresse,
                        code_postal_prestation = :cp, ville_prestation = :ville, distance_km = :km, nombre_personne = :nb,
                        prix_menu = :pm, remise = :remise, prix_livraison = :pl, prix_total = :total
                 WHERE commande_id = :id',
                [
                    'date'    => $data['date_prestation'],
                    'heure'   => $data['heure_livraison'],
                    'adresse' => $data['adresse_prestation'],
                    'cp'      => $data['code_postal_prestation'],
                    'ville'   => $data['ville_prestation'],
                    'km'      => $data['distance_km'],
                    'nb'      => (int) $data['nombre_personne'],
                    'pm'      => $prices['prix_menu'],
                    'remise'  => $prices['remise'],
                    'pl'      => $prices['prix_livraison'],
                    'total'   => $prices['prix_total'],
                    'id'      => $id,
                ]
            );
            $current = Database::fetch('SELECT statut FROM commande WHERE commande_id = :id', ['id' => $id]);
            self::addHistory($id, $current['statut'], $byUser, $comment);
        });
        StatsRepository::sync($id);
    }

    /** Annulation : le stock du menu est restitué. */
    public static function cancel(int $id, int $byUser, string $motif, ?string $modeContact): void
    {
        Database::transaction(function () use ($id, $byUser, $motif, $modeContact): void {
            $order = Database::fetch('SELECT menu_id, statut FROM commande WHERE commande_id = :id FOR UPDATE', ['id' => $id]);
            if (!$order || $order['statut'] === 'annulee') {
                return;
            }
            Database::run(
                "UPDATE commande SET statut = 'annulee', motif_annulation = :motif, mode_contact_annulation = :mode
                 WHERE commande_id = :id",
                ['motif' => $motif, 'mode' => $modeContact, 'id' => $id]
            );
            MenuRepository::incrementStock((int) $order['menu_id']);
            $comment = $motif . ($modeContact ? ' (client contacté par ' . ($modeContact === 'telephone' ? 'téléphone' : 'e-mail') . ')' : '');
            self::addHistory($id, 'annulee', $byUser, $comment);
        });
        StatsRepository::sync($id);
    }

    public static function setStatus(int $id, string $statut, int $byUser, ?string $comment = null): void
    {
        Database::transaction(function () use ($id, $statut, $byUser, $comment): void {
            Database::run(
                'UPDATE commande SET statut = :s, restitution_materiel = IF(:s2 = \'terminee\' AND pret_materiel = 1, 1, restitution_materiel)
                 WHERE commande_id = :id',
                ['s' => $statut, 's2' => $statut, 'id' => $id]
            );
            self::addHistory($id, $statut, $byUser, $comment);
        });
        StatsRepository::sync($id);
    }

    public static function setMaterialLent(int $id, bool $lent): void
    {
        Database::run('UPDATE commande SET pret_materiel = :p WHERE commande_id = :id', ['p' => $lent ? 1 : 0, 'id' => $id]);
    }

    private static function addHistory(int $id, string $statut, ?int $byUser, ?string $comment): void
    {
        Database::run(
            'INSERT INTO commande_historique (commande_id, statut, commentaire, modifie_par) VALUES (:id, :s, :c, :u)',
            ['id' => $id, 's' => $statut, 'c' => $comment, 'u' => $byUser]
        );
    }

    /** Toutes les commandes (pour la reconstruction des statistiques MongoDB). */
    public static function allForStats(): array
    {
        return Database::fetchAll(
            'SELECT c.commande_id, c.numero_commande, c.menu_id, m.titre AS menu_titre, c.date_commande, c.date_prestation,
                    c.nombre_personne, c.prix_menu, c.remise, c.prix_livraison, c.prix_total, c.statut
             FROM commande c JOIN menu m ON m.menu_id = c.menu_id'
        );
    }

    public static function forStats(int $id): ?array
    {
        return Database::fetch(
            'SELECT c.commande_id, c.numero_commande, c.menu_id, m.titre AS menu_titre, c.date_commande, c.date_prestation,
                    c.nombre_personne, c.prix_menu, c.remise, c.prix_livraison, c.prix_total, c.statut
             FROM commande c JOIN menu m ON m.menu_id = c.menu_id
             WHERE c.commande_id = :id',
            ['id' => $id]
        );
    }
}
