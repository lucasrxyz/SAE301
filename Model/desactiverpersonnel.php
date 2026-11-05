<?php
// Connexion à la base de données
require_once __DIR__ . '/config.php';
// Message d'erreur vide, et message de réussite selon l'adresse
$erreurDesactivation = '';
$personnelDesactive = isset($_GET['desactive']);

// S'exécute seulement si un formulaire est envoyé et si on a le droit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifierDroit('gerer_personnel')) {
    $idPersonnel = $_POST['id_personnel'] ?? null;
    // Bouton « désactiver »
    if (($_POST['desactiver'] ?? null) === 'true') {
        $datedepart = $_POST['date_depart'] ?? null;
        // La date de départ est obligatoire
        if ($datedepart) {
            // On ne supprime jamais : on marque la personne comme partie
            $requete = $pdo->prepare('UPDATE Personnel SET actif = false, date_depart = ? WHERE id = ?');
            $requete->execute([$datedepart, $idPersonnel]);
            // Son compte ne peut plus se connecter
            $requete = $pdo->prepare('UPDATE Compte SET actif = false, date_desactivation = ? WHERE id_personnel = ?');
            $requete->execute([$datedepart, $idPersonnel]);
            header('Location: ficheDetailleePersonnel.php?id=' . $idPersonnel . '&desactive=1');
            exit;
        } else {
            $erreurDesactivation = 'Veuillez saisir une date de départ.';
        }
    }
}
