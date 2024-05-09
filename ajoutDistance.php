<?php

session_start();
if(!isset($_SESSION["util_id"])){
  header("Location: index.php");
  exit(); 
}

require("config.php");
$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);

$dis_idCommune1 = $_POST['dis_idCommune1'];
$dis_idCommune2 = $_POST['dis_idCommune2'];
$dis_km = $_POST['dis_km'];

if(empty($dis_idCommune1) || empty($dis_idCommune2) || empty($dis_km) || !filter_var($dis_km, FILTER_VALIDATE_INT)) {

    echo "Erreur : Veuillez saisir tous les champs ! ";
    echo "<a href='parametrage.php'>Retour</a>";

} else {

    // Vérifier si les deux IDs de communes sont différents
    if ($dis_idCommune1 != $dis_idCommune2) {

        // Tri des villes par ordre alphabétique
        $communeQuery = $pdo->prepare('SELECT com_nom FROM commune WHERE com_id IN (:dis_idCommune1, :dis_idCommune2)');
        $communeQuery->bindParam(':dis_idCommune1', $dis_idCommune1, PDO::PARAM_INT);
        $communeQuery->bindParam(':dis_idCommune2', $dis_idCommune2, PDO::PARAM_INT);
        $communeQuery->execute();
        $communes = $communeQuery->fetchAll(PDO::FETCH_COLUMN);

        sort($communes);

        // Mise à jour des IDs de communes après le tri
        $dis_idCommune1 = $communes[0];
        $dis_idCommune2 = $communes[1];

        // Vérifier si la distance entre les deux villes existe déjà
        $existingDistanceQuery = $pdo->prepare('SELECT COUNT(*) AS count FROM distance WHERE (dis_idCommune1 = :dis_idCommune1 AND dis_idCommune2 = :dis_idCommune2) OR (dis_idCommune1 = :dis_idCommune2 AND dis_idCommune2 = :dis_idCommune1)');
        $existingDistanceQuery->bindParam(':dis_idCommune1', $dis_idCommune1, PDO::PARAM_INT);
        $existingDistanceQuery->bindParam(':dis_idCommune2', $dis_idCommune2, PDO::PARAM_INT);
        $existingDistanceQuery->execute();
        $existingDistance = $existingDistanceQuery->fetch(PDO::FETCH_ASSOC);

        if ($existingDistance['count'] > 0) {
            echo "Erreur : La distance entre ces deux villes existe déjà ! ";
            echo "<a href='parametrage.php'>Retour</a>";
        } else {
            // Insérer la distance entre les deux villes dans la base de données
            $insertDistanceQuery = $pdo->prepare('INSERT INTO distance (dis_idCommune1, dis_idCommune2, dis_km) VALUES (:dis_idCommune1, :dis_idCommune2, :dis_km)');
            $insertDistanceQuery->bindParam(':dis_idCommune1', $dis_idCommune1, PDO::PARAM_INT);
            $insertDistanceQuery->bindParam(':dis_idCommune2', $dis_idCommune2, PDO::PARAM_INT);
            $insertDistanceQuery->bindParam(':dis_km', $dis_km, PDO::PARAM_INT);
            $insertDistanceQuery->execute();

            header("Refresh:0;parametrage.php");
        }
    } else {
        echo "Erreur : Les deux villes sélectionnées sont identiques ! ";
        echo "<a href='parametrage.php'>Retour</a>";
    }
}

?>
