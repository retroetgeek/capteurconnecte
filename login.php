<?php
session_start();
include("config.php");

// Vérifier si le formulaire a été soumis
if($_SERVER["REQUEST_METHOD"] == "POST") {

    // Vérifier si le mot de passe saisi est correct
    $password = $_POST["password"];
    if($password === ADMIN_PASS) {
        // Le mot de passe est correct, rediriger l'utilisateur vers la page capteur.php
        $_SESSION["authenticated"] = true;
        header("Location: capteur.php");
        exit();
    } else {
        // Le mot de passe est incorrect, afficher un message d'erreur
        $errorMessage = "Mot de passe incorrect";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
	<title>Connexion</title>
</head>
<body>
	<h1>Connexion</h1>
	<?php if(isset($errorMessage)) { echo "<p>$errorMessage</p>"; } ?>
	<form method="post" action="login.php">
		<label for="password">Mot de passe :</label>
		<input type="password" name="password" id="password" />
		<button type="submit">Se connecter</button>
	</form>
</body>
</html>