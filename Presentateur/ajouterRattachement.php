<?php
// Vérifie que l'utilisateur est connecté
require __DIR__ . '/verifierconnexion.php';
require_once __DIR__ . '/../Model/Personnel.php';
require_once __DIR__ . '/../Model/Organisation.php';
require_once __DIR__ . '/../Model/Rattachement.php';

// Seuls le RH et l'administrateur peuvent ajouter un rattachement
Droits::exiger('gerer_personnel');

$personnelModele = new Personnel($pdo);
$organisationModele = new Organisation($pdo);
$rattachementModele = new Rattachement($pdo);

// Le numéro du personnel vient du formulaire, sinon de l'adresse
$idPersonnel = $_POST['id_personnel'] ?? $_GET['id'] ?? '';
$erreurRattachement = '';
$rattachementAjoute = isset($_GET['ajoute']);

// Valeurs saisies, ou valeurs par défaut à l'ouverture de la page
$idGroupeChoisi = $_POST['id_groupe'] ?? '';
$idDepartementChoisi = $_POST['id_departement'] ?? '';
$typeRattachementChoisi = $_POST['type_rattachement'] ?? 'principal';
$dateDebutSaisie = $_POST['date_debut'] ?? date('Y-m-d');
$dateFinSaisie = $_POST['date_fin'] ?? '';

// Cherche le personnel en base
$personnel = $personnelModele->trouver((int) $idPersonnel);

if (!$personnel) {
    $erreurRattachement = 'Personnel introuvable.';
}

// Listes des départements et des groupes pour le formulaire
$departements = $organisationModele->departements();
$groupes = $organisationModele->groupesAvecDepartement();

// Le code suivant s'exécute seulement quand le formulaire est envoyé
if ($personnel && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Une liste vide veut dire « pas de groupe » ou « pas de département » (null en base)
    $idGroupe = null;
    if ($idGroupeChoisi !== '') {
        $idGroupe = (int) $idGroupeChoisi;
    }

    $idDepartement = null;
    if ($idDepartementChoisi !== '') {
        $idDepartement = (int) $idDepartementChoisi;
    }

    // Récupère les dates du groupe ou du département choisi
    $structure = false;

    if ($idGroupe !== null) {
        $nomStructure = 'Ce groupe';
        $structure = $organisationModele->datesGroupe($idGroupe);
    }

    if ($idDepartement !== null) {
        $nomStructure = 'Ce département';
        $structure = $organisationModele->datesDepartement($idDepartement);
    }

    // Vérifie que la date de début existe vraiment
    $dateDebut = DateTime::createFromFormat('!Y-m-d', $dateDebutSaisie);
    $debutValide = $dateDebut && $dateDebut->format('Y-m-d') === $dateDebutSaisie;

    // La date de fin peut rester vide, sinon elle doit exister vraiment
    $finValide = true;
    if ($dateFinSaisie !== '') {
        $dateFin = DateTime::createFromFormat('!Y-m-d', $dateFinSaisie);
        $finValide = $dateFin && $dateFin->format('Y-m-d') === $dateFinSaisie;
    }

    // Contrôle les données : on garde le premier message d'erreur trouvé
    if (!$debutValide || !$finValide) {
        $erreurRattachement = 'Veuillez saisir des dates valides.';
    } elseif ($dateFinSaisie !== '' && $dateFinSaisie < $dateDebutSaisie) {
        $erreurRattachement = 'La date de fin ne peut pas être antérieure à la date de début.';
    } elseif ($idGroupe === null && $idDepartement === null) {
        $erreurRattachement = 'Choisissez soit un groupe, soit un département.';
    } elseif ($idGroupe !== null && $idDepartement !== null) {
        $erreurRattachement = 'Choisissez soit un groupe, soit un département, pas les deux.';
    } elseif (!in_array($typeRattachementChoisi, ['principal', 'secondaire'])) {
        $erreurRattachement = 'Choisissez un type de rattachement valide.';
    } elseif (!$structure) {
        $erreurRattachement = 'Le groupe ou le département sélectionné est invalide.';
    } elseif ($structure['date_fin'] !== null && $structure['date_fin'] < $dateDebutSaisie) {
        $erreurRattachement = $nomStructure . ' est clôturé depuis le ' . date('d/m/Y', strtotime($structure['date_fin'])) . ', impossible d\'y rattacher quelqu\'un à cette date.';
    } elseif ($structure['date_debut'] > $dateDebutSaisie) {
        $erreurRattachement = $nomStructure . ' n\'existe qu\'à partir du ' . date('d/m/Y', strtotime($structure['date_debut'])) . ', impossible d\'y rattacher quelqu\'un avant cette date.';
    } else {
        // Une date de fin vide veut dire « sans fin » : on compare avec une date très lointaine
        $finPourComparer = $dateFinSaisie;
        if ($finPourComparer === '') {
            $finPourComparer = '9999-12-31';
        }

        if ($rattachementModele->chevauche($idPersonnel, $idGroupe, $dateDebutSaisie, $finPourComparer)) {
            $erreurRattachement = 'Ce personnel possède déjà un rattachement à ce groupe sur une période qui chevauche les dates saisies.';
        } else {
            // En base, une date de fin vide s'enregistre avec NULL (toujours en cours)
            $dateFinEnregistree = null;
            if ($dateFinSaisie !== '') {
                $dateFinEnregistree = $dateFinSaisie;
            }

            $rattachementModele->ajouter($idPersonnel, $idDepartement, $idGroupe, $typeRattachementChoisi, $dateDebutSaisie, $dateFinEnregistree);

            // Recharge la page avec le message de réussite
            header('Location: index.php?page=ajouterRattachement&id=' . $idPersonnel . '&ajoute=1');
            exit;
        }
    }
}

require __DIR__ . '/../Vue/ajouterRattachement.php';
