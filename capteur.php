<?php
// Connexion à la base de données
include("config.php");
include("secure.php");

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME,DB_USER, DB_PASS);
} catch (PDOException $e) {
    die('Erreur : ' . $e->getMessage());
}


// Création d'un capteur
if(isset($_POST['create_capteur'])) {
        // Insertion du capteur
        $nom_capteur = "new";
        $stmt = $pdo->prepare("INSERT INTO capteur (nom_capteur) VALUES (:nom)");
        $stmt->bindParam(":nom", $nom_capteur);
        $stmt->execute();
        echo "Le capteur a été créé avec succès.";
}



// Suppression d'un capteur
if(isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // Vérification si le capteur existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM capteur WHERE id=:id");
    $stmt->bindParam(":id", $delete_id);
    $stmt->execute();
    $capteur_exists = $stmt->fetchColumn();

    if($capteur_exists) {
        // Affichage d'une confirmation de suppression
        echo "<script>
                if(confirm('Êtes-vous sûr de vouloir supprimer le capteur $delete_id ?')) {
                    window.location.href='capteur.php?confirm_delete_id=$delete_id';
                } else {
                    window.location.href='capteur.php';
                }
              </script>";
    } else {
        echo "Capteur introuvable.";
    }
}

// Confirmation de suppression d'un capteur
if(isset($_GET['confirm_delete_id'])) {
    $confirm_delete_id = $_GET['confirm_delete_id'];

    // Suppression du capteur
    $stmt = $pdo->prepare("DELETE FROM capteur WHERE id=:id");
    $stmt->bindParam(":id", $confirm_delete_id);
    $stmt->execute();

    echo "Le capteur a été supprimé avec succès.";
}

// Affichage de la liste des capteurs
$stmt = $pdo->query("SELECT * FROM capteur ORDER BY id ASC");

echo "<table>";
echo "<tr><th>ID</th><th>Nom</th><th>Zone</th><th>Couleur</th><th>Actions</th></tr>";

while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<tr>";
    echo "<td>".$row['id']."</td>";
    echo "<td>".$row['nom_capteur']."</td>";
    echo "<td>".$row['zone']."</td>";
    echo "<td style='background-color:".$row['couleur']."'>".$row['couleur']."</td>";
    echo "<td><a href='?delete_id=".$row['id']."'>Supprimer</a> | <a href='editer_capteur.php?id=".$row['id']."'>Editer</a></td>";
    echo "</tr>";
}

echo "</table>";



?>

<form method="post" >        
<input name="create_capteur" type="hidden" value="1"/>
    <input type="submit" name="Creer capteur" value="Creer capteur">
</form>