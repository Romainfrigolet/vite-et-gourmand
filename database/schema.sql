-- =====================================================================
--  Vite & Gourmand — Création de la base de données relationnelle
--  SGBD cible : MySQL 8 / MariaDB 10.6+ (moteur InnoDB, utf8mb4)
--  Écrit à la main (pas d'ORM, pas de migration générée).
--
--  Utilisation :
--    mysql -u root -p < database/schema.sql
--    mysql -u root -p vite_gourmand < database/data.sql
-- =====================================================================

CREATE DATABASE IF NOT EXISTS vite_gourmand
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE vite_gourmand;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS login_attempt;
DROP TABLE IF EXISTS contact_message;
DROP TABLE IF EXISTS avis;
DROP TABLE IF EXISTS commande_historique;
DROP TABLE IF EXISTS commande;
DROP TABLE IF EXISTS plat_allergene;
DROP TABLE IF EXISTS menu_plat;
DROP TABLE IF EXISTS menu_image;
DROP TABLE IF EXISTS plat;
DROP TABLE IF EXISTS menu;
DROP TABLE IF EXISTS allergene;
DROP TABLE IF EXISTS regime;
DROP TABLE IF EXISTS theme;
DROP TABLE IF EXISTS horaire;
DROP TABLE IF EXISTS password_reset;
DROP TABLE IF EXISTS utilisateur;
DROP TABLE IF EXISTS role;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- Rôles : utilisateur, employe, administrateur
-- ---------------------------------------------------------------------
CREATE TABLE role (
    role_id   INT UNSIGNED NOT NULL AUTO_INCREMENT,
    libelle   VARCHAR(50)  NOT NULL,
    PRIMARY KEY (role_id),
    UNIQUE KEY uq_role_libelle (libelle)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Utilisateurs (clients, employés, administrateur)
-- Le mot de passe est stocké haché (password_hash / bcrypt), jamais en clair.
-- ---------------------------------------------------------------------
CREATE TABLE utilisateur (
    utilisateur_id  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    email           VARCHAR(180) NOT NULL,
    password        VARCHAR(255) NOT NULL,
    nom             VARCHAR(80)  NOT NULL,
    prenom          VARCHAR(80)  NOT NULL,
    telephone       VARCHAR(20)  NULL,
    adresse_postale VARCHAR(255) NULL,
    code_postal     VARCHAR(10)  NULL,
    ville           VARCHAR(100) NULL,
    pays            VARCHAR(60)  NOT NULL DEFAULT 'France',
    role_id         INT UNSIGNED NOT NULL,
    actif           TINYINT(1)   NOT NULL DEFAULT 1,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (utilisateur_id),
    UNIQUE KEY uq_utilisateur_email (email),
    KEY idx_utilisateur_role (role_id),
    CONSTRAINT fk_utilisateur_role FOREIGN KEY (role_id) REFERENCES role (role_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Jetons de réinitialisation de mot de passe (stockés hachés, durée limitée)
-- ---------------------------------------------------------------------
CREATE TABLE password_reset (
    reset_id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    utilisateur_id  INT UNSIGNED NOT NULL,
    token_hash      CHAR(64)     NOT NULL,
    expires_at      DATETIME     NOT NULL,
    used_at         DATETIME     NULL,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (reset_id),
    UNIQUE KEY uq_reset_token (token_hash),
    CONSTRAINT fk_reset_utilisateur FOREIGN KEY (utilisateur_id)
        REFERENCES utilisateur (utilisateur_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Référentiels
-- ---------------------------------------------------------------------
CREATE TABLE theme (
    theme_id  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    libelle   VARCHAR(50)  NOT NULL,
    PRIMARY KEY (theme_id),
    UNIQUE KEY uq_theme_libelle (libelle)
) ENGINE=InnoDB;

CREATE TABLE regime (
    regime_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    libelle   VARCHAR(50)  NOT NULL,
    PRIMARY KEY (regime_id),
    UNIQUE KEY uq_regime_libelle (libelle)
) ENGINE=InnoDB;

CREATE TABLE allergene (
    allergene_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    libelle      VARCHAR(60)  NOT NULL,
    PRIMARY KEY (allergene_id),
    UNIQUE KEY uq_allergene_libelle (libelle)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Menus
-- prix_par_personne × nombre_personne_minimum = prix affiché du menu
-- quantite_restante = stock de commandes encore possibles
-- delai_commande_jours = nombre de jours minimum entre la commande et la prestation
-- ---------------------------------------------------------------------
CREATE TABLE menu (
    menu_id                 INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    titre                   VARCHAR(120)  NOT NULL,
    description             TEXT          NOT NULL,
    nombre_personne_minimum INT UNSIGNED  NOT NULL,
    prix_par_personne       DECIMAL(8,2)  NOT NULL,
    conditions              TEXT          NULL,
    delai_commande_jours    INT UNSIGNED  NOT NULL DEFAULT 3,
    quantite_restante       INT UNSIGNED  NOT NULL DEFAULT 0,
    theme_id                INT UNSIGNED  NOT NULL,
    regime_id               INT UNSIGNED  NOT NULL,
    actif                   TINYINT(1)    NOT NULL DEFAULT 1,
    created_at              DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (menu_id),
    KEY idx_menu_theme (theme_id),
    KEY idx_menu_regime (regime_id),
    CONSTRAINT fk_menu_theme  FOREIGN KEY (theme_id)  REFERENCES theme (theme_id),
    CONSTRAINT fk_menu_regime FOREIGN KEY (regime_id) REFERENCES regime (regime_id),
    CONSTRAINT chk_menu_min   CHECK (nombre_personne_minimum >= 1),
    CONSTRAINT chk_menu_prix  CHECK (prix_par_personne >= 0)
) ENGINE=InnoDB;

-- Galerie d'images d'un menu
CREATE TABLE menu_image (
    image_id   INT UNSIGNED NOT NULL AUTO_INCREMENT,
    menu_id    INT UNSIGNED NOT NULL,
    chemin     VARCHAR(255) NOT NULL,
    alt        VARCHAR(255) NOT NULL,
    position   INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (image_id),
    KEY idx_image_menu (menu_id),
    CONSTRAINT fk_image_menu FOREIGN KEY (menu_id) REFERENCES menu (menu_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Plats (entrée, plat, dessert) — un plat peut appartenir à plusieurs menus
-- ---------------------------------------------------------------------
CREATE TABLE plat (
    plat_id     INT UNSIGNED NOT NULL AUTO_INCREMENT,
    titre_plat  VARCHAR(120) NOT NULL,
    type_plat   ENUM('entree','plat','dessert') NOT NULL,
    description VARCHAR(500) NULL,
    PRIMARY KEY (plat_id),
    KEY idx_plat_type (type_plat)
) ENGINE=InnoDB;

-- Association menu <-> plat (n,n)
CREATE TABLE menu_plat (
    menu_id INT UNSIGNED NOT NULL,
    plat_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (menu_id, plat_id),
    KEY idx_menu_plat_plat (plat_id),
    CONSTRAINT fk_mp_menu FOREIGN KEY (menu_id) REFERENCES menu (menu_id) ON DELETE CASCADE,
    CONSTRAINT fk_mp_plat FOREIGN KEY (plat_id) REFERENCES plat (plat_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Association plat <-> allergène (n,n)
CREATE TABLE plat_allergene (
    plat_id      INT UNSIGNED NOT NULL,
    allergene_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (plat_id, allergene_id),
    KEY idx_pa_allergene (allergene_id),
    CONSTRAINT fk_pa_plat      FOREIGN KEY (plat_id)      REFERENCES plat (plat_id) ON DELETE CASCADE,
    CONSTRAINT fk_pa_allergene FOREIGN KEY (allergene_id) REFERENCES allergene (allergene_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Commandes
-- Les prix sont figés au moment de la commande (historique fiable).
-- ---------------------------------------------------------------------
CREATE TABLE commande (
    commande_id             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    numero_commande         VARCHAR(20)   NOT NULL,
    utilisateur_id          INT UNSIGNED  NOT NULL,
    menu_id                 INT UNSIGNED  NOT NULL,
    date_commande           DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_prestation         DATE          NOT NULL,
    heure_livraison         TIME          NOT NULL,
    adresse_prestation      VARCHAR(255)  NOT NULL,
    code_postal_prestation  VARCHAR(10)   NOT NULL,
    ville_prestation        VARCHAR(100)  NOT NULL,
    distance_km             DECIMAL(6,1)  NOT NULL DEFAULT 0,
    nombre_personne         INT UNSIGNED  NOT NULL,
    prix_menu               DECIMAL(10,2) NOT NULL,
    remise                  DECIMAL(10,2) NOT NULL DEFAULT 0,
    prix_livraison          DECIMAL(10,2) NOT NULL DEFAULT 0,
    prix_total              DECIMAL(10,2) NOT NULL,
    statut                  ENUM('en_attente','accepte','en_preparation','en_livraison','livre',
                                 'attente_retour_materiel','terminee','annulee')
                            NOT NULL DEFAULT 'en_attente',
    pret_materiel           TINYINT(1)    NOT NULL DEFAULT 0,
    restitution_materiel    TINYINT(1)    NOT NULL DEFAULT 0,
    motif_annulation        VARCHAR(500)  NULL,
    mode_contact_annulation ENUM('telephone','email') NULL,
    updated_at              DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (commande_id),
    UNIQUE KEY uq_commande_numero (numero_commande),
    KEY idx_commande_utilisateur (utilisateur_id),
    KEY idx_commande_menu (menu_id),
    KEY idx_commande_statut (statut),
    CONSTRAINT fk_commande_utilisateur FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (utilisateur_id),
    CONSTRAINT fk_commande_menu        FOREIGN KEY (menu_id)        REFERENCES menu (menu_id),
    CONSTRAINT chk_commande_personnes  CHECK (nombre_personne >= 1)
) ENGINE=InnoDB;

-- Historique des changements de statut (suivi de commande)
CREATE TABLE commande_historique (
    historique_id   INT UNSIGNED NOT NULL AUTO_INCREMENT,
    commande_id     INT UNSIGNED NOT NULL,
    statut          VARCHAR(30)  NOT NULL,
    commentaire     VARCHAR(500) NULL,
    modifie_par     INT UNSIGNED NULL,
    date_modification DATETIME   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (historique_id),
    KEY idx_historique_commande (commande_id),
    CONSTRAINT fk_historique_commande FOREIGN KEY (commande_id) REFERENCES commande (commande_id) ON DELETE CASCADE,
    CONSTRAINT fk_historique_auteur   FOREIGN KEY (modifie_par) REFERENCES utilisateur (utilisateur_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Avis clients (un avis par commande terminée, validé par un employé)
-- ---------------------------------------------------------------------
CREATE TABLE avis (
    avis_id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    commande_id    INT UNSIGNED NOT NULL,
    utilisateur_id INT UNSIGNED NOT NULL,
    note           TINYINT UNSIGNED NOT NULL,
    commentaire    VARCHAR(1000) NOT NULL,
    statut         ENUM('en_attente','valide','refuse') NOT NULL DEFAULT 'en_attente',
    created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (avis_id),
    UNIQUE KEY uq_avis_commande (commande_id),
    KEY idx_avis_statut (statut),
    CONSTRAINT fk_avis_commande    FOREIGN KEY (commande_id)    REFERENCES commande (commande_id) ON DELETE CASCADE,
    CONSTRAINT fk_avis_utilisateur FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (utilisateur_id),
    CONSTRAINT chk_avis_note CHECK (note BETWEEN 1 AND 5)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Horaires d'ouverture (affichés dans le pied de page)
-- ---------------------------------------------------------------------
CREATE TABLE horaire (
    horaire_id      INT UNSIGNED NOT NULL AUTO_INCREMENT,
    jour            ENUM('Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche') NOT NULL,
    heure_ouverture TIME NULL,
    heure_fermeture TIME NULL,
    ferme           TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (horaire_id),
    UNIQUE KEY uq_horaire_jour (jour)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Messages du formulaire de contact (traçabilité, en plus de l'envoi par mail)
-- ---------------------------------------------------------------------
CREATE TABLE contact_message (
    message_id  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    titre       VARCHAR(150) NOT NULL,
    description TEXT         NOT NULL,
    email       VARCHAR(180) NOT NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (message_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Tentatives de connexion (protection contre la force brute)
-- ---------------------------------------------------------------------
CREATE TABLE login_attempt (
    attempt_id   INT UNSIGNED NOT NULL AUTO_INCREMENT,
    email        VARCHAR(180) NOT NULL,
    ip_address   VARCHAR(45)  NOT NULL,
    attempted_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (attempt_id),
    KEY idx_attempt_email_date (email, attempted_at),
    KEY idx_attempt_ip_date (ip_address, attempted_at)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Utilisateur applicatif à privilèges restreints (principe du moindre privilège)
-- À adapter en production : mot de passe fort, hôte restreint.
-- ---------------------------------------------------------------------
-- CREATE USER IF NOT EXISTS 'vg_app'@'%' IDENTIFIED BY 'change-me';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON vite_gourmand.* TO 'vg_app'@'%';
-- FLUSH PRIVILEGES;
