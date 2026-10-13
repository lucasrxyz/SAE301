<?php
function verifierDroit($action)
{
    $roles = $_SESSION['roles'] ?? [];
    $niveau = $_SESSION['niveau'] ?? null;

    if ($niveau === null) {
        return false;
    }

    if ($action === 'declarer_activite' || $action === 'voir_organisation') {
        return $niveau <= 4;
    }

    if ($action === 'voir_tableau_bord') {
        return $niveau <= 4;
    }

    if ($action === 'voir_personnel') {
        return $niveau <= 3;
    }

    if ($action === 'gerer_personnel') {
        return in_array('Gestionnaire RH', $roles) || $niveau == 0;
    }

    if ($action === 'gerer_roles') {
        return $niveau == 0;
    }

    return false;
}

function exigerDroit($action)
{
    if (!verifierDroit($action)) {
        header('Location: accesRefuse.php');
        exit;
    }
}

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

function perimetreUtilisateur($pdo)
{
    $roles = $_SESSION['roles'] ?? [];
    $niveau = $_SESSION['niveau'] ?? null;

    if ($niveau !== null && ($niveau <= 1 || in_array('Gestionnaire RH', $roles))) {
        return ['type' => 'laboratoire', 'id' => ''];
    }

    if ($niveau == 4) {
        return ['type' => 'personnel', 'id' => $_SESSION['id_personnel']];
    }

    $requete = $pdo->prepare('SELECT id_departement, id_groupe FROM Rattachement
        WHERE id_personnel = ? AND responsable_groupe = TRUE
        AND date_debut <= CURRENT_DATE AND (date_fin IS NULL OR date_fin >= CURRENT_DATE)
        ORDER BY id_departement');
    $requete->execute([$_SESSION['id_personnel']]);
    $responsabilite = $requete->fetch(PDO::FETCH_ASSOC);

    if ($responsabilite && $responsabilite['id_departement']) {
        return ['type' => 'departement', 'id' => $responsabilite['id_departement']];
    }

    if ($responsabilite && $responsabilite['id_groupe']) {
        return ['type' => 'groupe', 'id' => $responsabilite['id_groupe']];
    }

    return ['type' => 'personnel', 'id' => $_SESSION['id_personnel']];
}

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

    return ["", []];
}