-- ===============================
-- TABLE "Departement"
-- ===============================
CREATE TABLE Departement (
    id serial PRIMARY KEY,
    nom TEXT NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE
);

-- ===============================
-- TABLE "Groupe"
-- ===============================
CREATE TABLE Groupe (
    id serial PRIMARY KEY,
    nom TEXT NOT NULL,
    id_departement INT NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE,

    FOREIGN KEY (id_departement) REFERENCES Departement(id)
);

-- ===============================
-- TABLE "Personnel"
-- ===============================
CREATE TABLE Personnel (
    id serial PRIMARY KEY,
    nom TEXT NOT NULL,
    prenom TEXT NOT NULL,
    mail TEXT NOT NULL,
    date_entree DATE NOT NULL,
    actif BOOLEAN NOT NULL DEFAULT TRUE
);

INSERT INTO Personnel (nom, prenom, mail, date_entree) VALUES
('Dupont', 'Jean', 'jean.dupont@gmail.com', '2021-03-15'),
('Martin', 'Marie', 'marie.martin@gmail.com', '2020-07-22'),
('Bernard', 'Lucas', 'lucas.bernard@gmail.com', '2022-01-10'),
('Durand', 'Sophie', 'sophie.durand@gmail.com', '2019-11-05'),
('Petit', 'Thomas', 'thomas.petit@gmail.com', '2023-04-18'),
('Leroy', 'Emma', 'emma.leroy@gmail.com', '2021-09-27'),
('Moreau', 'Hugo', 'hugo.moreau@gmail.com', '2020-02-14'),
('Robert', 'Clara', 'clara.robert@gmail.com', '2024-01-08'),
('Richard', 'Antoine', 'antoine.richard@gmail.com', '2018-06-30'),
('Michel', 'Julie', 'julie.michel@gmail.com', '2022-10-12');

-- ===============================
-- TABLE "Rattachement"
-- ===============================
CREATE TABLE Rattachement (
    id serial PRIMARY KEY,
    id_personnel INT NOT NULL,
    id_departement INT,
    id_groupe INT,
    date_debut DATE NOT NULL,
    date_fin DATE,

    FOREIGN KEY (id_personnel) REFERENCES Personnel(id),
    FOREIGN KEY (id_departement) REFERENCES Departement(id),
    FOREIGN KEY (id_groupe) REFERENCES Groupe(id),
);

-- ===============================
-- TABLE "Historique"
-- ===============================
CREATE TABLE Historique (
    id serial PRIMARY KEY,
    id_personnel INT NOT NULL,
    champ_concerne TEXT NOT NULL,
    valeur TEXT NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE,

    FOREIGN KEY (id_personnel) REFERENCES Personnel(id)
);