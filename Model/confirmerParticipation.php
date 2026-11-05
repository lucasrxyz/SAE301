<?php
    // Connexion à la base de données
    require_once __DIR__ . '/config.php';

    // Vérifie le droit de déclarer des activités
    exigerDroit('declarer_activite');

    // La personne connectée, et le message à afficher selon l'adresse
    $idPersonnel = $_SESSION['id_personnel'];
    $reponse = $_GET['reponse'] ?? '';

    // Le code suivant s'exécute seulement quand on clique sur Confirmer ou Refuser
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Récupère l'activité et la réponse
        $idActivite = (int) ($_POST['id_activite'] ?? 0);
        $confirme = isset($_POST['confirmer']) ? 'true' : 'false';

        // Enregistre la réponse : seulement sa propre ligne, et seulement si elle est en attente
        $requete = $pdo->prepare('UPDATE Activite SET confirme = ?
            WHERE id_activite = ? AND id_personnel = ? AND id_activite_principale IS NOT NULL AND confirme IS NULL');
        $requete->execute([$confirme, $idActivite, $idPersonnel]);

        // Recharge la page avec le message de réussite
        header('Location: mesParticipations.php?reponse=' . ($confirme === 'true' ? 'confirmee' : 'refusee'));
        exit;
    }

    // Mes participations, avec le porteur de chaque activité (en attente d'abord)
    $requete = $pdo->prepare('SELECT Activite.id_activite, Activite.titre, Activite.role, Activite.confirme,
        Activite.date_debut, Activite.date_fin, TypeActivite.nom_type, Porteur.prenom, Porteur.nom
        FROM Activite
        JOIN TypeActivite ON TypeActivite.id_type = Activite.id_type
        JOIN Activite Principale ON Principale.id_activite = Activite.id_activite_principale
        JOIN Personnel Porteur ON Porteur.id = Principale.id_personnel
        WHERE Activite.id_personnel = ?
        ORDER BY Activite.confirme IS NOT NULL, Activite.date_debut DESC');
    $requete->execute([$idPersonnel]);
    $participations = $requete->fetchAll(PDO::FETCH_ASSOC);

    // Réponses des participants aux activités dont je suis le porteur
    $requete = $pdo->prepare('SELECT Activite.titre, Activite.role, Activite.confirme, Personnel.prenom, Personnel.nom
        FROM Activite
        JOIN Activite Principale ON Principale.id_activite = Activite.id_activite_principale
        JOIN Personnel ON Personnel.id = Activite.id_personnel
        WHERE Principale.id_personnel = ?
        ORDER BY Activite.titre, Personnel.nom, Personnel.prenom');
    $requete->execute([$idPersonnel]);
    $reponsesParticipants = $requete->fetchAll(PDO::FETCH_ASSOC);
