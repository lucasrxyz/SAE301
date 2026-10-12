<?php
$grilleDroits = [
    'declarer_activite' => 4,
    'voir_organisation' => 4,
    'voir_tableau_bord' => 3,
    'voir_personnel' => 3,
    'gerer_personnel' => 'rh',
    'gerer_roles' => 0
];

function verifierDroit($action)
{
    global $grilleDroits;

    $roles = $_SESSION['roles'] ?? [];
    $niveau = $_SESSION['niveau'] ?? null;

    if (empty($roles) || $niveau === null || !isset($grilleDroits[$action])) {
        return false;
    }

    if ($grilleDroits[$action] === 'rh') {
        return in_array('Gestionnaire RH', $roles) || $niveau == 0;
    }

    return $niveau <= $grilleDroits[$action];
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

    if (in_array('Responsable de département', $roles)) {
        $requete = $pdo->prepare('SELECT id_departement FROM Rattachement
            WHERE id_personnel = ? AND responsable_groupe = TRUE AND id_departement IS NOT NULL AND date_fin IS NULL');
        $requete->execute([$_SESSION['id_personnel']]);
        $idDepartement = $requete->fetchColumn();
        if ($idDepartement) {
            return ['type' => 'departement', 'id' => $idDepartement];
        }
    }

    if (in_array('Responsable de groupe', $roles)) {
        $requete = $pdo->prepare('SELECT id_groupe FROM Rattachement
            WHERE id_personnel = ? AND responsable_groupe = TRUE AND id_groupe IS NOT NULL AND date_fin IS NULL');
        $requete->execute([$_SESSION['id_personnel']]);
        $idGroupe = $requete->fetchColumn();
        if ($idGroupe) {
            return ['type' => 'groupe', 'id' => $idGroupe];
        }
    }

    return ['type' => 'aucun', 'id' => ''];
}

function conditionPerimetre($perimetre)
{
    if ($perimetre['type'] === 'departement') {
        return [" AND Rattachement.date_fin IS NULL AND (Rattachement.id_departement = :perimetre OR Groupe.id_departement = :perimetre)", ['perimetre' => $perimetre['id']]];
    }
    if ($perimetre['type'] === 'groupe') {
        return [" AND Rattachement.date_fin IS NULL AND Rattachement.id_groupe = :perimetre", ['perimetre' => $perimetre['id']]];
    }
    if ($perimetre['type'] === 'aucun') {
        return [" AND FALSE", []];
    }
    return ["", []];
}