<?php
        session_start();
        if(!isset($_SESSION["util_id"])){
          header("Location: index.php");
          exit(); 
        }
        
        require("config.php");
		$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
		$stmt = $pdo->prepare("UPDATE mission SET mis_paye = 1 WHERE mis_id = ".$_POST['payer'].";");
		$stmt->execute();
		header("Refresh:0;paiement.php");
?>