<?php
    require_once __DIR__ . '/config.php';
    $requete = $pdo->prepare(
        "SELECT
        d.nom AS dept_nom,
        g.nom AS groupe_nom,
        p.nom AS person_nom
        FROM Departement d
        LEFT JOIN Groupe g ON g.id_departement = d.id_departement
        LEFT JOIN Personnel p ON p.id = g.id_groupe
        ORDER BY d.nom, g.nom, p.nom");

    $requete->execute();
    $stmt = $requete->fetchAll(PDO::FETCH_ASSOC);
?>