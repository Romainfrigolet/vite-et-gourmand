<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Chargeur minimal de fichier .env (aucune dépendance externe).
 * Les variables déjà définies par l'environnement (ex. hébergeur) sont prioritaires.
 */
final class Env
{
    public static function load(string $path): void
    {
        if (!is_file($path) || !is_readable($path)) {
            return;
        }

        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = array_map('trim', explode('=', $line, 2));
            // Retire les guillemets entourant la valeur
            if (preg_match('/^(["\'])(.*)\1$/', $value, $m)) {
                $value = $m[2];
            } else {
                // Retire un commentaire en fin de ligne (« valeur   # commentaire »)
                $value = trim((string) preg_replace('/\s+#.*$/', '', $value));
            }
            if (getenv($key) === false) {
                putenv("$key=$value");
                $_ENV[$key] = $value;
            }
        }
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $value = $_ENV[$key] ?? getenv($key);
        return ($value === false || $value === null || $value === '') ? $default : (string) $value;
    }
}
