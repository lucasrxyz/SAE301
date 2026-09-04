CREATE TABLE T_DEPARTMENT(
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255),
    code_department INT
);

CREATE TABLE T_LABORATORY(
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255),
    fk_department_id INT,

    FOREIGN KEY (fk_department_id) REFERENCES T_DEPARTMENT(id)
);

CREATE TABLE R_TYPE_USER(
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_type varchar(255)
);

CREATE TABLE R_SERVICE(
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_service varchar(255)
);
CREATE TABLE T_USER(
    id int AUTO_INCREMENT PRIMARY KEY,
    nom varchar(255),
    prenom varchar(255),
    username varchar(50),
    password text,
    is_responsible int,
    fk_service_id int,
    fk_type_user_id int,
    fk_laboratory_id int,

    FOREIGN KEY (fk_service_id) REFERENCES R_SERVICE(id),
    FOREIGN KEY (fk_type_user_id) REFERENCES R_TYPE_USER(id),
    FOREIGN KEY (fk_laboratory_id) REFERENCES T_LABORATORY(id)
);
CREATE TABLE R_ROLES(
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_role varchar(255)
);
CREATE TABLE T_USER_ROLES(
    id INT AUTO_INCREMENT PRIMARY KEY,
    fk_user_id int,
    fk_role_id int,

    FOREIGN KEY (fk_user_id) REFERENCES T_USER(id),
    FOREIGN KEY (fk_role_id) REFERENCES R_ROLES(id)
);
