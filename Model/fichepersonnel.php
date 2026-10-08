<?php
require_once __DIR__ . '/config.php';

$idPersonnel = $_GET['id'] ?? null;
$personnel = null;
$personnelIntrouvable = false;
$historiqueStatuts = [];

if ($idPersonnel !== null) {
    $requete = $pdo->prepare("SELECT * FROM Personnel WHERE id = :id");
    $requete->execute(['id' => $idPersonnel]);
    $personnel = $requete->fetch(PDO::FETCH_ASSOC);

    if (!$personnel) {
        $personnelIntrouvable = true;
    } else {
        $requete = $pdo->prepare(
            "SELECT statut, quotite_recherche, date_debut, date_fin
            FROM Personnel_Statut_Historique
            WHERE id_personnel = :id
            ORDER BY date_debut DESC"
        );
        $requete->execute(['id' => $idPersonnel]);
        $historiqueStatuts = $requete->fetchAll(PDO::FETCH_ASSOC);
    }
}