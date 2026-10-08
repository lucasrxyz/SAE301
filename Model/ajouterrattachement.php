<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['identifiant'])) {
    header('Location: ../Vue/connexion.php');
    exit;
}

$estAutorise = in_array('Gestionnaire RH', $_SESSION['roles'] ?? []) || in_array('Administrateur fonctionnel', $_SESSION['roles'] ?? []);

if (!$estAutorise) {
    header('Location: ../Vue/afficherListePersonnel.php');
    exit;
}

require_once __DIR__ . '/config.php';

$idPersonnel = $_POST['id_personnel'] ?? $_GET['id'] ?? '';
$personnel = null;
$erreurRattachement = '';
$rattachementAjoute = isset($_GET['ajoute']);
$idGroupeChoisi = $_POST['id_groupe'] ?? '';
$idDepartementChoisi = $_POST['id_departement'] ?? '';
$typeRattachementChoisi = $_POST['type_rattachement'] ?? 'principal';
$dateDebutSaisie = $_POST['date_debut'] ?? date('Y-m-d');
$dateFinSaisie = $_POST['date_fin'] ?? '';

if (filter_var($idPersonnel, FILTER_VALIDATE_INT) !== false && (int) $idPersonnel > 0) {
    $requete = $pdo->prepare('SELECT id, nom, prenom FROM Personnel WHERE id = ?');
    $requete->execute([$idPersonnel]);
    $personnel = $requete->fetch(PDO::FETCH_ASSOC);
}

if (!$personnel) {
    $erreurRattachement = 'Personnel introuvable.';
}

$requete = $pdo->query('SELECT id_departement, nom FROM Departement ORDER BY nom');
$departements = $requete->fetchAll(PDO::FETCH_ASSOC);

$requete = $pdo->query('SELECT g.id_groupe, g.nom AS nom_groupe, d.nom AS nom_departement FROM Groupe g JOIN Departement d ON d.id_departement = g.id_departement ORDER BY d.nom, g.nom');
$groupes = $requete->fetchAll(PDO::FETCH_ASSOC);

if ($personnel && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $dateDebut = DateTime::createFromFormat('!Y-m-d', $dateDebutSaisie);
    $dateFin = $dateFinSaisie === '' ? null : DateTime::createFromFormat('!Y-m-d', $dateFinSaisie);
    $debutValide = $dateDebut && $dateDebut->format('Y-m-d') === $dateDebutSaisie;
    $finValide = $dateFinSaisie === '' || ($dateFin && $dateFin->format('Y-m-d') === $dateFinSaisie);
    $groupeSelectionne = $idGroupeChoisi !== '';
    $departementSelectionne = $idDepartementChoisi !== '';

    if (!$debutValide || !$finValide) {
        $erreurRattachement = 'Veuillez saisir des dates valides.';
    } elseif ($dateFin && $dateFin < $dateDebut) {
        $erreurRattachement = 'La date de fin ne peut pas être antérieure à la date de début.';
    } elseif ($groupeSelectionne === $departementSelectionne) {
        $erreurRattachement = 'Choisissez soit un groupe, soit un département.';
    } elseif (!in_array($typeRattachementChoisi, ['principal', 'secondaire'], true)) {
        $erreurRattachement = 'Choisissez un type de rattachement valide.';
    } else {
        $idGroupe = null;
        $idDepartement = null;

        if ($groupeSelectionne && filter_var($idGroupeChoisi, FILTER_VALIDATE_INT) !== false && (int) $idGroupeChoisi > 0) {
            $requete = $pdo->prepare('SELECT id_groupe FROM Groupe WHERE id_groupe = ?');
            $requete->execute([$idGroupeChoisi]);
            $idGroupe = $requete->fetchColumn() ?: null;
        } elseif ($departementSelectionne && filter_var($idDepartementChoisi, FILTER_VALIDATE_INT) !== false && (int) $idDepartementChoisi > 0) {
            $requete = $pdo->prepare('SELECT id_departement FROM Departement WHERE id_departement = ?');
            $requete->execute([$idDepartementChoisi]);
            $idDepartement = $requete->fetchColumn() ?: null;
        }

        if (($groupeSelectionne && $idGroupe === null) || ($departementSelectionne && $idDepartement === null)) {
            $erreurRattachement = 'Le groupe ou le département sélectionné est invalide.';
        } else {
            try {
                $pdo->beginTransaction();
                $requete = $pdo->prepare('SELECT id FROM Personnel WHERE id = ? FOR UPDATE');
                $requete->execute([$idPersonnel]);

                if ($idGroupe !== null) {
                    $requeteChevauchement = 'SELECT COUNT(*) FROM Rattachement
                        WHERE id_personnel = ? AND id_groupe = ?
                        AND (date_fin IS NULL OR date_fin >= ?)';
                    $parametresChevauchement = [$idPersonnel, $idGroupe, $dateDebutSaisie];

                    if ($dateFinSaisie !== '') {
                        $requeteChevauchement .= ' AND date_debut <= ?';
                        $parametresChevauchement[] = $dateFinSaisie;
                    }

                    $requete = $pdo->prepare($requeteChevauchement);
                    $requete->execute($parametresChevauchement);

                    if ($requete->fetchColumn() > 0) {
                        $pdo->rollBack();
                        $erreurRattachement = 'Ce personnel possède déjà un rattachement à ce groupe sur une période qui chevauche les dates saisies.';
                    }
                }

                if ($erreurRattachement === '') {
                    $requete = $pdo->prepare(
                        'INSERT INTO Rattachement (id_personnel, id_departement, id_groupe, type_rattachement, date_debut, date_fin)
                        VALUES (?, ?, ?, ?, ?, ?)'
                    );
                    $requete->execute([$idPersonnel, $idDepartement, $idGroupe, $typeRattachementChoisi, $dateDebutSaisie, $dateFinSaisie ?: null]);
                    $pdo->commit();

                    header('Location: ajouterRattachement.php?id=' . urlencode((string) $idPersonnel) . '&ajoute=1');
                    exit;
                }
            } catch (Throwable $erreur) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $erreurRattachement = 'Une erreur est survenue lors de l’ajout du rattachement.';
            }
        }
    }
}