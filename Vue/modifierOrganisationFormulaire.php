<?php
    require __DIR__ . '/../Model/verifierconnexion.php';
    require __DIR__ . '/../Model/modifierOrganisation.php';

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter/Modifier Departement</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <?php require __DIR__ . '/navbar.php'; ?>

    <div class="page-contenu">
        <h1>Bienvenue sur la page de modification de l'organisation.</h1>

        <?php if ($message): ?>
            <p class="message-succes"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <?php if ($erreurOrganisation !== ''): ?>
            <p class="message-erreur"><?= htmlspecialchars($erreurOrganisation) ?></p>
        <?php endif; ?>
        <label>Selectionnez l'action souhaitée : </label>
        <form action="" method="post" class="form-choix">
            <div class="filtres-boutons">
                <label>Départements : </label>
                <button type="submit" name="choix_orga" value="ajouterDepartement">Ajouter</button>
                <button type="submit" name="choix_orga" value="modifierDepartement">Modifier</button>
                <button type="submit" name="choix_orga" value="supprimerDepartement">Clôturer</button>
            </div>

            <div class="filtres-boutons">
                <label>Groupes : </label>
                <button type="submit" name="choix_orga" value="ajouterGroupe">Ajouter</button>
                <button type="submit" name="choix_orga" value="modifierGroupe">Modifier</button>
                <button type="submit" name="choix_orga" value="supprimerGroupe">Clôturer</button>
            </div>
        </form>

        <?php if($choix_orga !== null) : ?>
            <form action="" method="post">
            <input type="hidden" name="choix_orga" value="<?= htmlspecialchars($choix_orga) ?>">

            <?php if($choix_orga === 'ajouterDepartement'): ?>
                    <label for="nom">Nom Département</label>
                    <input type="text" name="nom" id="nom" maxlength="50" value="" required>

                    <label for="date_debut">Date de début</label>
                    <input type="date" name="date_debut" id="date_debut" value="" required>

                    <button type="submit" name="valider">Ajouter le département</button>

            <?php elseif($choix_orga === 'modifierDepartement'): ?>
                    <label for="id_departement">Choisissez le département à modifier</label>
                      
                        <select name="id_departement" id="id_departement">
                            <?php foreach($departements as $departement): ?>
                            <option value="<?= $departement["id_departement"] ?>"><?= htmlspecialchars($departement["nom"]) ?></option>
                            <?php endforeach; ?>
                        </select>

                    <label for="nouveauNomDpt">Insérer le nouveau nom</label>
                        <input type="text" name="nouveauNomDpt" id="nouveauNomDpt" maxlength="50" value="" required>
                    <label for="nouvelleDateDebut">Modifier la date de début</label>
                        <input type="date" name="nouvelleDateDebut" id="nouvelleDateDebut" value="" required>
                    <label for="nouvelleDateFin">Modifier la date de fin</label>
                        <input type="date" name="nouvelleDateFin" id="nouvelleDateFin" value="">
                    <button type="submit" name="valider">Valider Modification</button>
            
            <?php elseif($choix_orga === 'supprimerDepartement'): ?>
                    <label for="id_departement">Choisissez le département à clôturer</label>

                        <select name="id_departement" id="id_departement">
                            <?php foreach($departements as $departement): ?>
                            <option value="<?= $departement["id_departement"] ?>"><?= htmlspecialchars($departement["nom"]) ?></option>
                            <?php endforeach; ?>
                        </select>

                    <label for="dateCloture">Date de clôture</label>    
                        <input type="date" name="dateCloture" id="dateCloture" value="" required>
                    <button type="submit" name="valider">Valider clôture</button>

            <?php elseif($choix_orga === 'ajouterGroupe'): ?>
                    <label for="nomGroupe">Nom Groupe</label>
                    <input type="text" name="nomGroupe" id="nom" maxlength="255" value="" required>

                    <label for="id_departement">Département du groupe</label>
                        <select name="id_departement" id="id_departement">
                        <?php foreach($departements as $departement): ?>
                    <option value="<?= $departement['id_departement'] ?>"><?= htmlspecialchars($departement['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label for="date_debut">Date de début</label>
                    <input type="date" name="date_debut" id="date_debut" value="" required>

                    <button type="submit" name="valider">Ajouter le groupe</button>

            <?php elseif($choix_orga === 'modifierGroupe'): ?>
                <label for="id_groupe">Choisissez le groupe à modifier</label>
                <select name="id_groupe" id="id_groupe">
                    <?php foreach($groupes as $groupe): ?>
                    <option value="<?= $groupe['id_groupe'] ?>"><?= htmlspecialchars($groupe['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
 
                <label for="nouveauNomGroupe">Insérer le nouveau nom</label>
                    <input type="text" name="nouveauNomGroupe" id="nouveauNomGroupe" maxlength="255" value="" required>
                <label for="nouvelleDateDebut">Modifier la date de début</label>
                    <input type="date" name="nouvelleDateDebut" id="nouvelleDateDebut" value="" required>
                <label for="nouvelleDateFin">Modifier la date de fin</label>
                    <input type="date" name="nouvelleDateFin" id="nouvelleDateFin" value="">
                <button type="submit" name="valider">Valider Modification</button>
 
            <?php elseif($choix_orga === 'supprimerGroupe'): ?>
                <label for="id_groupe">Choisissez le groupe à clôturer</label>
                <select name="id_groupe" id="id_groupe">
                    <?php foreach($groupes as $groupe): ?>
                    <option value="<?= $groupe['id_groupe'] ?>"><?= htmlspecialchars($groupe['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
 
                <label for="dateCloture">Date de clôture</label>
                <input type="date" name="dateCloture" id="dateCloture" value="" required>
                <button type="submit" name="valider">Valider clôture</button>
            <?php endif; ?>

            </form>
        <?php endif; ?>
                <p><a href="organisation.php">Retour à l'affichage de l'organisation</a></p>
    </div>
    
    <footer class="footer">
    <p>&copy; <?= date('Y') ?> Université Polytechnique Hauts-de-France - Tous droits réservés</p>
        <ul class="footer-liens">
            <li><a href="#">Mentions légales</a></li>
            <li><a href="#">Contact</a></li>
            <li><a href="#">Accessibilité</a></li>
        </ul>
    </footer>

</body>
</html>