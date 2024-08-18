<?php 
@include 'connection.php';

//liste des stagiaire qui sont absent
ob_start();
require_once('tcpdf.php');

if (isset($_GET['download']) && isset($_GET['month'])) {
    $selectedMonth = intval($_GET['month']);
    $selectedYear = date('Y'); // Assuming you want the current year

    // Query to fetch all interns (stagiaires) with status 'Archive' and whose start or end date falls within the selected month
    $query = mysqli_query($conn, "
        SELECT * 
        FROM `absence` 
        where MONTH(date_d) = '$selectedMonth' AND YEAR(date_d) = '$selectedYear'
    ");

    // Create a new PDF document
    $pdf = new TCPDF();
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Application');
    $pdf->SetTitle('Stagiaires qui sont absents du Mois');
    $pdf->AddPage();

    // Check if any stagiaires were found for the selected month
    if (mysqli_num_rows($query) > 0) {
        // Start building the HTML content for the PDF
        $html = '
        <style>
            h1 {
                font-family: Arial, sans-serif;
                color:#272745;
                font-size: 24px;
                text-align: center;
                margin-bottom: 20px;
            }
            table {
                width: 100%;
            }
            th, td {
                border: 0.5px solid black;
                padding: 10px;
            }
            th {
                background-color: #f2f2f2;
                color: #333;
            }
        </style>
        <h1>Liste des Stagiaires Qui Sont Absents du Mois ' . $selectedMonth . '</h1>
        <table>
            <thead>
                <tr>
                    <th>CIN</th>
                    <th>Début</th>
                    <th>Fin</th>
                    <th>Justification</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                </tr>
            </thead>
            <tbody>
        ';

        // Fetch and append each intern's details as a table row
        while ($stagiaire = mysqli_fetch_assoc($query)) {
            $html .= '
                <tr>
                    <td>' . $stagiaire['cin_stagiaire'] . '</td>
                    <td>' . $stagiaire['date_d'] . '</td>
                    <td>' . $stagiaire['date_f'] . '</td>
                    <td>' . $stagiaire['justification'] . '</td>
                    <td>' . $stagiaire['nom'] . '</td>
                    <td>' . $stagiaire['prenom'] . '</td>
                </tr>
            ';
        }

        // Close the table and the HTML content
        $html .= '
            </tbody>
        </table>
        ';

        // Write the HTML content to the PDF
        $pdf->writeHTML($html, true, false, true, false, '');
    } else {
        // If no stagiaires found, add a message to the PDF
        $html = '
        <style>
            h1 {
                font-family: Arial, sans-serif;
                color:#272745;
                font-size: 24px;
                text-align: center;
                margin-bottom: 20px;
            }
            p {
                font-family: Arial, sans-serif;
                font-size: 16px;
                text-align: center;
                color: #333;
            }
        </style>
        <h1>Liste des Stagiaires Qui Sont Absents du Mois ' . $selectedMonth . '</h1>
        <p>Aucun stagiaire n´est absent pour ce mois.</p>
        ';
        
        // Write the message to the PDF
        $pdf->writeHTML($html, true, false, true, false, '');
    }

    // Output the PDF document
    $pdf->Output('stagiaires_mois_' . $selectedMonth . '.pdf', 'I');
    exit;
}

ob_end_clean();


// Add an encadrant
if (isset($_POST['add'])) {
    $cin = mysqli_real_escape_string($conn, $_POST['cin']);
    $nom = mysqli_real_escape_string($conn, $_POST['nom']);
    $prenom = mysqli_real_escape_string($conn, $_POST['prenom']);
    $date_d = mysqli_real_escape_string($conn, $_POST['date_d']);
    $date_f = mysqli_real_escape_string($conn, $_POST['date_f']);
    $justif = mysqli_real_escape_string($conn, $_POST['justif']);

    $insert_query = mysqli_query($conn, "INSERT INTO `absence` (cin_stagiaire, date_d, date_f, justification, nom, prenom) VALUES ('$cin', '$date_d', '$date_f', '$justif', '$nom', '$prenom')") or die('query failed');

    if ($insert_query) {
        $message[] = 'Marked Successfully';
    } else {
        $message[] = 'Could Not Be Marked';
    }
}

// DELETE an encadrant
if (isset($_GET['delete'])) {
    $delete_nom = mysqli_real_escape_string($conn, $_GET['delete']); // Escape the input
    $delete_query = mysqli_query($conn, "DELETE FROM `absence` WHERE cin_stagiaire = '$delete_nom'") or die("connection failed");
    if ($delete_query) {
        header('location:absence.php');
        $message[] = 'The Stagiaire has been deleted';
    } else {
        header('location:absence.php');
        $message[] = 'The Stagiaire could not be deleted';
    }
}

// UPDATE an encadrant
if (isset($_POST['update_product'])) {
    $update_p_id = mysqli_real_escape_string($conn, $_POST['cin']);
    $update_p_debut = mysqli_real_escape_string($conn, $_POST['date_d']);
    $update_p_fin = mysqli_real_escape_string($conn, $_POST['date_f']);
    $update_p_justifi = mysqli_real_escape_string($conn, $_POST['justifi']);
    $update_p_nom = mysqli_real_escape_string($conn, $_POST['nom']);
    $update_p_prenom = mysqli_real_escape_string($conn, $_POST['prenom']);

    $update_query = mysqli_query($conn, "UPDATE `absence` SET date_d = '$update_p_debut', date_f = '$update_p_fin', justification = '$update_p_justifi', nom = '$update_p_nom', prenom = '$update_p_prenom' WHERE cin_stagiaire = '$update_p_id'");

    if ($update_query) {
        $message[] = 'Mark updated successfully';
        header('location:absence.php');
    } else {
        $message[] = 'Mark could not be updated';
        header('location:absence.php');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Absence</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" />
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="header.css" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>
<body>

    <?php
    if (isset($message)) {
        foreach ($message as $message) {
            echo '<div class="message"><span>' . $message . '</span> <i class="fas fa-times" onclick="this.parentElement.style.display = `none`;"></i></div>';
        }
    }
    ?>
    <!--------------------------------------header----------------------------------------------->
    <nav class="navbar navbar-inverse">
        <div class="container-fluid">
            <div class="navbar-header">
                <a class="navbar-brand" href="https://www.google.com/maps/place/Info+Pro/@33.604811,-7.523636,16z/data=!4m6!3m5!1s0xda7cb7d973efc39:0x4075a5b513770b2c!8m2!3d33.6048106!4d-7.5236365!16s%2Fg%2F11bxc4qs9j?hl=fr-FR&entry=ttu">INFOPRO</a>
            </div>
            <ul class="nav navbar-nav">
                <li><a class="brand" href="dashbord.php">Accueil</a></li>
                <li><a class="brand" href="stagiaire.php">Stagiaire</a></li>
                <li><a class="brand" href="encadrant.php">Encadrants</a></li>
                <li><a class="brand" href="service.php">Service</a></li>
                <li><a class="brand" href="archive.php">Archive</a></li>
                <li><a class="brand" href="absence.php">Abscence</a></li>
            </ul>
        </div>
    </nav>
    <!------------------------------------------------------------------------------------------->

    <!-------------------------------------table container------------------------------------>
    <h1 class="headline">Gestion Des Absences</h1>
   
    <div class="container">

    <section>
            <form action="" method="post" class="add-product-form" enctype="multipart/form-data">
                <h3>Marquer l'Absence</h3>
                <input type="text" name="cin" placeholder="CIN d'un Stagiaire" class="box" required>
                <input type="date" name="date_d"  placeholder="Date Debut" class="box" required>
                <input type="date" name="date_f" placeholder="Date Fin" class="box" required>
                <input type="text" name="justif" placeholder="Justification" class="box" required>
                <input type="text" name="nom" placeholder="Nom De Stagiaire" class="box" required>
                <input type="text" name="prenom" placeholder="Prenom De Stagiaire" class="box" required>
                <input type="submit" value="Marquer" name="add" class="btn">
            </form>
        </section>

        <div class="search-container">
            <form action="absence.php" method="GET">
                <input type="text" name="search" placeholder="Search..." class="search-input">
                <button type="submit" class="search-button">Search</button>
            </form>
        </div>


        <section class="display-product-table">
            <table>
                <thead>
                    <tr>
                        <th>CIN</th>
                        <th>Date Debut</th>
                        <th>Date Fin</th>
                        <th>Justification</th>
                        <th>NOM</th>
                        <th>PRENOM</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
                    $query = "SELECT * FROM `absence`";
                    if ($search) {
                        $query .= " WHERE cin_stagiaire LIKE '%$search%' OR nom LIKE '%$search%' OR prenom LIKE '%$search%'";
                    }
                    $select_product = mysqli_query($conn, $query);

                    if (mysqli_num_rows($select_product) > 0) {
                        while ($row = mysqli_fetch_assoc($select_product)) {
                    ?>
                    <tr>
                        <td><?php echo $row['cin_stagiaire']; ?></td>
                        <td><?php echo $row['date_d']; ?></td>
                        <td><?php echo $row['date_f']; ?></td>
                        <td><?php echo $row['justification']; ?></td>
                        <td><?php echo $row['nom']; ?></td>
                        <td><?php echo $row['prenom']; ?></td>
                        <td>
                            <a href="absence.php?delete=<?php echo $row['cin_stagiaire']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this?');"> <i class="fas fa-trash"></i> Supprimer </a>
                            <a href="absence.php?edit=<?php echo $row['cin_stagiaire']; ?>" class="option-btn"> <i class="fas fa-edit"></i> Modifier </a>
                        </td>
                    </tr>
                    <?php
                        }
                    } else {
                        echo "<tr><td colspan='6' class='empty'>No encadrants found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>
        <form action="absence.php" method="GET">
    <label for="month" class="lab">Téléchargez la liste des stagiaires absents pour le mois de votre choix :</label>
    <div class="form-group">
      <select class="form-control" id="month" name="month">
        <option value="1">Janvier</option>
        <option value="2">Février</option>
        <option value="3">Mars</option>
        <option value="4">Avril</option>
        <option value="5">Mai</option>
        <option value="6">Juin</option>
        <option value="7">Juillet</option>
        <option value="8">Août</option>
        <option value="9">Septembre</option>
        <option value="10">Octobre</option>
        <option value="11">Novembre</option>
        <option value="12">Décembre</option>
      </select>
      
      <button type="submit" class="btn btn-success" name="download" value="1">Télécharger</button>
    </div>
</form>

       
        <!------------------------------------------------------------------------------------------->

        <!-------------------------------------form de modification---------------------------------->
        <section class="edit-form-container">
            <?php
            if (isset($_GET['edit'])) {
                $edit_nom = mysqli_real_escape_string($conn, $_GET['edit']);
                $edit_query = mysqli_query($conn, "SELECT * FROM `absence` WHERE cin_stagiaire = '$edit_nom'");
                if (mysqli_num_rows($edit_query) > 0) {
                    while ($fetch_edit = mysqli_fetch_assoc($edit_query)) {
            ?>
            <form action="" method="post" enctype="multipart/form-data">
                <input type="hidden" name="cin_stagiaire" value="<?php echo $fetch_edit['cin_stagiaire']; ?>">
                <input type="date" class="box" required name="date_d" value="<?php echo $fetch_edit['date_d']; ?>">
                <input type="date" class="box" required name="date_f" value="<?php echo $fetch_edit['date_f']; ?>">
                <input type="text" class="box" required name="justifi" value="<?php echo $fetch_edit['justification']; ?>">
                <input type="text" class="box" required name="nom" value="<?php echo $fetch_edit['nom']; ?>">
                <input type="text" class="box" required name="prenom" value="<?php echo $fetch_edit['prenom']; ?>">
                <input type="submit" value="Updatet" name="update_product" class="btn">
                <input type="reset" value="Cancel" id="close-edit" class="option-btn">
            </form>
            <?php
                    }
                }
                echo "<script>document.querySelector('.edit-form-container').style.display = 'flex';</script>";
            }
            ?>
        </section>
        <!------------------------------------------------------------------------------------------->
       
    </div>

    
    <script src="absence.js"></script>
</body>
</html>
