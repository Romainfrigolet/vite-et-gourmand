<?php

declare(strict_types=1);

use App\Core\Csrf;
use App\Core\Env;
use App\Core\Session;
use App\Core\View;

/** Échappement HTML systématique des données affichées (protection XSS). */
function e(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = '/'): string
{
    return rtrim((string) Env::get('APP_URL', ''), '/') . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return '/' . ltrim($path, '/');
}

function redirect(string $path): never
{
    header('Location: ' . $path, true, 302);
    exit;
}

function back(string $fallback = '/'): never
{
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    // On ne redirige que vers notre propre domaine (pas de redirection ouverte)
    if ($referer !== '' && parse_url($referer, PHP_URL_HOST) === parse_url('http://' . $host, PHP_URL_HOST)) {
        $path = parse_url($referer, PHP_URL_PATH) ?? $fallback;
        $query = parse_url($referer, PHP_URL_QUERY);
        redirect($path . ($query ? '?' . $query : ''));
    }
    redirect($fallback);
}

function view(string $template, array $data = [], ?string $layout = 'layouts/main'): never
{
    echo View::render($template, $data, $layout);
    exit;
}

function json_response(mixed $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function abort(int $code, ?string $message = null): never
{
    http_response_code($code);
    $wantsJson = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
    $messages = [
        403 => "Vous n'avez pas accès à cette page.",
        404 => "La page demandée n'existe pas.",
        405 => 'Méthode non autorisée.',
        419 => 'Votre session a expiré, veuillez réessayer.',
        500 => 'Une erreur interne est survenue. Veuillez réessayer plus tard.',
    ];
    $message ??= $messages[$code] ?? 'Erreur';
    if ($wantsJson) {
        json_response(['error' => $message], $code);
    }
    echo View::render('errors/error', ['code' => $code, 'message' => $message, 'title' => 'Erreur ' . $code]);
    exit;
}

function csrf_token(): string
{
    return Csrf::token();
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(Csrf::token()) . '">';
}

/** Saisies du formulaire précédent (réaffichées après une erreur de validation). */
function old_input(): array
{
    static $old = null;
    $old ??= Session::pullOld();
    return $old;
}

function has_old(): bool
{
    return old_input() !== [];
}

function old(string $key, mixed $default = ''): string
{
    $value = old_input()[$key] ?? $default;
    return is_array($value) ? '' : (string) $value;
}

/** Valeurs multiples (cases à cocher) : anciennes saisies si présentes, sinon valeurs par défaut. */
function old_array(string $key, array $default = []): array
{
    return has_old() ? array_map('intval', (array) (old_input()[$key] ?? [])) : $default;
}

function errors(): array
{
    static $errors = null;
    $errors ??= Session::pullErrors();
    return $errors;
}

function error_for(string $field): ?string
{
    return errors()[$field] ?? null;
}

/** Attributs d'accessibilité et classe Bootstrap pour un champ en erreur. */
function invalid(string $field): string
{
    return error_for($field) ? ' is-invalid" aria-invalid="true" aria-describedby="' . e($field) . '-error' : '';
}

function field_error(string $field): string
{
    $msg = error_for($field);
    return $msg ? '<div id="' . e($field) . '-error" class="invalid-feedback d-block">' . e($msg) . '</div>' : '';
}

function price(float|string|null $amount): string
{
    return number_format((float) $amount, 2, ',', ' ') . ' €';
}

function date_fr(?string $date, bool $withTime = false): string
{
    if (!$date) {
        return '';
    }
    $ts = strtotime($date);
    return $withTime ? date('d/m/Y à H\hi', $ts) : date('d/m/Y', $ts);
}

function time_fr(?string $time): string
{
    return $time ? str_replace(':', 'h', substr($time, 0, 5)) : '';
}

function input(string $key, mixed $default = null): mixed
{
    $value = $_POST[$key] ?? $_GET[$key] ?? $default;
    return is_string($value) ? trim($value) : $value;
}

function client_ip(): string
{
    return substr((string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'), 0, 45);
}

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
}

function log_error(string $message): void
{
    $dir = BASE_PATH . '/storage/logs';
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    @file_put_contents($dir . '/app.log', '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL, FILE_APPEND);
    error_log($message);
}

function is_current(string $prefix): bool
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    return $prefix === '/' ? $path === '/' : str_starts_with($path, $prefix);
}

function nav_attr(string $prefix): string
{
    return is_current($prefix) ? ' active" aria-current="page' : '';
}

function normalize_phone(?string $phone): ?string
{
    $phone = preg_replace('/[\s.\-]/', '', (string) $phone);
    return $phone === '' ? null : $phone;
}

function phone_fr(?string $phone): string
{
    $p = (string) $phone;
    return preg_match('/^0\d{9}$/', $p) ? trim(chunk_split($p, 2, ' ')) : $p;
}
