<?php
require_once __DIR__ . '/config.php';

$estAutorise = in_array('Gestionnaire RH', $_SESSION['roles'] ?? []) || in_array('Administrateur fonctionnel', $_SESSION['roles'] ?? []);

if (!$estAutorise) {
    header('Location: afficherListePersonnel.php');
    exit;
}

$erreurPersonnel = '';
$personnelCree = isset($_GET['cree']);
$nomSaisi = '';
$prenomSaisi = '';
$mailSaisi = '';
$dateArriveeSaisie = '';
$statutSaisi = '';
$quotiteSaisie = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomSaisi = trim($_POST['nom'] ?? '');
    $prenomSaisi = trim($_POST['prenom'] ?? '');
    $mailSaisi = trim($_POST['mail'] ?? '');
    $dateArriveeSaisie = $_POST['date_arrivee'] ?? '';
    $statutSaisi = trim($_POST['statut'] ?? '');
    $quotiteSaisie = $_POST['quotite'] ?? '';

    $dateValide = DateTime::createFromFormat('!Y-m-d', $dateArriveeSaisie);
    $quotiteValide = filter_var($quotiteSaisie, FILTER_VALIDATE_FLOAT);

    if ($nomSaisi === '' || $prenomSaisi === '' || $mailSaisi === '' || $dateArriveeSaisie === '' || $statutSaisi === '' || $quotiteSaisie === '') {
        $erreurPersonnel = 'Veuillez remplir tous les champs.';
    } elseif (strlen($nomSaisi) > 50 || strlen($prenomSaisi) > 50 || strlen($mailSaisi) > 60 || strlen($statutSaisi) > 50) {
        $erreurPersonnel = 'Un ou plusieurs champs dépassent la longueur autorisée.';
    } elseif (!filter_var($mailSaisi, FILTER_VALIDATE_EMAIL)) {
        $erreurPersonnel = 'Veuillez saisir une adresse mail valide.';
    } elseif (!$dateValide || $dateValide->format('Y-m-d') !== $dateArriveeSaisie) {
        $erreurPersonnel = 'Veuillez saisir une date d’arrivée valide.';
    } elseif ($quotiteValide === false || $quotiteValide < 0 || $quotiteValide > 100) {
        $erreurPersonnel = 'La quotité doit être comprise entre 0 et 100 %.';
    } else {
        $requete = $pdo->prepare('SELECT COUNT(*) FROM Personnel WHERE LOWER(mail) = LOWER(?)');
        $requete->execute([$mailSaisi]);
        $mailDejaUtilise = $requete->fetchColumn() > 0;

        if ($mailDejaUtilise) {
            $erreurPersonnel = 'Cette adresse mail est déjà utilisée.';
        } else {
            try {
                $pdo->beginTransaction();

                $requete = $pdo->prepare('INSERT INTO Personnel (nom, prenom, mail, date_arrivee) VALUES (?, ?, ?, ?) RETURNING id');
                $requete->execute([$nomSaisi, $prenomSaisi, $mailSaisi, $dateArriveeSaisie]);
                $idPersonnel = $requete->fetchColumn();

                $requete = $pdo->prepare('INSERT INTO Personnel_Statut_Historique (id_personnel, statut, quotite_recherche, date_debut) VALUES (?, ?, ?, ?)');
                $requete->execute([$idPersonnel, $statutSaisi, $quotiteValide / 100, $dateArriveeSaisie]);

                $pdo->commit();

                header('Location: ajouterpersonnel.php?cree=1');
                exit;
            } catch (Throwable $erreur) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $erreurPersonnel = 'Une erreur est survenue lors de la création du personnel.';
            }
        }
    }
}