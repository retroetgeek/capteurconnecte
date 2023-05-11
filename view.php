<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Graphique de données de capteur</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">

<style>

.graphique-container .close {
    position: absolute;
    top: 0;
    right: 0;
    margin: 5px;
    font-size: 20px;
    font-weight: bold;
    line-height: 1;
    color: #000;
    text-shadow: 0 1px 0 #fff;
    opacity: 0.2;
    cursor: pointer;
}
.graphique-container:hover .close {
    opacity: 1;
}
    

.graphique-container {
    position: relative;
    margin-bottom: 20px;
    width:100%;

}
.graphique-container canvas {
    width:100%;
}

</style>
    
</head>
<body class="p-8 bg-gray-100" >


<?php
    // Connexion à la base de données
    include("config.php");
    try {
        $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME,DB_USER, DB_PASS);
    } catch (PDOException $e) {
        die('Erreur : ' . $e->getMessage());
    }
    
    // recuperation des capteurs
    $sql = "SELECT * FROM capteur ORDER BY id ";
    $stmt = $pdo->prepare($sql);
        // Exécution de la requête
        $stmt->execute();
        $data_capteur = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data_capteur[$row['id']] = array(
                'nom_capteur' => $row['nom_capteur'],
                'zone' => $row['zone'],
                'couleur' => $row['couleur']
            );
        }
    ?>    
    <script>
        var zone_capteur = [];
    </script>
    <?php    
    // recuperation des zones
    $sql = "SELECT DISTINCT zone FROM capteur ORDER BY zone ";
    $stmt = $pdo->prepare($sql);
        // Exécution de la requête
        $stmt->execute();
        $data_zone = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data_zone[$row['zone']] = array(
                'zone' => $row['zone']
            );

            ?>
            <script>
            zone_capteur.push('<?php echo $row['zone']; ?>');
            </script>
            <?php
        }


?>


    <form class="row">
        <div class="form-group col-md-3">
            <label for="capteur_id" class="mr-2 font-medium">Capteur :</label>
            <select id="capteur_id" name="capteur_id" class="form-control mr-8">
                <option value="">Tous</option>
                <?php 
                foreach ($data_capteur as $key_capteur => $value_capteur){
                    echo'<option value="'.$key_capteur.'">'.$value_capteur["nom_capteur"].' '.$key_capteur.' / zone '.$value_capteur["zone"].' </option>';
                } 
                ?>
            </select>
        </div>
        <br>
        <div class="form-group col-md-3">
            <label for="date_debut" class="mr-2 font-medium">Date de début :</label>
            <input type="datetime-local" id="date_debut" name="date_debut" value="<?= date('Y-m-d\TH:i:s', strtotime('today midnight')) ?>" class="form-control">
        </div>
        <div class="form-group col-md-3">
        <label for="date_fin" class="mr-2 font-medium">Date de fin :</label>
        <input type="datetime-local" id="date_fin" name="date_fin" value="<?= date('Y-m-d\TH:i:s', strtotime('tomorrow midnight -1 second')) ?>" class="form-control">
        </div>
        <br>
        <div class="form-group col-md-3">
        <label for="filtre" class="mr-2 font-medium">Filtre :</label>
        <select id="filtre" name="filtre" class="form-control">
            <option value="none">Aucun</option>
            <option selected="selected" value="average">Moyenne</option>
            <option value="cleaned">Nettoyé</option>
        </select>
        </div>
        <br>
        <div class="form-group col-md-3">
            <label for="zone" class="mr-2 font-medium">Zone :</label>
            <select id="zone" name="zone" class="form-control mr-8">                
                <option selected="selected" value="sep">Toutes séparées</option>
                <option value="comb">Toutes combinées</option>
                <?php 
                foreach ($data_zone as $key_zone => $value_zone){
                    echo'<option value="'.$key_zone.'">'.$key_zone.'</option>';
                } 
                ?>
            </select>
        </div>
        <br>
        <div class="form-group col-md-3">
        <label for="affichage" class="mr-2 font-medium">Affichage :</label>
        <select id="affichage" name="affichage" class="form-control">
            <option selected="selected" value="col-12">100%</option>
            <option value="col-6">50%</option>
            <option value="col-3">25%</option>
        </select>        
        </div>
        <br>
        <div class="form-group col-md-12">
        <input type="submit" value="Afficher le graphique" class="btn btn-primary">
        </div>
    </form>








    <div id="graphique-container"></div>
    <canvas id="graphique"></canvas>


    <script>
        $(function() {
            $('form').submit(function(e) {
                e.preventDefault();

                var capteur_id = $('#capteur_id').val();
                var date_debut = $('#date_debut').val();
                var date_fin = $('#date_fin').val();
                var filtre_form = $('#filtre').val();

                var zone = $('#zone').val();
                var affichage = $('#affichage').val();

                var zone_capteur_get = [zone];
                if(zone == "sep"){
                        zone_capteur_get=zone_capteur;
                }
                if(zone == "comb"){
                    var zone_capteur_get=new Array();
                    zone_capteur_get[0]="comb";
                }

                // bouclage avec les zones
                for (let index_zone in zone_capteur_get) {

                    $.ajax({
                        url: 'get_data.php',
                        type: 'GET',
                        data: {
                            capteur_id: capteur_id,
                            date_debut: date_debut,
                            date_fin: date_fin,
                            filtre: filtre_form,
                            zone:zone_capteur_get[index_zone]
                            
                        },
                        dataType: 'json',
                        success: function(data) {
                            var capteurs = <?php echo json_encode($data_capteur); ?>;
   
                            // Séparation des données en deux tableaux selon le capteur, on reconstitue un tableau avec un id capteur

                            var data_capteurs = [];
                            $.each(data, function(i, item) {
                                if (!data_capteurs[item.capteur]) {
                                data_capteurs[item.capteur] = [];
                                }                            
                            data_capteurs[item.capteur].push({x: new Date(item.x), y: item.y, capteur: item.capteur});
                            
                            });
                       

                            var container = document.createElement('div');
                            container.classList.add('graphique-container');
                            container.classList.add(affichage);
                            container.innerHTML = '<button class="close">&times;</button>';
                            container.appendChild(document.createElement('canvas'));

                            var ctx = container.lastChild.getContext('2d');


                        var data_chart = {
                            datasets: []
                        };
                        //on boucle sur tous les capteurs que l'on a retravaillé avec les données
                        $.each(data_capteurs, function(i, data_capteur) {                                               
                            if(data_capteur){
                            if (capteurs[i]) {
                                if(capteur_id == "" || capteur_id == i){
                                    data_chart.datasets.push({
                                        label: 'Capteur ' + capteurs[i].nom_capteur,
                                        data: data_capteur,
                                        backgroundColor: hexToRgba(capteurs[i].couleur, 1),
                                        borderColor: hexToRgba(capteurs[i].couleur, 1),
                                        borderWidth: 1,
                                        fill: false,
                                        tension: 0
                                    });
                                }
                            }
                            }
                        });

                        var options = {
                            aspectRatio: 2,
                                scales: {
                                    xAxes: [{
                                        type: 'time',
                                        time: {
                                            unit: 'minute'
                                        },
                                        ticks: {
                                            source: 'data'
                                        }
                                    }],
                                    yAxes: [{
                                        ticks: {
                                            beginAtZero: true
                                        }
                                    }]
                                },                            
                                title:{
                                    display:true,
                                    text: 'Zone '+zone_capteur_get[index_zone],
                                }
                                
                        };

                        var chart = new Chart(ctx, {
                            type: 'line',
                            data: data_chart,
                            options: options
                        });

                        $('#graphique-container').prepend(container);
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            console.log('Erreur AJAX : ' + textStatus + ' ' + errorThrown);
                        }
                    });
                    //fin ajax
                }//fin boucle zone


            });
        });




        $(document).on('click', '.graphique-container .close', function() {
        $(this).parent().remove();
        });


        $(function() {
            $('form').trigger('submit');
        });


        function hexToRgba(hex, opacity) {
            var r = parseInt(hex.substring(1, 3), 16);
            var g = parseInt(hex.substring(3, 5), 16);
            var b = parseInt(hex.substring(5, 7), 16);

            return 'rgba(' + r + ', ' + g + ', ' + b + ', ' + opacity + ')';
        }

    </script>
</body>
</html>



