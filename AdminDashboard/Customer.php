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
    <title> Customer - Admin </title>
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
                <a href="customer.php" class="active">
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
            <input type="text" placeholder="Search customers..." name="search">
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
                    <div class="title">Customer Management</div>
                    <div class="sales-details">
                        <table>
                            <tr>
                                <th class="topic">Customer ID</th>
                                <th class="topic">First Name</th>
                                <th class="topic">Last Name</th>
                                <th class="topic">Email</th>
                                <th class="topic">Address</th>
                                <th class="topic">Phone</th>
                                <th class="topic">Actions</th>
                            </tr>

                            <?php
                                // Handle customer deletion
                                if(isset($_GET['delete'])){
                                    $customerId = $_GET['delete'];
                                    $qry2 = "DELETE FROM customer WHERE AccID = $customerId";
                                    $qry3 = "DELETE FROM account WHERE AccID = $customerId";
                                    
                                    if($con->query($qry2) && $con->query($qry3)) {
                                        echo '<script>alert("Customer deleted successfully!"); window.location.href="Customer.php";</script>';
                                    } else {
                                        echo '<script>alert("Error deleting customer!");</script>';
                                    }
                                }

                                // Display customers with their account information
                                $qry = "SELECT c.*, a.AccEmail FROM customer c 
                                       LEFT JOIN account a ON c.AccID = a.AccID 
                                       ORDER BY c.customerID DESC";
                                $xyx = mysqli_query($con, $qry);

                                if($xyx && mysqli_num_rows($xyx) > 0) {
                                    while($collect = $xyx->fetch_assoc()){
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($collect['customerID']) . "</td>";
                                        echo "<td>" . htmlspecialchars($collect['firstName']) . "</td>";
                                        echo "<td>" . htmlspecialchars($collect['lastName'] ?? 'N/A') . "</td>";
                                        echo "<td>" . htmlspecialchars($collect['AccEmail'] ?? 'N/A') . "</td>";
                                        echo "<td>" . htmlspecialchars($collect['address'] ?? 'N/A') . "</td>";
                                        echo "<td>" . htmlspecialchars($collect['phone'] ?? 'N/A') . "</td>";
                                        echo '<td>';
                                        echo '<button type="button" onclick="viewCustomer('.$collect['AccID'].')" class="action-btn success-btn" style="margin-right: 5px;"><i class="fas fa-eye"></i> View</button>';
                                        echo '<button type="button" onclick="deleteCustomer('.$collect['AccID'].')" class="action-btn"><i class="fas fa-trash"></i> Delete</button>';
                                        echo '</td>';
                                        echo "</tr>";
                                    }
                                } else {
                                    echo '<tr><td colspan="7" style="text-align: center; color: #999; padding: 30px;">No customers found</td></tr>';
                                }
                                
                                mysqli_close($con);
                            ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        function deleteCustomer(customerId) {
            if(confirm('Are you sure you want to delete this customer? This action cannot be undone and will also delete their account.')) {
                window.location.href = 'Customer.php?delete=' + customerId;
            }
        }

        function viewCustomer(customerId) {
            // You can implement view customer details functionality here
            alert('View customer details functionality can be implemented here for customer ID: ' + customerId);
        }
    </script>

</body>

</html>