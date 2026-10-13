<?php

/**
 * Table de routage de l'application.
 * Middlewares : guest (non connecté), auth (connecté), customer (rôle utilisateur),
 *               staff (employé ou administrateur), admin (administrateur uniquement).
 *
 * @var \App\Core\Router $router
 */

declare(strict_types=1);

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
