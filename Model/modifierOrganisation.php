<?php
    require_once __DIR__ . '/verifierconnexion.php';

    exigerDroit('gerer_personnel');

    $choix_orga = $_POST['choix_orga'] ?? null;
    $message = null;
    $erreurOrganisation = '';

    $departements = $pdo->query("SELECT id_departement, nom FROM Departement ORDER BY nom")->fetchAll();
    $groupes = $pdo->query("SELECT id_groupe, nom FROM Groupe ORDER BY nom")->fetchAll();

    if (isset($_POST["valider"])) {
        $dateDebut = $_POST["date_debut"] ?? $_POST["nouvelleDateDebut"] ?? '';
        $dateFin = $_POST["nouvelleDateFin"] ?? '';
        $dateCloture = $_POST["dateCloture"] ?? '';

        if ($dateFin !== '' && $dateFin < $dateDebut) {
            $erreurOrganisation = 'La date de fin ne peut pas être antérieure à la date de début.';

        } elseif ($choix_orga === 'ajouterDepartement') {
            $requete = $pdo->prepare("INSERT INTO Departement (nom, date_debut) VALUES (?, ?)");
            $requete->execute([$_POST["nom"], $dateDebut]);
            $message = "Département ajouté";

        } elseif ($choix_orga === 'modifierDepartement') {
            $requete = $pdo->prepare("UPDATE Departement SET nom = ?, date_debut = ?, date_fin = ? WHERE id_departement = ?");
            $requete->execute([$_POST["nouveauNomDpt"], $dateDebut, $dateFin ?: null, $_POST["id_departement"]]);
            $message = "Département modifié";

        } elseif ($choix_orga === 'supprimerDepartement') {
            $requete = $pdo->prepare("UPDATE Departement SET date_fin = ? WHERE id_departement = ? AND date_debut <= ?");
            $requete->execute([$dateCloture, $_POST["id_departement"], $dateCloture]);
            $message = "Département clôturé";
            if ($requete->rowCount() === 0) {
                $message = null;
                $erreurOrganisation = 'La date de clôture ne peut pas être antérieure à la création du département.';
            }

        } elseif ($choix_orga === 'ajouterGroupe') {
            $requete = $pdo->prepare("INSERT INTO Groupe (id_departement, nom, date_debut) VALUES (?, ?, ?)");
            $requete->execute([$_POST["id_departement"], $_POST["nomGroupe"], $dateDebut]);
            $message = "Groupe ajouté";

        } elseif ($choix_orga === 'modifierGroupe') {
            $requete = $pdo->prepare("UPDATE Groupe SET nom = ?, date_debut = ?, date_fin = ? WHERE id_groupe = ?");
            $requete->execute([$_POST["nouveauNomGroupe"], $dateDebut, $dateFin ?: null, $_POST["id_groupe"]]);
            $message = "Groupe modifié";

        } elseif ($choix_orga === 'supprimerGroupe') {
            $requete = $pdo->prepare("UPDATE Groupe SET date_fin = ? WHERE id_groupe = ? AND date_debut <= ?");
            $requete->execute([$dateCloture, $_POST["id_groupe"], $dateCloture]);
            $message = "Groupe clôturé";
            if ($requete->rowCount() === 0) {
                $message = null;
                $erreurOrganisation = 'La date de clôture ne peut pas être antérieure à la création du groupe.';
            }
        }
    }