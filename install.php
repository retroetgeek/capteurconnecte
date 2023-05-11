<?php
include("config.php");

// Test de la connexion à la base de données
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME,DB_USER, DB_PASS);
} catch (PDOException $e) {
    die('Erreur : ' . $e->getMessage());
}

// Création de la table `capteur`
try {
$sql_capteur = "
CREATE TABLE IF NOT EXISTS `capteur` (
  `id` int(11) NOT NULL auto_increment,
  `nom_capteur` varchar(255) NOT NULL,
  `zone` int(11) NOT NULL default '0',
  `couleur` varchar(7) NOT NULL default '#000000',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;
";
$pdo->exec($sql_capteur);
} catch (PDOException $e) {
    die('Erreur : ' . $e->getMessage());
}



// Création de la table `donnee_capteur`
try {
$sql_donnee_capteur = "
CREATE TABLE IF NOT EXISTS `donnee_capteur` (
  `id` int(11) NOT NULL auto_increment,
  `valeur` float NOT NULL,
  `date_data` datetime NOT NULL,
  `id_capteur` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `id_capteur` (`id_capteur`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;
";
$pdo->exec($sql_donnee_capteur);
} catch (PDOException $e) {
    die('Erreur : ' . $e->getMessage());
}

echo "Les tables SQL ont été créées avec succès !";
?>