<?php
// Ouvre la session et charge les droits
session_start();
require_once __DIR__ . '/../Model/Droits.php';

// Déjà connecté : on va directement à la page d'accueil
if (!empty($_SESSION['identifiant'])) {
    header('Location: ' . Droits::pageAccueil());
    exit;
}

// Valeurs de départ du formulaire
$erreur = '';
$identifiant = '';

// Le code suivant s'exécute seulement quand le formulaire est envoyé
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifiant = trim($_POST['identifiant'] ?? '');
    $motDePasse = $_POST['mot_de_passe'] ?? '';

    // Les deux champs sont obligatoires
    if ($identifiant === '' || $motDePasse === '') {
        $erreur = 'Veuillez saisir votre identifiant et votre mot de passe.';
    } else {
        // Si la base ne répond pas, on passe dans le catch plus bas
        try {
            require_once __DIR__ . '/../Model/config.php';
            require_once __DIR__ . '/../Model/Compte.php';
            require_once __DIR__ . '/../Model/Personnel.php';

            $compteModele = new Compte($pdo);
            $personnelModele = new Personnel($pdo);

            // Cherche un compte actif avec cet identifiant
            $compte = $compteModele->trouverActif($identifiant);

            // Le compte existe et le mot de passe correspond à celui enregistré (crypté)
            if ($compte && password_verify($motDePasse, $compte['mot_de_passe_hash'])) {
                $roles = $compteModele->roles($compte['id_compte']);
                $personne = $personnelModele->prenomEtNom($compte['id_personnel']);

                // Change le numéro de session par sécurité
                session_regenerate_id(true);

                // Enregistre la personne connectée dans la session
                $_SESSION['id_compte'] = $compte['id_compte'];
                $_SESSION['id_personnel'] = $compte['id_personnel'];
                $_SESSION['identifiant'] = $compte['identifiant'];
                $_SESSION['roles'] = array_column($roles, 'nom_role');
                $_SESSION['niveau'] = $roles ? $roles[0]['niveau_pyramidal'] : null;
                $_SESSION['prenom'] = $personne['prenom'] ?? '';
                $_SESSION['nom'] = $personne['nom'] ?? '';

                header('Location: ' . Droits::pageAccueil());
                exit;
            }

            // Compte introuvable ou mauvais mot de passe : même message, pour ne rien révéler
            $erreur = 'Identifiant ou mot de passe incorrect.';
        } catch (PDOException $exception) {
            $erreur = 'La connexion est momentanément indisponible. Réessayez plus tard.';
        }
    }
}

require __DIR__ . '/../Vue/connexion.php';
