<?php 
    include('config.php');
    include('cart_functions.php');
    
    if(!isset($_GET['category'])){
        header('location:index.php');
    }

    // Handle add to cart
    if(isset($_POST['add_to_cart']) && isset($_SESSION['AccountID'])) {
        $productID = intval($_POST['product_id']);
        $quantity = intval($_POST['quantity']) ?: 1;
        
        // Get customer ID from session
        $customerQuery = "SELECT customerID FROM customer WHERE AccID = ?";
        $stmt = $con->prepare($customerQuery);
        $stmt->bind_param("i", $_SESSION['AccountID']);
        $stmt->execute();
        $customerResult = $stmt->get_result();
        
        if($customerResult->num_rows > 0) {
            $customer = $customerResult->fetch_assoc();
            $customerID = $customer['customerID'];
            
            if(addToCart($customerID, $productID, $quantity)) {
                echo '<script>
                    alert("Product added to cart successfully!");
                    window.location.href = "productlist.php?category=' . urlencode($_GET['category']) . '";
                </script>';
            } else {
                echo '<script>alert("Error adding product to cart!");</script>';
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title><?php echo htmlspecialchars($_GET['category']) ?> - QuicKart</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/x-icon" href="images/logo-icon.jpeg">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/style1.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .product-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid #f0f0f0;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .product-image {
            position: relative;
            height: 200px;
            overflow: hidden;
            background: linear-gradient(135deg, #ff4444, #ff6666);
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .product-card:hover .product-image img {
            transform: scale(1.05);
        }

        .product-info {
            padding: 20px;
        }

        .product-name {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            line-height: 1.3;
        }

        .product-unit {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }

        .product-price {
            font-size: 20px;
            font-weight: 700;
            color: #ff4444;
            margin-bottom: 15px;
        }

        .product-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .quantity-selector {
            display: flex;
            align-items: center;
            border: 2px solid #ff4444;
            border-radius: 25px;
            overflow: hidden;
            background: white;
        }

        .quantity-btn {
            background: #ff4444;
            color: white;
            border: none;
            width: 35px;
            height: 35px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: background 0.3s ease;
        }

        .quantity-btn:hover {
            background: #e03333;
        }

        .quantity-input {
            border: none;
            width: 50px;
            text-align: center;
            font-weight: 600;
            color: #333;
            background: transparent;
        }

        .add-to-cart-btn {
            flex: 1;
            background: linear-gradient(135deg, #ff4444, #e03333);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .add-to-cart-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 68, 68, 0.3);
        }

        .category-header {
            text-align: center;
            padding: 40px 20px;
            background: linear-gradient(135deg, #ff4444, #ff6666);
            color: white;
            margin-bottom: 30px;
        }

        .category-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .category-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .login-prompt {
            text-align: center;
            padding: 20px;
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 10px;
            margin: 20px;
            color: #856404;
        }

        .login-prompt a {
            color: #ff4444;
            text-decoration: none;
            font-weight: 600;
        }

        .login-prompt a:hover {
            text-decoration: underline;
        }

        .empty-category {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .empty-category i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }

        .cart-float-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: linear-gradient(135deg, #ff4444, #e03333);
            color: white;
            border: none;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            font-size: 24px;
            cursor: pointer;
            box-shadow: 0 8px 25px rgba(255, 68, 68, 0.3);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .cart-float-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 12px 35px rgba(255, 68, 68, 0.4);
        }

        .cart-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #fff;
            color: #ff4444;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            font-size: 12px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #ff4444;
        }

        @media (max-width: 768px) {
            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 15px;
                padding: 15px;
            }
            
            .category-title {
                font-size: 2rem;
            }
            
            .product-actions {
                flex-direction: column;
                gap: 10px;
            }
            
            .quantity-selector {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>

<body>
<?php include('header&footer/header.php'); ?>

<div class="category-header">
    <h1 class="category-title"><?php echo htmlspecialchars($_GET['category']); ?></h1>
    <p class="category-subtitle">Fresh, quality products delivered to your doorstep</p>
</div>

<?php if(!isset($_SESSION['AccountID'])): ?>
<div class="login-prompt">
    <i class="fas fa-info-circle"></i>
    <strong>Please <a href="login.php">login</a> to add items to your cart and place orders.</strong>
</div>
<?php endif; ?>

<div class="container">
    <div class="product-grid">
        <?php
        $category = mysqli_real_escape_string($con, $_GET['category']);
        $query = "SELECT * FROM products WHERE category = '$category' ORDER BY productName";
        $result = mysqli_query($con, $query);
        
        if($result && mysqli_num_rows($result) > 0) {
            while($product = $result->fetch_assoc()) {
                echo '<div class="product-card">';
                echo '<div class="product-image">';
                
                $imagePath = 'images/products/' . $product['imgName'];
                if(file_exists($imagePath)) {
                    echo '<img src="' . $imagePath . '" alt="' . htmlspecialchars($product['productName']) . '">';
                } else {
                    echo '<div style="display: flex; align-items: center; justify-content: center; height: 100%; background: #f8f9fa; color: #666;">';
                    echo '<i class="fas fa-image" style="font-size: 3rem;"></i>';
                    echo '</div>';
                }
                
                echo '</div>';
                echo '<div class="product-info">';
                echo '<h3 class="product-name">' . htmlspecialchars($product['productName']) . '</h3>';
                echo '<p class="product-unit">' . htmlspecialchars($product['unit']) . '</p>';
                echo '<div class="product-price">Rs. ' . number_format($product['price'], 2) . '</div>';
                
                if(isset($_SESSION['AccountID'])) {
                    echo '<form method="post" class="product-actions">';
                    echo '<input type="hidden" name="product_id" value="' . $product['productID'] . '">';
                    echo '<div class="quantity-selector">';
                    echo '<button type="button" class="quantity-btn" onclick="decreaseQuantity(this)">-</button>';
                    echo '<input type="number" name="quantity" value="1" min="1" max="10" class="quantity-input" readonly>';
                    echo '<button type="button" class="quantity-btn" onclick="increaseQuantity(this)">+</button>';
                    echo '</div>';
                    echo '<button type="submit" name="add_to_cart" class="add-to-cart-btn">';
                    echo '<i class="fas fa-cart-plus"></i> Add to Cart';
                    echo '</button>';
                    echo '</form>';
                } else {
                    echo '<div class="product-actions">';
                    echo '<a href="login.php" class="add-to-cart-btn" style="text-decoration: none;">';
                    echo '<i class="fas fa-sign-in-alt"></i> Login to Order';
                    echo '</a>';
                    echo '</div>';
                }
                
                echo '</div>';
                echo '</div>';
            }
        } else {
            echo '<div class="empty-category" style="grid-column: 1 / -1;">';
            echo '<i class="fas fa-box-open"></i>';
            echo '<h3>No products found in this category</h3>';
            echo '<p>Please check back later for new products.</p>';
            echo '</div>';
        }
        
        mysqli_close($con);
        ?>
    </div>
</div>

<?php if(isset($_SESSION['AccountID'])): ?>
<!-- Floating Cart Button -->
<button class="cart-float-btn" onclick="window.location.href='cart.php'">
    <i class="fas fa-shopping-cart"></i>
    <?php
    // Get cart count
    $customerQuery = "SELECT customerID FROM customer WHERE AccID = " . $_SESSION['AccountID'];
    $customerResult = mysqli_query($con, $customerQuery);
    if($customerResult && mysqli_num_rows($customerResult) > 0) {
        $customer = $customerResult->fetch_assoc();
        $cartCountQuery = "SELECT SUM(quantity) as total FROM cart WHERE customerID = " . $customer['customerID'];
        $cartCountResult = mysqli_query($con, $cartCountQuery);
        if($cartCountResult) {
            $cartCount = $cartCountResult->fetch_assoc();
            $totalItems = $cartCount['total'] ?? 0;
            if($totalItems > 0) {
                echo '<span class="cart-count">' . $totalItems . '</span>';
            }
        }
    }
    ?>
</button>
<?php endif; ?>

<?php include('header&footer/footer.html'); ?>

<script>
function increaseQuantity(btn) {
    const input = btn.parentElement.querySelector('.quantity-input');
    const currentValue = parseInt(input.value);
    if (currentValue < 10) {
        input.value = currentValue + 1;
    }
}

function decreaseQuantity(btn) {
    const input = btn.parentElement.querySelector('.quantity-input');
    const currentValue = parseInt(input.value);
    if (currentValue > 1) {
        input.value = currentValue - 1;
    }
}

// Add smooth scrolling for better UX
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});
</script>

</body>
</html>