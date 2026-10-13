<?php

/**
 * Table de routage de l'application.
 * Middlewares : guest (non connecté), auth (connecté), customer (rôle utilisateur),
 *               staff (employé ou administrateur), admin (administrateur uniquement).
 *
 * @var \App\Core\Router $router
 */

declare(strict_types=1);

use App\Controllers\AccountController;
use App\Controllers\AuthController;
use App\Controllers\CommandeController;
use App\Controllers\ContactController;
use App\Controllers\Gestion;
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

// --- Commande (clients connectés) ----------------------------------------------------------
$router->get('/commander', [CommandeController::class, 'form'], ['auth', 'customer']);
$router->get('/commander/{id}', [CommandeController::class, 'form'], ['auth', 'customer']);
$router->post('/commander/recapitulatif', [CommandeController::class, 'recap'], ['auth', 'customer']);
$router->post('/commander/confirmer', [CommandeController::class, 'confirm'], ['auth', 'customer']);
$router->get('/api/livraison', [CommandeController::class, 'delivery'], ['auth']);

// --- Espace utilisateur --------------------------------------------------------------------
$router->get('/mon-compte', [AccountController::class, 'index'], ['auth', 'customer']);
$router->get('/mon-compte/informations', [AccountController::class, 'profileForm'], ['auth', 'customer']);
$router->post('/mon-compte/informations', [AccountController::class, 'updateProfile'], ['auth', 'customer']);
$router->post('/mon-compte/supprimer', [AccountController::class, 'delete'], ['auth', 'customer']);
$router->get('/mon-compte/commandes/{id}', [AccountController::class, 'show'], ['auth', 'customer']);
$router->get('/mon-compte/commandes/{id}/modifier', [AccountController::class, 'editForm'], ['auth', 'customer']);
$router->post('/mon-compte/commandes/{id}/modifier', [AccountController::class, 'update'], ['auth', 'customer']);
$router->post('/mon-compte/commandes/{id}/annuler', [AccountController::class, 'cancel'], ['auth', 'customer']);
$router->post('/mon-compte/commandes/{id}/avis', [AccountController::class, 'review'], ['auth', 'customer']);

// --- Espace employé (accessible aussi à l'administrateur) ----------------------------------
$router->get('/gestion', [Gestion\DashboardController::class, 'index'], ['auth', 'staff']);
$router->get('/gestion/commandes', [Gestion\CommandeController::class, 'index'], ['auth', 'staff']);
$router->get('/gestion/commandes/{id}', [Gestion\CommandeController::class, 'show'], ['auth', 'staff']);
$router->post('/gestion/commandes/{id}/statut', [Gestion\CommandeController::class, 'status'], ['auth', 'staff']);
$router->post('/gestion/commandes/{id}/materiel', [Gestion\CommandeController::class, 'material'], ['auth', 'staff']);
$router->get('/gestion/commandes/{id}/modifier', [Gestion\CommandeController::class, 'editForm'], ['auth', 'staff']);
$router->post('/gestion/commandes/{id}/modifier', [Gestion\CommandeController::class, 'update'], ['auth', 'staff']);
$router->post('/gestion/commandes/{id}/annuler', [Gestion\CommandeController::class, 'cancel'], ['auth', 'staff']);

$router->get('/gestion/menus', [Gestion\MenuController::class, 'index'], ['auth', 'staff']);
$router->get('/gestion/menus/nouveau', [Gestion\MenuController::class, 'create'], ['auth', 'staff']);
$router->post('/gestion/menus/nouveau', [Gestion\MenuController::class, 'store'], ['auth', 'staff']);
$router->get('/gestion/menus/{id}/modifier', [Gestion\MenuController::class, 'edit'], ['auth', 'staff']);
$router->post('/gestion/menus/{id}/modifier', [Gestion\MenuController::class, 'update'], ['auth', 'staff']);
$router->post('/gestion/menus/{id}/supprimer', [Gestion\MenuController::class, 'delete'], ['auth', 'staff']);
$router->post('/gestion/menus/{id}/images', [Gestion\MenuController::class, 'addImage'], ['auth', 'staff']);
$router->post('/gestion/images/{id}/supprimer', [Gestion\MenuController::class, 'deleteImage'], ['auth', 'staff']);

$router->get('/gestion/plats', [Gestion\PlatController::class, 'index'], ['auth', 'staff']);
$router->get('/gestion/plats/nouveau', [Gestion\PlatController::class, 'create'], ['auth', 'staff']);
$router->post('/gestion/plats/nouveau', [Gestion\PlatController::class, 'store'], ['auth', 'staff']);
$router->get('/gestion/plats/{id}/modifier', [Gestion\PlatController::class, 'edit'], ['auth', 'staff']);
$router->post('/gestion/plats/{id}/modifier', [Gestion\PlatController::class, 'update'], ['auth', 'staff']);
$router->post('/gestion/plats/{id}/supprimer', [Gestion\PlatController::class, 'delete'], ['auth', 'staff']);

$router->get('/gestion/horaires', [Gestion\HoraireController::class, 'edit'], ['auth', 'staff']);
$router->post('/gestion/horaires', [Gestion\HoraireController::class, 'update'], ['auth', 'staff']);

$router->get('/gestion/avis', [Gestion\AvisController::class, 'index'], ['auth', 'staff']);
$router->post('/gestion/avis/{id}/valider', [Gestion\AvisController::class, 'validate'], ['auth', 'staff']);
$router->post('/gestion/avis/{id}/refuser', [Gestion\AvisController::class, 'refuse'], ['auth', 'staff']);
