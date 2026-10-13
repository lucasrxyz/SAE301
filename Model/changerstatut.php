<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/statuts.php';

$erreurStatut = '';
$statutChange = isset($_GET['statut_change']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['changer_statut']) && verifierDroit('gerer_personnel')) {
    $idPersonnel = (int) ($_POST['id_personnel'] ?? 0);
    $nouveauStatut = $_POST['nouveau_statut'] ?? '';
    $nouvelleQuotite = $_POST['nouvelle_quotite'] ?? '';
    $dateEffet = $_POST['date_effet'] ?? '';

    $requete = $pdo->prepare('SELECT date_debut FROM Personnel_Statut_Historique WHERE id_personnel = ? AND date_fin IS NULL');
    $requete->execute([$idPersonnel]);
    $debutStatutActuel = $requete->fetchColumn();

    $date = DateTime::createFromFormat('!Y-m-d', $dateEffet);

    if (!in_array($nouveauStatut, $statutsPossibles)) {
        $erreurStatut = 'Veuillez choisir un statut de la liste.';
    } elseif (!is_numeric($nouvelleQuotite) || $nouvelleQuotite < 0 || $nouvelleQuotite > 100) {
        $erreurStatut = 'La quotité de recherche doit être comprise entre 0 et 100 %.';
    } elseif (!$date || $date->format('Y-m-d') !== $dateEffet) {
        $erreurStatut = 'Veuillez saisir une date d\'effet valide.';
    } elseif ($debutStatutActuel && $dateEffet <= $debutStatutActuel) {
        $erreurStatut = 'La date d\'effet doit être après le début du statut actuel (' . date('d/m/Y', strtotime($debutStatutActuel)) . ').';
    } else {
        $pdo->beginTransaction();

        $requete = $pdo->prepare("UPDATE Personnel_Statut_Historique SET date_fin = ?::date - 1 WHERE id_personnel = ? AND date_fin IS NULL");
        $requete->execute([$dateEffet, $idPersonnel]);

        $requete = $pdo->prepare('INSERT INTO Personnel_Statut_Historique (id_personnel, statut, quotite_recherche, date_debut) VALUES (?, ?, ?, ?)');
        $requete->execute([$idPersonnel, $nouveauStatut, $nouvelleQuotite / 100, $dateEffet]);

        $pdo->commit();

        header('Location: ficheDetailleePersonnel.php?id=' . $idPersonnel . '&statut_change=1');
        exit;
    }
}