<?php
    require_once __DIR__ . '/config.php';

    $choix_orga = $_POST['choix_orga'] ?? null;
    $message = null;
    $departements = $pdo->query("SELECT id_departement, nom FROM Departement ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);

    $groupes = $pdo->query("SELECT id_groupe, nom FROM Groupe ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);

    if (isset($_POST["valider"])) {
        if($choix_orga === 'ajouterDepartement') {
            $requete = $pdo->prepare("INSERT INTO Departement(nom, date_debut) VALUES(?, ?)");
            $requete->execute([$_POST["nom"], $_POST["date_debut"]]);
            $message = "Département ajouté";
    
        }elseif($choix_orga ==='modifierDepartement') {
            $requete = $pdo->prepare("UPDATE Departement SET nom = ?, date_debut = ?, date_fin = ? WHERE id_departement = ?");
            $requete->execute([$_POST["nouveauNomDpt"], $_POST["nouvelleDateDebut"], $_POST["nouvelleDateFin"] ?: null, $_POST["id_departement"]]);
            $message = "Département modifié";

        }elseif($choix_orga ==='supprimerDepartement') {
            $requete = $pdo->prepare("UPDATE Departement SET date_fin = ? WHERE id_departement = ?");
            $requete->execute([$_POST["dateCloture"], $_POST["id_departement"]]);
            $message = "Département clôture";
       
        }else if($choix_orga === 'ajouterGroupe') {
            $requete = $pdo->prepare("INSERT INTO Groupe (id_departement, nom, date_debut) VALUES (?, ?, ?)");
            $requete->execute([$_POST["id_departement"], $_POST["nomGroupe"], $_POST["date_debut"]]);
            $message = "Groupe ajouté";

        }elseif($choix_orga === 'modifierGroupe') {
            $requete = $pdo->prepare("UPDATE Groupe SET nom = ?, date_debut = ?, date_fin = ? WHERE id_groupe = ?");
            $requete->execute([$_POST["nouveauNomGroupe"], $_POST["nouvelleDateDebut"], $_POST["nouvelleDateFin"] ?: null, $_POST["id_groupe"]]);
            $message = "Groupe modifié";

        }elseif($choix_orga === 'supprimerGroupe') {
            $requete = $pdo->prepare('UPDATE Groupe SET date_fin = ? WHERE id_groupe = ?');
            $requete->execute([$_POST["dateCloture"], $_POST["id_groupe"]]);
            $message = "Groupe clôturé";

        } else {
        }
    }

?>