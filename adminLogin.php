<?php
    include('config.php');
    if(isset($_SESSION['email']) && $_SESSION['AccountID'] == "Admin"){
        header('location:AdminDashboard/Dashboard.php');
    }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="images/logo-icon.jpeg">
    <title>Admin Login - QuicKart</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/style1.css">
</head>

<body>
    <?php include('header&footer/header.php'); ?>

    <?php
    if(isset($_POST['submit'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];
 
        $qry = "SELECT * FROM account WHERE AccEmail='$email' AND AccID=1";
        $res = $con->query($qry);
        $acc = $res->fetch_assoc();
        $count = mysqli_num_rows($res);
        
        if($count == 1) {
            if($acc['AccPassword'] == $password){
                echo '<script>alert("Admin Login Successful!")</script>';
                $_SESSION['name'] = "Admin";
                $_SESSION['AccountID'] = "Admin";
                $_SESSION['email'] = $email;
                header('location:AdminDashboard/Dashboard.php');
            } else {
                echo '<script>alert("Incorrect Password!")</script>';
            }
        } else {
            echo '<script>alert("Admin Account Not Found!")</script>';
        }
    }
    ?>

    <section class="container">
        <div class="form login">
            <div class="form-content">
                <div class="formHeader">Admin Login</div>
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                    <div class="field input-field">
                        <input type="email" name="email" placeholder="Admin Email" class="input" required>
                    </div>

                    <div class="field input-field">
                        <input type="password" name="password" placeholder="Admin Password" class="password" required>
                    </div>

                    <div class="field button-field">
                        <button type="submit" name="submit" class="button-field" value="submit">Admin Login</button>
                    </div>
                </form>

                <div class="form-link">
                    <span>Customer Login? <a href="login.php">Click Here</a></span>
                </div>
            </div>
        </div>
    </section>

    <?php include('header&footer/footer.html'); ?>
    <script src="js/script.js"></script>
</body>

</html>