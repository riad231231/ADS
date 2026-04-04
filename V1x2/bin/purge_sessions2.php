#!/usr/bin/env php
<?php

// Connexion à la base de données en une seule étape avec mysqli_connect
$link = mysqli_connect('lesailesoproot.mysql.db', 'lesailesoproot', 'Moissy77', 'lesailesoproot');

if (!$link) {
    die('Erreur de connexion : ' . mysqli_connect_error());
}

// Exécution de la requête TRUNCATE sur la table iv42i_session
$sql = "DELETE FROM iv42i_session WHERE time < UNIX_TIMESTAMP() - 600;";

if (!mysqli_query($link, $sql)) {
    die('Erreur SQL : ' . mysqli_error($link));
}

// Fermeture de la connexion
mysqli_close($link);

?>
