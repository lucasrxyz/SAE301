<?php
    require_once __DIR__ . '/config.php';

    exigerDroit('gerer_personnel');

    $typesHal = [
        'ART' => 'Article de revue',
        'COMM' => 'Communication en conférence avec actes',
        'OUV' => 'Ouvrage',
        'COUV' => 'Chapitre d\'ouvrage'
    ];

    $erreurImport = '';
    $nbImportees = isset($_GET['importees']) ? (int) $_GET['importees'] : -1;
    $idPersonnel = (int) ($_POST['id_personnel'] ?? 0);
    $publications = [];
    $nbNouvelles = 0;
    $nbAutresTypes = 0;
    $apercuAffiche = false;

    $requete = $pdo->query("SELECT id, nom, prenom, id_idhal FROM Personnel WHERE id_idhal IS NOT NULL AND id_idhal <> '' ORDER BY nom, prenom");
    $personnels = $requete->fetchAll(PDO::FETCH_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['apercu']) || isset($_POST['importer']))) {
        $idHal = '';
        foreach ($personnels as $personnel) {
            if ($personnel['id'] == $idPersonnel) {
                $idHal = $personnel['id_idhal'];
            }
        }

        if ($idHal === '') {
            $erreurImport = 'Veuillez choisir un personnel qui a un IdHAL.';
        } else {
            $adresse = 'https://api.archives-ouvertes.fr/search/?q=authIdHal_s:' . urlencode($idHal) . '&wt=xml&fl=title_s,producedDate_s,docType_s&rows=500';
            $contexte = stream_context_create(['http' => ['timeout' => 15]]);
            $contenu = @file_get_contents($adresse, false, $contexte);

            libxml_use_internal_errors(true);
            $xml = $contenu === false ? false : simplexml_load_string($contenu);

            if ($xml === false) {
                $erreurImport = 'HAL ne répond pas pour le moment. Réessayez plus tard.';
            } else {
                $apercuAffiche = true;

                $dejaVues = [];
                $idTypes = [];
                $requete = $pdo->prepare('SELECT id_type FROM TypeActivite WHERE nom_type = ?');
                foreach ($typesHal as $codeHal => $nomType) {
                    $requete->execute([$nomType]);
                    $idTypes[$codeHal] = $requete->fetchColumn();
                }

                foreach ($xml->result->doc as $doc) {
                    $titre = '';
                    $date = '';
                    $codeHal = '';

                    foreach ($doc->children() as $champ) {
                        $nomChamp = (string) $champ['name'];
                        if ($nomChamp === 'title_s') {
                            $titre = trim((string) $champ->str);
                        }
                        if ($nomChamp === 'producedDate_s') {
                            $date = trim((string) $champ);
                        }
                        if ($nomChamp === 'docType_s') {
                            $codeHal = trim((string) $champ);
                        }
                    }

                    if (strlen($date) === 4) {
                        $date = $date . '-01-01';
                    } elseif (strlen($date) === 7) {
                        $date = $date . '-01';
                    }

                    $dateValide = DateTime::createFromFormat('!Y-m-d', $date);

                    if (!isset($typesHal[$codeHal])) {
                        $nbAutresTypes++;
                    } elseif ($titre !== '' && $dateValide && $dateValide->format('Y-m-d') === $date) {
                        $requete = $pdo->prepare('SELECT COUNT(*) FROM Activite WHERE LOWER(titre) = LOWER(LEFT(?, 255)) AND date_debut::date = ?');
                        $requete->execute([$titre, $date]);
                        $cle = strtolower($titre) . '|' . $date;
                        $dejaPresente = $requete->fetchColumn() > 0 || isset($dejaVues[$cle]);
                        $dejaVues[$cle] = true;

                        if (!$dejaPresente) {
                            $nbNouvelles++;
                        }

                        $publications[] = [
                            'titre' => $titre,
                            'date' => $date,
                            'type' => $typesHal[$codeHal],
                            'id_type' => $idTypes[$codeHal],
                            'deja_presente' => $dejaPresente
                        ];
                    }
                }

                if (isset($_POST['importer'])) {
                    $nbImportees = 0;

                    $pdo->beginTransaction();

                    foreach ($publications as $publication) {
                        $requete = $pdo->prepare('SELECT COUNT(*) FROM Activite WHERE LOWER(titre) = LOWER(LEFT(?, 255)) AND date_debut::date = ?');
                        $requete->execute([$publication['titre'], $publication['date']]);

                        if ($requete->fetchColumn() == 0) {
                            $requete = $pdo->prepare('INSERT INTO Activite (id_type, id_personnel, titre, est_collective, date_debut, date_fin) VALUES (?, ?, LEFT(?, 255), TRUE, ?, ?)');
                            $requete->execute([$publication['id_type'], $idPersonnel, $publication['titre'], $publication['date'], $publication['date']]);
                            $nbImportees++;
                        }
                    }

                    $pdo->commit();

                    header('Location: importerPublications.php?importees=' . $nbImportees);
                    exit;
                }
            }
        }
    }
