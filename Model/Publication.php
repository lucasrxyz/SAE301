<?php
// Publications récupérées sur HAL et enregistrées comme activités
class Publication
{
    // Correspondance entre les codes de HAL et nos types d'activité
    const TYPES_HAL = [
        'ART' => 'Article de revue',
        'COMM' => 'Communication en conférence avec actes',
        'OUV' => 'Ouvrage',
        'COUV' => 'Chapitre d\'ouvrage'
    ];

    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Interroge l'API de HAL, qui répond en XML. Renvoie false si HAL ne répond pas
    public function chercherSurHal($idHal)
    {
        $adresse = 'https://api.archives-ouvertes.fr/search/?q=authIdHal_s:' . urlencode($idHal) . '&wt=xml&fl=title_s,producedDate_s,docType_s&rows=500';
        $contexte = stream_context_create(['http' => ['timeout' => 15]]);
        $contenu = @file_get_contents($adresse, false, $contexte);

        // Lit le XML sans afficher d'erreur à l'écran
        libxml_use_internal_errors(true);
        return $contenu === false ? false : simplexml_load_string($contenu);
    }

    // Transforme la réponse de HAL en liste de publications et compte les nouvelles et les types ignorés
    public function lireReponse($xml)
    {
        $publications = [];
        $nbNouvelles = 0;
        $nbAutresTypes = 0;
        $dejaVues = [];

        // Retrouve le numéro de chaque type d'activité
        $idTypes = [];
        $requete = $this->pdo->prepare('SELECT id_type FROM TypeActivite WHERE nom_type = ?');
        foreach (self::TYPES_HAL as $codeHal => $nomType) {
            $requete->execute([$nomType]);
            $idTypes[$codeHal] = $requete->fetchColumn();
        }

        // Parcourt chaque document renvoyé par HAL
        foreach ($xml->result->doc as $doc) {
            $titre = '';
            $date = '';
            $codeHal = '';

            // Récupère le titre, la date et le type
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

            // HAL donne parfois seulement l'année ou le mois : on complète au 1er
            if (strlen($date) === 4) {
                $date = $date . '-01-01';
            } elseif (strlen($date) === 7) {
                $date = $date . '-01';
            }

            // Vérifie que la date existe vraiment
            $dateValide = DateTime::createFromFormat('!Y-m-d', $date);

            // Type non géré : on le compte et on l'ignore
            if (!isset(self::TYPES_HAL[$codeHal])) {
                $nbAutresTypes++;
            } elseif ($titre !== '' && $dateValide && $dateValide->format('Y-m-d') === $date) {
                // Doublon : même titre et même date, déjà en base ou déjà vu dans cette réponse
                $cle = strtolower($titre) . '|' . $date;
                $dejaPresente = $this->existe($titre, $date) || isset($dejaVues[$cle]);
                $dejaVues[$cle] = true;

                if (!$dejaPresente) {
                    $nbNouvelles++;
                }

                $publications[] = [
                    'titre' => $titre,
                    'date' => $date,
                    'type' => self::TYPES_HAL[$codeHal],
                    'id_type' => $idTypes[$codeHal],
                    'deja_presente' => $dejaPresente
                ];
            }
        }

        return [$publications, $nbNouvelles, $nbAutresTypes];
    }

    // Vrai si une activité a déjà ce titre et cette date
    public function existe($titre, $date)
    {
        $requete = $this->pdo->prepare('SELECT COUNT(*) FROM Activite WHERE LOWER(titre) = LOWER(LEFT(?, 255)) AND date_debut::date = ?');
        $requete->execute([$titre, $date]);
        return $requete->fetchColumn() > 0;
    }

    // Enregistre les publications qui ne sont pas encore en base et renvoie leur nombre
    public function importer($publications, $idPersonnel)
    {
        $nbImportees = 0;

        // Si une écriture plante, on annule tout
        $this->pdo->beginTransaction();

        foreach ($publications as $publication) {
            if (!$this->existe($publication['titre'], $publication['date'])) {
                $requete = $this->pdo->prepare('INSERT INTO Activite (id_type, id_personnel, titre, est_collective, date_debut, date_fin) VALUES (?, ?, LEFT(?, 255), TRUE, ?, ?)');
                $requete->execute([$publication['id_type'], $idPersonnel, $publication['titre'], $publication['date'], $publication['date']]);
                $nbImportees++;
            }
        }

        $this->pdo->commit();
        return $nbImportees;
    }
}
