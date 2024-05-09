<!doctype html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Paiement des Frais</title>
    <?php include 'header.php';
    // Initialiser la session
    session_start();
    // Vérifiez si l'utilisateur est connecté, sinon redirigez-le vers la page de connexion
    if (!isset($_SESSION["util_id"]) || !isset($_SESSION["util_comptable"])) {
        header("Location: index.php");
        exit();
    }
    if ($_SESSION["util_comptable"] != 1) {
        echo '<div class="alert alert-danger m-5" role="alert">
        Vous n\'êtes pas autorisé
        </div>';
        exit();
    }
    ?>

<body>
    <div class="container my-5">
        <h3 class="my-3">Paiement des missions</h3>

        <?php
        require "config.php";

        // Connexion à la base de données avec PDO
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Erreur de connexion à la base de données : " . $e->getMessage();
            exit();
        }

        $id_salarie = $_SESSION['util_id'];

        $stmt = $pdo->prepare("
            SELECT
                utilisateurs.util_nom,
                utilisateurs.util_prenom,
                mission.mis_dateDepart,
                mission.mis_dateRetour,
                commune.com_nom,
                commune.com_CP,
                mission.mis_id,
                mission.mis_paye,
                distance.dis_km,
                agence.age_id,
                agence.age_idCommune,
                distance.dis_idCommune1,
                distance.dis_idCommune2
            FROM
                utilisateurs
            INNER JOIN
                mission ON mission.mis_idUtilisateur = utilisateurs.util_id
            INNER JOIN
                commune ON mission.mis_idCommune = commune.com_id
            LEFT JOIN
                distance ON (distance.dis_idCommune1 = utilisateurs.util_idAgence AND distance.dis_idCommune2 = mission.mis_idCommune) 
                            OR (distance.dis_idCommune1 = mission.mis_idCommune AND distance.dis_idCommune2 = utilisateurs.util_idAgence)
            LEFT JOIN
                agence ON utilisateurs.util_idAgence = agence.age_id
            WHERE
                mission.mis_valide = 1
            ORDER BY
                mission.mis_dateDepart
        ");
        $stmt->execute();

        echo '<table class="table table-striped text-center">
            <thead class="thead-light">
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
            <tbody>';

        function dateMySQLToFrLong($date)
        {
            //--- Les noms des jours en français
            $jour[0] = "Dimanche";
            $jour[1] = "Lundi";
            $jour[2] = "Mardi";
            $jour[3] = "Mercredi";
            $jour[4] = "Jeudi";
            $jour[5] = "Vendredi";
            $jour[6] = "Samedi";
            //--- Les noms des mois en français
            $mois[1] = "janvier";
            $mois[2] = "février";
            $mois[3] = "mars";
            $mois[4] = "avril";
            $mois[5] = "mai";
            $mois[6] = "juin";
            $mois[7] = "juillet";
            $mois[8] = "août";
            $mois[9] = "septembre";
            $mois[10] = "octobre";
            $mois[11] = "novembre";
            $mois[12] = "décembre";

            $d1 = date("w/j/n/Y", strtotime($date));
            $d2 = explode("/", $d1);
            return $jour[$d2[0]] . " " . $d2[1] . " " . $mois[$d2[2]] . " " . $d2[3];
        }

        function formaterDateFr($date)
        { // j/m/aa vers jj/mm/aaaa
            if (strpos($date, "/") < 2)
                $date = "0" . $date;
            $lg = strlen($date);
            $result = substr($date, 0, 3);
            $date = substr($date, 3, $lg);
            if (strpos($date, "/") < 2)
                $date = "0" . $date;
            $lg = strlen($date);
            $result = $result . substr($date, 0, 3);
            $date = substr($date, 3, $lg);
            if (strlen($date) == 2)
                $date = "20" . $date;
            $result = $result . $date;
            return $result;
        }

        while ($row = $stmt->fetch()) {
            $dateDepart = new DateTime($row['mis_dateDepart']);
            $dateRetour = new DateTime($row['mis_dateRetour']);
            $nbNuits = $dateDepart->diff($dateRetour)->days + 1; // Calcul du nombre de nuits
        
            // Récupérer les paramètres
            $stmtParam = $pdo->prepare("SELECT par_MtauKM, par_MtHebergement FROM parametres");
            $stmtParam->execute();
            $parametres = $stmtParam->fetch();

            // Calcul du prix de l'hébergement
            $prixHebergement = $parametres['par_MtHebergement'] * ($nbNuits - 1);

            // Vérifier si une distance existe entre l'agence et la commune de la mission dans la table distance
            $stmtDistance = $pdo->prepare("SELECT dis_km FROM distance WHERE (dis_idCommune1 = :agenceCommune AND dis_idCommune2 = :missionCommune) OR (dis_idCommune1 = :missionCommune AND dis_idCommune2 = :agenceCommune)");
            $stmtDistance->bindParam(':agenceCommune', $row['dis_idCommune1'], PDO::PARAM_INT);
            $stmtDistance->bindParam(':missionCommune', $row['dis_idCommune2'], PDO::PARAM_INT);
            $stmtDistance->execute();
            $distance = $stmtDistance->fetch();

            if ($distance) {
                // Une distance existe, calculer le montant
                $montant = ($distance['dis_km'] * $parametres['par_MtauKM']) + $prixHebergement;
            } else {
                // Aucune distance trouvée
                $montant = 'Distance non définie';
            }
            if ($row['mis_paye'] == 0) {
                $payer = '<td>
                        <form action="payer.php" method="post">
                            <button value="' . $row["mis_id"] . '" name="payer" type="submit" class="btn btn-sm btn-outline-dark">Rembourser</button>
                        </form>
                    </td>';
            } else {
                $payer = '<td>Remboursée</td>';
            }

            echo '<tr><td>' . $row["util_nom"] . '</td>
                <td>' . $row["util_prenom"] . '</td>
                <td>' . dateMySQLToFrLong($row["mis_dateDepart"]) . '</td>
                <td>' . dateMySQLToFrLong($row["mis_dateRetour"]) . '</td>
                <td>' . $row["com_nom"] . ' (' . $row["com_CP"] . ')' . '</td>
                <td>' . $montant . ' </td>' . $payer . '</tr>';
        }

        echo '</tbody></table>';
        ?>
    </div>

    <!-- Link vers jQuery et Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>