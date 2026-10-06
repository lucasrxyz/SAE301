CREATE TABLE Personnel(
    id int NOT NULL PRIMARY KEY,
    nom text NOT NULL,
    prenom text NOT NULL,
    mail text NOT NULL,
    date_entree DATE NOT NULL
);

INSERT INTO Personnel (id, nom, prenom, mail, date_entree) VALUES
(1, 'Dupont', 'Jean', 'jean.dupont@gmail.com', '2021-03-15'),
(2, 'Martin', 'Marie', 'marie.martin@gmail.com', '2020-07-22'),
(3, 'Bernard', 'Lucas', 'lucas.bernard@gmail.com', '2022-01-10'),
(4, 'Durand', 'Sophie', 'sophie.durand@gmail.com', '2019-11-05'),
(5, 'Petit', 'Thomas', 'thomas.petit@gmail.com', '2023-04-18'),
(6, 'Leroy', 'Emma', 'emma.leroy@gmail.com', '2021-09-27'),
(7, 'Moreau', 'Hugo', 'hugo.moreau@gmail.com', '2020-02-14'),
(8, 'Robert', 'Clara', 'clara.robert@gmail.com', '2024-01-08'),
(9, 'Richard', 'Antoine', 'antoine.richard@gmail.com', '2018-06-30'),
(10, 'Michel', 'Julie', 'julie.michel@gmail.com', '2022-10-12');