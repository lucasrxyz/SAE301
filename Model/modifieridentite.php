<?php
require_once __DIR__ . '/config.php';

$erreurIdentite = '';
$messageIdentite = '';
$estRH = in_array('Gestionnaire RH', $_SESSION['roles'] ?? []) || in_array('Administrateur fonctionnel', $_SESSION['roles'] ?? []);

if (isset($_GET['identifiants'])) {
    $messageIdentite = 'Les identifiants ORCID et IdHAL ont été mis à jour.';
}
if (isset($_GET['nomchange'])) {
    $messageIdentite = 'Le nom a été modifié. L\'ancien nom est gardé dans l\'historique.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $estRH && isset($_POST['modifier_identifiants'])) {
    $idPersonnel = $_POST['id_personnel'] ?? '';
    $orcid = trim($_POST['id_orcid'] ?? '');
    $idhal = trim($_POST['id_idhal'] ?? '');

    $requete = $pdo->prepare('SELECT nom, prenom FROM Personnel WHERE id_orcid = ? AND id <> ?');
    $requete->execute([$orcid, $idPersonnel]);
    $doublonOrcid = $requete->fetch(PDO::FETCH_ASSOC);

    $requete = $pdo->prepare('SELECT nom, prenom FROM Personnel WHERE id_idhal = ? AND id <> ?');
    $requete->execute([$idhal, $idPersonnel]);
    $doublonIdhal = $requete->fetch(PDO::FETCH_ASSOC);

    if ($orcid !== '' && !preg_match('/^\d{4}-\d{4}-\d{4}-\d{3}[\dX]$/', $orcid)) {
        $erreurIdentite = 'L\'ORCID doit avoir la forme 0000-0000-0000-0000 (le dernier caractère peut être un X).';
    } elseif ($orcid !== '' && $doublonOrcid) {
        $erreurIdentite = 'Cet ORCID est déjà utilisé par ' . $doublonOrcid['prenom'] . ' ' . $doublonOrcid['nom'] . '.';
    } elseif ($idhal !== '' && $doublonIdhal) {
        $erreurIdentite = 'Cet IdHAL est déjà utilisé par ' . $doublonIdhal['prenom'] . ' ' . $doublonIdhal['nom'] . '.';
    } else {
        $requete = $pdo->prepare('UPDATE Personnel SET id_orcid = ?, id_idhal = ? WHERE id = ?');
        $requete->execute([$orcid === '' ? null : $orcid, $idhal === '' ? null : $idhal, $idPersonnel]);
        header('Location: ficheDetailleePersonnel.php?id=' . $idPersonnel . '&identifiants=1');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $estRH && isset($_POST['changer_nom'])) {
    $idPersonnel = $_POST['id_personnel'] ?? '';
    $nouveauNom = trim($_POST['nouveau_nom'] ?? '');
    $dateChangement = $_POST['date_changement'] ?? '';

    $requete = $pdo->prepare('SELECT nom FROM Personnel WHERE id = ?');
    $requete->execute([$idPersonnel]);
    $nomActuel = $requete->fetchColumn();

    if ($nouveauNom === '' || $dateChangement === '') {
        $erreurIdentite = 'Le nouveau nom et la date sont obligatoires.';
    } elseif ($nouveauNom === $nomActuel) {
        $erreurIdentite = 'Le nouveau nom est identique au nom actuel.';
    } else {
        $pdo->beginTransaction();
        $requete = $pdo->prepare('INSERT INTO Personnel_Nom_Historique (id_personnel, ancien_nom, date_changement) VALUES (?, ?, ?)');
        $requete->execute([$idPersonnel, $nomActuel, $dateChangement]);
        $requete = $pdo->prepare('UPDATE Personnel SET nom = ? WHERE id = ?');
        $requete->execute([$nouveauNom, $idPersonnel]);
        $pdo->commit();
        header('Location: ficheDetailleePersonnel.php?id=' . $idPersonnel . '&nomchange=1');
        exit;
    }
}