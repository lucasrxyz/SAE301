<?php
// Vérifie que l'utilisateur est connecté
require __DIR__ . '/verifierconnexion.php';
require_once __DIR__ . '/../Model/Personnel.php';
require_once __DIR__ . '/../Model/Rattachement.php';

// Seuls les utilisateurs ayant ce droit peuvent générer leur rapport
Droits::exiger('declarer_activite');

$personnelModele = new Personnel($pdo);
$rattachementModele = new Rattachement($pdo);
$activiteModele = new Activite($pdo);

// Valeurs par défaut : utilisées par la Vue au premier affichage
$erreurRapport = '';
$dateDebut = '';
$dateFin = '';

if (isset($_POST['enregistrer'])) {
    // Données du formulaire
    $idPersonnel = $_SESSION['id_personnel'];
    $dateDebut = $_POST['date_debut'];
    $dateFin = $_POST['date_fin'];
    $format = $_POST['formatRapport'];

    // Un élément est sur la période s'il commence avant la fin de la période
    // et se termine après son début (ou n'est pas terminé)
    $personnel = $personnelModele->identite($idPersonnel);
    $statuts = $personnelModele->statutsSurPeriode($idPersonnel, $dateDebut, $dateFin);
    $rattachements = $rattachementModele->surPeriode($idPersonnel, $dateDebut, $dateFin);

    // Ses propres activités et ses participations confirmées, rangées par catégorie
    $activitesParCategorie = $activiteModele->surPeriodeParCategorie($idPersonnel, $dateDebut, $dateFin);

    // Envoi du fichier selon le format choisi
    if ($format === 'word') {
        // Le gabarit HTML est envoyé avec l'extension .doc,
        // Word et LibreOffice l'ouvrent comme un document modifiable
        header('Content-Type: application/msword; charset=UTF-8');
        header('Content-Disposition: attachment; filename="rapport.doc"');
        // Ligne pour lire les accents
        echo "\xEF\xBB\xBF";

        require __DIR__ . '/../Vue/gabaritRapport.php';
        exit;
    } elseif ($format === 'csv') {
        // Une ligne par activité, lisible dans Excel
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="rapport.csv"');
        // Ligne pour lire les accents
        echo "\xEF\xBB\xBF";

        // php://output = on écrit directement dans la réponse envoyée au navigateur
        $sortie = fopen('php://output', 'w');

        // Séparateur ';' (Excel français), guillemets '"' et échappement '\\'
        fputcsv($sortie, ['Catégorie', 'Type', 'Titre', 'Date début', 'Date fin'], ';', '"', '\\');

        if ($activitesParCategorie) {
            foreach ($activitesParCategorie as $categorie => $activites) {
                foreach ($activites as $activite) {
                    fputcsv($sortie, [
                        $categorie,
                        $activite['nom_type'],
                        $activite['titre'],
                        date('d/m/Y', strtotime($activite['date_debut'])),
                        $activite['date_fin'] === null ? 'En cours' : date('d/m/Y', strtotime($activite['date_fin']))
                    ], ';', '"', '\\');
                }
            }
        } else {
            fputcsv($sortie, ['Aucune donnée sur la période.'], ';', '"', '\\');
        }

        fclose($sortie);
        exit;
    } else {
        // Format inconnu : la page se réaffiche avec ce message
        $erreurRapport = 'Veuillez choisir un format valide.';
    }
}

require __DIR__ . '/../Vue/genererRapport.php';
