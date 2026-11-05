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
        // Cherche les dates avant lesquelles la personne ne peut pas partir
        $requete = $pdo->prepare('SELECT date_arrivee FROM Personnel WHERE id = ?');
        $requete->execute([$idPersonnel]);
        $dateArrivee = $requete->fetchColumn();

        $requete = $pdo->prepare('SELECT MAX(date_debut) FROM Personnel_Statut_Historique WHERE id_personnel = ? AND date_fin IS NULL');
        $requete->execute([$idPersonnel]);
        $debutStatut = $requete->fetchColumn();

        $requete = $pdo->prepare('SELECT MAX(date_debut) FROM Rattachement WHERE id_personnel = ? AND date_fin IS NULL');
        $requete->execute([$idPersonnel]);
        $debutRattachement = $requete->fetchColumn();

        $requete = $pdo->prepare('SELECT date_activation FROM Compte WHERE id_personnel = ?');
        $requete->execute([$idPersonnel]);
        $activationCompte = $requete->fetchColumn();

        // On garde la plus récente de ces dates
        $dateMinimum = max($dateArrivee, $debutStatut, $debutRattachement, $activationCompte);

        if (!$datedepart) {
            // La date de départ est obligatoire
            $erreurDesactivation = 'Veuillez saisir une date de départ.';
        } elseif ($datedepart < $dateMinimum) {
            $erreurDesactivation = 'La date de départ doit être au moins le ' . date('d/m/Y', strtotime($dateMinimum)) . '.';
        } else {
            // Si une écriture plante, on annule tout
            $pdo->beginTransaction();

            // On ne supprime jamais : on marque la personne comme partie
            $requete = $pdo->prepare('UPDATE Personnel SET actif = false, date_depart = ? WHERE id = ?');
            $requete->execute([$datedepart, $idPersonnel]);

            // Son compte ne peut plus se connecter
            $requete = $pdo->prepare('UPDATE Compte SET actif = false, date_desactivation = ? WHERE id_personnel = ?');
            $requete->execute([$datedepart, $idPersonnel]);

            // Ferme son statut en cours à la date de départ
            $requete = $pdo->prepare('UPDATE Personnel_Statut_Historique SET date_fin = ? WHERE id_personnel = ? AND date_fin IS NULL');
            $requete->execute([$datedepart, $idPersonnel]);

            // Ferme ses rattachements en cours à la date de départ
            $requete = $pdo->prepare('UPDATE Rattachement SET date_fin = ? WHERE id_personnel = ? AND date_fin IS NULL');
            $requete->execute([$datedepart, $idPersonnel]);

            $pdo->commit();

            // Recharge la fiche avec le message de réussite
            header('Location: ficheDetailleePersonnel.php?id=' . $idPersonnel . '&desactive=1');
            exit;
        }
    }
}
