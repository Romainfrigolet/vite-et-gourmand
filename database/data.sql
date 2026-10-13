-- =====================================================================
--  Vite & Gourmand — Jeu de données initial
--  À exécuter après schema.sql :  mysql -u root -p vite_gourmand < database/data.sql
--
--  Comptes de démonstration (mots de passe conformes à la politique :
--  10 caractères min., majuscule, minuscule, chiffre, caractère spécial) :
--    Administrateur : jose@viteetgourmand.fr   / Jose#Admin2026
--    Employée       : julie@viteetgourmand.fr  / Julie#Employe2026
--    Utilisateur    : client@exemple.fr        / Client#Demo2026
--    Utilisateur    : marie.durand@exemple.fr  / Client#Demo2026
--  Les mots de passe sont stockés hachés (bcrypt, coût 12).
-- =====================================================================

USE vite_gourmand;

SET NAMES utf8mb4;
START TRANSACTION;

-- ---------------------------------------------------------------------
-- Rôles
-- ---------------------------------------------------------------------
INSERT INTO role (role_id, libelle) VALUES
    (1, 'utilisateur'),
    (2, 'employe'),
    (3, 'administrateur');

-- ---------------------------------------------------------------------
-- Utilisateurs
-- Le compte administrateur de José est créé ici : il est impossible
-- de créer un compte administrateur depuis l'application.
-- ---------------------------------------------------------------------
INSERT INTO utilisateur (utilisateur_id, email, password, nom, prenom, telephone, adresse_postale, code_postal, ville, role_id, actif) VALUES
    (1, 'jose@viteetgourmand.fr',  '$2y$12$7OT.DGPWPlkvmzDBgYHk1.k4z7oDdlXevsCNr6FrYON60LYUJ2ULW', 'Martinez', 'José',  '0556000001', '12 rue Sainte-Catherine', '33000', 'Bordeaux', 3, 1),
    (2, 'julie@viteetgourmand.fr', '$2y$12$v3cGcqXFJ2cR54c9Vlc0bunRu/lExsTi53.DkJ.jZRcN5FnpDnlL2', 'Laurent',  'Julie', '0556000002', '12 rue Sainte-Catherine', '33000', 'Bordeaux', 2, 1),
    (3, 'client@exemple.fr',       '$2y$12$AuE5S5oSDVkU2xD1SMSB0Ot3wcAqDtG7nLBUDSLU.RkZ0X1xB2a0.', 'Bernard',  'Lucas', '0612345678', '8 cours de l''Intendance', '33000', 'Bordeaux', 1, 1),
    (4, 'marie.durand@exemple.fr', '$2y$12$AuE5S5oSDVkU2xD1SMSB0Ot3wcAqDtG7nLBUDSLU.RkZ0X1xB2a0.', 'Durand',   'Marie', '0698765432', '3 allée des Vignes',       '33600', 'Pessac',   1, 1),
    (5, 'thomas.ancien@viteetgourmand.fr', '$2y$12$v3cGcqXFJ2cR54c9Vlc0bunRu/lExsTi53.DkJ.jZRcN5FnpDnlL2', 'Petit', 'Thomas', '0556000005', NULL, NULL, NULL, 2, 0);

-- ---------------------------------------------------------------------
-- Référentiels
-- ---------------------------------------------------------------------
INSERT INTO theme (theme_id, libelle) VALUES
    (1, 'Noël'), (2, 'Pâques'), (3, 'Classique'), (4, 'Évènement');

INSERT INTO regime (regime_id, libelle) VALUES
    (1, 'Classique'), (2, 'Végétarien'), (3, 'Vegan'), (4, 'Sans gluten');

-- Les 14 allergènes à déclaration obligatoire (règlement UE n°1169/2011)
INSERT INTO allergene (allergene_id, libelle) VALUES
    (1, 'Gluten'), (2, 'Crustacés'), (3, 'Œufs'), (4, 'Poissons'), (5, 'Arachides'),
    (6, 'Soja'), (7, 'Lait'), (8, 'Fruits à coque'), (9, 'Céleri'), (10, 'Moutarde'),
    (11, 'Graines de sésame'), (12, 'Sulfites'), (13, 'Lupin'), (14, 'Mollusques');

-- ---------------------------------------------------------------------
-- Plats
-- ---------------------------------------------------------------------
INSERT INTO plat (plat_id, titre_plat, type_plat, description) VALUES
    -- Entrées
    (1,  'Foie gras mi-cuit et chutney de figues',      'entree',  'Foie gras du Sud-Ouest, chutney maison, pain d''épices toasté.'),
    (2,  'Huîtres du bassin d''Arcachon',               'entree',  'Six huîtres n°3, mignonnette à l''échalote.'),
    (3,  'Velouté de potimarron, éclats de châtaigne',  'entree',  'Velouté onctueux, crème légère et châtaignes torréfiées.'),
    (4,  'Œufs mimosa aux herbes du jardin',            'entree',  'Recette traditionnelle revisitée, ciboulette et cerfeuil.'),
    (5,  'Asperges vertes, vinaigrette agrumes',        'entree',  'Asperges de saison, vinaigrette orange-citron vert.'),
    (6,  'Tartare de légumes croquants',                'entree',  'Légumes de saison taillés au couteau, huile de noisette.'),
    (7,  'Assortiment de verrines et toasts',           'entree',  'Douze pièces salées par personne.'),
    -- Plats
    (8,  'Chapon rôti, sauce aux morilles',             'plat',    'Chapon fermier, jus corsé aux morilles, gratin dauphinois.'),
    (9,  'Filet de bœuf, sauce bordelaise',             'plat',    'Bœuf de Bazas, sauce au vin de Bordeaux et moelle, pommes grenaille.'),
    (10, 'Gigot d''agneau de sept heures',              'plat',    'Agneau confit au thym, légumes printaniers.'),
    (11, 'Wellington de champignons et marrons',        'plat',    'Feuilleté de champignons, marrons et épinards.'),
    (12, 'Curry de légumes au lait de coco',            'plat',    'Légumes de saison, riz basmati parfumé.'),
    (13, 'Dos de cabillaud, beurre blanc',              'plat',    'Cabillaud rôti, beurre blanc, purée de céleri.'),
    (14, 'Assortiment de mini-burgers et brochettes',   'plat',    'Pièces chaudes pour cocktail : burgers, brochettes de volaille.'),
    -- Desserts
    (15, 'Bûche chocolat praliné',                      'dessert', 'Bûche maison, chocolat noir 64 % et praliné noisette.'),
    (16, 'Canelés bordelais',                           'dessert', 'Trois canelés par personne, recette traditionnelle.'),
    (17, 'Nid de Pâques chocolat et œufs pralinés',     'dessert', 'Mousse au chocolat, œufs en praliné.'),
    (18, 'Salade de fruits frais, menthe',              'dessert', 'Fruits de saison, sirop léger à la menthe.'),
    (19, 'Pièce montée de choux',                       'dessert', 'Choux à la vanille, nougatine.'),
    (20, 'Mignardises sucrées',                         'dessert', 'Macarons, tartelettes, financiers.');

INSERT INTO plat_allergene (plat_id, allergene_id) VALUES
    (1, 1), (1, 12),
    (2, 14),
    (3, 7), (3, 8),
    (4, 3), (4, 10),
    (5, 10),
    (6, 8),
    (7, 1), (7, 3), (7, 7), (7, 4),
    (8, 7), (8, 12),
    (9, 7), (9, 12), (9, 9),
    (10, 9),
    (11, 1), (11, 3), (11, 7), (11, 8),
    (13, 4), (13, 7), (13, 9),
    (14, 1), (14, 3), (14, 11), (14, 10),
    (15, 1), (15, 3), (15, 7), (15, 8),
    (16, 1), (16, 3), (16, 7),
    (17, 3), (17, 7), (17, 8), (17, 6),
    (19, 1), (19, 3), (19, 7),
    (20, 1), (20, 3), (20, 7), (20, 8);

-- ---------------------------------------------------------------------
-- Menus
-- ---------------------------------------------------------------------
INSERT INTO menu (menu_id, titre, description, nombre_personne_minimum, prix_par_personne, conditions, delai_commande_jours, quantite_restante, theme_id, regime_id) VALUES
    (1, 'Réveillon de Noël tradition',
        'Le grand classique des fêtes : foie gras, huîtres du bassin, chapon aux morilles et bûche maison. Un menu généreux pour réunir toute la famille.',
        8, 52.00,
        'À commander au minimum 14 jours avant la prestation. Les huîtres et le foie gras doivent être conservés au réfrigérateur (0 à 4 °C) dès la livraison et consommés le jour même.',
        14, 10, 1, 1),
    (2, 'Noël végétarien gourmand',
        'Toute la magie de Noël sans viande ni poisson : velouté de potimarron, Wellington de champignons et marrons, bûche chocolat praliné.',
        6, 39.00,
        'À commander au minimum 10 jours avant la prestation. À conserver au réfrigérateur ; réchauffer le Wellington 20 minutes à 180 °C.',
        10, 12, 1, 2),
    (3, 'Déjeuner de Pâques en famille',
        'Œufs mimosa, gigot d''agneau de sept heures et nid de Pâques au chocolat : le repas de Pâques comme à la maison, sans la vaisselle.',
        10, 36.00,
        'À commander au minimum 7 jours avant la prestation. Le gigot est livré chaud dans des caissons isothermes prêtés (à restituer).',
        7, 8, 2, 1),
    (4, 'Pâques printanier vegan',
        'Un menu 100 % végétal et de saison : asperges vertes aux agrumes, curry de légumes au lait de coco et salade de fruits frais à la menthe.',
        6, 31.00,
        'À commander au minimum 5 jours avant la prestation. À conserver au réfrigérateur et à consommer sous 24 heures.',
        5, 15, 2, 3),
    (5, 'Menu bordelais classique',
        'Notre signature toute l''année : velouté de saison, filet de bœuf sauce bordelaise et canelés. Idéal pour un repas d''affaires ou un dîner entre amis.',
        4, 38.00,
        'À commander au minimum 3 jours avant la prestation. À conserver au réfrigérateur.',
        3, 20, 3, 1),
    (6, 'Cocktail dînatoire',
        'Verrines, toasts, mini-burgers, brochettes et mignardises : un cocktail complet pour vos inaugurations, anniversaires et soirées d''entreprise.',
        20, 26.00,
        'À commander au minimum 21 jours avant la prestation. Matériel de service prêté (plateaux, présentoirs) : à restituer sous 10 jours ouvrés, sans quoi 600 € de frais seront facturés (voir CGV).',
        21, 6, 4, 1),
    (7, 'Buffet de mariage prestige',
        'Le menu des grands jours : huîtres, cabillaud au beurre blanc ou filet de bœuf, et la traditionnelle pièce montée de choux.',
        40, 58.00,
        'À commander au minimum 30 jours avant la prestation. Dégustation préalable sur rendez-vous. Matériel prêté à restituer sous 10 jours ouvrés (600 € de frais en cas de non-restitution).',
        30, 3, 4, 1),
    (8, 'Végétal de saison',
        'Tartare de légumes croquants, curry de légumes et salade de fruits : léger, coloré et sans produit animal.',
        4, 27.00,
        'À commander au minimum 3 jours avant la prestation. À consommer sous 24 heures.',
        3, 0, 3, 3);

INSERT INTO menu_plat (menu_id, plat_id) VALUES
    (1, 1), (1, 2), (1, 8), (1, 15),
    (2, 3), (2, 11), (2, 15),
    (3, 4), (3, 10), (3, 17),
    (4, 5), (4, 12), (4, 18),
    (5, 3), (5, 9), (5, 16),
    (6, 7), (6, 14), (6, 20),
    (7, 2), (7, 13), (7, 9), (7, 19),
    (8, 6), (8, 12), (8, 18);

INSERT INTO menu_image (menu_id, chemin, alt, position) VALUES
    (1, 'assets/img/menus/noel-1.svg', 'Table de réveillon dressée avec foie gras et chapon', 0),
    (1, 'assets/img/menus/noel-2.svg', 'Bûche de Noël chocolat praliné', 1),
    (2, 'assets/img/menus/noel-veg-1.svg', 'Wellington de champignons tranché', 0),
    (2, 'assets/img/menus/noel-veg-2.svg', 'Velouté de potimarron en assiette creuse', 1),
    (3, 'assets/img/menus/paques-1.svg', 'Gigot d''agneau et légumes printaniers', 0),
    (3, 'assets/img/menus/paques-2.svg', 'Nid de Pâques au chocolat', 1),
    (4, 'assets/img/menus/paques-vegan-1.svg', 'Asperges vertes et agrumes', 0),
    (4, 'assets/img/menus/paques-vegan-2.svg', 'Curry de légumes au lait de coco', 1),
    (5, 'assets/img/menus/bordelais-1.svg', 'Filet de bœuf sauce bordelaise', 0),
    (5, 'assets/img/menus/bordelais-2.svg', 'Canelés bordelais', 1),
    (6, 'assets/img/menus/cocktail-1.svg', 'Plateau de verrines et toasts', 0),
    (6, 'assets/img/menus/cocktail-2.svg', 'Mini-burgers et brochettes', 1),
    (7, 'assets/img/menus/mariage-1.svg', 'Pièce montée de choux', 0),
    (7, 'assets/img/menus/mariage-2.svg', 'Buffet de mariage dressé', 1),
    (8, 'assets/img/menus/vegetal-1.svg', 'Tartare de légumes croquants', 0),
    (8, 'assets/img/menus/vegetal-2.svg', 'Salade de fruits frais à la menthe', 1);

-- ---------------------------------------------------------------------
-- Horaires
-- ---------------------------------------------------------------------
INSERT INTO horaire (jour, heure_ouverture, heure_fermeture, ferme) VALUES
    ('Lundi',    '09:00', '18:00', 0),
    ('Mardi',    '09:00', '18:00', 0),
    ('Mercredi', '09:00', '18:00', 0),
    ('Jeudi',    '09:00', '18:00', 0),
    ('Vendredi', '09:00', '19:00', 0),
    ('Samedi',   '10:00', '17:00', 0),
    ('Dimanche', NULL,    NULL,    1);

-- ---------------------------------------------------------------------
-- Commandes de démonstration (statuts variés)
-- prix_menu = prix_par_personne × nombre_personne
-- remise = 10 % si nombre_personne >= minimum + 5
-- prix_livraison = 0 à Bordeaux, sinon 5 € + 0,59 €/km
-- ---------------------------------------------------------------------
INSERT INTO commande (commande_id, numero_commande, utilisateur_id, menu_id, date_commande, date_prestation, heure_livraison,
                      adresse_prestation, code_postal_prestation, ville_prestation, distance_km, nombre_personne,
                      prix_menu, remise, prix_livraison, prix_total, statut, pret_materiel, restitution_materiel) VALUES
    (1, 'VG-2026-0001', 3, 5, '2026-05-02 10:12:00', '2026-05-16', '12:00:00', '8 cours de l''Intendance', '33000', 'Bordeaux', 0, 6,  228.00, 0.00,  0.00, 228.00, 'terminee', 0, 0),
    (2, 'VG-2026-0002', 4, 3, '2026-03-20 18:40:00', '2026-04-05', '11:30:00', '3 allée des Vignes', '33600', 'Pessac', 5.4, 15, 540.00, 54.00, 8.19, 494.19, 'terminee', 1, 1),
    (3, 'VG-2026-0003', 4, 6, '2026-06-01 09:05:00', '2026-06-28', '19:00:00', '15 quai des Chartrons', '33000', 'Bordeaux', 0, 25, 650.00, 65.00, 0.00, 585.00, 'terminee', 1, 1),
    (4, 'VG-2026-0004', 3, 4, '2026-04-01 14:22:00', '2026-04-12', '12:30:00', '8 cours de l''Intendance', '33000', 'Bordeaux', 0, 6,  186.00, 0.00,  0.00, 186.00, 'terminee', 0, 0),
    (5, 'VG-2026-0005', 3, 7, '2026-08-20 16:00:00', '2026-09-26', '18:00:00', 'Château Lamothe, 1 route de Lamothe', '33650', 'Saint-Médard-d''Eyrans', 14.2, 50, 2900.00, 290.00, 13.38, 2623.38, 'attente_retour_materiel', 1, 0),
    (6, 'VG-2026-0006', 4, 5, '2026-10-05 11:10:00', '2026-10-24', '20:00:00', '3 allée des Vignes', '33600', 'Pessac', 5.4, 4, 152.00, 0.00, 8.19, 160.19, 'en_preparation', 0, 0),
    (7, 'VG-2026-0007', 3, 1, '2026-10-10 09:30:00', '2026-12-24', '19:30:00', '8 cours de l''Intendance', '33000', 'Bordeaux', 0, 10, 520.00, 0.00, 0.00, 520.00, 'en_attente', 0, 0),
    (8, 'VG-2026-0008', 4, 2, '2026-10-11 20:15:00', '2026-12-25', '12:00:00', '3 allée des Vignes', '33600', 'Pessac', 5.4, 12, 468.00, 46.80, 8.19, 429.39, 'accepte', 0, 0);

INSERT INTO commande_historique (commande_id, statut, commentaire, modifie_par, date_modification) VALUES
    (1, 'en_attente', 'Commande passée par le client', 3, '2026-05-02 10:12:00'),
    (1, 'accepte', NULL, 2, '2026-05-02 15:00:00'),
    (1, 'en_preparation', NULL, 2, '2026-05-16 08:00:00'),
    (1, 'en_livraison', NULL, 2, '2026-05-16 11:20:00'),
    (1, 'livre', NULL, 2, '2026-05-16 11:55:00'),
    (1, 'terminee', NULL, 2, '2026-05-16 12:00:00'),
    (2, 'en_attente', 'Commande passée par le client', 4, '2026-03-20 18:40:00'),
    (2, 'accepte', NULL, 2, '2026-03-21 09:00:00'),
    (2, 'en_preparation', NULL, 2, '2026-04-05 07:30:00'),
    (2, 'en_livraison', NULL, 2, '2026-04-05 10:45:00'),
    (2, 'livre', NULL, 2, '2026-04-05 11:25:00'),
    (2, 'attente_retour_materiel', 'Caissons isothermes prêtés', 2, '2026-04-05 11:30:00'),
    (2, 'terminee', 'Matériel restitué', 2, '2026-04-09 10:00:00'),
    (3, 'en_attente', 'Commande passée par le client', 4, '2026-06-01 09:05:00'),
    (3, 'accepte', NULL, 1, '2026-06-01 11:00:00'),
    (3, 'en_preparation', NULL, 2, '2026-06-28 14:00:00'),
    (3, 'en_livraison', NULL, 2, '2026-06-28 18:15:00'),
    (3, 'livre', NULL, 2, '2026-06-28 18:50:00'),
    (3, 'attente_retour_materiel', 'Présentoirs prêtés', 2, '2026-06-28 19:00:00'),
    (3, 'terminee', 'Matériel restitué', 2, '2026-07-02 16:00:00'),
    (4, 'en_attente', 'Commande passée par le client', 3, '2026-04-01 14:22:00'),
    (4, 'accepte', NULL, 2, '2026-04-01 17:00:00'),
    (4, 'en_preparation', NULL, 2, '2026-04-12 09:00:00'),
    (4, 'en_livraison', NULL, 2, '2026-04-12 12:00:00'),
    (4, 'livre', NULL, 2, '2026-04-12 12:25:00'),
    (4, 'terminee', NULL, 2, '2026-04-12 12:30:00'),
    (5, 'en_attente', 'Commande passée par le client', 3, '2026-08-20 16:00:00'),
    (5, 'accepte', NULL, 1, '2026-08-21 10:00:00'),
    (5, 'en_preparation', NULL, 2, '2026-09-26 10:00:00'),
    (5, 'en_livraison', NULL, 2, '2026-09-26 16:30:00'),
    (5, 'livre', NULL, 2, '2026-09-26 17:40:00'),
    (5, 'attente_retour_materiel', 'Présentoirs et plateaux prêtés', 2, '2026-09-26 18:00:00'),
    (6, 'en_attente', 'Commande passée par le client', 4, '2026-10-05 11:10:00'),
    (6, 'accepte', NULL, 2, '2026-10-05 16:00:00'),
    (6, 'en_preparation', NULL, 2, '2026-10-12 09:00:00'),
    (7, 'en_attente', 'Commande passée par le client', 3, '2026-10-10 09:30:00'),
    (8, 'en_attente', 'Commande passée par le client', 4, '2026-10-11 20:15:00'),
    (8, 'accepte', NULL, 2, '2026-10-12 10:00:00');

-- ---------------------------------------------------------------------
-- Avis clients
-- ---------------------------------------------------------------------
INSERT INTO avis (commande_id, utilisateur_id, note, commentaire, statut, created_at) VALUES
    (1, 3, 5, 'Le filet de bœuf était parfaitement cuit et la sauce bordelaise exceptionnelle. Livraison à l''heure, rien à redire !', 'valide', '2026-05-17 10:00:00'),
    (2, 4, 5, 'Un déjeuner de Pâques mémorable, le gigot fondait dans la bouche. Toute la famille a adoré.', 'valide', '2026-04-10 09:30:00'),
    (3, 4, 4, 'Cocktail très apprécié par nos invités, présentation soignée. Les mini-burgers sont partis en premier !', 'valide', '2026-07-03 12:00:00'),
    (4, 3, 3, 'Bon menu vegan, mais j''aurais aimé des portions un peu plus généreuses.', 'en_attente', '2026-04-13 18:00:00');

COMMIT;
