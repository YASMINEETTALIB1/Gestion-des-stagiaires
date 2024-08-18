<?php
// Connexion à la base de données
@include 'connection.php';

if (!$conn) {
    die('Connection failed');
}
// Récupérer le nombre total de stagiaires pour l'année en cours
$current_year = date('Y');
$total_stagiaires_sql = "SELECT COUNT(*) as total FROM stagiaire WHERE YEAR(date_d) = $current_year";
$total_stagiaires_result = $conn->query($total_stagiaires_sql);
$total_stagiaires = $total_stagiaires_result->fetch_assoc()['total'];
// Récupérer le nombre de stagiaires par service
$stagiaires_par_service_sql = "
    SELECT service.nom as service_name, COUNT(stagiaire.cin) as count 
    FROM stagiaire 
    JOIN service ON stagiaire.service = service.nom 
    GROUP BY service.nom";
$stagiaires_par_service_result = $conn->query($stagiaires_par_service_sql);

// Récupérer le nombre total de stagiaires pour un mois
$total_stagiaires_sql1 = "SELECT COUNT(*) as total1, Month(date_d) as month1 FROM stagiaire GROUP BY  Month(date_d)";
$total_stagiaires_result1 = $conn->query($total_stagiaires_sql1);

ob_start();

require_once('tcpdf.php');

if (isset($_GET['download']) && isset($_GET['month'])) {
    $selectedMonth = intval($_GET['month']);

    // Query to fetch all interns (stagiaires) whose start or end date falls within the selected month
    $query = mysqli_query($conn, "SELECT * FROM `stagiaire` WHERE MONTH(date_d) = '$selectedMonth'");

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
        <p>Aucun stagiaire trouvé pour le mois sélectionné.</p>
        ';
        
        // Write the message to the PDF
        $pdf->writeHTML($html, true, false, true, false, '');
    }

    // Output the PDF document
    $pdf->Output('stagiaires_mois_' . $selectedMonth . '.pdf', 'I');
    exit;
}

ob_end_clean(); // Clean the output buffer and turn off output buffering
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <title>DASHBORD DES STAGIAIRES</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" />
    <link rel="stylesheet" href="header.css" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        h1{
            font-family: cursive;
        }
        .container {
            width: 80%;
            margin: 0 auto;
        }
        .header {
            color: black;
            padding: 20px;
            text-align: center;
        }
        .card {
            background-color: white;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
        }
        .card h2 {
            margin: 0;
            font-size: 24px;
        }
        .card p {
            font-size: 18px;
            margin: 10px 0 0;
        }
        .service-list {
            margin: 20px 0;
        }
        .service-item {
            background-color: #f9f9f9;
            padding: 10px;
            margin: 10px 0;
            border-left: 5px solid #4CAF50;
        }
        .form-group
        {
            padding-left: 16.75rem !important;
            padding-right: 80rem !important;
            padding-top: 2rem !important;
            padding-bottom: 2rem !important;
        }
        .btn-success{
            background-color: #c6535d !important;
            border-color: #c6535d !important;
        }
        .form-group{
            display: flex;
            gap: 2rem;
        }
        .lab{
            margin-left: 16.75rem ;
            font-family: cursive;
            font-size: 2rem;
            margin-top: 3rem;
        }
    </style>
<body>
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
    <div class="header">
        <h1>Dashboard des Stagiaires</h1>
    </div>
    <div class="container">
        <div class="card">
            <h2>Total des Stagiaires :</h2>
            <p><?php echo $total_stagiaires; ?></p>
        </div>

        <div class="card">
            <h2>Stagiaires par Service :</h2>
            <div class="service-list">
                <?php while($row = $stagiaires_par_service_result->fetch_assoc()): ?>
                    <div class="service-item">
                        <strong><?php echo $row['service_name']; ?></strong>: <?php echo $row['count']; ?> stagiaires
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <div class="card">
            <h2>Stagiaires par Mois :</h2>
            <div class="service-list">
                <?php while($row1 = $total_stagiaires_result1->fetch_assoc()): ?>
                    <div class="service-item">
                    Mois <strong><?php echo $row1['month1']; ?></strong> : <?php echo $row1['total1']; ?> 
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
    <form action="dashbord.php" method="GET">
    <label for="month" class="lab">Téléchargez les stagiaires du mois de votre choix :</label>
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
<?php include 'footer.html'; ?>
</body>
</html>