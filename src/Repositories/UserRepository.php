<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

/**
 * Accès aux données des utilisateurs (requêtes SQL préparées).
 */
final class UserRepository
{
    private const SELECT = 'SELECT u.*, r.libelle AS role
                            FROM utilisateur u
                            JOIN role r ON r.role_id = u.role_id';

    public static function findById(int $id): ?array
    {
        return Database::fetch(self::SELECT . ' WHERE u.utilisateur_id = :id', ['id' => $id]);
    }

    public static function findByEmail(string $email): ?array
    {
        return Database::fetch(self::SELECT . ' WHERE u.email = :email', ['email' => mb_strtolower(trim($email))]);
    }

    public static function emailExists(string $email, ?int $exceptId = null): bool
    {
        $row = Database::fetch(
            'SELECT 1 FROM utilisateur WHERE email = :email AND utilisateur_id <> :id',
            ['email' => mb_strtolower(trim($email)), 'id' => $exceptId ?? 0]
        );
        return $row !== null;
    }

    private static function roleId(string $role): int
    {
        return (int) Database::fetch('SELECT role_id FROM role WHERE libelle = :l', ['l' => $role])['role_id'];
    }

    /** Création d'un compte client : le rôle « utilisateur » est imposé côté serveur. */
    public static function createCustomer(array $data): int
    {
        Database::run(
            'INSERT INTO utilisateur (email, password, nom, prenom, telephone, adresse_postale, code_postal, ville, role_id)
             VALUES (:email, :password, :nom, :prenom, :telephone, :adresse, :cp, :ville, :role)',
            [
                'email'     => mb_strtolower(trim($data['email'])),
                'password'  => password_hash($data['password'], PASSWORD_DEFAULT),
                'nom'       => $data['nom'],
                'prenom'    => $data['prenom'],
                'telephone' => normalize_phone($data['telephone']),
                'adresse'   => $data['adresse_postale'],
                'cp'        => $data['code_postal'],
                'ville'     => $data['ville'],
                'role'      => self::roleId('utilisateur'),
            ]
        );
        return Database::lastInsertId();
    }

    /** Création d'un compte employé par l'administrateur (jamais de compte administrateur). */
    public static function createEmployee(array $data): int
    {
        Database::run(
            'INSERT INTO utilisateur (email, password, nom, prenom, role_id)
             VALUES (:email, :password, :nom, :prenom, :role)',
            [
                'email'    => mb_strtolower(trim($data['email'])),
                'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                'nom'      => $data['nom'],
                'prenom'   => $data['prenom'],
                'role'     => self::roleId('employe'),
            ]
        );
        return Database::lastInsertId();
    }

    public static function updateProfile(int $id, array $data): void
    {
        Database::run(
            'UPDATE utilisateur
             SET email = :email, nom = :nom, prenom = :prenom, telephone = :telephone,
                 adresse_postale = :adresse, code_postal = :cp, ville = :ville
             WHERE utilisateur_id = :id',
            [
                'email'     => mb_strtolower(trim($data['email'])),
                'nom'       => $data['nom'],
                'prenom'    => $data['prenom'],
                'telephone' => normalize_phone($data['telephone']),
                'adresse'   => $data['adresse_postale'],
                'cp'        => $data['code_postal'],
                'ville'     => $data['ville'],
                'id'        => $id,
            ]
        );
    }

    public static function updatePassword(int $id, string $password): void
    {
        Database::run(
            'UPDATE utilisateur SET password = :p WHERE utilisateur_id = :id',
            ['p' => password_hash($password, PASSWORD_DEFAULT), 'id' => $id]
        );
    }

    /** Met à jour le hachage si l'algorithme par défaut de PHP a évolué. */
    public static function rehashIfNeeded(array $user, string $password): void
    {
        if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
            self::updatePassword((int) $user['utilisateur_id'], $password);
        }
    }

    public static function employees(): array
    {
        return Database::fetchAll(
            self::SELECT . " WHERE r.libelle = 'employe' ORDER BY u.actif DESC, u.nom, u.prenom"
        );
    }

    public static function setActive(int $id, bool $active): void
    {
        // Seuls les comptes employés peuvent être désactivés par cette méthode
        Database::run(
            "UPDATE utilisateur u JOIN role r ON r.role_id = u.role_id
             SET u.actif = :actif
             WHERE u.utilisateur_id = :id AND r.libelle = 'employe'",
            ['actif' => $active ? 1 : 0, 'id' => $id]
        );
    }

    public static function customers(): array
    {
        return Database::fetchAll(
            self::SELECT . " WHERE r.libelle = 'utilisateur' ORDER BY u.nom, u.prenom"
        );
    }

    /**
     * Droit à l'effacement (RGPD) : les données personnelles sont anonymisées.
     * Les commandes sont conservées (obligation comptable) mais ne sont plus rattachées à une personne identifiable.
     */
    public static function anonymize(int $id): void
    {
        Database::run(
            "UPDATE utilisateur
             SET email = CONCAT('supprime-', utilisateur_id, '@anonyme.invalid'),
                 password = :pwd, nom = 'Compte', prenom = 'supprimé', telephone = NULL,
                 adresse_postale = NULL, code_postal = NULL, ville = NULL, actif = 0
             WHERE utilisateur_id = :id",
            ['pwd' => password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT), 'id' => $id]
        );
        Database::run('DELETE FROM password_reset WHERE utilisateur_id = :id', ['id' => $id]);
    }

    // --- Réinitialisation du mot de passe -------------------------------------------------

    /** Crée un jeton de réinitialisation ; seul son hachage SHA-256 est stocké en base. */
    public static function createResetToken(int $userId): string
    {
        $token = bin2hex(random_bytes(32));
        Database::run('DELETE FROM password_reset WHERE utilisateur_id = :id', ['id' => $userId]);
        Database::run(
            'INSERT INTO password_reset (utilisateur_id, token_hash, expires_at)
             VALUES (:id, :hash, DATE_ADD(NOW(), INTERVAL 1 HOUR))',
            ['id' => $userId, 'hash' => hash('sha256', $token)]
        );
        return $token;
    }

    public static function findValidReset(string $token): ?array
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            return null;
        }
        return Database::fetch(
            'SELECT * FROM password_reset
             WHERE token_hash = :hash AND used_at IS NULL AND expires_at > NOW()',
            ['hash' => hash('sha256', $token)]
        );
    }

    public static function consumeReset(int $resetId): void
    {
        Database::run('UPDATE password_reset SET used_at = NOW() WHERE reset_id = :id', ['id' => $resetId]);
    }
}
