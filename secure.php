<?php
session_start();

// Vérifier si l'utilisateur est authentifié
if(!isset($_SESSION["authenticated"]) || $_SESSION["authenticated"] !== true) {
    // L'utilisateur n'est pas authentifié, rediriger vers la page de connexion
    header("Location: login.php");
    exit();
}
?>