# SAE301

La colonne `type_rattachement` et la table `Enseignement` sont définies dans `Model/base.sql`. Pour une base existante, appliquer uniquement les instructions de migration correspondantes (`ALTER TABLE Rattachement` et création de `Enseignement`, avec sa contrainte d'unicité), sans relancer le script complet qui recrée les tables.