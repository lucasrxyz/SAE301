<?php
// Vérifie que l'utilisateur est connecté
require __DIR__ . '/verifierconnexion.php';
require_once __DIR__ . '/../Model/Personnel.php';
require_once __DIR__ . '/../Model/Rattachement.php';

// Il faut le droit de voir le personnel
Droits::exiger('voir_personnel');

$personnelModele = new Personnel($pdo);
$rattachementModele = new Rattachement($pdo);
$activiteModele = new Activite($pdo);
$statutsPossibles = Personnel::STATUTS;

// Le RH et l'administrateur peuvent agir sur la fiche ; le RH ne voit pas le contenu scientifique
$estRH = Droits::verifier('gerer_personnel');
$voirContenu = Droits::verifier('voir_contenu_scientifique');

// Messages d'erreur vides, et messages de réussite selon l'adresse
$erreurDesactivation = '';
$personnelDesactive = isset($_GET['desactive']);
$erreurIdentite = '';
$messageIdentite = '';
$erreurClotureRattachement = '';
$rattachementCloture = isset($_GET['rattachement_cloture']);
$erreurStatut = '';
$statutChange = isset($_GET['statut_change']);
$erreurResponsable = '';
$responsableNomme = isset($_GET['responsable_nomme']);

if (isset($_GET['identifiants'])) {
    $messageIdentite = 'Les identifiants ORCID et IdHAL ont été mis à jour.';
}
if (isset($_GET['nomchange'])) {
    $messageIdentite = 'Le nom a été modifié. L\'ancien nom est gardé dans l\'historique.';
}

// Rattachement à clôturer et date de fin (aujourd'hui par défaut)
$idRattachementCloture = $_POST['id_rattachement'] ?? '';
$dateFinClotureSaisie = $_POST['date_fin_rattachement'] ?? date('Y-m-d');

// Formulaire « désactiver le personnel »
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $estRH && ($_POST['desactiver'] ?? null) === 'true') {
    $idPersonnel = $_POST['id_personnel'] ?? null;
    $datedepart = $_POST['date_depart'] ?? null;
    $dateMinimum = $personnelModele->dateMinimumDepart($idPersonnel);

    if (!$datedepart) {
        $erreurDesactivation = 'Veuillez saisir une date de départ.';
    } elseif ($datedepart < $dateMinimum) {
        $erreurDesactivation = 'La date de départ doit être au moins le ' . date('d/m/Y', strtotime($dateMinimum)) . '.';
    } else {
        $personnelModele->desactiver($idPersonnel, $datedepart);
        header('Location: ficheDetailleePersonnel.php?id=' . $idPersonnel . '&desactive=1');
        exit;
    }
}

// Formulaire « ORCID et IdHAL »
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $estRH && isset($_POST['modifier_identifiants'])) {
    $idPersonnel = $_POST['id_personnel'] ?? '';
    $orcid = trim($_POST['id_orcid'] ?? '');
    $idhal = trim($_POST['id_idhal'] ?? '');

    $doublonOrcid = $personnelModele->autreAvecOrcid($orcid, $idPersonnel);
    $doublonIdhal = $personnelModele->autreAvecIdhal($idhal, $idPersonnel);

    // Contrôle : forme de l'ORCID, puis doublons (les champs vides sont permis)
    if ($orcid !== '' && !preg_match('/^\d{4}-\d{4}-\d{4}-\d{3}[\dX]$/', $orcid)) {
        $erreurIdentite = 'L\'ORCID doit avoir la forme 0000-0000-0000-0000 (le dernier caractère peut être un X).';
    } elseif ($orcid !== '' && $doublonOrcid) {
        $erreurIdentite = 'Cet ORCID est déjà utilisé par ' . $doublonOrcid['prenom'] . ' ' . $doublonOrcid['nom'] . '.';
    } elseif ($idhal !== '' && $doublonIdhal) {
        $erreurIdentite = 'Cet IdHAL est déjà utilisé par ' . $doublonIdhal['prenom'] . ' ' . $doublonIdhal['nom'] . '.';
    } else {
        $personnelModele->modifierIdentifiants($idPersonnel, $orcid, $idhal);
        header('Location: ficheDetailleePersonnel.php?id=' . $idPersonnel . '&identifiants=1');
        exit;
    }
}

// Formulaire « changer le nom »
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $estRH && isset($_POST['changer_nom'])) {
    $idPersonnel = $_POST['id_personnel'] ?? '';
    $nouveauNom = trim($_POST['nouveau_nom'] ?? '');
    $dateChangement = $_POST['date_changement'] ?? '';
    $nomActuel = $personnelModele->nomActuel($idPersonnel);

    // Contrôle : champs remplis et nom vraiment différent
    if ($nouveauNom === '' || $dateChangement === '') {
        $erreurIdentite = 'Le nouveau nom et la date sont obligatoires.';
    } elseif ($nouveauNom === $nomActuel) {
        $erreurIdentite = 'Le nouveau nom est identique au nom actuel.';
    } else {
        $personnelModele->changerNom($idPersonnel, $nomActuel, $nouveauNom, $dateChangement);
        header('Location: ficheDetailleePersonnel.php?id=' . $idPersonnel . '&nomchange=1');
        exit;
    }
}

// Formulaire « clôturer un rattachement »
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cloturer_rattachement']) && $estRH) {
    $idPersonnel = (int) ($_POST['id_personnel'] ?? 0);

    // Le rattachement doit appartenir à cette personne et être encore en cours
    $rattachement = $rattachementModele->enCours((int) $idRattachementCloture, $idPersonnel);
    $date = DateTime::createFromFormat('!Y-m-d', $dateFinClotureSaisie);

    if (!$rattachement) {
        $erreurClotureRattachement = 'Ce rattachement est introuvable ou déjà clôturé.';
    } elseif (!$date || $date->format('Y-m-d') !== $dateFinClotureSaisie) {
        $erreurClotureRattachement = 'Veuillez saisir une date de fin valide.';
    } elseif ($dateFinClotureSaisie < $rattachement['date_debut']) {
        $erreurClotureRattachement = 'La date de fin ne peut pas être antérieure à la date de début.';
    } else {
        $rattachementModele->cloturer((int) $idRattachementCloture, $dateFinClotureSaisie);
        header('Location: ficheDetailleePersonnel.php?id=' . $idPersonnel . '&rattachement_cloture=1');
        exit;
    }
}

// Formulaire « changer le statut »
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['changer_statut']) && $estRH) {
    $idPersonnel = (int) ($_POST['id_personnel'] ?? 0);
    $nouveauStatut = $_POST['nouveau_statut'] ?? '';
    $nouvelleQuotite = $_POST['nouvelle_quotite'] ?? '';
    $dateEffet = $_POST['date_effet'] ?? '';

    $debutStatutActuel = $personnelModele->debutStatutActuel($idPersonnel);
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
        $personnelModele->changerStatut($idPersonnel, $nouveauStatut, $nouvelleQuotite, $dateEffet);
        header('Location: ficheDetailleePersonnel.php?id=' . $idPersonnel . '&statut_change=1');
        exit;
    }
}

// Formulaire « nommer responsable »
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nommer_responsable']) && $estRH) {
    $idPersonnel = (int) ($_POST['id_personnel'] ?? 0);
    $idRattachement = (int) ($_POST['id_rattachement'] ?? 0);
    $dateEffet = $_POST['date_nomination'] ?? '';

    // Le rattachement doit être en cours et sans responsabilité
    $rattachement = $rattachementModele->enCoursSansResponsable($idRattachement, $idPersonnel);
    $date = DateTime::createFromFormat('!Y-m-d', $dateEffet);

    if (!$rattachement) {
        $erreurResponsable = 'Ce rattachement n\'existe pas, est terminé ou a déjà un responsable.';
    } elseif (!$date || $date->format('Y-m-d') !== $dateEffet) {
        $erreurResponsable = 'Veuillez saisir une date d\'effet valide.';
    } elseif ($dateEffet <= $rattachement['date_debut']) {
        $erreurResponsable = 'La date d\'effet doit être après le début du rattachement (' . date('d/m/Y', strtotime($rattachement['date_debut'])) . ').';
    } else {
        $rattachementModele->nommerResponsable($rattachement, $idPersonnel, $dateEffet);
        header('Location: ficheDetailleePersonnel.php?id=' . $idPersonnel . '&responsable_nomme=1');
        exit;
    }
}

// Données de la fiche
$idPersonnel = $_GET['id'] ?? null;
$personnel = null;
$personnelIntrouvable = false;
$historiqueStatuts = [];
$anciensNoms = [];
$historiqueRattachements = [];
$activites = [];
$enseignements = [];

if ($idPersonnel !== null) {
    // Limite à ce que la personne connectée a le droit de voir
    $personnel = $personnelModele->trouverDansPerimetre($idPersonnel, Droits::perimetre($pdo));

    if (!$personnel) {
        $personnelIntrouvable = true;
    } else {
        $historiqueStatuts = $personnelModele->historiqueStatuts($idPersonnel);
        $anciensNoms = $personnelModele->anciensNoms($idPersonnel);
        $historiqueRattachements = $rattachementModele->historique($idPersonnel);
        $activites = $activiteModele->activitesFiche($idPersonnel);
        foreach ($activites as $numero => $activite) {
            $activites[$numero]['debut_affiche'] = formaterDate($activite['date_debut']);
            $activites[$numero]['fin_affichee'] = $activite['date_fin'] === null ? 'En cours' : formaterDate($activite['date_fin']);
        }
        $enseignements = $activiteModele->enseignementsFiche($idPersonnel);
    }
}

require __DIR__ . '/../Vue/ficheDetailleePersonnel.php';
