<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ob_start(); // Start output buffering

try {
    // Connexion à la base de données
    @include 'connection.php';

    if (!$conn) {
        throw new Exception('Connection failed');
    }

    if (isset($_GET['month'])) {
        $selected_month = $_GET['month'];

        // Récupérer les stagiaires du mois sélectionné
        $stagiaires_mois_sql = "SELECT * FROM stagiaire WHERE MONTH(date_d) = $selected_month";
        $stagiaires_mois_result = $conn->query($stagiaires_mois_sql);

        if (!$stagiaires_mois_result) {
            throw new Exception('Database query failed: ' . $conn->error);
        }

        // Initialiser FPDF
        require('fpdf/fpdf.php');
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);

        // Titre du PDF
        $pdf->Cell(0, 10, 'Liste des stagiaires du mois ' . $selected_month, 0, 1, 'C');
        $pdf->Ln(10);

        // Ajouter les en-têtes du tableau
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(40, 10, 'CIN', 1);
        $pdf->Cell(40, 10, 'Nom', 1);
        $pdf->Cell(40, 10, 'Prenom', 1);
        $pdf->Cell(40, 10, 'Date Debut', 1);
        $pdf->Cell(30, 10, 'Service', 1);
        $pdf->Ln();

        // Ajouter les données des stagiaires
        $pdf->SetFont('Arial', '', 12);
        while ($row = $stagiaires_mois_result->fetch_assoc()) {
            $pdf->Cell(40, 10, $row['cin'], 1);
            $pdf->Cell(40, 10, $row['Nom'], 1);
            $pdf->Cell(40, 10, $row['prenom'], 1);
            $pdf->Cell(40, 10, $row['date_d'], 1);
            $pdf->Cell(30, 10, $row['service'], 1);
            $pdf->Ln();
        }

        // Téléchargement du PDF
        $pdf->Output('D', 'stagiaires_mois_' . $selected_month . '.pdf');
        ob_end_flush(); // End buffering and flush output
        exit;
    }

    ob_end_flush(); // End buffering and flush output

} catch (Exception $e) {
    ob_end_clean(); // Clean the buffer to prevent any output
    echo 'Error: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>DASHBORD DES STAGIAIRES</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <style>
        /* Your CSS styles */
    </style>
</head>
<body>
    <nav class="navbar navbar-inverse">
        <div class="container-fluid">
            <div class="navbar-header">
                <a class="navbar-brand" href="#">INFOPRO</a>
            </div>
            <ul class="nav navbar-nav">
                <li><a class="brand" href="dashbord.php">Accueil</a></li>
                <li><a class="brand" href="#">Stagiaire</a></li>
                <li><a class="brand" href="encadrant.php">Encadrants</a></li>
                <li><a class="brand" href="service.php">Service</a></li>
                <li><a class="brand" href="#">Archive</a></li>
            </ul>
        </div>
    </nav>

    <div class="header">
        <h1>Dashboard des Stagiaires</h1>
    </div>
    <div class="container">
        <!-- Existing Cards -->
    </div>
    <form action="test.php" method="GET">
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
            <button type="submit" class="btn btn-success">Télécharger</button>
        </div>
    </form>
</body>
</html>
