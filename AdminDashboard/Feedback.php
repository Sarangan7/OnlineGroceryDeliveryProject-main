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
    <title> Feedback-Admin </title>
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
                <a href="Feedback.php" class="active">
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
            <input type="text" placeholder="Search feedback..." name="search">
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
            <div class="sales-boxes">
                <div class="product-list box">
                    <div class="title">Feedback Management</div>
                    <div class="sales-details">
                        <table>
                            <tr>
                                <th class="topic">Feedback ID</th>
                                <th class="topic">Date</th>
                                <th class="topic">Name</th>
                                <th class="topic">Email</th>
                                <th class="topic">Details</th>
                                <th class="topic">Status</th>
                                <th class="topic">Actions</th>
                            </tr>

                            <?php
                                // Handle marking feedback as read
                                if(isset($_GET['read'])){
                                    $feedbackId = $_GET['read'];
                                    $qry2 = "UPDATE feedback SET response=1 WHERE feedbackID = $feedbackId";
                                    if($con->query($qry2)) {
                                        echo '<script>alert("Feedback marked as read!"); window.location.href="Feedback.php";</script>';
                                    }
                                }

                                // Display all feedback
                                $xyx = mysqli_query($con, "SELECT * FROM feedback ORDER BY feedbackID DESC");
                                
                                if($xyx && mysqli_num_rows($xyx) > 0) {
                                    while($collect = $xyx->fetch_assoc()){
                                        $statusClass = $collect['response'] == 0 ? 'unread' : 'read';
                                        $statusText = $collect['response'] == 0 ? 'Unread' : 'Read';
                                        $statusIcon = $collect['response'] == 0 ? 'fas fa-envelope' : 'fas fa-envelope-open';
                                        
                                        echo "<tr class='$statusClass'>";
                                        echo "<td>" . htmlspecialchars($collect['feedbackID']) . "</td>";
                                        echo "<td>" . date('M d, Y', strtotime($collect['Ftime'])) . "</td>";
                                        echo "<td>" . htmlspecialchars($collect['cName']) . "</td>";
                                        echo "<td>" . htmlspecialchars($collect['cEmail']) . "</td>";
                                        echo "<td style='max-width: 200px; word-wrap: break-word;'>" . htmlspecialchars($collect['feedback']) . "</td>";
                                        echo "<td>";
                                        echo "<span class='status-badge " . ($collect['response'] == 0 ? 'unread' : 'read') . "'>";
                                        echo "<i class='$statusIcon'></i> $statusText";
                                        echo "</span>";
                                        echo "</td>";
                                        echo "<td>";
                                        
                                        if($collect['response'] == 0) {
                                            echo '<button type="button" onclick="markAsRead('.$collect['feedbackID'].')" class="action-btn success-btn">';
                                            echo '<i class="fas fa-check"></i> Mark Read';
                                            echo '</button>';
                                        } else {
                                            echo '<span style="color: #28a745; font-weight: 500;"><i class="fas fa-check-circle"></i> Read</span>';
                                        }
                                        
                                        echo '</td>';
                                        echo "</tr>";
                                    }
                                } else {
                                    echo '<tr><td colspan="7" style="text-align: center; color: #999; padding: 30px;">No feedback found</td></tr>';
                                }
                                
                                mysqli_close($con);
                            ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .status-badge.unread {
            background: linear-gradient(135deg, #ffeaa7, #fdcb6e);
            color: #e17055;
        }
        
        .status-badge.read {
            background: linear-gradient(135deg, #d1f2eb, #a3e4d7);
            color: #00b894;
        }
        
        tr.unread {
            background-color: #fff3cd !important;
        }
        
        tr.unread:hover {
            background-color: #ffeaa7 !important;
        }
    </style>

    <script>
        function markAsRead(feedbackId) {
            if(confirm('Mark this feedback as read?')) {
                window.location.href = 'Feedback.php?read=' + feedbackId;
            }
        }
    </script>

</body>

</html>