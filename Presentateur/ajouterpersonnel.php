<?php
// Vérifie que l'utilisateur est connecté
require __DIR__ . '/verifierconnexion.php';
require_once __DIR__ . '/../Model/Personnel.php';

// Seuls le RH et l'administrateur peuvent ajouter un personnel
Droits::exiger('gerer_personnel');

$personnelModele = new Personnel($pdo);
$statutsPossibles = Personnel::STATUTS;

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

    // Contrôle les données : on garde le premier message d'erreur trouvé
    if (in_array('', [$nomSaisi, $prenomSaisi, $mailSaisi, $dateArriveeSaisie, $statutSaisi, $quotiteSaisie])) {
        $erreurPersonnel = 'Veuillez remplir tous les champs.';
    } elseif (strlen($nomSaisi) > 50 || strlen($prenomSaisi) > 50 || strlen($mailSaisi) > 60) {
        $erreurPersonnel = 'Un ou plusieurs champs dépassent la longueur autorisée.';
    } elseif (!in_array($statutSaisi, $statutsPossibles)) {
        $erreurPersonnel = 'Veuillez choisir un statut dans la liste.';
    } elseif (!filter_var($mailSaisi, FILTER_VALIDATE_EMAIL)) {
        $erreurPersonnel = 'Veuillez saisir une adresse mail valide.';
    } elseif ($personnelModele->mailUtilise($mailSaisi, 0)) {
        $erreurPersonnel = 'Cette adresse mail est déjà utilisée.';
    } elseif (!$dateValide) {
        $erreurPersonnel = 'Veuillez saisir une date d\'arrivée valide.';
    } elseif ($quotite === false || $quotite < 0 || $quotite > 100) {
        $erreurPersonnel = 'La quotité doit être comprise entre 0 et 100 %.';
    } else {
        $idPersonnel = $personnelModele->ajouter($nomSaisi, $prenomSaisi, $mailSaisi, $dateArriveeSaisie, $statutSaisi, $quotite);

        // Envoie vers la page pour lui ajouter un rattachement
        header('Location: index.php?page=ajouterRattachement&id=' . $idPersonnel);
        exit;
    }
}

require __DIR__ . '/../Vue/ajouterpersonnel.php';
