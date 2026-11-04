<?php
require_once __DIR__ . '/config.php';

$erreurResponsable = '';
$responsableNomme = isset($_GET['responsable_nomme']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nommer_responsable']) && verifierDroit('gerer_personnel')) {
    $idPersonnel = (int) ($_POST['id_personnel'] ?? 0);
    $idRattachement = (int) ($_POST['id_rattachement'] ?? 0);
    $dateEffet = $_POST['date_nomination'] ?? '';

    $requete = $pdo->prepare('SELECT * FROM Rattachement WHERE id = ? AND id_personnel = ? AND date_fin IS NULL AND responsable_groupe = FALSE');
    $requete->execute([$idRattachement, $idPersonnel]);
    $rattachement = $requete->fetch();

    $date = DateTime::createFromFormat('!Y-m-d', $dateEffet);

    if (!$rattachement) {
        $erreurResponsable = 'Ce rattachement n\'existe pas, est terminé ou a déjà un responsable.';
    } elseif (!$date || $date->format('Y-m-d') !== $dateEffet) {
        $erreurResponsable = 'Veuillez saisir une date d\'effet valide.';
    } elseif ($dateEffet <= $rattachement['date_debut']) {
        $erreurResponsable = 'La date d\'effet doit être après le début du rattachement (' . date('d/m/Y', strtotime($rattachement['date_debut'])) . ').';
    } else {
        $pdo->beginTransaction();

        $requete = $pdo->prepare("UPDATE Rattachement SET date_fin = ?::date - 1 WHERE id = ?");
        $requete->execute([$dateEffet, $idRattachement]);

        $requete = $pdo->prepare('INSERT INTO Rattachement (id_personnel, id_departement, id_groupe, type_rattachement, responsable_groupe, date_debut) VALUES (?, ?, ?, ?, TRUE, ?)');
        $requete->execute([$idPersonnel, $rattachement['id_departement'], $rattachement['id_groupe'], $rattachement['type_rattachement'], $dateEffet]);

        $nomRole = $rattachement['id_groupe'] ? 'Responsable de groupe' : 'Responsable de département';

        $requete = $pdo->prepare('SELECT id_compte FROM Compte WHERE id_personnel = ?');
        $requete->execute([$idPersonnel]);
        $idCompte = $requete->fetchColumn();

        $requete = $pdo->prepare('SELECT id_role FROM Role WHERE nom_role = ?');
        $requete->execute([$nomRole]);
        $idRole = $requete->fetchColumn();

        if ($idCompte && $idRole) {
            $requete = $pdo->prepare('INSERT INTO Compte_Role (id_compte, id_role) VALUES (?, ?) ON CONFLICT DO NOTHING');
            $requete->execute([$idCompte, $idRole]);
        }

        $pdo->commit();

        header('Location: ficheDetailleePersonnel.php?id=' . $idPersonnel . '&responsable_nomme=1');
        exit;
    }
}