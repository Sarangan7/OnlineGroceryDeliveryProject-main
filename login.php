<?php
        include('config.php');
        if(isset($_SESSION['cName'])){
            header('location:index.php');
        }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="images/logo-icon.jpeg">
    <title> Customer Login </title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/style1.css">

</head>

<body>

<?php include('header&footer/header.php'); ?>

<?php
    if(isset($_POST['submit'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];
 
        // Only allow customer login (exclude admin account)
        $qry = "SELECT * FROM account WHERE AccEmail='$email' AND AccID != 1";
        $res = $con->query($qry);
        $acc = $res->fetch_assoc();
        $count = mysqli_num_rows($res);
        
        if($count == 1) {
            if($acc['AccPassword'] == $password){
                echo '<script>alert("Login successful!!")</script>';
                $qry2 = "SELECT * FROM customer WHERE AccID='".$acc['AccID']."'"; 
                $res2 = $con->query($qry2);
                $cus = $res2->fetch_assoc();
                $_SESSION['cName'] = $cus['firstName'];
                $_SESSION['AccountID'] = $cus['AccID'];
                header('location:index.php');
            } else {
                echo '<script>alert("Incorrect Password!")</script>';
            }
        } else {
            echo '<script>alert("Customer Account Not Found!")</script>';
        }
    }
?>

    <section class="container">
        <div class="form login">
            <div class="form-content">
                <div class="formHeader">Customer Login</div>

                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                        <div class="field input-field">
                            <input type="email" name="email" placeholder="Enter Email" class="input" required>
                        </div>

                        <div class="field input-field">
                            <input type="password" name="password" placeholder="Password" class="password" required>
                        </div>

                        <div class="field button-field">
                            <button type="submit" name="submit" class="button-field" value="submit">Login</button>
                        </div>
                        
                    </form>

                <div class="form-link">
                    <span>Don't have an account? <a href="Signup.php">Signup</a></span>
                    <br><br>
                    <span>Admin Login? <a href="adminLogin.php">Click Here</a></span>
                </div>
            </div>

        </div>

    </section>

    <?php include('header&footer/footer.html'); ?>
    <script src="js/script.js"></script>
</body>

</html>