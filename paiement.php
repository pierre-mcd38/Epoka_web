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

        $stmt = $pdo->prepare("SELECT salarie.nom_salarie, salarie.prenom_salarie, mission.debut, mission.fin, commune.comNom, commune.comCp, mission.id_mission, mission.payer FROM salarie, mission, commune WHERE mission.id_salarie = salarie.id_salarie AND mission.id_commune = commune.comId AND mission.valid = 1 ORDER BY mission.debut ");
        $stmt->execute();

        $calculMontant = 'SELECT (((DATEDIFF(mission.fin, mission.debut) + 1) * parametre.forfait_journalier) + (ROUND(trajet.distance * parametre.idem_kilometre)*2)) as montant FROM mission JOIN trajet ON (trajet.id_arrive_com = mission.id_commune OR trajet.id_debut_com = mission.id_commune) JOIN salarie ON (salarie.id_agence = trajet.id_arrive_com OR salarie.id_agence = trajet.id_debut_com) AND salarie.id_salarie = mission.id_salarie JOIN parametre WHERE mission.id_mission = :idMis AND (trajet.id_debut_com = salarie.id_agence OR trajet.id_arrive_com = salarie.id_agence)';
    
        
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
        
            
            $reqMontant = $pdo->prepare($calculMontant);
            $reqMontant->bindParam(':idMis', $row['id_mission'], PDO::PARAM_INT);
            $reqMontant->execute();
            $montant = $reqMontant->fetch();
            
            if ($row['payer'] == 0) {
                $payer = '<td>
                    <form action="updatePayer.php" method="post">
                        <button value="'.$row["id_mission"].'" name="payer" type="submit" class="btn btn-sm btn-outline-dark">Rembourser</button>
                    </form>
                </td>';
            } else {
                $payer = '<td>Remboursée</td>';
            }
        
            echo ('<tr><td>' . $row["nom_salarie"] . '</td>
                <td>' . $row["prenom_salarie"] . '</td>
                <td>' . $row["debut"] . '</td>
                <td>' . $row["fin"] . '</td>
                <td>' . $row["comNom"] . ' ('. $row["comCp"] .')' . '</td>');
        
                // var_dump($montant); die;
            if ($montant) {
                echo '<td>' . $montant[0] . ' €</td>' . $payer . '</tr>';
            } else {
                echo '<td>Distance non définie</td><td></td></tr>';
            }
        }
        
        echo ('</tbody></table>');
        ?>
    

        
        </table>
    </div>

    
	
    <?php include('footer.php'); ?>
       