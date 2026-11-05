<?php
// Connexion à la base de données
require_once __DIR__ . '/config.php';

// Message d'erreur vide au départ, et message de réussite si l'adresse contient rattachement_cloture
$erreurClotureRattachement = '';
$rattachementCloture = isset($_GET['rattachement_cloture']);
// Rattachement à clôturer et date de fin (aujourd'hui par défaut)
$idRattachementCloture = $_POST['id_rattachement'] ?? '';
$dateFinClotureSaisie = $_POST['date_fin_rattachement'] ?? date('Y-m-d');

// S'exécute seulement si on a envoyé ce formulaire et si on a le droit de gérer le personnel
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cloturer_rattachement']) && verifierDroit('gerer_personnel')) {
    // Récupère le personnel concerné
    $idPersonnel = (int) ($_POST['id_personnel'] ?? 0);

    // Cherche ce rattachement : il doit appartenir à cette personne et être encore en cours
    $requete = $pdo->prepare('SELECT date_debut FROM Rattachement WHERE id = ? AND id_personnel = ? AND date_fin IS NULL');
    $requete->execute([(int) $idRattachementCloture, $idPersonnel]);
    $rattachement = $requete->fetch();

    // Transforme le texte en date, pour vérifier qu'elle existe vraiment
    $date = DateTime::createFromFormat('!Y-m-d', $dateFinClotureSaisie);

    // Contrôle les données : on garde le premier message d'erreur trouvé
    if (!$rattachement) {
        $erreurClotureRattachement = 'Ce rattachement est introuvable ou déjà clôturé.';
    } elseif (!$date || $date->format('Y-m-d') !== $dateFinClotureSaisie) {
        $erreurClotureRattachement = 'Veuillez saisir une date de fin valide.';
    } elseif ($dateFinClotureSaisie < $rattachement['date_debut']) {
        $erreurClotureRattachement = 'La date de fin ne peut pas être antérieure à la date de début.';
    } else {
        // Tout est bon : on met la date de fin sur le rattachement
        $requete = $pdo->prepare('UPDATE Rattachement SET date_fin = ? WHERE id = ?');
        $requete->execute([$dateFinClotureSaisie, (int) $idRattachementCloture]);

        // Recharge la fiche avec le message de réussite
        header('Location: ficheDetailleePersonnel.php?id=' . $idPersonnel . '&rattachement_cloture=1');
        exit;
    }
}