<?php
session_start();

if (!empty($_SESSION['identifiant'])) {
    header('Location: afficherListePersonnel.php');
    exit;
}

$erreur = '';
$identifiant = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifiant = trim($_POST['identifiant'] ?? '');
    $motDePasse = $_POST['mot_de_passe'] ?? '';

    if ($identifiant === '' || $motDePasse === '') {
        $erreur = 'Veuillez saisir votre identifiant et votre mot de passe.';
    } else {
        try {
            require_once __DIR__ . '/../Model/config.php';
            $requete = $pdo->prepare(
                'SELECT id_compte, identifiant, mot_de_passe_hash
                 FROM Compte
                                 WHERE identifiant = :identifiant'
            );
            $requete->execute(['identifiant' => $identifiant]);
            $compte = $requete->fetch(PDO::FETCH_ASSOC);

            if ($compte && password_verify($motDePasse, $compte['mot_de_passe_hash'])) {
                session_regenerate_id(true);
                $_SESSION['id_compte'] = $compte['id_compte'];
                $_SESSION['identifiant'] = $compte['identifiant'];

                header('Location: afficherListePersonnel.php');
                exit;
            }

            $erreur = 'Identifiant ou mot de passe incorrect.';
        } catch (PDOException $exception) {
            $erreur = 'La connexion est momentanément indisponible. Réessayez plus tard.';
        }
    }
}
?>