<?php
// Vérifie que l'utilisateur est connecté
require __DIR__ . '/verifierconnexion.php';

// Vérifie le droit de déclarer des activités
Droits::exiger('declarer_activite');

$activiteModele = new Activite($pdo);

// La personne connectée, et le message à afficher selon l'adresse
$idPersonnel = $_SESSION['id_personnel'];
$reponse = $_GET['reponse'] ?? '';

// Le code suivant s'exécute seulement quand on clique sur Confirmer ou Refuser
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idActivite = (int) ($_POST['id_activite'] ?? 0);
    $confirme = isset($_POST['confirmer']) ? 'true' : 'false';

    $activiteModele->repondre($idActivite, $idPersonnel, $confirme);

    // Recharge la page avec le message de réussite
    header('Location: mesParticipations.php?reponse=' . ($confirme === 'true' ? 'confirmee' : 'refusee'));
    exit;
}

// Mes participations, et les réponses aux activités dont je suis le porteur
$participations = $activiteModele->participations($idPersonnel);
foreach ($participations as $numero => $participation) {
    $participations[$numero]['debut_affiche'] = formaterDate($participation['date_debut']);
    $participations[$numero]['fin_affichee'] = $participation['date_fin'] === null ? '—' : formaterDate($participation['date_fin']);
}
$reponsesParticipants = $activiteModele->reponsesParticipants($idPersonnel);

require __DIR__ . '/../Vue/mesParticipations.php';
