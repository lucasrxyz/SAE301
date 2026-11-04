<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/droits.php';

function decodifierValeurICS($valeur)
{
    return strtr($valeur, [
        '\\n' => "\n",
        '\\N' => "\n",
        '\\,' => ',',
        '\\;' => ';',
        '\\\\' => '\\'
    ]);
}

function convertirDateICS($valeur, $parametres)
{
    $valeur = trim($valeur);
    $zoneLocale = new DateTimeZone('Europe/Paris');

    if (preg_match('/^(\d{8})$/', $valeur, $correspondance)) {
        $date = DateTimeImmutable::createFromFormat('!Ymd', $correspondance[1], $zoneLocale);
        $erreurs = DateTimeImmutable::getLastErrors();
        if (!$date || ($erreurs !== false && ($erreurs['warning_count'] || $erreurs['error_count']))) {
            return null;
        }
        return $date->format('Y-m-d 00:00:00');
    }

    if (!preg_match('/^(\d{8})T(\d{4})(\d{2})?(Z)?$/i', $valeur, $correspondance)) {
        return null;
    }

    $dateHeure = $correspondance[1] . 'T' . $correspondance[2] . ($correspondance[3] ?? '00');
    $zone = !empty($correspondance[4]) ? new DateTimeZone('UTC') : $zoneLocale;

    if (preg_match('/(?:^|;)TZID=(?:"([^"]+)"|([^;:]+))/i', $parametres, $timezone)) {
        try {
            $zone = new DateTimeZone($timezone[1] !== '' ? $timezone[1] : trim($timezone[2]));
        } catch (Exception $exception) {
            return null;
        }
    }

    $date = DateTimeImmutable::createFromFormat('!Ymd\THis', $dateHeure, $zone);
    $erreurs = DateTimeImmutable::getLastErrors();
    if (!$date || ($erreurs !== false && ($erreurs['warning_count'] || $erreurs['error_count']))) {
        return null;
    }

    return $date->setTimezone($zoneLocale)->format('Y-m-d H:i:s');
}

function analyserFichierICS($contenu)
{
    if (substr($contenu, 0, 3) === "\xEF\xBB\xBF") {
        $contenu = substr($contenu, 3);
    }

    $lignes = preg_split('/\r\n|\n|\r/', $contenu);
    $lignesDepliees = [];

    foreach ($lignes as $ligne) {
        if (($ligne[0] ?? '') === ' ' || ($ligne[0] ?? '') === "\t") {
            if ($lignesDepliees) {
                $index = count($lignesDepliees) - 1;
                $lignesDepliees[$index] .= substr($ligne, 1);
            }
        } else {
            $lignesDepliees[] = $ligne;
        }
    }

    $evenements = [];
    $evenement = null;
    foreach ($lignesDepliees as $ligne) {
        if (strcasecmp(trim($ligne), 'BEGIN:VEVENT') === 0) {
            if ($evenement !== null) {
                return null;
            }
            $evenement = [];
            continue;
        }

        if (strcasecmp(trim($ligne), 'END:VEVENT') === 0) {
            if ($evenement === null) {
                return null;
            }
            foreach (['SUMMARY', 'DTSTART', 'DTEND'] as $champ) {
                if (!isset($evenement[$champ])) {
                    return null;
                }
            }

            $intitule = trim(decodifierValeurICS($evenement['SUMMARY']['valeur']));
            $nombreCaracteres = preg_match_all('/./us', $intitule);
            $dateDebut = convertirDateICS($evenement['DTSTART']['valeur'], $evenement['DTSTART']['parametres']);
            $dateFin = convertirDateICS($evenement['DTEND']['valeur'], $evenement['DTEND']['parametres']);
            if ($intitule === '' || $nombreCaracteres === false || $nombreCaracteres > 255 || $dateDebut === null || $dateFin === null || $dateFin < $dateDebut) {
                return null;
            }

            $evenements[] = [
                'intitule' => $intitule,
                'date_debut' => $dateDebut,
                'date_fin' => $dateFin
            ];
            $evenement = null;
            continue;
        }

        if ($evenement === null || strpos($ligne, ':') === false) {
            continue;
        }

        [$propriete, $valeur] = explode(':', $ligne, 2);
        $segments = explode(';', $propriete);
        $nom = strtoupper(array_shift($segments));
        if (in_array($nom, ['SUMMARY', 'DTSTART', 'DTEND'], true)) {
            $evenement[$nom] = [
                'parametres' => implode(';', $segments),
                'valeur' => $valeur
            ];
        }
    }

    if ($evenement !== null || !$evenements) {
        return null;
    }

    return $evenements;
}

$erreurICS = '';
$evenementsApercu = [];
$jetonImport = '';
$personnelPreselectionne = '';
$messageImport = '';

$requete = $pdo->query('SELECT id, nom, prenom, mail FROM Personnel ORDER BY nom, prenom, id');
$personnelsImportables = $requete->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['importe'], $_GET['doublons']) && ctype_digit((string) $_GET['importe']) && ctype_digit((string) $_GET['doublons'])) {
    $messageImport = (int) $_GET['importe'] . ' enseignement(s) importé(s), ' . (int) $_GET['doublons'] . ' doublon(s) ignoré(s).';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['previsualiser'])) {
    $personnelPreselectionne = is_string($_POST['id_personnel'] ?? null) ? $_POST['id_personnel'] : '';
    $fichier = $_FILES['fichier'] ?? null;

    if (!ctype_digit($personnelPreselectionne) || !in_array((int) $personnelPreselectionne, array_column($personnelsImportables, 'id'))) {
        $erreurICS = 'Veuillez choisir un personnel valide.';
    } elseif (!is_array($fichier) || ($fichier['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $erreurICS = 'Le fichier ICS n’a pas pu être téléversé.';
    } elseif (!is_string($fichier['name'] ?? null) || !is_string($fichier['tmp_name'] ?? null)) {
        $erreurICS = 'Le fichier téléversé est invalide.';
    } elseif (strtolower(pathinfo($fichier['name'] ?? '', PATHINFO_EXTENSION)) !== 'ics') {
        $erreurICS = 'Fichier ICS invalide.';
    } elseif (($fichier['size'] ?? 0) > 5 * 1024 * 1024) {
        $erreurICS = 'Le fichier ICS ne doit pas dépasser 5 Mo.';
    } elseif (!is_uploaded_file($fichier['tmp_name'] ?? '')) {
        $erreurICS = 'Le fichier téléversé est invalide.';
    } else {
        $contenu = file_get_contents($fichier['tmp_name']);
        if ($contenu === false) {
            $erreurICS = 'Impossible de lire le fichier ICS.';
        } else {
            $evenements = analyserFichierICS($contenu);
            if ($evenements === null) {
                $erreurICS = 'Fichier ICS invalide.';
            } else {
                $jetonImport = bin2hex(random_bytes(32));
                $_SESSION['imports_enseignement'][$jetonImport] = [
                    'id_personnel' => (int) $personnelPreselectionne,
                    'evenements' => $evenements
                ];
                header('Location: importerEnseignement.php?jeton=' . urlencode($jetonImport));
                exit;
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['importer'])) {
    $jeton = is_string($_POST['jeton'] ?? null) ? $_POST['jeton'] : '';
    $importEnAttente = $_SESSION['imports_enseignement'][$jeton] ?? null;

    if (!preg_match('/^[a-f0-9]{64}$/', $jeton) || !is_array($importEnAttente)) {
        $erreurICS = 'Cet aperçu a expiré ou n’est plus disponible. Veuillez recommencer.';
    } else {
        $requete = $pdo->prepare("INSERT INTO Activite (id_type, id_personnel, titre, date_debut, date_fin)
            SELECT TypeActivite.id_type, :id_personnel, :intitule, :date_debut, :date_fin
            FROM TypeActivite
            WHERE TypeActivite.nom_type = 'Cours'
            AND NOT EXISTS (
                SELECT 1 FROM Activite
                WHERE Activite.id_type = TypeActivite.id_type
                AND Activite.id_personnel = :id_personnel
                AND Activite.titre = :intitule
                AND Activite.date_debut = :date_debut
            )");
        $nombreImportes = 0;
        $pdo->beginTransaction();
        try {
            foreach ($importEnAttente['evenements'] as $evenement) {
                $requete->execute([
                    'id_personnel' => $importEnAttente['id_personnel'],
                    'intitule' => $evenement['intitule'],
                    'date_debut' => $evenement['date_debut'],
                    'date_fin' => $evenement['date_fin']
                ]);
                $nombreImportes += $requete->rowCount();
            }
            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }

        $nombreDoublons = count($importEnAttente['evenements']) - $nombreImportes;
        unset($_SESSION['imports_enseignement'][$jeton]);
        header('Location: importerEnseignement.php?importe=' . $nombreImportes . '&doublons=' . $nombreDoublons);
        exit;
    }
}

if (isset($_GET['jeton']) && is_string($_GET['jeton'])) {
    $jetonImport = $_GET['jeton'];
    $importEnAttente = $_SESSION['imports_enseignement'][$jetonImport] ?? null;
    if (preg_match('/^[a-f0-9]{64}$/', $jetonImport) && is_array($importEnAttente)) {
        $evenementsApercu = $importEnAttente['evenements'];
        $personnelPreselectionne = (string) $importEnAttente['id_personnel'];
    } else {
        $jetonImport = '';
        $erreurICS = 'Cet aperçu a expiré ou n’est plus disponible. Veuillez recommencer.';
    }
} elseif ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $personnelPreselectionne = is_string($_GET['id_personnel'] ?? null) ? $_GET['id_personnel'] : '';
}
