<?php
    // Connexion à la base de données
    include("config.php");



    try {


        ///reception.php?capteur_id=2&data=8.8&date=2023-01-28 14:45:22


        try {
            $conn = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME,DB_USER, DB_PASS);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
            // Vérifier que la connexion est établie avec succès
            if (!$conn) {
                echo("La connexion à la base de données a échoué");
            }
            
        } catch (PDOException $e) {
            die('Erreur : ' . $e->getMessage());
        }


        $capteur_id = $_GET['capteur_id'];
        $data = $_GET['data'];
        $date = $_GET['date'];
        $key = $_GET['key'];
        if(!empty($key)){
            if($key != KEY){
                die();
            }
        }
        else{
            die();
        }


        if(!empty($capteur_id) && !empty($data) && !empty($date)){
            $date = str_replace('_', ' ', $date);
            $stmt = $conn->prepare("INSERT INTO donnee_capteur (id_capteur, valeur, date_data) VALUES (:capteur_id, :data, :date_data)");
            $stmt->bindValue(':capteur_id', $capteur_id, PDO::PARAM_INT);
            $stmt->bindValue(':data', $data, PDO::PARAM_STR);
            $stmt->bindValue(':date_data', $date, PDO::PARAM_STR);
            $stmt->execute();
            echo "oui";
        }
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
    $conn = null;

?>