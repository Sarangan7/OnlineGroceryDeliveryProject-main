<?php
    include('../config.php');
    if(!isset($_SESSION['email']) || $_SESSION['AccountID'] != "Admin"){
        header('location:../adminLogin.php');
    }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title> Profile - Admin </title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/x-icon" href="../images/logo-icon.jpeg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="js/script.js"></script>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="sidebar">
        <div class="logo-details">
            <img src="../images/logo.png" id="logo">
        </div>
        <ul class="nav-links">
            <li>
                <a href="Dashboard.php">
                    <img src="../icons/dashboard.png" alt="" class="icon">
                    <span class="links_name">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="Products.php">
                    <img src="../icons/products.png" alt="" class="icon">
                    <span class="links_name">Product</span>
                </a>
            </li>
            <li>
                <a href="customer.php">
                    <img src="../icons/customer.png" alt="" class="icon">
                    <span class="links_name">Customers</span>
                </a>
            </li>
            <li>
                <a href="Feedback.php">
                    <img src="../icons/feedback.png" alt="" class="icon">
                    <span class="links_name">Feedback</span>
                </a>
            </li>

            <li class="log_out">
                <a onclick="logout();">
                    <img src="../icons/logout.png" alt="" class="icon">
                    <span class="links_name">Log out</span>
                </a>
            </li>
        </ul>
    </div>
    <section class="home-section">
        <nav>
        <form action="#" method="get">
            <div class="search-box">
            <input type="text" placeholder="Search..." name="search">
            <button type="submit">
            <img src="../icons/search.png" alt="" class="icon">
            </button>
            </div>
        </form>
            <div class="profile-details">
                <img src="../icons/admin.png" class="icon">
                <a href="profile.php"><span class="admin_name">Admin </span></a>
            </div>
        </nav>

        <div class="home-content">
            <div class="sales-boxes">
                <div class="profile-box box">
                    <div class="title">
                        <i class="fas fa-user-cog"></i> Admin Profile Settings
                    </div>
                    <div class="sales-details">
                        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                            <div class="field input-field">
                                <input type="password" placeholder="Enter current password" class="password" name="oldPassword" required>
                            </div>
                            <div class="field input-field">
                                <input type="password" placeholder="Enter new password" class="password" name="newPassword" required>
                            </div>
                            <div class="field input-field">
                                <input type="password" placeholder="Confirm new password" class="password" name="newPassword2" required>
                            </div>
                        
                            <div class="field button-field">
                                <button type="submit" name="change">
                                    <i class="fas fa-key"></i> Change Password
                                </button>
                            </div>
                        </form>

                        <?php
                            if(isset($_POST['change'])){
                                $oldPass = $_POST['oldPassword'];
                                $newPass = $_POST['newPassword'];
                                $conPass = $_POST['newPassword2'];
                                $qry1 = "SELECT * FROM account WHERE AccID=1"; 

                                $res = $con->query($qry1);
                                $personDetail = $res->fetch_assoc();

                                if($oldPass == $personDetail['AccPassword'] && $newPass == $conPass){
                                    $qry2 = "UPDATE account SET AccPassword='".$newPass."' WHERE AccID=1";
        
                                    if($con->query($qry2) === TRUE){
                                        echo '<div class="alert alert-success"><i class="fas fa-check-circle"></i> Password changed successfully!</div>';
                                    } else {
                                        echo '<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> Something went wrong! Please try again.</div>';
                                    }
                                } else if($oldPass == $personDetail['AccPassword']){
                                    echo '<div class="alert alert-error"><i class="fas fa-exclamation-triangle"></i> New passwords do not match!</div>';
                                } else {
                                    echo '<div class="alert alert-error"><i class="fas fa-times-circle"></i> Current password is incorrect!</div>';
                                }
                            }

                            mysqli_close($con);
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

</body>

</html>