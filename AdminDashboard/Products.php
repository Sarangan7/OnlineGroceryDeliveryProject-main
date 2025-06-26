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
    <title> Products - Admin </title>
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
                <a href="Products.php" class="active">
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
            <input type="text" placeholder="Search products..." name="search">
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
            <!-- Add Product Button -->
            <div class="add-product-section">
                <button class="add-product-btn" onclick="showAddProductForm()">
                    <i class="fas fa-plus"></i>
                    Add New Product
                </button>
            </div>

            <div class="sales-boxes">
                <div class="product-list box">
                    <div class="title">Products Management</div>
                    <div class="sales-details">
                        <table>
                            <tr>
                                <th class="topic">Product ID</th>
                                <th class="topic">Product Name</th>
                                <th class="topic">Unit</th>
                                <th class="topic">Price (Rs.)</th>
                                <th class="topic">Category</th>
                                <th class="topic">Image</th>
                                <th class="topic">Actions</th>
                            </tr>

                            <?php
                                // Handle product deletion
                                if(isset($_GET['delete'])){
                                    $productId = $_GET['delete'];
                                    $qry3 = "SELECT * FROM products WHERE productID = $productId";
                                    $productDet = $con->query($qry3);
                                    
                                    if($productDet && $productDet->num_rows > 0) {
                                        $imageName = $productDet->fetch_assoc();
                                        $qry2 = "DELETE FROM products WHERE productID = $productId";
                                        
                                        if($con->query($qry2)) {
                                            // Try to delete the image file
                                            if(file_exists('../images/products/'.$imageName['imgName'])) {
                                                unlink('../images/products/'.$imageName['imgName']);
                                            }
                                            echo '<script>alert("Product deleted successfully!"); window.location.href="Products.php";</script>';
                                        } else {
                                            echo '<script>alert("Error deleting product!");</script>';
                                        }
                                    }
                                }

                                // Handle product addition
                                if(isset($_POST['add_product'])){
                                    $name = mysqli_real_escape_string($con, $_POST['productName']);
                                    $unit = mysqli_real_escape_string($con, $_POST['unit']);
                                    $price = floatval($_POST['price']);
                                    $category = mysqli_real_escape_string($con, $_POST['category']);

                                    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                                        $imgDet = pathinfo($_FILES['image']['name']);
                                        $ext = "." . strtolower($imgDet['extension']);
                                        $fileName = preg_replace('/[^A-Za-z0-9\-]/', '', $name . $unit) . $ext;
                                        
                                        // Check if upload directory exists
                                        if(!is_dir('../images/products/')) {
                                            mkdir('../images/products/', 0777, true);
                                        }
                                        
                                        $qry = "INSERT INTO products(productName, unit, price, category, imgName) VALUES('$name', '$unit', $price, '$category', '$fileName')";
                                        
                                        if(move_uploaded_file($_FILES['image']['tmp_name'], '../images/products/'.$fileName)){
                                            if($con->query($qry)) {
                                                echo '<script>alert("New product added successfully!"); window.location.href="Products.php";</script>';
                                            } else {
                                                echo '<script>alert("Database error: ' . $con->error . '");</script>';
                                            }
                                        } else {
                                            echo '<script>alert("Error uploading image!");</script>';
                                        }
                                    } else {
                                        echo '<script>alert("Please select a valid image file!");</script>';
                                    }
                                }
                                
                                // Display products
                                $xyx = mysqli_query($con, "SELECT * FROM products ORDER BY productID DESC");
                                
                                if($xyx && mysqli_num_rows($xyx) > 0) {
                                    while($collect = $xyx->fetch_assoc()){
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($collect['productID']) . "</td>";
                                        echo "<td>" . htmlspecialchars($collect['productName']) . "</td>";
                                        echo "<td>" . htmlspecialchars($collect['unit']) . "</td>";
                                        echo "<td>Rs. " . number_format($collect['price'], 2) . "</td>";
                                        echo "<td>" . htmlspecialchars($collect['category']) . "</td>";
                                        echo "<td>";
                                        if($collect['imgName'] && file_exists('../images/products/'.$collect['imgName'])) {
                                            echo '<img src="../images/products/'.$collect['imgName'].'" alt="Product Image" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">';
                                        } else {
                                            echo '<span style="color: #999;">No Image</span>';
                                        }
                                        echo "</td>";
                                        echo '<td>';
                                        echo '<button type="button" onclick="editProduct('.$collect['productID'].')" class="action-btn success-btn" style="margin-right: 5px;"><i class="fas fa-edit"></i> Edit</button>';
                                        echo '<button type="button" onclick="deleteProduct('.$collect['productID'].')" class="action-btn"><i class="fas fa-trash"></i> Delete</button>';
                                        echo '</td>';
                                        echo "</tr>";
                                    }
                                } else {
                                    echo '<tr><td colspan="7" style="text-align: center; color: #999; padding: 30px;">No products found</td></tr>';
                                }
                            ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Add Product Modal -->
    <div id="addProductModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeAddProductForm()">&times;</span>
            <h2 style="margin-bottom: 20px; color: #333;">Add New Product</h2>
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
                <div class="field">
                    <input type="text" name="productName" placeholder="Product Name" required>
                </div>
                <div class="field">
                    <input type="text" name="unit" placeholder="Unit (e.g., 1kg, 500g, 1L)" required>
                </div>
                <div class="field">
                    <input type="number" name="price" placeholder="Price (Rs.)" step="0.01" min="0" required>
                </div>
                <div class="field">
                    <select name="category" required>
                        <option value="">Select Category</option>
                        <option value="Vegetables">Vegetables</option>
                        <option value="Fruits">Fruits</option>
                        <option value="Dairy Products">Dairy Products</option>
                        <option value="Beverages">Beverages</option>
                        <option value="Snacks">Snacks</option>
                    </select>
                </div>
                <div class="field">
                    <input type="file" name="image" accept="image/*" required>
                </div>
                <div class="field">
                    <button type="submit" name="add_product">
                        <i class="fas fa-plus"></i> Add Product
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showAddProductForm() {
            document.getElementById('addProductModal').style.display = 'block';
        }

        function closeAddProductForm() {
            document.getElementById('addProductModal').style.display = 'none';
        }

        function deleteProduct(productId) {
            if(confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
                window.location.href = 'Products.php?delete=' + productId;
            }
        }

        function editProduct(productId) {
            // You can implement edit functionality here
            alert('Edit functionality can be implemented here for product ID: ' + productId);
        }

        // Close modal when clicking outside of it
        window.onclick = function(event) {
            var modal = document.getElementById('addProductModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>

</body>

</html>