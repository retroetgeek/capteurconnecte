# capteurconnecte

Pour l'installation envoyer sur votre serveur par ftp le contenu du repertoire exepté le dossier Arduino qui lui contient le code arduino.

Pour la configuration modifier les données de connexion dans le fichier config.php

define('DB_HOST', 'adresseDeLaBaseDeDonnee'); // l'adresse de l'hôte de la base de données
define('DB_USER', 'nomUtilisateurBasededonnee'); // le nom d'utilisateur pour se connecter à la base de données
define('DB_PASS', 'MotDepasseBaseDeDonnee'); // le mot de passe pour se connecter à la base de données
define('DB_NAME', 'nombasededonnee'); // le nom de la base de données

define('KEY', 'CleDonneeEsp'); // clé données esp, doit etre la même sur l esp
define('ADMIN_PASS', 'motdepasseadminbackoffice'); // mot de passe admin

Pour la partie arduino televerser le programme IotWeb.ino ainsi que config.h qui lui doit etre modifié avec les informations wifi et site web

#define SSID "NOM_DE_MON_WIFI"
#define PASSWORD "MOT_DE_PASSE_WIFI"

#define KEY "CleDonneeEsp"
#define HOST "urldevotresitesanshttp.fr"

Lancer votre site web et aller sur la page install.php avec https://lenomdemonsite.fr/install.php

Puis aller sur la page https://lenomdemonsite.fr/capteur.php ou https://lenomdemonsite.fr/login.php

Se connecter avec le mot de passe du fichier config.php (ADMIN_PASS)

Cree son capteur, puis l'éditer et choisir son nom, zone, couleur et modifier

Votre capteur est ajouté, il a un identifiant maintenant

Partie arduino c'est la ou vous allez devoir ajouter l'identifiant du capteur 
Dans la fonction bool mesuresCapteur(void *)
Vous retrouvez ce qu'il faut modifier "recordSensorData(1,h);" , ici le 1 est l'identifiant du capteur, vous devez mettre le votre et adapter dans cette fonction a votre capteur.
Ici j'ai utilisé le capteur DHT et j'ai récupéré juste avant la temperature et l'humidité
Imaginons que je veux seulement recuperer la valeur de la luminosité avec un capteur fait pour ça; je récupère la valeur de luminosité et je l'insere dans "recordSensorData(8,luminosite);"
J'aurai seulement un seul recordSensorData dans la fonction vu que j'ai un seul capteur.

Je pense que j'ai résumé l'essentiel, pour creuser un peu plus demandez a chat gpt avec le code fourni
Enjoy
https://retroetgeek.com