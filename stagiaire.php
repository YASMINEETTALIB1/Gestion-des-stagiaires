<?php 
@include 'connection.php';

// Function to update status based on the current date
function updateStagiaireStatut($conn) {
    $today = date('Y-m-d'); // Get today's date
    // Update statut to "en cours" if date_d <= today and date_f >= today
    $update_query1 = mysqli_query($conn, "UPDATE stagiaire SET statut = 'en cours' WHERE date_d <= '$today' AND date_f >= '$today' AND statut != 'archive'") or die('query failed');
    
    // Update statut to "archive" if date_f < today
    $update_query2 = mysqli_query($conn, "UPDATE stagiaire SET statut = 'archive' WHERE date_f < '$today' AND statut != 'archive'") or die('query failed');
    
    // Check for errors
    if (!$update_query1 || !$update_query2) {
        die('Error updating stagiaire status');
    }
}

// Call the function to update statuses on page load
updateStagiaireStatut($conn);


// Add an stagiaire
if (isset($_POST['add'])) {
  $cin = mysqli_real_escape_string($conn, $_POST['cin']);
  $cin_enc = mysqli_real_escape_string($conn, $_POST['cin_enc']);
  $nom = mysqli_real_escape_string($conn, $_POST['nom']);
  $prenom= mysqli_real_escape_string($conn, $_POST['prenom']);
  $date_d = mysqli_real_escape_string($conn, $_POST['date_d']);
  $date_f = mysqli_real_escape_string($conn, $_POST['date_f']);
  $statut = mysqli_real_escape_string($conn, $_POST['statut']);
  $tel = mysqli_real_escape_string($conn, $_POST['tel']);
  $service = mysqli_real_escape_string($conn, $_POST['service']);
  $etab = mysqli_real_escape_string($conn, $_POST['etab']);

    $insert_query = mysqli_query($conn, "INSERT INTO `stagiaire` (cin,cin_enc, nom, prenom, date_d, date_f, statut, Telephone, service, etablissement) VALUES ('$cin', '$cin_enc', '$nom', '$prenom', '$date_d', '$date_f', '$statut', '$tel', '$service', '$etab')") or die('query failed');

    if ($insert_query) {
        $message[] = 'Stagiaire added successfully';
        updateStagiaireStatut($conn);
    } else {
        $message[] = 'Stagiaire could not be added';
    }
}

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

//PROFIL PDF
ob_start(); 
require_once('tcpdf.php'); 
if (isset($_GET['pdf'])) {
    $cin = mysqli_real_escape_string($conn, $_GET['pdf']);
    $query = mysqli_query($conn, "SELECT * FROM `stagiaire` WHERE cin = '$cin'");
    if (mysqli_num_rows($query) > 0) {
        $stagiaire = mysqli_fetch_assoc($query);
        if (!defined('PDF_CREATOR')) {
            define('PDF_CREATOR', 'TCPDF');
        }
        if (!defined('PDF_HEADER_TITLE')) {
            define('PDF_HEADER_TITLE', 'Profil Stagiaire');
        }
        $pdf = new TCPDF();
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Application');
        $pdf->SetTitle('Profil Stagiaire');
        $pdf->AddPage();
        $html = '
            <style>
    h1 {
        font-family: Arial, sans-serif;
        color: #272745;
        font-size: 24px;
        text-align: center;
    }
        strong{
        color :  #272745;
      }
        p{
        
        font-size:16px;}
        </style>
            <h1>Profil de Stagiaire</h1>
            <p><strong>CIN:</strong> ' . $stagiaire['cin'] . '</p>
            <p><strong>CIN Encadrant:</strong> ' . $stagiaire['cin_enc'] . '</p>
            <p><strong>Nom:</strong> ' . $stagiaire['Nom'] . '</p>
            <p><strong>Prenom:</strong> ' . $stagiaire['prenom'] . '</p>
            <p><strong>Date Debut:</strong> ' . $stagiaire['date_d'] . '</p>
            <p><strong>Date Fin:</strong> ' . $stagiaire['date_f'] . '</p>
            <p><strong>Statut:</strong> ' . $stagiaire['statut'] . '</p>
            <p><strong>Telephone:</strong> ' . $stagiaire['Telephone'] . '</p>
            <p><strong>Service:</strong> ' . $stagiaire['service'] . '</p>
            <p><strong>Etablissement:</strong> ' . $stagiaire['etablissement'] . '</p>
        ';
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output('profil_stagiaire_' . $stagiaire['cin'] . '.pdf', 'I');
        exit;
    } else {
        echo "Stagiaire not found!";
    }
}
ob_end_clean();

// Start output buffering
ob_start(); 

require_once('tcpdf.php'); 

// Check if 'attest' is set in the GET request
if (isset($_GET['attest'])) {
    $cin = mysqli_real_escape_string($conn, $_GET['attest']);
    $query = mysqli_query($conn, "SELECT * FROM `stagiaire` WHERE cin = '$cin'");
    
    if (mysqli_num_rows($query) > 0) {
        $stagiaire1 = mysqli_fetch_assoc($query);
        
        // Define TCPDF constants
        if (!defined('PDF_CREATOR')) {
            define('PDF_CREATOR', 'TCPDF');
        }
        if (!defined('PDF_HEADER_TITLE')) {
            define('PDF_HEADER_TITLE', 'Attestation Stagiaire');
        }

        // Create a new PDF document
        $pdf = new TCPDF();
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Application');
        $pdf->SetTitle('Attestation Stagiaire');

        // Add a page
        $pdf->AddPage();

        // Add custom HTML content with CSS styling
        $html = '
        <style>
            h1 {
                font-size: 20px;
                font-weight: bold;
                text-align: center;
                margin-bottom: 20px;
                color: #003366;
            }
            .p {
                font-size: 14px;
                color: #333333;
                text-align: justify;
                line-height: 1.6;
            }
             .header {
                font-size: 7px;
                font-weight: bold;
                margin-bottom: 20px;

            }
            .header p {
                margin: 0; /* Remove margin between paragraphs */
                padding: 0; /* Remove padding between paragraphs */
            }
            .signature {
                margin-top: 50px;
                color: #003366;
            }
        strong{
        
        text-decoration: underline;
      }
        </style>

        <div class="header">
            <p>ROYAUME DU MAROC
            <p>MINISTÈRE DE L´INTÉRIEUR</p>
            <p>INFOPRO CASABLANCA</p>
            <p>DIVISION DES RESSOURCES HUMAINES</p>
        </div>

        <h1>Attestation de Stagiaire</h1>

        <p class ="p">Le Gouverneur de INFOPRO de Casablanca, atteste que M./Mlle <strong>' . $stagiaire1['Nom'] . ' ' . $stagiaire1['prenom'] . '</strong>, titulaire de la C.I.N N° <strong>' . $stagiaire1['cin'] . '</strong>, a effectué un stage au entreprise INFOPRO de Casablanca, durant la période du <strong>' . $stagiaire1['date_d'] . '</strong> au <strong>' . $stagiaire1['date_f'] . '</strong>.</p>

        <p class ="p">En foi de quoi, la présente attestation lui est délivrée sur sa demande, pour servir et valoir ce que de droit. Elle n’engage en aucun cas l’administration.</p>

        <div class="signature">
            <p class="p">Casablanca, le ' . date("d-m-Y") . '</p>
            <p class="p">Signature :</p>
        </div>
        ';

        // Write the HTML content
        $pdf->writeHTML($html, true, false, true, false, '');

        // Output the PDF document
        $pdf->Output('Attestation_stagiaire_' . $stagiaire1['cin'] . '.pdf', 'I');
        
        exit;
    } else {
        echo "Stagiaire not found!";
    }
}

ob_end_clean();


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>LISTE DES ENCADRANTS</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" />
    <link rel="stylesheet" href="style1.css">
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
    <h1 class="headline">Gestion Des Stagiaires</h1>
   
    <div class="container">

        <div class="search-container">
            <form action="stagiaire.php" method="GET">
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
                        <th>INFORMATIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
                    $query = "SELECT * FROM `stagiaire`";
                    if ($search) {
                        $query .= " WHERE cin LIKE '%$search%' OR nom LIKE '%$search%' OR prenom LIKE '%$search%'";
                    }
                    $select_product = mysqli_query($conn, $query);

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
                            <a href="stagiaire.php?delete=<?php echo $row['cin']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this?');"> Supprimer </a>
                            <a href="stagiaire.php?edit=<?php echo $row['cin']; ?>" class="option-btn"> Modifier </a>
                        </td>
                        <td>
                        <a href="stagiaire.php?pdf=<?php echo $row['cin']; ?>" class="delete-btn">
    Profil 
    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
        <path fill="currentColor" fill-rule="evenodd" d="M8 7a4 4 0 1 1 8 0a4 4 0 0 1-8 0m0 6a5 5 0 0 0-5 5a3 3 0 0 0 3 3h12a3 3 0 0 0 3-3a5 5 0 0 0-5-5z" clip-rule="evenodd"/>
    </svg>
</a>

                            <a href="stagiaire.php?attest=<?php echo $row['cin']; ?>" class="option-btn">Attestation </a>
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

        <section>
            <form action="" method="post" class="add-product-form" enctype="multipart/form-data">
                <h3>Ajouter Un Stagiaire</h3>
                <input type="text" name="cin" placeholder="CIN de Stagiaire" class="box" required>
                <input type="text" name="cin_enc" placeholder="CIN d'Encadrant" class="box" required>
                <input type="text" name="nom" placeholder="Nom Stagiaire" class="box" required>
                <input type="text" name="prenom" placeholder="Prenom Stagiaire" class="box" required>
                <input type="date" name="date_d"  placeholder="Date Debut" class="box" required>
                <input type="date" name="date_f" placeholder="Date Fin" class="box" required>
                <input type="text" name="statut" placeholder="Statut" class="box" required>
                <input type="number" name="tel" min="0" placeholder="Telephone" class="box" required>
                <input type="text" name="service" placeholder="Service" class="box" required>
                <input type="text" name="etab" placeholder="Etablissement" class="box" required>
                <input type="submit" value="Ajouter" name="add" class="btn">
            </form>
        </section>
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

    <script src="stagiaire.js"></script>
</body>
</html>
