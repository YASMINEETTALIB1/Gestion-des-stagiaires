<?php 
@include 'connection.php';

ob_start();
require_once('tcpdf.php');

if (isset($_GET['download']) && isset($_GET['month'])) {
    $selectedMonth = intval($_GET['month']);
    $selectedYear = date('Y'); // Assuming you want the current year

    // Query to fetch all interns (stagiaires) with status 'Archive' and whose start or end date falls within the selected month
    $query = mysqli_query($conn, "
        SELECT * 
        FROM `stagiaire` 
        WHERE statut = 'Archive'
        AND (
            (MONTH(date_d) = '$selectedMonth' AND YEAR(date_d) = '$selectedYear') 
           )
    ");

    // Create a new PDF document
    $pdf = new TCPDF();
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Application');
    $pdf->SetTitle('Stagiaires du Mois');
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
        <h1>Liste des Stagiaires du Mois ' . $selectedMonth . '</h1>
        <table>
            <thead>
                <tr>
                    <th>CIN</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Début</th>
                    <th>Fin</th>
                    <th>Statut</th>
                    <th>Téléphone</th>
                    <th>Service</th>
                    <th>Etablissement</th>
                </tr>
            </thead>
            <tbody>
        ';

        // Fetch and append each intern's details as a table row
        while ($stagiaire = mysqli_fetch_assoc($query)) {
            $html .= '
                <tr>
                    <td>' . $stagiaire['cin'] . '</td>
                    <td>' . $stagiaire['Nom'] . '</td>
                    <td>' . $stagiaire['prenom'] . '</td>
                    <td>' . $stagiaire['date_d'] . '</td>
                    <td>' . $stagiaire['date_f'] . '</td>
                    <td>' . $stagiaire['statut'] . '</td>
                    <td>' . $stagiaire['Telephone'] . '</td>
                    <td>' . $stagiaire['service'] . '</td>
                    <td>' . $stagiaire['etablissement'] . '</td>
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
        <h1>Liste des Stagiaires du Mois ' . $selectedMonth . '</h1>
        <p>Aucun Historique trouvé pour le mois sélectionné.</p>
        ';
        
        // Write the message to the PDF
        $pdf->writeHTML($html, true, false, true, false, '');
    }

    // Output the PDF document
    $pdf->Output('stagiaires_mois_' . $selectedMonth . '.pdf', 'I');
    exit;
}

ob_end_clean();


// DELETE an stagiaire
if (isset($_GET['delete'])) {
    $delete_nom = mysqli_real_escape_string($conn, $_GET['delete']); // Escape the input
    $delete_query = mysqli_query($conn, "DELETE FROM `stagiaire` WHERE cin = '$delete_nom'") or die("connection failed");
    if ($delete_query) {
        header('location:stagiaire.php');
        $message[] = 'The encadrant has been deleted';
    } else {
        header('location:stagiaire.php');
        $message[] = 'The encadrant could not be deleted';
    }
}

// UPDATE an stagiaire
if (isset($_POST['update_product'])) {
    $update_p_id = mysqli_real_escape_string($conn, $_POST['cin']);
    $update_p_enc = mysqli_real_escape_string($conn, $_POST['cin_enc']);
    $update_p_name = mysqli_real_escape_string($conn, $_POST['nom']);
    $update_p_prenom = mysqli_real_escape_string($conn, $_POST['prenom']);
    $update_p_dated = mysqli_real_escape_string($conn, $_POST['date_d']);
    $update_p_datef = mysqli_real_escape_string($conn, $_POST['date_f']);
    $update_p_statut = mysqli_real_escape_string($conn, $_POST['statut']);
    $update_p_tel = mysqli_real_escape_string($conn, $_POST['tel']);
    $update_p_service = mysqli_real_escape_string($conn, $_POST['service']);
    $update_p_etab = mysqli_real_escape_string($conn, $_POST['etabl']);

    $update_query = mysqli_query($conn, "UPDATE `stagiaire` SET cin_enc = '$update_p_enc' , nom = '$update_p_name', prenom = '$update_p_prenom', date_d = '$update_p_dated', date_f = '$update_p_datef' ,  statut = '$update_p_statut' ,  Telephone = '$update_p_tel' ,  service = ' $update_p_service' ,  etablissement = '$update_p_etab'  WHERE cin = '$update_p_id'");

    if ($update_query) {
        $message[] = 'Encadrant updated successfully';
        header('location:stagiaire.php');
        updateStagiaireStatut($conn);
    } else {
        $message[] = 'Encadrant could not be updated';
        header('location:stagiaire.php');
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Archives</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" />
    <link rel="stylesheet" href="header.css" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="archive.css">
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
                <a class="navbar-brand" href="#">INFOPRO</a>
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
    <h1 class="headline">Notre Historiques</h1>
   
    <div class="container">

        <div class="search-container">
            <form action="archive.php" method="GET">
                <input type="text" name="search" placeholder="Search..." class="search-input">
                <button type="submit" class="search-button">Search</button>
            </form>
        </div>

        <section class="display-product-table">
            <table>
                <thead>
                    <tr>
                        <th>CIN</th>
                        <th>CIN_Enc</th>
                        <th>NOM</th>
                        <th>PRENOM</th>
                        <th>DEBUT</th>
                        <th>FIN</th>
                        <th>STATUT</th>
                        <th>TELEPHONE</th>
                        <th>SERVICE</th>
                        <th>ETABLISSEMENT</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
                    $query = "SELECT * FROM `stagiaire` WHERE statut = 'archive'";
                    if ($search) {
                        $query .= " AND (cin LIKE '%$search%' OR nom LIKE '%$search%' OR prenom LIKE '%$search%')";
                    }
                    $select_product = mysqli_query($conn, $query);
                    if (!$select_product) {
                        die('Query Error: ' . mysqli_error($conn));
                    }
                    if (mysqli_num_rows($select_product) > 0) {
                        while ($row = mysqli_fetch_assoc($select_product)) {
                    ?>
                    <tr>
                        <td><?php echo $row['cin']; ?></td>
                        <td><?php echo $row['cin_enc']; ?></td>
                        <td><?php echo $row['Nom']; ?></td>
                        <td><?php echo $row['prenom']; ?></td>
                        <td><?php echo $row['date_d']; ?></td>
                        <td><?php echo $row['date_f']; ?></td>
                        <td><?php echo $row['statut']; ?></td>
                        <td><?php echo $row['Telephone']; ?></td>
                        <td><?php echo $row['service']; ?></td>
                        <td><?php echo $row['etablissement']; ?></td>

                        <td>
                            <a href="archive.php?delete=<?php echo $row['cin']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this?');"> Supprimer </a>
                            <a href="archive.php?edit=<?php echo $row['cin']; ?>" class="option-btn"> Modifier </a>
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
        <form action="archive.php" method="GET">
    <label for="month" class="lab">Téléchargez la liste des stagiaires archivés pour le mois de votre choix :</label>
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
                $edit_query = mysqli_query($conn, "SELECT * FROM `stagiaire` WHERE cin = '$edit_nom'");
                if (mysqli_num_rows($edit_query) > 0) {
                    while ($fetch_edit = mysqli_fetch_assoc($edit_query)) {
            ?>
            <form action="" method="post" enctype="multipart/form-data">
                <input type="hidden" name="cin" value="<?php echo $fetch_edit['cin']; ?>">
                <input type="text" name="cin_enc" value="<?php echo $fetch_edit['cin_enc']; ?>">
                <input type="text" class="box" required name="nom" value="<?php echo $fetch_edit['Nom']; ?>">
                <input type="text" class="box" required name="prenom" value="<?php echo $fetch_edit['prenom']; ?>">
                <input type="date" class="box" required name="date_d" value="<?php echo $fetch_edit['date_d']; ?>">
                <input type="date" class="box" required name="date_f" value="<?php echo $fetch_edit['date_f']; ?>">
                <input type="text" class="box" required name="statut" value="<?php echo $fetch_edit['statut']; ?>">
                <input type="number" class="box" required name="tel" value="<?php echo $fetch_edit['Telephone']; ?>">
                <input type="text" class="box" required name="service" value="<?php echo $fetch_edit['service']; ?>">
                <input type="text" class="box" required name="etabl" value="<?php echo $fetch_edit['etablissement']; ?>">
                <input type="submit" value="Modifier Ce Stagiaire" name="update_product" class="btn">
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

    <script src="archive.js"></script>
</body>
</html>
