<?php
require_once __DIR__ . '/config.php';

$erreurClotureRattachement = '';
$rattachementCloture = isset($_GET['rattachement_cloture']);
$idRattachementCloture = $_POST['id_rattachement'] ?? '';
$dateFinClotureSaisie = $_POST['date_fin_rattachement'] ?? date('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cloturer_rattachement']) && verifierDroit('gerer_personnel')) {
    $idPersonnel = (int) ($_POST['id_personnel'] ?? 0);

    $requete = $pdo->prepare('SELECT date_debut FROM Rattachement WHERE id = ? AND id_personnel = ? AND date_fin IS NULL');
    $requete->execute([(int) $idRattachementCloture, $idPersonnel]);
    $rattachement = $requete->fetch();

    $date = DateTime::createFromFormat('!Y-m-d', $dateFinClotureSaisie);

    if (!$rattachement) {
        $erreurClotureRattachement = 'Ce rattachement est introuvable ou déjà clôturé.';
    } elseif (!$date || $date->format('Y-m-d') !== $dateFinClotureSaisie) {
        $erreurClotureRattachement = 'Veuillez saisir une date de fin valide.';
    } elseif ($dateFinClotureSaisie < $rattachement['date_debut']) {
        $erreurClotureRattachement = 'La date de fin ne peut pas être antérieure à la date de début.';
    } else {
        $requete = $pdo->prepare('UPDATE Rattachement SET date_fin = ? WHERE id = ?');
        $requete->execute([$dateFinClotureSaisie, (int) $idRattachementCloture]);

        header('Location: ficheDetailleePersonnel.php?id=' . $idPersonnel . '&rattachement_cloture=1');
        exit;
    }
}