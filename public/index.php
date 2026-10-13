<?php

/**
 * Contrôleur frontal : point d'entrée unique de l'application.
 * Seul le dossier public/ est exposé par le serveur web ;
 * le code source, la configuration et les fichiers .env restent inaccessibles.
 */

declare(strict_types=1);

use App\Core\Env;
use App\Core\Router;
use App\Core\Session;

define('BASE_PATH', dirname(__DIR__));

// Sert directement les fichiers statiques avec le serveur interne de PHP (développement)
if (PHP_SAPI === 'cli-server') {
    $file = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (is_file($file) && !str_ends_with($file, '.php')) {
        return false;
    }
}

require BASE_PATH . '/vendor/autoload.php';

Env::load(BASE_PATH . '/.env');
date_default_timezone_set('Europe/Paris');

$debug = Env::get('APP_DEBUG', 'false') === 'true';
ini_set('display_errors', $debug ? '1' : '0');
error_reporting(E_ALL);

// En-têtes de sécurité HTTP
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self'; script-src 'self'; "
    . "font-src 'self'; connect-src 'self'; frame-ancestors 'none'; form-action 'self'; base-uri 'self'; object-src 'none'");
if (is_https()) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}
header_remove('X-Powered-By');

// Aucune erreur technique n'est affichée à l'utilisateur en production
set_exception_handler(function (Throwable $e) use ($debug): void {
    log_error($e::class . ': ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    if ($debug) {
        http_response_code(500);
        echo '<pre>' . e((string) $e) . '</pre>';
        return;
    }
    abort(500);
});

Session::start();

$router = new Router();
require BASE_PATH . '/config/routes.php';
$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
