<?php
require_once __DIR__ . '/config.php';

$erreurClotureRattachement = '';
$rattachementCloture = ($_GET['rattachement_cloture'] ?? '') === '1';
$idRattachementCloture = $_POST['id_rattachement'] ?? '';
$dateFinClotureSaisie = $_POST['date_fin_rattachement'] ?? date('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cloturer_rattachement'])) {
    $estAutorise = in_array('Gestionnaire RH', $_SESSION['roles'] ?? [], true)
        || in_array('Administrateur fonctionnel', $_SESSION['roles'] ?? [], true);

    if (!$estAutorise) {
        header('Location: afficherListePersonnel.php');
        exit;
    }

    $idPersonnel = $_GET['id'] ?? '';
    $idPersonnelPost = $_POST['id_personnel'] ?? '';
    $dateFin = is_string($dateFinClotureSaisie)
        ? DateTime::createFromFormat('!Y-m-d', $dateFinClotureSaisie)
        : false;
    $dateFinValide = $dateFin && $dateFin->format('Y-m-d') === $dateFinClotureSaisie;

    if (
        filter_var($idPersonnel, FILTER_VALIDATE_INT) === false || (int) $idPersonnel <= 0
        || filter_var($idPersonnelPost, FILTER_VALIDATE_INT) === false || (int) $idPersonnelPost !== (int) $idPersonnel
        || filter_var($idRattachementCloture, FILTER_VALIDATE_INT) === false || (int) $idRattachementCloture <= 0
    ) {
        $erreurClotureRattachement = 'Le rattachement sélectionné est invalide.';
    } elseif (!$dateFinValide) {
        $erreurClotureRattachement = 'Veuillez saisir une date de fin valide.';
    } else {
        $requete = $pdo->prepare(
            'SELECT date_debut, date_fin FROM Rattachement WHERE id = ? AND id_personnel = ?'
        );
        $requete->execute([$idRattachementCloture, $idPersonnel]);
        $rattachement = $requete->fetch(PDO::FETCH_ASSOC);

        if (!$rattachement) {
            $erreurClotureRattachement = 'Le rattachement sélectionné est introuvable.';
        } elseif ($rattachement['date_fin'] !== null) {
            $erreurClotureRattachement = 'Ce rattachement est déjà clôturé.';
        } elseif ($dateFinClotureSaisie < $rattachement['date_debut']) {
            $erreurClotureRattachement = 'La date de fin ne peut pas être antérieure à la date de début.';
        } else {
            $requete = $pdo->prepare(
                'UPDATE Rattachement
                SET date_fin = ?
                WHERE id = ? AND id_personnel = ? AND date_fin IS NULL AND date_debut <= ?'
            );
            $requete->execute([
                $dateFinClotureSaisie,
                $idRattachementCloture,
                $idPersonnel,
                $dateFinClotureSaisie
            ]);

            if ($requete->rowCount() === 1) {
                header('Location: ficheDetailleePersonnel.php?id=' . urlencode((string) $idPersonnel) . '&rattachement_cloture=1');
                exit;
            }

            $erreurClotureRattachement = 'Le rattachement n’a pas pu être clôturé. Actualisez la page puis réessayez.';
        }
    }
}
