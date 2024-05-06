<!doctype html>
<html lang="fr">
 <title>Paramétrage</title>
 <?php include('header.php'); 
  // Initialiser la session
  session_start();
  // Vérifiez si l'utilisateur est connecté, sinon redirigez-le vers la page de connexion
  if(!isset($_SESSION["util_id"]) || !isset($_SESSION["util_responsable"]) || !isset($_SESSION["util_comptable"])){
    header("Location: index.php");
    exit(); 
    }
    if($_SESSION["util_comptable"] != 1 ) {
        echo '<div class="alert m-5 alert-danger" role="alert">
        Vous n\'êtes pas autorisé
        </div>';
    exit(); 
    }
   if($_SESSION["util_responsable"] == 1 ) {
      echo '<div class="alert m-5 alert-danger" role="alert">
      Vous n\'êtes pas autorisé
      </div>';
   exit(); 
   }
?>
	
   <body>
   <div class="container my-5 w-25 text-center ">
      <h3 class="my-3">Paramétrage de l'application</h3>

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


      ?>
     
      <form name="indemnité" method="post" action="updateParametrage.php">
      <div class="form-outline mb-4">
			<label class="form-label" for="par_MtauKM" style="font-size:smaller; ">Remboursement au kilométrage :</label>
			<input type="text" name="par_MtauKM" style="margin-right:10px"class="form-control text-center">
		</div>
				
		<div class="form-outline mb-4">
			<label class="form-label" for="par_MtHebergement" style="font-size:smaller; ">Indemnité d'hebergement journalié :</label>
			<input type="number" name="par_MtHebergement" style="margin-right:10px"class="form-control text-center">
		</div>
      <input type="submit" class="btn btn-sm btn-outline-dark" placeholder="Modifier">
      </form>
      </div>

      <div class="container justify-content-between my-5">
      <hr class="my-5">
   <div class="row">
      <div class="col">
         <div class="container text-center">
            <h3 class="my-3">Ajouter une distance</h3>
            <form name="indemnité" method="post" action="insertTrajet.php">
               <div class="form-outline mb-4">
               <label class="form-label" for="dis_idCommune1" style="font-size:smaller;">De :</label>
                  <select name="dis_idCommune1" class="form-select">
                     <option value="">Sélectionner une commune</option>
                     <?php
                     
                     $query = $pdo->query("SELECT * FROM commune ORDER BY com_Nom");
                     while ($commune = $query->fetch()) {
                           echo '<option value="'.$commune['com_id'].'">'.$commune['com_nom']. ' ('.$commune['com_CP'].')'.'</option>';
                     }
                     ?>
                  </select>
               </div>
                     
               <div class="form-outline mb-4">
                  <label class="form-label" for="dis_idCommune2" style="font-size:smaller; ">A :</label>
                  <select name="dis_idCommune2" class="form-select">
                     <option value="">Sélectionner une commune</option>
                     <?php
                     
                     $query = $pdo->query("SELECT * FROM commune ORDER BY com_nom");
                     while ($commune = $query->fetch()) {
                           echo '<option value="'.$commune['com_id'].'">'.$commune['com_nom']. ' ('.$commune['com_CP'].')'.'</option>';
                     }
                     ?>
                  </select>
               </div>

               <div class="form-outline mb-4">
                  <label class="form-label" for="dis_km" style="font-size:smaller; ">Distance en km :</label>
                  <input type="number" name="dis_km" style="margin-right:10px"class="form-control">
               </div>

               <input type="submit" class="btn btn-sm btn-outline-dark" placeholder="Ajouter">
            </form>
         </div>
      </div>

      <div class="col">
         <div class="container">
            <table class="table table-striped text-center table-responsive{-sm|-md|-lg|-xl} rounded-2">
               <thead>
                  <tr>
                     <th>De </th>
                     <th>à</th>
                     <th>Distance</th>
                  </tr>
               </thead>
               <tbody>
                  <?php
                  $stmt = $pdo->prepare("SELECT distance.dis_idCommune1 as De, distance.dis_idCommune2 as A, trajet.distance, trajet.id_arrive_com, trajet.id_debut_com FROM distance WHERE trajet.id_debut_com = depart.comId AND trajet.id_arrive_com = arrive.comId; ");
                  $stmt->execute();
                  
                  while ($row = $stmt->fetch()) {
                     echo ('<tr><td>' . $row["De"] . '</td>
                     <td>' . $row["A"] . '</td>
                     <td>' . $row["distance"] .' Km'. '</td></tr>');
                  }
                  ?>
               </tbody>
            </table>
         </div>
      </div>
      <hr class="my-5">
   </div>
</div>

   
      
        
        

      
	
<?php //include('footer.php'); ?>
       