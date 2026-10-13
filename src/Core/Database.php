<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * Connexion unique (singleton) à la base relationnelle via PDO.
 * - Requêtes préparées natives (ATTR_EMULATE_PREPARES = false) contre l'injection SQL
 * - Exceptions activées pour ne jamais ignorer une erreur SQL
 */
final class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                Env::get('DB_HOST', '127.0.0.1'),
                Env::get('DB_PORT', '3306'),
                Env::get('DB_NAME', 'vite_gourmand')
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            // Connexion chiffrée (TLS) si l'hébergeur de la base l'exige (ex. Aiven)
            $ca = Env::get('DB_SSL_CA');
            if ($ca !== null && is_file($ca)) {
                $options[PDO::MYSQL_ATTR_SSL_CA] = $ca;
            }

            self::$pdo = new PDO($dsn, Env::get('DB_USER', 'root'), Env::get('DB_PASSWORD', ''), $options);
            // Aligne le fuseau horaire MySQL sur celui de PHP (Europe/Paris)
            self::$pdo->exec("SET time_zone = '" . (new \DateTime())->format('P') . "'");
        }

        return self::$pdo;
    }

    /** Exécute une requête préparée et retourne le PDOStatement. */
    public static function run(string $sql, array $params = []): \PDOStatement
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function fetch(string $sql, array $params = []): ?array
    {
        $row = self::run($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::run($sql, $params)->fetchAll();
    }

    public static function lastInsertId(): int
    {
        return (int) self::pdo()->lastInsertId();
    }

    public static function transaction(callable $callback): mixed
    {
        $pdo = self::pdo();
        $pdo->beginTransaction();
        try {
            $result = $callback($pdo);
            $pdo->commit();
            return $result;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
