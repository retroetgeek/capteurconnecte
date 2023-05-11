<?php
include("config.php");
include("secure.php");
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME,DB_USER, DB_PASS);
} catch (PDOException $e) {
    die('Erreur : ' . $e->getMessage());
}

// Vérifie si l'ID du capteur a été envoyé en paramètre d'URL
if(isset($_GET['id'])) {
    $id = $_GET['id'];

    // Vérifie si le formulaire a été soumis
    if(isset($_POST['submit'])) {
        $nom_capteur = $_POST['nom_capteur'];
        $zone = $_POST['zone'];
        $couleur = $_POST['couleur'];

        // Met à jour les informations du capteur dans la base de données
        $stmt = $pdo->prepare("UPDATE capteur SET nom_capteur=?, zone=?, couleur=? WHERE id=?");
        $stmt->execute([$nom_capteur, $zone, $couleur, $id]);
        
        // Redirige vers la page des capteurs
        header("Location: capteur.php");
        exit();
    }

    // Récupère les informations du capteur à partir de la base de données
    $stmt = $pdo->prepare("SELECT * FROM capteur WHERE id=?");
    $stmt->execute([$id]);
    $capteur = $stmt->fetch();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Éditer un capteur</title>
</head>
<body>
    <h1>Éditer un capteur</h1>
    <form method="post" action="editer_capteur.php?id=<?php echo $id; ?>">
        
        <label>Nom du capteur :</label>
        <input type="text" name="nom_capteur" value="<?php echo $capteur['nom_capteur']; ?>"><br><br>
        <label>Zone :</label>
        <input type="text" name="zone" value="<?php echo $capteur['zone']; ?>"><br><br>
        <label>Couleur :</label>
        <input type="color" name="couleur" value="<?php echo $capteur['couleur']; ?>"><span><?php echo strtoupper(substr($capteur['couleur'], 1)); ?></span>
        <input type="submit" name="submit" value="Modifier">
    </form>
</body>
</html>

<?php
} else {
    // Si l'ID du capteur n'a pas été envoyé en paramètre d'URL, redirige vers la page des capteurs
    header("Location: capteur.php");
    exit();
}
?>