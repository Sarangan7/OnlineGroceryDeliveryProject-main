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
    <title> Dashboard - Admin </title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js"></script>
    <link rel="icon" type="image/x-icon" href="../images/logo-icon.jpeg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                <a href="Dashboard.php" class="active">
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
                <a href="profile.php"><span class="admin_name">Admin</span></a>
            </div>
        </nav>

        <div class="home-content">
            <div class="overview-boxes">
                <div class="box">
                    <div class="right-side">
                        <div class="box-topic">Total Customers</div>
                        <div class="number">
                            <?php
                                $qry = "SELECT COUNT(*) as count FROM customer";
                                $xyz = mysqli_query($con, $qry);
                                $result = $xyz->fetch_assoc();
                                echo $result['count'];
                            ?>
                        </div>
                    </div>
                    <div class="cart">
                        <i class="fas fa-users"></i>
                    </div>
                </div>

                <div class="box">
                    <div class="right-side">
                        <div class="box-topic">Total Products</div>
                        <div class="number">
                            <?php
                                $qry2 = "SELECT COUNT(*) as count FROM products";
                                $xyz = mysqli_query($con, $qry2);
                                $result = $xyz->fetch_assoc();
                                echo $result['count'];
                            ?>
                        </div>
                    </div>
                    <div class="cart">
                        <i class="fas fa-box"></i>
                    </div>
                </div>

                <div class="box">
                    <div class="right-side">
                        <div class="box-topic">Total Feedback</div>
                        <div class="number">
                            <?php
                                $qry3 = "SELECT COUNT(*) as count FROM feedback";
                                $xyz = mysqli_query($con, $qry3);
                                $result = $xyz->fetch_assoc();
                                echo $result['count'];
                            ?>
                        </div>
                    </div>
                    <div class="cart">
                        <i class="fas fa-comments"></i>
                    </div>
                </div>

                <div class="box">
                    <div class="right-side">
                        <div class="box-topic">Unread Feedback</div>
                        <div class="number">
                            <?php
                                $qry4 = "SELECT COUNT(*) as count FROM feedback WHERE response = 0";
                                $xyz = mysqli_query($con, $qry4);
                                $result = $xyz->fetch_assoc();
                                echo $result['count'];
                            ?>
                        </div>
                    </div>
                    <div class="cart">
                        <i class="fas fa-bell"></i>
                    </div>
                </div>
            </div>

            <div class="sales-boxes">
                <div class="dashboard box">
                    <div class="title">Recent Unread Feedback</div>
                    <div class="sales-details">
                        <table>                           
                            <?php
                                $qry = "SELECT * FROM feedback WHERE response=0 ORDER BY feedbackID DESC LIMIT 5";
                                $xyz = mysqli_query($con, $qry);
                                $rowCount = mysqli_num_rows($xyz);
                                
                                if($rowCount < 1){
                                    echo '<tr><td colspan="3" style="text-align: center; color: #999; padding: 30px;">No Unread Feedback</td></tr>';
                                } else {
                                    echo '<tr><th class="topic">Date</th><th class="topic">Name</th><th class="topic">Details</th></tr>';
                                    
                                    while($collect = $xyz->fetch_assoc()){
                                        echo "<tr>";
                                        echo "<td>" . date('M d, Y', strtotime($collect['Ftime'])) . "</td>";
                                        echo "<td>" . htmlspecialchars($collect['cName']) . "</td>";
                                        echo "<td>" . htmlspecialchars(substr($collect['feedback'], 0, 50)) . "...</td>";
                                        echo "</tr>";
                                    }
                                }
                                mysqli_close($con);
                            ?>
                        </table>
                    </div>
                    <div class="button">
                        <a href="Feedback.php">
                            <i class="fas fa-eye"></i> View All Feedback
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

</body>

</html>