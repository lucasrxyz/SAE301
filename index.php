<?php
// Routeur : toutes les pages passent par index.php?page=...
// Il regarde la page demandée et appelle le présentateur correspondant
$page = $_GET['page'] ?? null;

// Aucune page demandée : accueil (connexion ou page d'arrivée selon le rôle)
if ($page === null) {
    require __DIR__ . '/Presentateur/accueil.php';
    exit;
}

switch ($page) {
    case 'connexion':
        require __DIR__ . '/Presentateur/connexion.php';
        break;

    case 'deconnexion':
        require __DIR__ . '/Presentateur/deconnexion.php';
        break;

    case 'accesRefuse':
        require __DIR__ . '/Presentateur/accesRefuse.php';
        break;

    case 'tableauDeBord':
        require __DIR__ . '/Presentateur/tableauDeBord.php';
        break;

    case 'afficherListePersonnel':
        require __DIR__ . '/Presentateur/afficherListePersonnel.php';
        break;

    case 'ficheDetailleePersonnel':
        require __DIR__ . '/Presentateur/ficheDetailleePersonnel.php';
        break;

    case 'ajouterpersonnel':
        require __DIR__ . '/Presentateur/ajouterpersonnel.php';
        break;

    case 'modifierPersonnel':
        require __DIR__ . '/Presentateur/modifierPersonnel.php';
        break;

    case 'ajouterRattachement':
        require __DIR__ . '/Presentateur/ajouterRattachement.php';
        break;

    case 'organisation':
        require __DIR__ . '/Presentateur/organisation.php';
        break;

    case 'modifierOrganisationFormulaire':
        require __DIR__ . '/Presentateur/modifierOrganisationFormulaire.php';
        break;

    case 'afficherGroupeParDate':
        require __DIR__ . '/Presentateur/afficherGroupeParDate.php';
        break;

    case 'declarerActivite':
        require __DIR__ . '/Presentateur/declarerActivite.php';
        break;

    case 'mesParticipations':
        require __DIR__ . '/Presentateur/mesParticipations.php';
        break;

    case 'genererRapport':
        require __DIR__ . '/Presentateur/genererRapport.php';
        break;

    case 'importerEnseignement':
        require __DIR__ . '/Presentateur/importerEnseignement.php';
        break;

    case 'importerPublications':
        require __DIR__ . '/Presentateur/importerPublications.php';
        break;

    case 'creerCompte':
        require __DIR__ . '/Presentateur/creerCompte.php';
        break;

    case 'gererRoles':
        require __DIR__ . '/Presentateur/gererRoles.php';
        break;

    // Dans tous les autres cas, erreur
    default:
        http_response_code(404);
        echo 'Page non trouvée';
        break;
}
