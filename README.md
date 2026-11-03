# SAE301

L'import des enseignements nécessite la table `Enseignement`. Pour une base existante, exécuter une seule fois la migration suivante, sans relancer le script complet `Model/base.sql` qui recrée les tables :

```sql
CREATE TABLE Enseignement (
    id serial PRIMARY KEY,
    id_personnel INT NOT NULL REFERENCES Personnel(id),
    intitule varchar(255) NOT NULL,
    date_debut timestamp NOT NULL,
    date_fin timestamp NOT NULL,
    UNIQUE (id_personnel, intitule, date_debut)
);
```
