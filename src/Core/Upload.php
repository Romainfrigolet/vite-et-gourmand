<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Téléversement sécurisé des images de menus.
 * - Type MIME vérifié sur le contenu réel du fichier (finfo), pas sur l'extension
 * - Taille limitée à 2 Mo
 * - Nom de fichier régénéré aléatoirement (pas de nom fourni par l'utilisateur)
 * - Fichier stocké dans un dossier où PHP n'est pas exécuté
 */
final class Upload
{
    private const MAX_SIZE = 2 * 1024 * 1024;
    private const ALLOWED = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    /**
     * @return array{ok: bool, path?: string, error?: string}
     */
    public static function image(array $file): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'error' => "Le fichier n'a pas pu être envoyé."];
        }
        if ($file['size'] > self::MAX_SIZE) {
            return ['ok' => false, 'error' => "L'image ne doit pas dépasser 2 Mo."];
        }
        if (!is_uploaded_file($file['tmp_name'])) {
            return ['ok' => false, 'error' => 'Fichier invalide.'];
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!isset(self::ALLOWED[$mime]) || @getimagesize($file['tmp_name']) === false) {
            return ['ok' => false, 'error' => 'Seules les images JPEG, PNG ou WebP sont acceptées.'];
        }

        $relative = 'uploads/' . bin2hex(random_bytes(16)) . '.' . self::ALLOWED[$mime];
        $target = BASE_PATH . '/public/' . $relative;
        if (!is_dir(dirname($target))) {
            mkdir(dirname($target), 0775, true);
        }
        if (!move_uploaded_file($file['tmp_name'], $target)) {
            return ['ok' => false, 'error' => "L'image n'a pas pu être enregistrée."];
        }

        return ['ok' => true, 'path' => $relative];
    }
}
