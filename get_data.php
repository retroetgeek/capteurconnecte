<?php
    // Connexion à la base de données
    include("config.php");


    function nettoyer_retro($data) {
        $data_final=array();// c'est nos données finales

            $valeur_reference=array();
            $date_reference=array();
            $valeur_final=array();
            $date_final=array();


            // boucle pour parcourir toute les données
            foreach ($data as $row) {

                $capteur = $row['capteur'];
                // sert a inscrire les dernieres valeurs triés
                $valeur_final[$capteur]=$row['y'];
                $date_final[$capteur]=$row['x'];

                //echo "valeur en cours :".$capteur."/".$valeur_final[$capteur]."/".$date_final[$capteur]."<br>";


                if(!isset($valeur_reference[$capteur])){
                    $data_final[]= array(
                        'x' => $row['x'],
                        'y' => $row['y'],
                        'capteur' => $row['capteur']
                    );
                    $valeur_reference[$capteur]=$row['y'];
                    $date_reference[$capteur]=$row['x'];
                    //echo"premier <br>";
                }
                else{
                    // si la valeur en cours est la même que la derniere valeur alors on met a jour le marqueur de derniere valeur
                    if($row['y']==$valeur_reference[$capteur]){
                        $valeur_reference[$capteur]=$row['y'];
                        $date_reference[$capteur]=$row['x'];
                        //echo"Maj last<br>";
                    }
                    else
                    {
                        //echo"insertion data <br>";
                        $data_final[]= array(
                            'x' => $date_reference[$capteur],
                            'y' => $valeur_reference[$capteur],
                            'capteur' => $row['capteur']
                        );
                        $data_final[]= array(
                            'x' => $row['x'],
                            'y' => $row['y'],
                            'capteur' => $row['capteur']
                        );

                        $valeur_reference[$capteur]=$row['y'];
                        $date_reference[$capteur]=$row['x'];
                    }
                }
            }

            foreach($valeur_final as $key => $value ){
            // on oublie aps les valeurs finale
                $data_final[]= array(
                    'x' => $date_final[$key],
                    'y' => $value,
                    'capteur' => $key
                );
                //echo"on oublie pas <br>";
            }
    return $data_final;

    }




    function moyenne_par_heure($data) {
        $data_final=array();// c'est nos données finales
        $moyennes = array();
        foreach ($data as $row) {
            $heure = date('Y-m-d H', strtotime($row['x']));
            $heure=$heure.":00:00";
            if (!isset($moyennes[$heure][$row['capteur']])) {
                $moyennes[$heure][$row['capteur']] = array('sum' => 0, 'count' => 0);
            }
            $moyennes[$heure][$row['capteur']]['sum'] += $row['y'];
            $moyennes[$heure][$row['capteur']]['count']++;
        }
        foreach ($moyennes as $heure => $capteurs) {
            foreach ($capteurs as $capteur => $values) {
                //$moyennes[$heure][$capteur] = number_format($values['sum'] / $values['count'], 2);
                $data_final[]= array(
                    'x' => $heure,
                    'y' => number_format($values['sum'] / $values['count'], 2),
                    'capteur' => "$capteur"
                );
            }
        }
        return $data_final;
    }


// get_data.php?date_debut=2023-02-22%2000:00:00&date_fin=2023-02-22%2000:30:00



    try {
        $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME,DB_USER, DB_PASS);
    } catch (PDOException $e) {
        die('Erreur : ' . $e->getMessage());
    }
    





    // Récupération des paramètres de requête
    $capteur_id = isset($_GET['capteur_id']) ? $_GET['capteur_id'] : "";
    $date_debut = isset($_GET['date_debut']) ? $_GET['date_debut'] : date("Y-m-d 00:00:00");
    $date_fin = isset($_GET['date_fin']) ? $_GET['date_fin'] : date("Y-m-d 23:59:59");
    $zone = isset($_GET['zone']) ? $_GET['zone'] : "";

    if($zone == 'comb'){
        $sql_zone = "";
    }
    else{
        $sql_zone = " AND C.zone = :zone ";
    }

    // Préparation de la requête SQL
    if ($capteur_id == "") {
        $sql = "SELECT * FROM donnee_capteur AS DC , capteur AS C WHERE DC.date_data BETWEEN :date_debut AND :date_fin AND C.id=DC.id_capteur ".$sql_zone." ORDER BY date_data";
        $stmt = $pdo->prepare($sql);
    } else {
        $sql = "SELECT * FROM donnee_capteur AS DC , capteur AS C WHERE DC.id_capteur = :capteur_id AND DC.date_data BETWEEN :date_debut AND :date_fin AND C.id=DC.id_capteur ".$sql_zone." ORDER BY date_data";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":capteur_id", $capteur_id);
    }

    //echo $sql ;
 

    $stmt->bindParam(":date_debut", $date_debut);
    $stmt->bindParam(":date_fin", $date_fin);


    if($zone == 'comb'){
        
    }
    else{
        $stmt->bindParam(":zone", $zone);
    }
    

    // Exécution de la requête
    $stmt->execute();
    $data = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = array(
            'x' => $row['date_data'],
            'y' => $row['valeur'],
            'capteur' => $row['id_capteur']
        );
    }

        
        if (isset($_GET['filtre'])) {
            $tri = $_GET['filtre'];
            if ($tri == 'average') {
                $data = moyenne_par_heure($data);
            } elseif ($tri == 'cleaned') {
                $data = nettoyer_retro($data);
            }
        }




    // Conversion du tableau en format JSON
    $json_data = json_encode($data);
    echo $json_data;


    ?>