<?php
// Connexion à la base de données
require_once __DIR__ . '/config.php';
// Liste des statuts autorisés
require_once __DIR__ . '/statuts.php';

// Message d'erreur vide au départ, et message de réussite si l'adresse contient statut_change
$erreurStatut = '';
$statutChange = isset($_GET['statut_change']);

// S'exécute seulement si on a envoyé ce formulaire et si on a le droit de gérer le personnel
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['changer_statut']) && verifierDroit('gerer_personnel')) {
    // Récupère ce que l'utilisateur a saisi
    $idPersonnel = (int) ($_POST['id_personnel'] ?? 0);
    $nouveauStatut = $_POST['nouveau_statut'] ?? '';
    $nouvelleQuotite = $_POST['nouvelle_quotite'] ?? '';
    $dateEffet = $_POST['date_effet'] ?? '';

    // Cherche la date de début du statut actuel (celui qui n'a pas de date de fin)
    $requete = $pdo->prepare('SELECT date_debut FROM Personnel_Statut_Historique WHERE id_personnel = ? AND date_fin IS NULL');
    $requete->execute([$idPersonnel]);
    $debutStatutActuel = $requete->fetchColumn();

    // Transforme le texte en date, pour vérifier qu'elle existe vraiment
    $date = DateTime::createFromFormat('!Y-m-d', $dateEffet);

    // Contrôle les données : on garde le premier message d'erreur trouvé
    if (!in_array($nouveauStatut, $statutsPossibles)) {
        $erreurStatut = 'Veuillez choisir un statut de la liste.';
    } elseif (!is_numeric($nouvelleQuotite) || $nouvelleQuotite < 0 || $nouvelleQuotite > 100) {
        $erreurStatut = 'La quotité de recherche doit être comprise entre 0 et 100 %.';
    } elseif (!$date || $date->format('Y-m-d') !== $dateEffet) {
        $erreurStatut = 'Veuillez saisir une date d\'effet valide.';
    } elseif ($debutStatutActuel && $dateEffet <= $debutStatutActuel) {
        $erreurStatut = 'La date d\'effet doit être après le début du statut actuel (' . date('d/m/Y', strtotime($debutStatutActuel)) . ').';
    } else {
        // Tout est bon : les deux écritures réussissent ensemble ou pas du tout
        $pdo->beginTransaction();

        // Ferme l'ancien statut la veille du nouveau
        $requete = $pdo->prepare("UPDATE Personnel_Statut_Historique SET date_fin = ?::date - 1 WHERE id_personnel = ? AND date_fin IS NULL");
        $requete->execute([$dateEffet, $idPersonnel]);

        // Ajoute le nouveau statut (la quotité passe de 80 à 0.8)
        $requete = $pdo->prepare('INSERT INTO Personnel_Statut_Historique (id_personnel, statut, quotite_recherche, date_debut) VALUES (?, ?, ?, ?)');
        $requete->execute([$idPersonnel, $nouveauStatut, $nouvelleQuotite / 100, $dateEffet]);

        // Valide les deux écritures
        $pdo->commit();

        // Recharge la fiche avec le message de réussite
        header('Location: ficheDetailleePersonnel.php?id=' . $idPersonnel . '&statut_change=1');
        exit;
    }
}