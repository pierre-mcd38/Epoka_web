<!doctype html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Validation des missions</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <?php
    include('header.php');
    session_start();
    // Vérifiez si l'utilisateur est connecté, sinon redirigez-le vers la page de connexion
    if (!isset($_SESSION["util_id"]) || !isset($_SESSION["util_responsable"])) {
        header("Location: index.php");
        exit();
    }
    if ($_SESSION["util_responsable"] != 1) {
        echo '<div class="alert m-5 alert-danger" role="alert">
        Vous n\'êtes pas autorisé
        </div>';
        exit();
    }
    ?>

    <div class="container my-5">
        <h3 class="my-3">Validation des missions</h3>

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

        $stmt = $pdo->prepare("SELECT utilisateurs.util_nom, utilisateurs.util_prenom, mission.mis_dateDepart, mission.mis_dateRetour, commune.com_nom, commune.com_CP, mission.mis_id, mission.mis_valide FROM utilisateurs INNER JOIN mission ON utilisateurs.util_id = mission.mis_idUtilisateur INNER JOIN commune ON mission.mis_idCommune = commune.com_id WHERE utilisateurs.util_idResponsable = :id_salarie ORDER BY mission.mis_dateDepart");
        $stmt->execute(array(':id_salarie' => $id_salarie));

        echo '<table class="table table-striped text-center table-responsive{-sm|-md|-lg|-xl} rounded-2">
        <thead>
            <tr>
                <th>Nom du salarié</th>
                <th>Prénom du salarié</th>
                <th>Début de la mission</th>
                <th>Fin de la mission</th>
                <th>Lieu de la mission</th>
                <th>Validation</th>
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
            return ($jour[$d2[0]] . " " . $d2[1] . " " . $mois[$d2[2]] . " " . $d2[3]);
        };

        function formaterDateFr($date)
        { // j/m/aa vers jj/mm/aaaa
            if (strpos($date, "/") < 2) $date = "0" . $date;
            $lg = strlen($date);
            $result = substr($date, 0, 3);
            $date = substr($date, 3, $lg);
            if (strpos($date, "/") < 2) $date = "0" . $date;
            $lg = strlen($date);
            $result = $result . substr($date, 0, 3);
            $date = substr($date, 3, $lg);
            if (strlen($date) == 2) $date = "20" . $date;
            $result = $result . $date;
            return $result;
        };

        setlocale(LC_TIME, 'fr_FR.UTF-8'); // Définit le format de la date en français

        while ($row = $stmt->fetch()) {
            if ($row['mis_valide'] == 0) {
                $validation = '<td>
                    <form action="valider.php" method="post">
                    <button value="' . $row["mis_id"] . '" name="valider" type="submit" class="btn btn-sm btn-outline-dark">Valider</button>
                    </form>';
            } else {
                $validation = '<td>Validée';
            }

            // Convertir les dates en format français avec les fonctions définies ci-dessus
            $dateDepart_fr = dateMySQLToFrLong($row["mis_dateDepart"]);
            $dateRetour_fr = dateMySQLToFrLong($row["mis_dateRetour"]);

            echo '<tr class="table-row">
                <td>' . $row["util_nom"] . '</td>
                <td>' . $row["util_prenom"] . '</td>
                <td>' . $dateDepart_fr . '</td>
                <td>' . $dateRetour_fr . '</td>
                <td>' . $row["com_nom"] . ' (' . $row["com_CP"] . ')</td>' . $validation . '</tr>';
        }
        echo '</tbody></table>';





        ?>
    </div>

</body>

</html>