<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

/**
 * Avis clients : déposés après une commande terminée, publiés après validation par l'équipe.
 */
final class AvisRepository
{
    public const STATUTS = ['en_attente' => 'En attente', 'valide' => 'Validé', 'refuse' => 'Refusé'];

    public static function validated(int $limit = 6): array
    {
        return Database::fetchAll(
            "SELECT a.note, a.commentaire, a.created_at, u.prenom, LEFT(u.nom, 1) AS initiale, m.titre AS menu
             FROM avis a
             JOIN utilisateur u ON u.utilisateur_id = a.utilisateur_id
             JOIN commande c ON c.commande_id = a.commande_id
             JOIN menu m ON m.menu_id = c.menu_id
             WHERE a.statut = 'valide'
             ORDER BY a.created_at DESC
             LIMIT " . max(1, $limit)
        );
    }

    public static function averageValidated(): ?float
    {
        $row = Database::fetch("SELECT AVG(note) AS moyenne, COUNT(*) AS nb FROM avis WHERE statut = 'valide'");
        return (int) $row['nb'] > 0 ? round((float) $row['moyenne'], 1) : null;
    }

    public static function forStaff(?string $statut = null): array
    {
        $sql = 'SELECT a.*, u.prenom, u.nom, u.email, c.numero_commande, m.titre AS menu
                FROM avis a
                JOIN utilisateur u ON u.utilisateur_id = a.utilisateur_id
                JOIN commande c ON c.commande_id = a.commande_id
                JOIN menu m ON m.menu_id = c.menu_id';
        $params = [];
        if ($statut !== null && isset(self::STATUTS[$statut])) {
            $sql .= ' WHERE a.statut = :statut';
            $params['statut'] = $statut;
        }
        return Database::fetchAll($sql . " ORDER BY a.statut = 'en_attente' DESC, a.created_at DESC", $params);
    }

    public static function forOrder(int $commandeId): ?array
    {
        return Database::fetch('SELECT * FROM avis WHERE commande_id = :id', ['id' => $commandeId]);
    }

    public static function create(int $commandeId, int $userId, int $note, string $commentaire): void
    {
        Database::run(
            'INSERT INTO avis (commande_id, utilisateur_id, note, commentaire) VALUES (:c, :u, :n, :t)',
            ['c' => $commandeId, 'u' => $userId, 'n' => $note, 't' => $commentaire]
        );
    }

    public static function setStatus(int $avisId, string $statut): void
    {
        Database::run('UPDATE avis SET statut = :s WHERE avis_id = :id', ['s' => $statut, 'id' => $avisId]);
    }

    public static function countPending(): int
    {
        return (int) Database::fetch("SELECT COUNT(*) AS nb FROM avis WHERE statut = 'en_attente'")['nb'];
    }
}
