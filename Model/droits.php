<?php
// Répond vrai ou faux : la personne connectée a-t-elle le droit de faire cette action ?
function verifierDroit($action)
{
    // Les rôles et le niveau viennent de la session (0 = le plus haut)
    $roles = $_SESSION['roles'] ?? [];
    $niveau = $_SESSION['niveau'] ?? null;

    // Pas de rôle : aucun droit
    if ($niveau === null) {
        return false;
    }

    // Déclarer une activité et voir l'organisation : tout le monde
    if ($action === 'declarer_activite' || $action === 'voir_organisation') {
        return $niveau <= 4;
    }

    // Tableau de bord : tout le monde
    if ($action === 'voir_tableau_bord') {
        return $niveau <= 4;
    }

    // Liste du personnel : à partir du niveau responsable
    if ($action === 'voir_personnel') {
        return $niveau <= 3;
    }
    
    // Contenu scientifique : jamais pour le gestionnaire RH
    if ($action === 'voir_contenu_scientifique') {
        return $niveau <= 1 || ($niveau <= 3 && !in_array('Gestionnaire RH', $roles));
    }

    // Gérer le personnel : gestionnaire RH ou administrateur
    if ($action === 'gerer_personnel') {
        return in_array('Gestionnaire RH', $roles) || $niveau == 0;
    }

    // Gérer les rôles : administrateur seulement
    if ($action === 'gerer_roles') {
        return $niveau == 0;
    }

    return false;
}

// Bloque la page : sans le droit, on renvoie vers « Accès refusé »
function exigerDroit($action)
{
    if (!verifierDroit($action)) {
        header('Location: accesRefuse.php');
        exit;
    }
}

// Choisit la page où arriver après la connexion
function pageAccueil()
{
    if (verifierDroit('voir_tableau_bord')) {
        return 'tableauDeBord.php';
    }
    if (verifierDroit('declarer_activite')) {
        return 'declarerActivite.php';
    }
    return 'accesRefuse.php';
}

// Dit ce que la personne peut voir : le laboratoire, un département, un groupe ou elle-même
function perimetreUtilisateur($pdo)
{
    $roles = $_SESSION['roles'] ?? [];
    $niveau = $_SESSION['niveau'] ?? null;

    // Direction et RH : tout le laboratoire
    if ($niveau !== null && ($niveau <= 1 || in_array('Gestionnaire RH', $roles))) {
        return ['type' => 'laboratoire', 'id' => ''];
    }

    // Personnel simple : seulement lui-même
    if ($niveau == 4) {
        return ['type' => 'personnel', 'id' => $_SESSION['id_personnel']];
    }

    // Cherche la responsabilité en cours de cette personne
    $requete = $pdo->prepare('SELECT id_departement, id_groupe FROM Rattachement
        WHERE id_personnel = ? AND responsable_groupe = TRUE
        AND date_debut <= CURRENT_DATE AND (date_fin IS NULL OR date_fin >= CURRENT_DATE)
        ORDER BY id_departement');
    $requete->execute([$_SESSION['id_personnel']]);
    $responsabilite = $requete->fetch(PDO::FETCH_ASSOC);

    // Responsable de département
    if ($responsabilite && $responsabilite['id_departement']) {
        return ['type' => 'departement', 'id' => $responsabilite['id_departement']];
    }

    // Responsable de groupe
    if ($responsabilite && $responsabilite['id_groupe']) {
        return ['type' => 'groupe', 'id' => $responsabilite['id_groupe']];
    }

    return ['type' => 'personnel', 'id' => $_SESSION['id_personnel']];
}

// Transforme le périmètre en morceau de requête SQL et en paramètres
function conditionPerimetre($perimetre)
{
    if ($perimetre['type'] === 'departement') {
        return [" AND Rattachement.date_fin IS NULL AND (Rattachement.id_departement = :perimetre OR Groupe.id_departement = :perimetre)", ['perimetre' => $perimetre['id']]];
    }

    if ($perimetre['type'] === 'groupe') {
        return [" AND Rattachement.date_fin IS NULL AND Rattachement.id_groupe = :perimetre", ['perimetre' => $perimetre['id']]];
    }

    if ($perimetre['type'] === 'personnel') {
        return [" AND Rattachement.id_personnel = :perimetre", ['perimetre' => $perimetre['id']]];
    }

    // Tout le laboratoire : aucun filtre à ajouter
    return ["", []];
}