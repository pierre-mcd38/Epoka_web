<!doctype html>
<html lang="fr">
 <title>Paiement des Frais</title>
 <?php include('header.php'); 
  // Initialiser la session
  session_start();
  // Vérifiez si l'utilisateur est connecté, sinon redirigez-le vers la page de connexion
  if(!isset($_SESSION["util_id"]) || !isset($_SESSION["util_comptable"])){
    header("Location: index.php");
    exit(); 
    }
    if($_SESSION["util_comptable"] != 1 ) {
        echo '<div class="alert m-5 alert-danger" role="alert">
        Vous n\'êtes pas autorisé
        </div>';
    exit(); 
    }
?>
	
 <body>
 <div class="container my-5">
        <h3 class="my-3">Paiement des missions</h3>

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

        $id_salarie = $_SESSION['util_id'];

        $stmt = $pdo->prepare("SELECT utilisateurs.util_nom, utilisateurs.util_prenom, mission.mis_dateDepart, mission.mis_dateRetour, commune.com_nom, commune.com_CP, mission.mis_id, mission.mis_paye FROM utilisateurs, mission, commune WHERE mission.mis_idUtilisateur = utilisateurs.util_id AND mission.mis_idCommune = commune.com_id AND mission.mis_valide = 1 ORDER BY mission.mis_dateDepart ");
        $stmt->execute();
    
        echo ('<table class="table table-striped text-center table-responsive{-sm|-md|-lg|-xl}">
        <thead>
            <tr>
                <th>Nom du salarié</th>
                <th>Prénom du salarié</th>
                <th>Début de la mission</th>
                <th>Fin de la mission</th>
                <th>Lieu de la mission</th>
                <th>Montant</th>
                <th>Remboursement</th>
            </tr>
        </thead>
        <tbody>');
        
        while ($row = $stmt->fetch()) {
            $dateDepart = new DateTime($row['mis_dateDepart']);
            $dateRetour = new DateTime($row['mis_dateRetour']);
            $nbJours = $dateDepart->diff($dateRetour)->days; // Calcul du nombre de jours de la mission
            
            // Récupérer le prix d'hébergement depuis la table parametres
            $stmtParam = $pdo->prepare("SELECT par_MtHebergement FROM parametres");
            $stmtParam->execute();
            $parametres = $stmtParam->fetch();
            $prixHebergement = $parametres['par_MtHebergement'] * ($nbJours - 1); // Calcul du prix de l'hébergement
            
            // Récupérer la distance entre les deux villes de la mission depuis la table distance
            $stmtDistance = $pdo->prepare("SELECT dis_km FROM distance WHERE dis_idCommune1 = :idCommune1 AND dis_idCommune2 = :idCommune2");
            $stmtDistance->bindParam(':idCommune1', $row['mis_idCommune'], PDO::PARAM_INT);
            $stmtDistance->bindParam(':idCommune2', $row['mis_idCommune'], PDO::PARAM_INT);
            $stmtDistance->execute();
            $distance = $stmtDistance->fetch();
            
            if ($distance) {
                $montant = ($distance['dis_km'] * $parametres['par_MtauKM']) + $prixHebergement; // Calcul du montant total
            } else {
                $montant = 'Distance non définie'; // Si la distance n'est pas définie
            }
            
            if ($row['mis_paye'] == 0) {
                $payer = '<td>
                    <form action="updatePayer.php" method="post">
                        <button value="'.$row["mis_id"].'" name="payer" type="submit" class="btn btn-sm btn-outline-dark">Rembourser</button>
                    </form>
                </td>';
            } else {
                $payer = '<td>Remboursée</td>';
            }
        
            echo '<tr><td>' . $row["util_nom"] . '</td>
                <td>' . $row["util_prenom"] . '</td>
                <td>' . $row["mis_dateDepart"] . '</td>
                <td>' . $row["mis_dateRetour"] . '</td>
                <td>' . $row["com_nom"] . ' ('. $row["com_CP"] .')' . '</td>
                <td>' . $montant . ' €</td>' . $payer . '</tr>';
        }
        
        echo '</tbody></table>';
        ?>
    
    </div>

