CREATE TABLE Personnel(
    id serial NOT NULL PRIMARY KEY,
    nom text NOT NULL,
    prenom text NOT NULL,
    mail text NOT NULL,
    date_entree DATE NOT NULL
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

SELECT * FROM Personnel
WHERE Personnel.nom = 'Dupont';