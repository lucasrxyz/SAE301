<?php
    require_once __DIR__ . '/config.php';

    exigerDroit('declarer_activite');

    $idPersonnel = $_SESSION['id_personnel'];
    $reponse = $_GET['reponse'] ?? '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $idActivite = (int) ($_POST['id_activite'] ?? 0);
        $confirme = isset($_POST['confirmer']) ? 'true' : 'false';

        $requete = $pdo->prepare('UPDATE Activite SET confirme = ?
            WHERE id_activite = ? AND id_personnel = ? AND id_activite_principale IS NOT NULL AND confirme IS NULL');
        $requete->execute([$confirme, $idActivite, $idPersonnel]);

        header('Location: mesParticipations.php?reponse=' . ($confirme === 'true' ? 'confirmee' : 'refusee'));
        exit;
    }

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

    $requete = $pdo->prepare('SELECT Activite.titre, Activite.role, Activite.confirme, Personnel.prenom, Personnel.nom
        FROM Activite
        JOIN Activite Principale ON Principale.id_activite = Activite.id_activite_principale
        JOIN Personnel ON Personnel.id = Activite.id_personnel
        WHERE Principale.id_personnel = ?
        ORDER BY Activite.titre, Personnel.nom, Personnel.prenom');
    $requete->execute([$idPersonnel]);
    $reponsesParticipants = $requete->fetchAll(PDO::FETCH_ASSOC);
