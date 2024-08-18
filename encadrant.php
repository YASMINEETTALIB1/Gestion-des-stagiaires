<?php 
@include 'connection.php';

// Add an encadrant
if (isset($_POST['add'])) {
    $cin_enc = mysqli_real_escape_string($conn, $_POST['cin_enc']);
    $nom = mysqli_real_escape_string($conn, $_POST['nom']);
    $prenom = mysqli_real_escape_string($conn, $_POST['prenom']);
    $tel = mysqli_real_escape_string($conn, $_POST['tel']);
    $div = mysqli_real_escape_string($conn, $_POST['division']);

    $insert_query = mysqli_query($conn, "INSERT INTO `encadrant` (cin_enc, nom, prenom, Telephone, division) VALUES ('$cin_enc', '$nom', '$prenom', '$tel', '$div')") or die('query failed');

    if ($insert_query) {
        $message[] = 'Encadrant added successfully';
    } else {
        $message[] = 'Encadrant could not be added';
    }
}

// DELETE an encadrant
if (isset($_GET['delete'])) {
    $delete_nom = mysqli_real_escape_string($conn, $_GET['delete']); // Escape the input
    $delete_query = mysqli_query($conn, "DELETE FROM `encadrant` WHERE cin_enc = '$delete_nom'") or die("connection failed");
    if ($delete_query) {
        header('location:encadrant.php');
        $message[] = 'The encadrant has been deleted';
    } else {
        header('location:encadrant.php');
        $message[] = 'The encadrant could not be deleted';
    }
}

// UPDATE an encadrant
if (isset($_POST['update_product'])) {
    $update_p_id = mysqli_real_escape_string($conn, $_POST['cin_enc']);
    $update_p_name = mysqli_real_escape_string($conn, $_POST['nom']);
    $update_p_prenom = mysqli_real_escape_string($conn, $_POST['prenom']);
    $update_p_tel = mysqli_real_escape_string($conn, $_POST['tel']);
    $update_p_div = mysqli_real_escape_string($conn, $_POST['div']);

    $update_query = mysqli_query($conn, "UPDATE `encadrant` SET nom = '$update_p_name', prenom = '$update_p_prenom', Telephone = '$update_p_tel', division = '$update_p_div' WHERE cin_enc = '$update_p_id'");

    if ($update_query) {
        $message[] = 'Encadrant updated successfully';
        header('location:encadrant.php');
    } else {
        $message[] = 'Encadrant could not be updated';
        header('location:encadrant.php');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>LISTE DES ENCADRANTS</title>
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
    <h1 class="headline">Gestion Des Encadrants</h1>
   
    <div class="container">

        <div class="search-container">
            <form action="encadrant.php" method="GET">
                <input type="text" name="search" placeholder="Search..." class="search-input">
                <button type="submit" class="search-button">Search</button>
            </form>
        </div>

        <section class="display-product-table">
            <table>
                <thead>
                    <tr>
                        <th>CIN</th>
                        <th>NOM</th>
                        <th>PRENOM</th>
                        <th>TELEPHONE</th>
                        <th>DIVISION</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
                    $query = "SELECT * FROM `encadrant`";
                    if ($search) {
                        $query .= " WHERE cin_enc LIKE '%$search%' OR nom LIKE '%$search%' OR prenom LIKE '%$search%' OR division LIKE '%$search%'";
                    }
                    $select_product = mysqli_query($conn, $query);

                    if (mysqli_num_rows($select_product) > 0) {
                        while ($row = mysqli_fetch_assoc($select_product)) {
                    ?>
                    <tr>
                        <td><?php echo $row['cin_enc']; ?></td>
                        <td><?php echo $row['nom']; ?></td>
                        <td><?php echo $row['prenom']; ?></td>
                        <td><?php echo $row['Telephone']; ?></td>
                        <td><?php echo $row['division']; ?></td>
                        <td>
                            <a href="encadrant.php?delete=<?php echo $row['cin_enc']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this?');"> <i class="fas fa-trash"></i> Supprimer </a>
                            <a href="encadrant.php?edit=<?php echo $row['cin_enc']; ?>" class="option-btn"> <i class="fas fa-edit"></i> Modifier </a>
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
                <h3>Ajouter Un Encadrant</h3>
                <input type="text" name="cin_enc" placeholder="CIN d'Encadrant" class="box" required>
                <input type="text" name="nom" placeholder="Nom Encadrant" class="box" required>
                <input type="text" name="prenom" placeholder="Prenom Encadrant" class="box" required>
                <input type="number" name="tel" min="0" placeholder="Telephone" class="box" required>
                <input type="text" name="division" placeholder="Division" class="box" required>
                <input type="submit" value="Ajouter" name="add" class="btn">
            </form>
        </section>
        <!------------------------------------------------------------------------------------------->

        <!-------------------------------------form de modification---------------------------------->
        <section class="edit-form-container">
            <?php
            if (isset($_GET['edit'])) {
                $edit_nom = mysqli_real_escape_string($conn, $_GET['edit']);
                $edit_query = mysqli_query($conn, "SELECT * FROM `encadrant` WHERE cin_enc = '$edit_nom'");
                if (mysqli_num_rows($edit_query) > 0) {
                    while ($fetch_edit = mysqli_fetch_assoc($edit_query)) {
            ?>
            <form action="" method="post" enctype="multipart/form-data">
                <input type="hidden" name="cin_enc" value="<?php echo $fetch_edit['cin_enc']; ?>">
                <input type="text" class="box" required name="nom" value="<?php echo $fetch_edit['nom']; ?>">
                <input type="text" class="box" required name="prenom" value="<?php echo $fetch_edit['prenom']; ?>">
                <input type="number" class="box" required name="tel" value="<?php echo $fetch_edit['Telephone']; ?>">
                <input type="text" class="box" required name="div" value="<?php echo $fetch_edit['division']; ?>">
                <input type="submit" value="Update the Encadrant" name="update_product" class="btn">
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

    <script src="encadrant.js"></script>
</body>
</html>
