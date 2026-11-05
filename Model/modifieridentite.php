<?php
// Connexion à la base de données
require_once __DIR__ . '/config.php';

// Messages vides, et droit de modifier réservé au RH et à l'administrateur
$erreurIdentite = '';
$messageIdentite = '';
$estRH = verifierDroit('gerer_personnel');

// Message de réussite selon l'adresse de la page
if (isset($_GET['identifiants'])) {
    $messageIdentite = 'Les identifiants ORCID et IdHAL ont été mis à jour.';
}
if (isset($_GET['nomchange'])) {
    $messageIdentite = 'Le nom a été modifié. L\'ancien nom est gardé dans l\'historique.';
}

// Formulaire « ORCID et IdHAL »
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $estRH && isset($_POST['modifier_identifiants'])) {
    $idPersonnel = $_POST['id_personnel'] ?? '';
    $orcid = trim($_POST['id_orcid'] ?? '');
    $idhal = trim($_POST['id_idhal'] ?? '');

    // Regarde si une autre personne a déjà cet ORCID
    $requete = $pdo->prepare('SELECT nom, prenom FROM Personnel WHERE id_orcid = ? AND id <> ?');
    $requete->execute([$orcid, $idPersonnel]);
    $doublonOrcid = $requete->fetch(PDO::FETCH_ASSOC);

    // Regarde si une autre personne a déjà cet IdHAL
    $requete = $pdo->prepare('SELECT nom, prenom FROM Personnel WHERE id_idhal = ? AND id <> ?');
    $requete->execute([$idhal, $idPersonnel]);
    $doublonIdhal = $requete->fetch(PDO::FETCH_ASSOC);

    // Contrôle : forme de l'ORCID, puis doublons (les champs vides sont permis)
    if ($orcid !== '' && !preg_match('/^\d{4}-\d{4}-\d{4}-\d{3}[\dX]$/', $orcid)) {
        $erreurIdentite = 'L\'ORCID doit avoir la forme 0000-0000-0000-0000 (le dernier caractère peut être un X).';
    } elseif ($orcid !== '' && $doublonOrcid) {
        $erreurIdentite = 'Cet ORCID est déjà utilisé par ' . $doublonOrcid['prenom'] . ' ' . $doublonOrcid['nom'] . '.';
    } elseif ($idhal !== '' && $doublonIdhal) {
        $erreurIdentite = 'Cet IdHAL est déjà utilisé par ' . $doublonIdhal['prenom'] . ' ' . $doublonIdhal['nom'] . '.';
    } else {
        // Enregistre les identifiants (un champ vide devient NULL)
        $requete = $pdo->prepare('UPDATE Personnel SET id_orcid = ?, id_idhal = ? WHERE id = ?');
        $requete->execute([$orcid === '' ? null : $orcid, $idhal === '' ? null : $idhal, $idPersonnel]);
        header('Location: ficheDetailleePersonnel.php?id=' . $idPersonnel . '&identifiants=1');
        exit;
    }
}

// Formulaire « changer le nom »
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $estRH && isset($_POST['changer_nom'])) {
    $idPersonnel = $_POST['id_personnel'] ?? '';
    $nouveauNom = trim($_POST['nouveau_nom'] ?? '');
    $dateChangement = $_POST['date_changement'] ?? '';

    // Récupère le nom actuel
    $requete = $pdo->prepare('SELECT nom FROM Personnel WHERE id = ?');
    $requete->execute([$idPersonnel]);
    $nomActuel = $requete->fetchColumn();

    // Contrôle : champs remplis et nom vraiment différent
    if ($nouveauNom === '' || $dateChangement === '') {
        $erreurIdentite = 'Le nouveau nom et la date sont obligatoires.';
    } elseif ($nouveauNom === $nomActuel) {
        $erreurIdentite = 'Le nouveau nom est identique au nom actuel.';
    } else {
        // Si une des deux écritures plante, on annule tout
        $pdo->beginTransaction();
        // Garde l'ancien nom dans l'historique
        $requete = $pdo->prepare('INSERT INTO Personnel_Nom_Historique (id_personnel, ancien_nom, date_changement) VALUES (?, ?, ?)');
        $requete->execute([$idPersonnel, $nomActuel, $dateChangement]);
        // Enregistre le nouveau nom
        $requete = $pdo->prepare('UPDATE Personnel SET nom = ? WHERE id = ?');
        $requete->execute([$nouveauNom, $idPersonnel]);
        $pdo->commit();
        header('Location: ficheDetailleePersonnel.php?id=' . $idPersonnel . '&nomchange=1');
        exit;
    }
}