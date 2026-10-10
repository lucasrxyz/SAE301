DROP TABLE IF EXISTS Activite CASCADE;
DROP TABLE IF EXISTS TypeActivite CASCADE;
DROP TABLE IF EXISTS Rattachement CASCADE;
DROP TABLE IF EXISTS Groupe CASCADE;
DROP TABLE IF EXISTS Departement CASCADE;
DROP TABLE IF EXISTS Compte_Role CASCADE;
DROP TABLE IF EXISTS Compte CASCADE;
DROP TABLE IF EXISTS Role CASCADE;
DROP TABLE IF EXISTS Personnel_Statut_Historique CASCADE;
DROP TABLE IF EXISTS Personnel CASCADE;

-- ===============================
-- TABLE "Personnel"
-- ===============================
CREATE TABLE Personnel (
    id serial PRIMARY KEY,
    nom varchar(50) NOT NULL,
    prenom varchar(50) NOT NULL,
    mail varchar(60) NOT NULL,
    id_orcid varchar(19),
    id_idhal varchar(100),
    actif BOOLEAN DEFAULT TRUE,
    date_arrivee DATE NOT NULL,
    date_depart DATE
);

-- ===============================
-- TABLE "Personnel_Statut_Historique"
-- ===============================
CREATE TABLE Personnel_Statut_Historique (
    id serial PRIMARY KEY,
    id_personnel INT NOT NULL REFERENCES Personnel(id),
    statut varchar(50) NOT NULL,
    quotite_recherche DECIMAL NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE
);

-- ===============================
-- TABLE "Role"
-- ===============================
CREATE TABLE Role (
    id_role serial PRIMARY KEY,
    nom_role TEXT NOT NULL,
    niveau_pyramidal INT NOT NULL
);

-- ===============================
-- TABLE "Compte"
-- ===============================
CREATE TABLE Compte (
    id_compte serial PRIMARY KEY,
    id_personnel INT NOT NULL REFERENCES Personnel(id),
    identifiant varchar(100) NOT NULL,
    mot_de_passe_hash varchar(255) NOT NULL,
    actif BOOLEAN DEFAULT TRUE,
    date_activation DATE not null ,
    date_desactivation DATE
);

-- ===============================
-- TABLE "Compte_Role"
-- ===============================
CREATE TABLE Compte_Role (
    id_compte INT NOT NULL REFERENCES Compte(id_compte),
    id_role INT NOT NULL REFERENCES Role(id_role),
    PRIMARY KEY (id_compte, id_role)
);

-- ===============================
-- TABLE "Departement"
-- ===============================
CREATE TABLE Departement (
    id_departement serial PRIMARY KEY,
    nom varchar(50) NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE
);

-- ===============================
-- TABLE "Groupe"
-- ===============================
CREATE TABLE Groupe (
    id_groupe serial PRIMARY KEY,
    id_departement INT NOT NULL REFERENCES Departement(id_departement),
    nom varchar(255) NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE
);

-- ===============================
-- TABLE "Rattachement"
-- ===============================
CREATE TABLE Rattachement (
    id serial PRIMARY KEY,
    id_personnel INT NOT NULL REFERENCES Personnel(id),
    id_departement INT REFERENCES Departement(id_departement),
    id_groupe INT REFERENCES Groupe(id_groupe),
    type_rattachement varchar(20) NOT NULL DEFAULT 'principal'
        CHECK (type_rattachement IN ('principal', 'secondaire')),
    responsable_groupe BOOLEAN DEFAULT false,
    date_debut DATE NOT NULL,
    date_fin DATE
);

-- Migration pour les bases existantes (exécuter cette instruction seule).
ALTER TABLE Rattachement
ADD COLUMN IF NOT EXISTS type_rattachement varchar(20) NOT NULL DEFAULT 'principal'
    CHECK (type_rattachement IN ('principal', 'secondaire'));

-- ===============================
-- TABLE "TypeActivite"
-- ===============================
CREATE TABLE TypeActivite (
    id_type serial PRIMARY KEY,
    categorie varchar(102) NOT NULL,
    nom_type varchar(102) NOT NULL,
    est_collective boolean default false
);

-- ===============================
-- TABLE "Activite"
-- ===============================
CREATE TABLE Activite (
    id_activite serial PRIMARY KEY,
    id_type INT NOT NULL REFERENCES TypeActivite(id_type),
    id_personnel INT NOT NULL REFERENCES Personnel(id),
    titre varchar(255) NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE,
    donnees JSON
);

-- ===============================
-- DONNÉES DE TEST
-- ===============================
INSERT INTO Personnel (nom, prenom, mail, id_orcid, id_idhal, actif, date_arrivee, date_depart) VALUES
('Dupont', 'Jean', 'jean.dupont@uphf.fr', '0000-0001-1111-1111', 'jean-dupont', TRUE, '2021-03-15', NULL),
('Martin', 'Marie', 'marie.martin@uphf.fr', '0000-0001-2222-2222', 'marie-martin', TRUE, '2020-07-22', NULL),
('Martin', 'Marie', 'marie.martin2@uphf.fr', '0000-0001-3333-3333', 'marie-martin-2', TRUE, '2023-09-01', NULL),
('Bernard', 'Lucas', 'lucas.bernard@uphf.fr', NULL, NULL, TRUE, '2022-01-10', NULL),
('Durand', 'Sophie', 'sophie.durand@uphf.fr', '0000-0001-4444-4444', 'sophie-durand', TRUE, '2019-11-05', NULL),
('Petit', 'Thomas', 'thomas.petit@uphf.fr', NULL, NULL, TRUE, '2023-04-18', NULL),
('Leroy', 'Emma', 'emma.leroy@uphf.fr', NULL, NULL, TRUE, '2021-09-27', NULL),
('Moreau', 'Hugo', 'hugo.moreau@uphf.fr', '0000-0001-5555-5555', 'hugo-moreau', TRUE, '2015-02-14', NULL),
('Robert', 'Clara', 'clara.robert@uphf.fr', NULL, NULL, TRUE, '2024-01-08', NULL),
('Richard', 'Antoine', 'antoine.richard@uphf.fr', '0000-0001-6666-6666', 'antoine-richard', TRUE, '2012-06-30', NULL),
('Michel', 'Julie', 'julie.michel@uphf.fr', NULL, NULL, TRUE, '2018-10-12', NULL),
('Garnier', 'Paul', 'paul.garnier@uphf.fr', NULL, NULL, FALSE, '2017-09-01', '2023-08-31'),
('Lambert', 'Nadia', 'nadia.lambert@uphf.fr', NULL, NULL, TRUE, '2016-03-01', NULL),
('Admin', 'Système', 'admin@uphf.fr', NULL, NULL, TRUE, '2024-01-01', NULL);

INSERT INTO Personnel_Statut_Historique (id_personnel, statut, quotite_recherche, date_debut, date_fin) VALUES
(1, 'Maître de conférences', 0.50, '2021-03-15', NULL),
(2, 'Doctorant', 1.00, '2020-07-22', '2023-09-30'),
(2, 'Post-doctorant', 1.00, '2023-10-01', NULL),
(3, 'Doctorant', 1.00, '2023-09-01', NULL),
(4, 'Maître de conférences', 0.50, '2022-01-10', NULL),
(5, 'Chargé de recherche', 1.00, '2019-11-05', NULL),
(6, 'Ingénieur d''études', 0.30, '2023-04-18', NULL),
(7, 'Doctorant', 1.00, '2021-09-27', NULL),
(8, 'Maître de conférences', 0.50, '2015-02-14', '2020-08-31'),
(8, 'Professeur des universités', 0.50, '2020-09-01', NULL),
(9, 'Doctorant', 1.00, '2024-01-08', NULL),
(10, 'Directeur de recherche', 1.00, '2012-06-30', NULL),
(11, 'Ingénieur de recherche', 0.60, '2018-10-12', NULL),
(12, 'Maître de conférences', 0.50, '2017-09-01', '2023-08-31');

INSERT INTO Role (nom_role, niveau_pyramidal) VALUES
('Administrateur fonctionnel', 0),
('Directeur de laboratoire', 1),
('Responsable de département', 2),
('Responsable de groupe', 3),
('Gestionnaire RH', 3),
('Personnel', 4);

INSERT INTO Compte (id_personnel, identifiant, mot_de_passe_hash, actif, date_activation, date_desactivation) VALUES
(14, 'admin', '$2y$10$StOpeirYH5XGBzyrcZbWOu2lyMY1CJagfvXuSpZc0Yz9GGA6oTTRy', TRUE, '2024-01-01', NULL),
(13, 'rh.labo', '$2y$10$StOpeirYH5XGBzyrcZbWOu2lyMY1CJagfvXuSpZc0Yz9GGA6oTTRy', TRUE, '2024-01-01', NULL),
(10, 'arichard', '$2y$10$StOpeirYH5XGBzyrcZbWOu2lyMY1CJagfvXuSpZc0Yz9GGA6oTTRy', TRUE, '2024-01-01', NULL),
(8, 'hmoreau', '$2y$10$StOpeirYH5XGBzyrcZbWOu2lyMY1CJagfvXuSpZc0Yz9GGA6oTTRy', TRUE, '2024-01-01', NULL),
(1, 'jdupont', '$2y$10$StOpeirYH5XGBzyrcZbWOu2lyMY1CJagfvXuSpZc0Yz9GGA6oTTRy', TRUE, '2024-01-01', NULL),
(2, 'mmartin', '$2y$10$StOpeirYH5XGBzyrcZbWOu2lyMY1CJagfvXuSpZc0Yz9GGA6oTTRy', TRUE, '2024-01-01', NULL),
(12, 'pgarnier', '$2y$10$StOpeirYH5XGBzyrcZbWOu2lyMY1CJagfvXuSpZc0Yz9GGA6oTTRy', FALSE, '2017-09-01', '2023-08-31');

INSERT INTO Compte_Role (id_compte, id_role) VALUES
(1, 1),
(2, 5),
(3, 2), (3, 6),
(4, 3), (4, 6),
(5, 4), (5, 6),
(6, 6),
(7, 6);

INSERT INTO Departement (nom, date_debut, date_fin) VALUES
('Informatique', '2010-01-01', NULL),
('Mathématiques', '2010-01-01', NULL),
('Automatique', '2010-01-01', '2022-12-31');

INSERT INTO Groupe (id_departement, nom, date_debut, date_fin) VALUES
(1, 'Systèmes interactifs', '2012-01-01', NULL),
(1, 'Cybersécurité', '2019-09-01', NULL),
(2, 'Optimisation', '2012-01-01', NULL),
(3, 'Robotique', '2012-01-01', '2022-12-31');

INSERT INTO Rattachement (id_personnel, id_departement, id_groupe, responsable_groupe, date_debut, date_fin) VALUES
(1, NULL, 1, FALSE, '2021-03-15', NULL),
(1, NULL, 2, FALSE, '2022-01-01', NULL),
(1, NULL, 1, TRUE, '2023-01-01', NULL),
(2, NULL, 2, FALSE, '2020-07-22', NULL),
(3, NULL, 1, FALSE, '2023-09-01', NULL),
(4, NULL, 1, FALSE, '2022-01-10', NULL),
(5, NULL, 3, FALSE, '2019-11-05', NULL),
(6, NULL, 2, FALSE, '2023-04-18', NULL),
(7, NULL, 4, FALSE, '2021-09-27', '2022-12-31'),
(7, NULL, 1, FALSE, '2023-01-01', NULL),
(8, NULL, 1, FALSE, '2015-02-14', NULL),
(8, 1, NULL, TRUE, '2020-09-01', NULL),
(9, NULL, 3, FALSE, '2024-01-08', NULL),
(10, NULL, 3, FALSE, '2012-06-30', NULL),
(11, 1, NULL, FALSE, '2018-10-12', NULL),
(12, NULL, 4, FALSE, '2017-09-01', '2022-12-31'),
(12, NULL, 4, TRUE, '2018-01-01', '2022-12-31'),
(12, NULL, 1, FALSE, '2023-01-01', '2023-08-31');

INSERT INTO TypeActivite (categorie, nom_type, est_collective) VALUES
('Publication', 'Article de revue', TRUE),
('Publication', 'Communication en conférence avec actes', TRUE),
('Publication', 'Ouvrage', TRUE),
('Publication', 'Chapitre d''ouvrage', TRUE),
('Encadrement doctoral', 'Direction de thèse', TRUE),
('Encadrement doctoral', 'Co-encadrement de thèse', TRUE),
('Contrat', 'Projet de recherche', TRUE),
('Événement', 'Organisation d''un événement scientifique', TRUE),
('Rayonnement', 'Relecture d''article', FALSE),
('Rayonnement', 'Expertise de dossier', FALSE),
('Rayonnement', 'Conférence invitée', FALSE),
('Rayonnement', 'Jury de thèse', FALSE),
('Rayonnement', 'Comité de programme de conférence', FALSE),
('Rayonnement', 'Comité de sélection', FALSE),
('Responsabilité', 'Administration et animation de structures', FALSE);

INSERT INTO Activite (id_type, id_personnel, titre, date_debut, date_fin, donnees) VALUES
(1, 1, 'Interfaces adaptatives pour la gestion de crise', '2024-03-10', '2024-03-10', '{"revue": "IJHCS", "hal_id": "hal-04512345"}'),
(2, 2, 'Détection d''intrusion par apprentissage', '2024-04-20', '2024-04-25', '{"conference": "ACM CCS", "lieu": "Salt Lake City"}'),
(5, 8, 'Thèse d''Emma Leroy', '2021-09-27', NULL, '{"doctorant": 7}'),
(7, 5, 'Projet ANR OPTIMA', '2022-01-01', '2025-12-31', '{"financeur": "ANR", "montant": 250000}'),
(8, 1, 'Journées IHM 2024', '2024-11-05', '2024-11-07', '{"lieu": "Valenciennes"}'),
(9, 10, 'Relecture IEEE TSE', '2024-02-01', '2024-02-15', '{"revue": "IEEE TSE"}'),
(11, 8, 'Keynote EICS 2023', '2023-06-27', '2023-06-27', '{"lieu": "Swansea"}'),
(12, 4, 'Jury de thèse Université de Lille', '2024-06-14', '2024-06-14', '{"universite": "Lille"}'),
(13, 2, 'Comité de programme ESORICS 2024', '2024-01-15', '2024-05-30', '{"conference": "ESORICS"}'),
(1, 12, 'Commande robuste de bras manipulateurs', '2021-05-12', '2021-05-12', '{"revue": "Automatica"}');


