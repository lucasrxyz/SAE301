<?php
require_once __DIR__ . '/config.php';

exigerDroit('gerer_personnel');

require_once __DIR__ . '/statuts.php';

$erreurPersonnel = '';
$personnelCree = isset($_GET['cree']);
$nomSaisi = '';
$prenomSaisi = '';
$mailSaisi = '';
$dateArriveeSaisie = '';
$statutSaisi = '';
$quotiteSaisie = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomSaisi = trim($_POST['nom'] ?? '');
    $prenomSaisi = trim($_POST['prenom'] ?? '');
    $mailSaisi = trim($_POST['mail'] ?? '');
    $dateArriveeSaisie = $_POST['date_arrivee'] ?? '';
    $statutSaisi = $_POST['statut'] ?? '';
    $quotiteSaisie = $_POST['quotite'] ?? '';

    $date = DateTime::createFromFormat('!Y-m-d', $dateArriveeSaisie);
    $dateValide = $date && $date->format('Y-m-d') === $dateArriveeSaisie;
    $quotite = filter_var($quotiteSaisie, FILTER_VALIDATE_FLOAT);

    $requete = $pdo->prepare('SELECT COUNT(*) FROM Personnel WHERE LOWER(mail) = LOWER(?)');
    $requete->execute([$mailSaisi]);
    $mailDejaUtilise = $requete->fetchColumn() > 0;

    if (in_array('', [$nomSaisi, $prenomSaisi, $mailSaisi, $dateArriveeSaisie, $statutSaisi, $quotiteSaisie])) {
        $erreurPersonnel = 'Veuillez remplir tous les champs.';
    } elseif (strlen($nomSaisi) > 50 || strlen($prenomSaisi) > 50 || strlen($mailSaisi) > 60) {
        $erreurPersonnel = 'Un ou plusieurs champs dépassent la longueur autorisée.';
    } elseif (!in_array($statutSaisi, $statutsPossibles)) {
        $erreurPersonnel = 'Veuillez choisir un statut dans la liste.';
    } elseif (!filter_var($mailSaisi, FILTER_VALIDATE_EMAIL)) {
        $erreurPersonnel = 'Veuillez saisir une adresse mail valide.';
    } elseif ($mailDejaUtilise) {
        $erreurPersonnel = 'Cette adresse mail est déjà utilisée.';
    } elseif (!$dateValide) {
        $erreurPersonnel = 'Veuillez saisir une date d\'arrivée valide.';
    } elseif ($quotite === false || $quotite < 0 || $quotite > 100) {
        $erreurPersonnel = 'La quotité doit être comprise entre 0 et 100 %.';
    } else {
        $pdo->beginTransaction();

        $requete = $pdo->prepare('INSERT INTO Personnel (nom, prenom, mail, date_arrivee) VALUES (?, ?, ?, ?) RETURNING id');
        $requete->execute([$nomSaisi, $prenomSaisi, $mailSaisi, $dateArriveeSaisie]);
        $idPersonnel = $requete->fetchColumn();

        $requete = $pdo->prepare('INSERT INTO Personnel_Statut_Historique (id_personnel, statut, quotite_recherche, date_debut) VALUES (?, ?, ?, ?)');
        $requete->execute([$idPersonnel, $statutSaisi, $quotite / 100, $dateArriveeSaisie]);

        $pdo->commit();

        header('Location: ajouterRattachement.php?id=' . $idPersonnel);
        exit;
    }
}