<?php
// Connexion à la base de données
require_once __DIR__ . '/config.php';

// Seuls le RH et l'administrateur peuvent ajouter un personnel
exigerDroit('gerer_personnel');

// Liste des statuts autorisés
require_once __DIR__ . '/statuts.php';

// Valeurs de départ du formulaire (vides à l'ouverture de la page)
$erreurPersonnel = '';
$personnelCree = isset($_GET['cree']);
$nomSaisi = '';
$prenomSaisi = '';
$mailSaisi = '';
$dateArriveeSaisie = '';
$statutSaisi = '';
$quotiteSaisie = '';

// Le code suivant s'exécute seulement quand le formulaire est envoyé
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupère ce que l'utilisateur a saisi
    $nomSaisi = trim($_POST['nom'] ?? '');
    $prenomSaisi = trim($_POST['prenom'] ?? '');
    $mailSaisi = trim($_POST['mail'] ?? '');
    $dateArriveeSaisie = $_POST['date_arrivee'] ?? '';
    $statutSaisi = $_POST['statut'] ?? '';
    $quotiteSaisie = $_POST['quotite'] ?? '';

    // Vérifie que la date existe vraiment (le 31/02 est refusé)
    $date = DateTime::createFromFormat('!Y-m-d', $dateArriveeSaisie);
    $dateValide = $date && $date->format('Y-m-d') === $dateArriveeSaisie;
    // Transforme la quotité en nombre (false si ce n'est pas un nombre)
    $quotite = filter_var($quotiteSaisie, FILTER_VALIDATE_FLOAT);

    // Regarde si ce mail existe déjà, sans tenir compte des majuscules
    $requete = $pdo->prepare('SELECT COUNT(*) FROM Personnel WHERE LOWER(mail) = LOWER(?)');
    $requete->execute([$mailSaisi]);
    $mailDejaUtilise = $requete->fetchColumn() > 0;

    // Contrôle les données : on garde le premier message d'erreur trouvé
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
        // Tout est bon : les deux ajouts réussissent ensemble ou pas du tout
        $pdo->beginTransaction();

        // Crée le personnel et récupère son numéro
        $requete = $pdo->prepare('INSERT INTO Personnel (nom, prenom, mail, date_arrivee) VALUES (?, ?, ?, ?) RETURNING id');
        $requete->execute([$nomSaisi, $prenomSaisi, $mailSaisi, $dateArriveeSaisie]);
        $idPersonnel = $requete->fetchColumn();

        // Crée son premier statut (la quotité passe de 80 à 0.8)
        $requete = $pdo->prepare('INSERT INTO Personnel_Statut_Historique (id_personnel, statut, quotite_recherche, date_debut) VALUES (?, ?, ?, ?)');
        $requete->execute([$idPersonnel, $statutSaisi, $quotite / 100, $dateArriveeSaisie]);

        // Valide les deux ajouts
        $pdo->commit();

        // Envoie vers la page pour lui ajouter un rattachement
        header('Location: ajouterRattachement.php?id=' . $idPersonnel);
        exit;
    }
}