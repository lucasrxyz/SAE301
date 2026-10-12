<?php
    require_once __DIR__ . '/config.php';

$debut = $_GET['debut'] ?? date('d-m-y');
$fin = $_GET['fin'] ?? date('d-m-y');
$stmt = [];

if ($debut <= $fin) {
$requete = $pdo->prepare( 
    "SELECT Departement.nom AS dept_nom, Groupe.nom AS groupe_nom, TO_CHAR(Groupe.date_debut,
    'DD/MM/YYYY') AS date_debut,
    COALESCE(TO_CHAR(Groupe.date_fin, 'DD/MM/YYYY'), 'En cours') AS date_fin
    FROM Groupe
    JOIN Departement ON Departement.id_departement = Groupe.id_departement
    WHERE Groupe.date_debut <= ? AND (Groupe.date_fin
    IS NULL OR Groupe.date_fin >= ?)
    ORDER BY dept_nom, groupe_nom;");
    
    $requete->execute([$debut, $fin]); 
    $stmt = $requete->fetchAll(PDO::FETCH_ASSOC); } ?>
