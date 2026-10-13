<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Moteur de vues PHP natif.
 * Toutes les données dynamiques doivent être échappées avec e() dans les gabarits (anti-XSS).
 */
final class View
{
    public static function render(string $template, array $data = [], ?string $layout = 'layouts/main'): string
    {
        $content = self::capture($template, $data);
        if ($layout === null) {
            return $content;
        }
        return self::capture($layout, array_merge($data, ['content' => $content]));
    }

    public static function capture(string $template, array $data = []): string
    {
        $file = BASE_PATH . '/views/' . $template . '.php';
        if (!is_file($file)) {
            throw new \RuntimeException("Vue introuvable : $template");
        }
        extract($data, EXTR_SKIP);
        ob_start();
        require $file;
        return (string) ob_get_clean();
    }
}
