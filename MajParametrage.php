<?php

session_start();
if (!isset($_SESSION["util_id"])) {
    header("Location: index.php");
    exit();
}

require ("config.php");
$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);

$par_MtauKM = $_POST['par_MtauKM'];
$par_MtHebergement = $_POST['par_MtHebergement'];

if (empty($par_MtauKM) || empty($par_MtHebergement)) {
    echo "Erreur : Veuillez saisir tous les champs ! ";
    echo "<a href='parametrage.php'>Retour</a>";
} else {
    $req = $pdo->prepare('UPDATE parametres SET par_MtauKM = :par_MtauKM, par_MtHebergement = :par_MtHebergement');
    $req->bindValue(':par_MtauKM', $par_MtauKM, PDO::PARAM_STR);
    $req->bindValue(':par_MtHebergement', $par_MtHebergement, PDO::PARAM_STR);
    $req->execute();

    echo '<div class="alert m-5 alert-success" role="alert">
    Paramètre modifié avec succès
    </div>';

    header("Refresh:0;parametrage.php");
}



?>