<?php
// Vérifie que l'utilisateur est connecté
require __DIR__ . '/verifierconnexion.php';
require_once __DIR__ . '/../Model/Personnel.php';
require_once __DIR__ . '/../Model/Organisation.php';

// Vérifie le droit de déclarer des activités
Droits::exiger('declarer_activite');

$activiteModele = new Activite($pdo);
$personnelModele = new Personnel($pdo);
$organisationModele = new Organisation($pdo);

// Messages et personne connectée
$erreurActivite = '';
$activiteEnregistree = isset($_GET['enregistree']);
$idPersonnel = $_SESSION['id_personnel'];
// Individuelle ou collective : choisi avec les boutons en haut de la page
$mode = $_POST['mode'] ?? '';
if ($mode !== 'individuelle' && $mode !== 'collective') {
    $mode = '';
}

// Types d'activité et collaborateurs possibles pour le formulaire
$tousLesTypes = $activiteModele->types();
$listePersonnels = $personnelModele->collaborateursPossibles($idPersonnel);

// Valeurs du formulaire : vides à l'ouverture, gardées quand on filtre ou en cas d'erreur
$typeactivite = $_POST['id_type'] ?? '';
$titre = trim($_POST['titre'] ?? '');
$datedebut = $_POST['date_debut'] ?? '';
$datefin = $_POST['date_fin'] ?? '';
$enCours = isset($_POST['en_cours']);
$description = trim($_POST['description'] ?? '');
$collaborateurs = $_POST['collaborateurs'] ?? [];

// Filtres de la liste des collaborateurs
$filtreNom = trim($_POST['filtre_nom'] ?? '');
$filtreDepartement = $_POST['filtre_departement'] ?? '';
$filtreGroupe = $_POST['filtre_groupe'] ?? '';

// Départements et groupes encore ouverts, pour les listes de filtres
$departementsFiltre = $organisationModele->departementsOuverts();
$groupesFiltre = $organisationModele->groupesOuverts();

// Collaborateurs qui correspondent aux filtres
$collaborateursFiltres = $personnelModele->collaborateursFiltres($idPersonnel, $filtreNom, $filtreDepartement, $filtreGroupe);

if (isset($_POST['enregistrer'])) {
    // Sans date de fin, la fin vaut le début ; « en cours » veut dire pas de fin
    $dateFinEnregistree = $datefin === '' ? $datedebut : $datefin;
    if ($enCours) {
        $dateFinEnregistree = null;
    }

    // Le type doit être dans la liste
    $typeValide = in_array($typeactivite, array_column($tousLesTypes, 'id_type'));

    // Chaque collaborateur doit être dans la liste
    $collaborateurInvalide = false;
    foreach ($collaborateurs as $idCollaborateur) {
        if (!in_array($idCollaborateur, array_column($listePersonnels, 'id'))) {
            $collaborateurInvalide = true;
        }
    }

    // Contrôle les données : on garde le premier message d'erreur trouvé
    if ($mode === '') {
        $erreurActivite = 'Veuillez choisir une activité individuelle ou collective.';
    } elseif (!$typeValide) {
        $erreurActivite = 'Veuillez choisir un type d\'activité.';
    } elseif ($titre === '' || $datedebut === '') {
        $erreurActivite = 'Le titre et la date de début sont obligatoires.';
    } elseif (!$enCours && $datefin !== '' && $datefin < $datedebut) {
        $erreurActivite = 'La date de fin doit être après la date de début.';
    } elseif ($mode === 'collective' && count($collaborateurs) === 0) {
        $erreurActivite = 'Veuillez choisir au moins un collaborateur.';
    } elseif ($mode === 'collective' && $collaborateurInvalide) {
        $erreurActivite = 'Un collaborateur choisi n\'existe pas.';
    } else {
        if ($mode === 'individuelle') {
            $activiteModele->declarerIndividuelle($typeactivite, $idPersonnel, $titre, $description, $datedebut, $dateFinEnregistree);
        } else {
            $activiteModele->declarerCollective($typeactivite, $idPersonnel, $titre, $description, $datedebut, $dateFinEnregistree, $collaborateurs);
        }

        // Recharge la page avec le message de réussite
        header('Location: declarerActivite.php?enregistree=1');
        exit;
    }
}

// Filtres et tri de la liste « Mes activités »
$recherche = trim($_GET['recherche'] ?? '');
$filtreType = $_GET['type'] ?? '';
$filtreEtat = $_GET['etat'] ?? '';
$filtreDebut = $_GET['debut'] ?? '';
$filtreFin = $_GET['fin'] ?? '';
$tri = $_GET['tri'] ?? 'date';
$ordre = ($_GET['ordre'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

// Seules les colonnes de tri prévues sont acceptées
if (!isset(Activite::COLONNES_TRI[$tri])) {
    $tri = 'date';
}

$mesActivites = $activiteModele->mesActivites($idPersonnel, $recherche, $filtreType, $filtreEtat, $filtreDebut, $filtreFin, $tri, $ordre);

// Prépare les liens de tri, qui gardent les filtres en cours
$filtresUrl = http_build_query([
    'recherche' => $recherche,
    'type' => $filtreType,
    'etat' => $filtreEtat,
    'debut' => $filtreDebut,
    'fin' => $filtreFin
]);
$ordreInverse = $ordre === 'asc' ? 'desc' : 'asc';
$filtreActif = $recherche !== '' || $filtreType !== '' || $filtreEtat !== '' || $filtreDebut !== '' || $filtreFin !== '';

require __DIR__ . '/../Vue/declarerActivite.php';
