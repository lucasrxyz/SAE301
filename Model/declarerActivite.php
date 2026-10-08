<?php
require_once __DIR__ . '/config.php';

$erreurActivite = '';
$activiteEnregistree = isset($_GET['enregistree']);
$idPersonnel = $_SESSION['id_personnel'];

$requete = $pdo->query('SELECT id_type, categorie, nom_type FROM TypeActivite WHERE est_collective = FALSE ORDER BY categorie, nom_type');
$typesIndividuels = $requete->fetchAll(PDO::FETCH_ASSOC);

$typeactivite = '';
$titre = '';
$datedebut = '';
$datefin = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $typeactivite = $_POST['id_type'] ?? '';
    $titre = trim($_POST['titre'] ?? '');
    $datedebut = $_POST['date_debut'] ?? '';
    $datefin = $_POST['date_fin'] ?? '';

    $typeValide = in_array($typeactivite, array_column($typesIndividuels, 'id_type'));

    if (!$typeValide) {
        $erreurActivite = 'Veuillez choisir un type d\'activité individuelle.';
    } elseif ($titre === '' || $datedebut === '') {
        $erreurActivite = 'Le titre et la date de début sont obligatoires.';
    } elseif ($datefin !== '' && $datefin < $datedebut) {
        $erreurActivite = 'La date de fin doit être après la date de début.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO Activite (id_type, id_personnel, titre, date_debut, date_fin) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$typeactivite, $idPersonnel, $titre, $datedebut, $datefin === '' ? null : $datefin]);

        header('Location: declarerActivite.php?enregistree=1');
        exit;
    }
}

$requete = $pdo->prepare(
    'SELECT a.titre, a.date_debut, a.date_fin, t.categorie, t.nom_type
    FROM Activite a
    JOIN TypeActivite t ON t.id_type = a.id_type
    WHERE a.id_personnel = ?
    ORDER BY a.date_debut DESC'
);
$requete->execute([$idPersonnel]);
$mesActivites = $requete->fetchAll(PDO::FETCH_ASSOC);