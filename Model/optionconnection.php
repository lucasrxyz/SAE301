<?php
// Ouvre la session et charge les droits
session_start();
require_once __DIR__ . '/droits.php';
// Déjà connecté : on va directement à la page d'accueil
if (!empty($_SESSION['identifiant'])) {
    header('Location: ' . pageAccueil());
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
            require_once __DIR__ . '/config.php';

            // Cherche un compte actif avec cet identifiant
            $requete = $pdo->prepare(
                'SELECT id_compte, id_personnel, identifiant, mot_de_passe_hash
                FROM Compte
                WHERE identifiant = :identifiant AND actif = TRUE'
            );
            $requete->execute(['identifiant' => $identifiant]);
            $compte = $requete->fetch(PDO::FETCH_ASSOC);

            // Le compte existe et le mot de passe correspond à celui enregistré (crypté)
            if ($compte && password_verify($motDePasse, $compte['mot_de_passe_hash'])) {
                // Récupère les rôles du compte, du plus haut au plus bas
                $requete = $pdo->prepare(
                    'SELECT r.nom_role, r.niveau_pyramidal
                    FROM Compte_Role cr
                    JOIN Role r ON r.id_role = cr.id_role
                    WHERE cr.id_compte = :idCompte
                    ORDER BY r.niveau_pyramidal'
                );
                $requete->execute(['idCompte' => $compte['id_compte']]);
                $roles = $requete->fetchAll(PDO::FETCH_ASSOC);
                // Récupère le prénom et le nom pour les afficher
                $requete = $pdo->prepare('SELECT prenom, nom FROM Personnel WHERE id = :id');
                $requete->execute(['id' => $compte['id_personnel']]);
                $personne = $requete->fetch(PDO::FETCH_ASSOC);
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
                // Envoie vers la page d'accueil
                header('Location: ' . pageAccueil());
                exit;
            }

            // Compte introuvable ou mauvais mot de passe : même message, pour ne rien révéler
            $erreur = 'Identifiant ou mot de passe incorrect.';
        } catch (PDOException $exception) {
            $erreur = 'La connexion est momentanément indisponible. Réessayez plus tard.';
        }
    }
}