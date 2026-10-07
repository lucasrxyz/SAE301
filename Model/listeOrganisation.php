<?php
    require_once __DIR__ . '/config.php';
    $requete = $pdo->prepare(
        "SELECT
        d.nom AS dept_nom,
        g.nom AS groupe_nom,
        p.nom AS person_nom
        FROM Departement d
        LEFT JOIN Groupe g ON g.id_departement = d.id_departement
        LEFT JOIN Rattachement r ON r.id_groupe = g.id_groupe AND r.date_fin IS NULL
        LEFT JOIN Personnel p ON p.id = r.id_personnel
        ORDER BY d.nom, g.nom, p.nom");

    $requete->execute();
    $stmt = $requete->fetchAll(PDO::FETCH_ASSOC);
?>