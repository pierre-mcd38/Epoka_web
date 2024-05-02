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
        $stmt->execute(array(':id_salarie' => $id_salarie)); // Correction ici

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

        while ($row = $stmt->fetch()) {
            if ($row['mis_valide'] == 0) { // Correction du nom de colonne
                $validation = '<td>
                <form action="majValidation.php" method="post">
                <button value="' . $row["mis_id"] . '" name="valider" type="submit" class="btn btn-sm btn-outline-dark">Valider</button>
                </form>';
            } else {
                $validation = '<td>Validée';
            }

            echo '<tr class="table-row">
                <td>' . $row["util_nom"] . '</td>
                <td>' . $row["util_prenom"] . '</td>
                <td>' . $row["mis_dateDepart"] . '</td>
                <td>' . $row["mis_dateRetour"] . '</td>
                <td>' . $row["com_nom"] . ' (' . $row["com_CP"] . ')</td>' . $validation . '</tr>';
        }
        echo '</tbody></table>';

        ?>
    </div>

</body>

</html>