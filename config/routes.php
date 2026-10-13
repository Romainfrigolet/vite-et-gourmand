<?php

/**
 * Table de routage de l'application.
 * Middlewares : guest (non connecté), auth (connecté), customer (rôle utilisateur),
 *               staff (employé ou administrateur), admin (administrateur uniquement).
 *
 * @var \App\Core\Router $router
 */

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\ContactController;
use App\Controllers\HomeController;
use App\Controllers\MenuController;
use App\Controllers\PageController;

// --- Pages publiques -----------------------------------------------------------------------
$router->get('/', [HomeController::class, 'index']);
$router->get('/menus', [MenuController::class, 'index']);
$router->get('/menus/{id}', [MenuController::class, 'show']);
$router->get('/api/menus', [MenuController::class, 'filter']);
$router->get('/contact', [ContactController::class, 'show']);
$router->post('/contact', [ContactController::class, 'send']);
$router->get('/mentions-legales', [PageController::class, 'mentions']);
$router->get('/cgv', [PageController::class, 'cgv']);
$router->get('/confidentialite', [PageController::class, 'confidentialite']);

// --- Authentification ----------------------------------------------------------------------
$router->get('/inscription', [AuthController::class, 'registerForm'], ['guest']);
$router->post('/inscription', [AuthController::class, 'register'], ['guest']);
$router->get('/connexion', [AuthController::class, 'loginForm'], ['guest']);
$router->post('/connexion', [AuthController::class, 'login'], ['guest']);
$router->post('/deconnexion', [AuthController::class, 'logout'], ['auth']);
$router->get('/mot-de-passe-oublie', [AuthController::class, 'forgotForm'], ['guest']);
$router->post('/mot-de-passe-oublie', [AuthController::class, 'forgot'], ['guest']);
$router->get('/reinitialiser-mot-de-passe', [AuthController::class, 'resetForm'], ['guest']);
$router->post('/reinitialiser-mot-de-passe', [AuthController::class, 'reset'], ['guest']);
