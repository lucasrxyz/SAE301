<?php
// Vérifie que l'utilisateur est connecté
require __DIR__ . '/verifierconnexion.php';
require_once __DIR__ . '/../Model/Organisation.php';

// Seuls le RH et l'administrateur peuvent modifier l'organisation
Droits::exiger('gerer_personnel');

$organisationModele = new Organisation($pdo);

// Action choisie dans le formulaire, et messages vides au départ
$choix_orga = $_POST['choix_orga'] ?? null;
$message = null;
$erreurOrganisation = '';

// Listes des départements et des groupes pour le formulaire
$departements = $organisationModele->departements();
$groupes = $organisationModele->groupes();

// Le code suivant s'exécute seulement quand on clique sur Valider
if (isset($_POST["valider"])) {
    // Récupère les dates saisies
    $dateDebut = $_POST["date_debut"] ?? $_POST["nouvelleDateDebut"] ?? '';
    $dateFin = $_POST["nouvelleDateFin"] ?? '';
    $dateCloture = $_POST["dateCloture"] ?? '';

    // Refuse une fin avant le début
    if ($dateFin !== '' && $dateFin < $dateDebut) {
        $erreurOrganisation = 'La date de fin ne peut pas être antérieure à la date de début.';
    } elseif ($choix_orga === 'ajouterDepartement') {
        $organisationModele->ajouterDepartement($_POST["nom"], $dateDebut);
        $message = "Département ajouté";
    } elseif ($choix_orga === 'modifierDepartement') {
        $organisationModele->modifierDepartement($_POST["id_departement"], $_POST["nouveauNomDpt"], $dateDebut, $dateFin ?: null);
        $message = "Département modifié";
    } elseif ($choix_orga === 'supprimerDepartement') {
        // Clôture : on met une date de fin, on ne supprime pas
        if ($organisationModele->cloturerDepartement($_POST["id_departement"], $dateCloture)) {
            $message = "Département clôturé";
        } else {
            $erreurOrganisation = 'La date de clôture ne peut pas être antérieure à la création du département.';
        }
    } elseif ($choix_orga === 'ajouterGroupe') {
        $organisationModele->ajouterGroupe($_POST["id_departement"], $_POST["nomGroupe"], $dateDebut);
        $message = "Groupe ajouté";
    } elseif ($choix_orga === 'modifierGroupe') {
        $organisationModele->modifierGroupe($_POST["id_groupe"], $_POST["nouveauNomGroupe"], $dateDebut, $dateFin ?: null);
        $message = "Groupe modifié";
    } elseif ($choix_orga === 'supprimerGroupe') {
        if ($organisationModele->cloturerGroupe($_POST["id_groupe"], $dateCloture)) {
            $message = "Groupe clôturé";
        } else {
            $erreurOrganisation = 'La date de clôture ne peut pas être antérieure à la création du groupe.';
        }
    }
}

require __DIR__ . '/../Vue/modifierOrganisationFormulaire.php';
