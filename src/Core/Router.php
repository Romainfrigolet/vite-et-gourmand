<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Routeur minimal : associe une méthode HTTP + un chemin à un contrôleur.
 * Les paramètres dynamiques s'écrivent {id} (uniquement des chiffres).
 * Chaque route peut déclarer des middlewares d'accès : guest, auth, customer, staff, admin.
 * Toute requête POST est soumise à la vérification du jeton CSRF.
 */
final class Router
{
    private array $routes = [];

    public function get(string $path, array $handler, array $middlewares = []): void
    {
        $this->add('GET', $path, $handler, $middlewares);
    }

    public function post(string $path, array $handler, array $middlewares = []): void
    {
        $this->add('POST', $path, $handler, $middlewares);
    }

    private function add(string $method, string $path, array $handler, array $middlewares): void
    {
        $pattern = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>\d+)', rtrim($path, '/') ?: '/') . '$#';
        $this->routes[] = compact('method', 'pattern', 'handler', 'middlewares');
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = rawurldecode(parse_url($uri, PHP_URL_PATH) ?? '/');
        $path = rtrim($path, '/') ?: '/';

        $methodAllowed = false;
        foreach ($this->routes as $route) {
            if (!preg_match($route['pattern'], $path, $matches)) {
                continue;
            }
            if ($route['method'] !== $method) {
                $methodAllowed = true;
                continue;
            }

            if ($method === 'POST' && !Csrf::verify($_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
                abort(419, 'Votre session a expiré, veuillez recharger la page et réessayer.');
            }

            foreach ($route['middlewares'] as $middleware) {
                $this->applyMiddleware($middleware);
            }

            $params = array_map('intval', array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY));
            [$class, $action] = $route['handler'];
            (new $class())->$action(...$params);
            return;
        }

        abort($methodAllowed ? 405 : 404);
    }

    private function applyMiddleware(string $middleware): void
    {
        switch ($middleware) {
            case 'guest':
                if (Auth::check()) {
                    redirect('/');
                }
                break;
            case 'auth':
                if (!Auth::check()) {
                    Session::set('_intended', $_SERVER['REQUEST_URI'] ?? '/');
                    Session::flash('info', 'Veuillez vous connecter ou créer un compte pour continuer.');
                    redirect('/connexion');
                }
                break;
            case 'customer':
                if (!Auth::isCustomer()) {
                    abort(403);
                }
                break;
            case 'staff':
                if (!Auth::isStaff()) {
                    abort(403);
                }
                break;
            case 'admin':
                if (!Auth::isAdmin()) {
                    abort(403);
                }
                break;
        }
    }
}
