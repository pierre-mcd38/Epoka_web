<?php
require("config.php");

// Connexion à la base de données avec PDO
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Erreur de connexion à la base de données : " . $e->getMessage();
    exit();
}

session_start();

try {
    if(isset($_POST["login"]) && isset($_POST["motdepasse"])) { 
        $login = $_POST["login"];
        $motdepasse = $_POST["motdepasse"];
        
        $stmt = $pdo->prepare("SELECT util_id, util_nom, util_prenom, util_mdp, util_responsable, util_comptable FROM utilisateurs WHERE util_id = :util_id AND util_mdp = :util_mdp");
        $stmt->execute(array(':util_id' => $login, ':util_mdp' => $motdepasse));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $_SESSION['util_id'] = $row['util_id'];
            $_SESSION['util_nom'] = $row['util_nom'];
            $_SESSION['util_prenom'] = $row['util_prenom'];
            $_SESSION['util_responsable'] = $row['util_responsable'];
            $_SESSION['util_comptable'] = $row['util_comptable'];
            
            header('Location: accueil.php');
            exit();
        } else {
            header('Location: index.php');
            exit();
        }
    } else {
        // Redirection si les paramètres ne sont pas définis
        header('Location: index.php');
        exit();
    }
} catch (Exception $e) {
    $erreur = "erreur lors de l'authentification : " . $e->getMessage();
    die($erreur);
}
?>
