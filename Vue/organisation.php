<?php
    require __DIR__ . '/../Model/listeOrganisation.php';
?>

<!DOCTYPE html>
<html lang='fr'>
<head>
        <meta charset="utf-8">
        <meta name="viewport", content="width=device-width, initial-scale=1.0">
    <title>Organisation</title>
</head>
<body>
    <div>Selectionnez la vue souhaitée</div>

    <table>
        <caption>Affichage de l'organisation</caption>
        <thead>
            <tr>
                <th scope="col">Département</th>

                <th scope="col">Groupe</th>

                <th scope="col">Personnel</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($stmt as $lignes): ?>
                <tr> <?php htmlspecialchars($lignes['dept_nom']) ?></tr>
                <tr> <?php htmlspecialchars($lignes['groupe_nom']) ?></tr>
                <tr> <?php htmlspecialchars($lignes['person_nom']) ?></tr>            
           <?php endforeach;?>
        </tbody>
    </table>
</body>

</html>
