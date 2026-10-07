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
            require_once __DIR__ . '/config.php';

            $requete = $pdo->prepare(
                'SELECT id_compte, id_personnel, identifiant, mot_de_passe_hash
                FROM Compte
                WHERE identifiant = :identifiant AND actif = TRUE'
            );
            $requete->execute(['identifiant' => $identifiant]);
            $compte = $requete->fetch(PDO::FETCH_ASSOC);

            if ($compte && password_verify($motDePasse, $compte['mot_de_passe_hash'])) {
                $requete = $pdo->prepare(
                    'SELECT r.nom_role, r.niveau_pyramidal
                    FROM Compte_Role cr
                    JOIN Role r ON r.id_role = cr.id_role
                    WHERE cr.id_compte = :idCompte
                    ORDER BY r.niveau_pyramidal'
                );
                $requete->execute(['idCompte' => $compte['id_compte']]);
                $roles = $requete->fetchAll(PDO::FETCH_ASSOC);

                session_regenerate_id(true);
                $_SESSION['id_compte'] = $compte['id_compte'];
                $_SESSION['id_personnel'] = $compte['id_personnel'];
                $_SESSION['identifiant'] = $compte['identifiant'];
                $_SESSION['roles'] = array_column($roles, 'nom_role');
                $_SESSION['niveau'] = $roles ? $roles[0]['niveau_pyramidal'] : 4;

                header('Location: afficherListePersonnel.php');
                exit;
            }

            $erreur = 'Identifiant ou mot de passe incorrect.';
        } catch (PDOException $exception) {
            $erreur = 'La connexion est momentanément indisponible. Réessayez plus tard.';
        }
    }
}