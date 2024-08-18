<?php 
@include 'connection.php';
//ajouter un service.
if(isset($_POST['add'])){
  $p_name = $_POST['p_name'];
  $p_resp = $_POST['p_resp'];
  $p_desc = $_POST['p_desc'];

  $insert_query = mysqli_query($conn, "INSERT INTO `service`(nom, responsable,description1) VALUES('$p_name', '$p_resp', '$p_desc')") or die('query failed');

  if($insert_query){
     $message[] = 'Service add succesfully';
  }else{
     $message[] = 'Service could not add the product';
  }
};

//DELETE A SERVICE FROM MY DATABASE AND SHOW IT IN MY PAGE SERVICE.PHP
if (isset($_GET['delete'])) {
    $delete_nom = $_GET['delete'];
    $delete_query = mysqli_query($conn, "DELETE FROM `service` WHERE id = $delete_nom") or die("connection failed");
    if ($delete_query) {
        header('location:service.php');
        $message[] = 'The service has been deleted';
    } else {
        header('location:service.php');
        $message = 'The service could not be deleted';
    }
}

// UPDATE A SERVICE IN MY DATABASE AND SHOW IT IN MY PAGE SERVICE.PHP
if (isset($_POST['update_product'])) {
    $update_p_id = $_POST['update_s_id'];
    $update_p_name = $_POST['update_s_nom'];
    $update_p_resp = $_POST['update_s_responsable'];
    $update_p_des = $_POST['update_s_description1'];

    $update_query = mysqli_query($conn, "UPDATE `service` SET nom = '$update_p_name', responsable = '$update_p_resp', description1 = '$update_p_des' WHERE id = '$update_p_id'");

    if ($update_query) {
        $message[] = 'Service updated successfully';
        header('location:service.php');
    } else {
        $message[] = 'Service could not be updated';
        header('location:service.php');
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>LISTE DES SERVICES</title>
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
    <h1 class="headline">Gestion Des Services</h1>
   
    <div class="container">

        <div class="search-container">
            <form action="service.php" method="GET">
                <input type="text" name="search" placeholder="Search..." class="search-input">
                <button type="submit" class="search-button">Search</button>
            </form>
        </div>

        <section class="display-product-table">
            <table>
                <thead>
                    <tr>
                        <th>NOM</th>
                        <th>RESPONSABLE</th>
                        <th>DESCRIPTION</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
                    $query = "SELECT * FROM `service`";
                    if ($search) {
                        $query .= " WHERE nom LIKE '%$search%' OR responsable LIKE '%$search%' OR description1 LIKE '%$search%'";
                    }
                    $select_product = mysqli_query($conn, $query);

                    if (mysqli_num_rows($select_product) > 0) {
                        while ($row = mysqli_fetch_assoc($select_product)) {
                    ?>
                    <tr>
                        <td><?php echo $row['nom']; ?></td>
                        <td><?php echo $row['responsable']; ?></td>
                        <td><?php echo $row['description1']; ?></td>
                        <td>
                            <a href="service.php?delete=<?php echo $row['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this?');"> <i class="fas fa-trash"></i> Supprimer </a>
                            <a href="service.php?edit=<?php echo $row['id']; ?>" class="option-btn"> <i class="fas fa-edit"></i> Modifier </a>
                        </td>
                    </tr>
                    <?php
                        }
                    } else {
                        echo "<tr><td colspan='4' class='empty'>No products found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>

        <section>
              <form action="" method="post" class="add-product-form" enctype="multipart/form-data">
                <h3>Ajouter Un Service </h3>
                <input type="text" name="p_name" placeholder="Nom Service" class="box" required>
                <input type="text" name="p_resp" placeholder="Responsable" class="box" required>
                <input type="text" name="p_desc" min="0" placeholder="Description" class="box" required>
                <input type="submit" value="Ajouter" name="add" class="btn">
              </form>
        </section>
        <!------------------------------------------------------------------------------------------->

        <!-------------------------------------form de modification---------------------------------->
        <section class="edit-form-container">
            <?php
            if (isset($_GET['edit'])) {
                $edit_nom = $_GET['edit'];
                $edit_query = mysqli_query($conn, "SELECT * FROM `service` WHERE id = $edit_nom");
                if (mysqli_num_rows($edit_query) > 0) {
                    while ($fetch_edit = mysqli_fetch_assoc($edit_query)) {
            ?>
            <form action="" method="post" enctype="multipart/form-data">
                <input type="hidden" name="update_s_id" value="<?php echo $fetch_edit['id']; ?>">
                <input type="text" class="box" required name="update_s_nom" value="<?php echo $fetch_edit['nom']; ?>">
                <input type="text" class="box" required name="update_s_responsable" value="<?php echo $fetch_edit['responsable']; ?>">
                <input type="text" class="box" required name="update_s_description1" value="<?php echo $fetch_edit['description1']; ?>">
                <input type="submit" value="Update the Service" name="update_product" class="btn">
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

    

    <script src="script.js"></script>
</body>
</html>
