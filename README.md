# Vite & Gourmand

Application web du traiteur bordelais **Vite & Gourmand**. Elle permet de présenter les menus, de les commander en ligne et de gérer l'activité (commandes, menus, avis, employés, statistiques).

> Projet réalisé dans le cadre de l'ECF du titre professionnel **Développeur Web et Web Mobile** (Studi).

| | |
|---|---|
| **Application en ligne** | *à compléter après déploiement* |
| **Gestion de projet** | *lien du tableau Kanban à compléter* |
| **Documentation** | dossier [`docs/`](docs/) : manuel d'utilisation, charte graphique, documentation technique, gestion de projet |

---

## Sommaire

1. [Fonctionnalités](#fonctionnalités)
2. [Stack technique](#stack-technique)
3. [Installation en local avec Docker (recommandé)](#1-installation-en-local-avec-docker-recommandé)
4. [Installation en local sans Docker](#2-installation-en-local-sans-docker)
5. [Comptes de démonstration](#comptes-de-démonstration)
6. [Structure du projet](#structure-du-projet)
7. [Base de données](#base-de-données)
8. [Sécurité](#sécurité)
9. [Déploiement](#déploiement)
10. [Workflow Git](#workflow-git)

---

## Fonctionnalités

| Profil | Fonctionnalités |
|---|---|
| **Visiteur** | Accueil (présentation, équipe, avis validés), vue globale des menus avec **filtres dynamiques sans rechargement** (prix max., fourchette de prix, thème, régime, nombre de personnes), fiche détaillée d'un menu (galerie, plats, allergènes, conditions), contact, création de compte, mentions légales / CGV / confidentialité |
| **Utilisateur** | Connexion, mot de passe oublié (lien par e-mail), commande d'un menu (calcul de la livraison et de la remise, récapitulatif détaillé), suivi de commande, modification / annulation tant que la commande n'est pas acceptée, avis (note 1 à 5 + commentaire), modification de ses informations, suppression du compte (RGPD) |
| **Employé** | Gestion des commandes (filtres par statut / client, changement de statut, prêt de matériel, modification ou annulation après contact du client), gestion des menus (galerie d'images), des plats (allergènes), des horaires, modération des avis |
| **Administrateur** | Tout ce que fait l'employé + création / désactivation des comptes employés + statistiques (graphique des commandes par menu et chiffre d'affaires filtrable par menu et période, **données issues de MongoDB**) |

E-mails automatiques : bienvenue, réinitialisation du mot de passe, confirmation de commande, retour de matériel (600 € de frais), invitation à laisser un avis, modification / annulation par l'équipe, création d'un compte employé, message de contact.

## Stack technique

| Couche | Technologie |
|---|---|
| Front-end | HTML5, CSS3, **Bootstrap 5.3**, JavaScript natif (fetch), Chart.js 4 |
| Back-end | **PHP 8.3** sans framework (architecture MVC maison), **PDO** |
| Base relationnelle | **MySQL 8** / MariaDB 10.6+ |
| Base NoSQL | **MongoDB 7** (extension `ext-mongodb`) |
| E-mails | PHPMailer (SMTP) |
| Environnement | Docker / Docker Compose, Composer, Git |
| Déploiement | Render (Docker) + Aiven (MySQL) + MongoDB Atlas + Brevo (SMTP) |

---

## 1. Installation en local avec Docker (recommandé)

**Prérequis :** [Docker Desktop](https://www.docker.com/products/docker-desktop/) et Git.

```bash
git clone https://github.com/Romainfrigolet/vite-et-gourmand.git
cd vite-et-gourmand
docker compose up -d --build
```

C'est tout : au premier démarrage, MySQL exécute automatiquement `database/schema.sql` puis `database/data.sql`, et le conteneur de l'application synchronise les statistiques dans MongoDB.

| Service | Adresse |
|---|---|
| Application | http://localhost:8080 |
| Boîte mail de test (Mailpit) | http://localhost:8025 |
| MySQL | `localhost:3307` (utilisateur `vg_app` / `vg_app_password`) |
| MongoDB | `mongodb://localhost:27018` |

Commandes utiles :

```bash
docker compose logs -f app                           # journaux de l'application
docker compose exec app php bin/sync-stats.php       # reconstruire les statistiques MongoDB
docker compose down -v                               # tout arrêter ET réinitialiser les données
```

## 2. Installation en local sans Docker

**Prérequis :** PHP ≥ 8.2 avec les extensions `pdo_mysql`, `mongodb`, `mbstring`, `fileinfo` ; Composer ; MySQL 8 (ou MariaDB) ; MongoDB 7.

```bash
# 1. Récupérer le projet et ses dépendances
git clone https://github.com/Romainfrigolet/vite-et-gourmand.git
cd vite-et-gourmand
composer install

# 2. Configurer l'environnement
cp .env.example .env          # puis renseigner DB_*, MONGO_* et MAIL_*

# 3. Créer la base relationnelle et insérer les données
mysql -u root -p < database/schema.sql
mysql -u root -p vite_gourmand < database/data.sql
#    (alternative sans client mysql : php bin/install-db.php)

# 4. Créer un utilisateur MySQL à privilèges restreints (recommandé)
mysql -u root -p -e "CREATE USER 'vg_app'@'localhost' IDENTIFIED BY 'mot-de-passe-fort';
                     GRANT SELECT, INSERT, UPDATE, DELETE ON vite_gourmand.* TO 'vg_app'@'localhost';"

# 5. Alimenter MongoDB à partir des commandes MySQL
php bin/sync-stats.php

# 6. Lancer le serveur de développement
php -S localhost:8000 -t public public/index.php
```

Ouvrir http://localhost:8000. Avec `MAIL_DRIVER=log`, les e-mails ne sont pas envoyés mais enregistrés en HTML dans `storage/mails/`.

## Comptes de démonstration

| Rôle | Identifiant | Mot de passe |
|---|---|---|
| Administrateur (José) | `jose@viteetgourmand.fr` | `Jose#Admin2026` |
| Employée (Julie) | `julie@viteetgourmand.fr` | `Julie#Employe2026` |
| Utilisateur | `client@exemple.fr` | `Client#Demo2026` |
| Utilisateur | `marie.durand@exemple.fr` | `Client#Demo2026` |

> Le compte administrateur est créé directement en base (`data.sql`) : il est **impossible de créer un administrateur depuis l'application**. En production, changez ces mots de passe.

## Structure du projet

```
├── bin/                  Scripts en ligne de commande (install-db, sync-stats)
├── config/routes.php     Table de routage (URL → contrôleur + droits d'accès)
├── database/             schema.sql (création) et data.sql (données), écrits à la main
├── docker/               Configuration Apache / PHP et script de démarrage du conteneur
├── docs/                 Documentations PDF du projet
├── public/               SEUL dossier exposé par le serveur web
│   ├── index.php         Contrôleur frontal (point d'entrée unique, en-têtes de sécurité)
│   ├── assets/           CSS, JavaScript, polices, images, librairies (Bootstrap, Chart.js)
│   └── uploads/          Images téléversées (exécution de PHP interdite)
├── src/
│   ├── Core/             Socle : routeur, PDO, MongoDB, session, CSRF, validation, mailer…
│   ├── Controllers/      Contrôleurs (public, compte, commande, Gestion/, Admin/)
│   ├── Repositories/     Accès aux données (requêtes SQL préparées, agrégations MongoDB)
│   ├── Services/         Règles métier (prix, livraison, cycle de vie des commandes)
│   └── helpers.php       Fonctions utilitaires (échappement e(), formatage…)
├── storage/              Journaux et e-mails de développement (non versionnés)
├── views/                Gabarits PHP (layouts, pages, e-mails)
├── Dockerfile
└── docker-compose.yml
```

## Base de données

- **MySQL** (source de vérité) : 17 tables — `role`, `utilisateur`, `password_reset`, `theme`, `regime`, `allergene`, `menu`, `menu_image`, `plat`, `menu_plat`, `plat_allergene`, `commande`, `commande_historique`, `avis`, `horaire`, `contact_message`, `login_attempt`. Le modèle reprend le MCD de l'énoncé en l'enrichissant (historique des statuts, galerie d'images, jetons de réinitialisation, anti-force brute).
- **MongoDB** : collection `commandes_stats`. Chaque commande créée ou modifiée y est copiée (document dénormalisé). Les statistiques de l'administrateur sont calculées par un pipeline d'agrégation MongoDB (`$match` → `$group` → `$sort`).

## Sécurité

- Requêtes **préparées PDO** partout (injection SQL), émulation désactivée
- Échappement systématique des sorties avec `htmlspecialchars` (XSS) + **Content-Security-Policy** stricte (aucun script inline)
- Jeton **CSRF** sur tous les formulaires POST
- Mots de passe hachés avec `password_hash` (bcrypt) ; politique : 10 caractères, majuscule, minuscule, chiffre, caractère spécial
- Session : cookie `HttpOnly`, `SameSite=Lax`, `Secure` en HTTPS, régénération de l'identifiant à la connexion, expiration après 2 h
- **Limitation des tentatives de connexion** (5 échecs / 15 min), messages d'erreur génériques (pas d'énumération des comptes)
- Jeton de réinitialisation aléatoire, **stocké haché**, valable 1 h, à usage unique
- Contrôle d'accès par rôle sur chaque route + vérification de propriété des commandes (IDOR)
- Upload : type MIME réel vérifié, 2 Mo max., nom aléatoire, PHP désactivé dans `uploads/`
- En-têtes HTTP : `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `HSTS`
- Secrets dans `.env` (non versionné), erreurs techniques jamais affichées en production
- Utilisateur MySQL applicatif à privilèges restreints

## Déploiement

Le déploiement complet (Render + Aiven + MongoDB Atlas + Brevo) est décrit pas à pas dans la **documentation technique** (`docs/documentation-technique.pdf`, partie « Déploiement »). En résumé :

1. Créer une base MySQL (Aiven) et y importer les données : `php bin/install-db.php` avec le `.env` pointant vers Aiven.
2. Créer un cluster MongoDB Atlas gratuit (M0) et récupérer l'URI `mongodb+srv://…`.
3. Créer un compte Brevo et récupérer les identifiants SMTP.
4. Sur Render : *New → Web Service → dépôt GitHub*, environnement **Docker**, puis renseigner les variables d'environnement (`APP_ENV=production`, `APP_DEBUG=false`, `APP_URL`, `DB_*`, `DB_SSL_CA`, `MONGO_*`, `MAIL_*`).
5. Chaque `git push` sur `main` redéploie automatiquement l'application.

## Workflow Git

- `main` : version stable, déployée
- `develop` : intégration
- `feature/*` (et `fix/*`) : une branche par fonctionnalité, créée depuis `develop`, fusionnée dans `develop` après tests (`--no-ff`)
- `develop` est fusionnée dans `main` une fois testée
