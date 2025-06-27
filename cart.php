<?php
include('config.php');
include('cart_functions.php');

if(!isset($_SESSION['AccountID'])) {
    header('location: login.php');
    exit();
}

// Get customer ID
$customerQuery = "SELECT customerID FROM customer WHERE AccID = ?";
$stmt = $con->prepare($customerQuery);
$stmt->bind_param("i", $_SESSION['AccountID']);
$stmt->execute();
$customerResult = $stmt->get_result();

if($customerResult->num_rows == 0) {
    header('location: login.php');
    exit();
}

$customer = $customerResult->fetch_assoc();
$customerID = $customer['customerID'];

// Handle cart updates
if(isset($_POST['update_cart'])) {
    $productID = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    
    if(updateCartQuantity($customerID, $productID, $quantity)) {
        echo '<script>alert("Cart updated successfully!");</script>';
    }
}

if(isset($_POST['remove_item'])) {
    $productID = intval($_POST['product_id']);
    
    if(removeFromCart($customerID, $productID)) {
        echo '<script>alert("Item removed from cart!");</script>';
    }
}

// Get cart items
$cartItems = getCartItems($customerID);
$cartTotal = getCartTotal($customerID);
$deliveryInfo = getDeliveryChargeForCustomer($customerID);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - QuicKart</title>
    <link rel="icon" type="image/x-icon" href="images/logo-icon.jpeg">
    <link href="css/style1.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .cart-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            min-height: 80vh;
        }

        .cart-header {
            text-align: center;
            padding: 40px 20px;
            background: linear-gradient(135deg, #ff4444, #ff6666);
            color: white;
            margin-bottom: 30px;
            border-radius: 15px;
        }

        .cart-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .cart-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin-top: 20px;
        }

        .cart-items {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .cart-item {
            display: grid;
            grid-template-columns: 100px 1fr auto auto auto;
            gap: 20px;
            padding: 20px;
            border-bottom: 1px solid #f0f0f0;
            align-items: center;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .item-image {
            width: 80px;
            height: 80px;
            border-radius: 10px;
            object-fit: cover;
            border: 2px solid #f0f0f0;
        }

        .item-details h3 {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .item-details p {
            color: #666;
            font-size: 14px;
        }

        .item-price {
            font-size: 18px;
            font-weight: 700;
            color: #ff4444;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            border: 2px solid #ff4444;
            border-radius: 25px;
            overflow: hidden;
        }

        .qty-btn {
            background: #ff4444;
            color: white;
            border: none;
            width: 35px;
            height: 35px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }

        .qty-btn:hover {
            background: #e03333;
        }

        .qty-input {
            border: none;
            width: 50px;
            text-align: center;
            font-weight: 600;
            background: transparent;
        }

        .remove-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .remove-btn:hover {
            background: #c82333;
            transform: translateY(-1px);
        }

        .cart-summary {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            padding: 25px;
            height: fit-content;
            position: sticky;
            top: 20px;
        }

        .summary-title {
            font-size: 24px;
            font-weight: 700;
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #f0f0f0;
        }

        .summary-row:last-child {
            border-bottom: 2px solid #ff4444;
            font-weight: 700;
            font-size: 18px;
            color: #ff4444;
        }

        .checkout-btn {
            width: 100%;
            background: linear-gradient(135deg, #ff4444, #e03333);
            color: white;
            border: none;
            padding: 15px;
            border-radius: 25px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .checkout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 68, 68, 0.3);
        }

        .empty-cart {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .empty-cart i {
            font-size: 5rem;
            color: #ddd;
            margin-bottom: 20px;
        }

        .continue-shopping {
            background: #28a745;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 25px;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .continue-shopping:hover {
            background: #218838;
            transform: translateY(-2px);
        }

        .delivery-info {
            background: #e8f5e8;
            border: 1px solid #28a745;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .delivery-info h4 {
            color: #28a745;
            margin-bottom: 10px;
        }

        .delivery-info p {
            margin: 5px 0;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .cart-content {
                grid-template-columns: 1fr;
            }
            
            .cart-item {
                grid-template-columns: 80px 1fr;
                gap: 15px;
            }
            
            .item-controls {
                grid-column: 1 / -1;
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-top: 15px;
            }
        }
    </style>
</head>

<body>
    <?php include('header&footer/header.php'); ?>

    <div class="cart-container">
        <div class="cart-header">
            <h1 class="cart-title"><i class="fas fa-shopping-cart"></i> Your Shopping Cart</h1>
            <p>Review your items and proceed to checkout</p>
        </div>

        <?php if($cartItems->num_rows > 0): ?>
        <div class="cart-content">
            <div class="cart-items">
                <?php while($item = $cartItems->fetch_assoc()): ?>
                <div class="cart-item">
                    <img src="images/products/<?php echo $item['imgName']; ?>" 
                         alt="<?php echo htmlspecialchars($item['productName']); ?>" 
                         class="item-image">
                    
                    <div class="item-details">
                        <h3><?php echo htmlspecialchars($item['productName']); ?></h3>
                        <p><?php echo htmlspecialchars($item['unit']); ?></p>
                        <p>Category: <?php echo htmlspecialchars($item['category']); ?></p>
                    </div>
                    
                    <div class="item-price">
                        Rs. <?php echo number_format($item['price'], 2); ?>
                    </div>
                    
                    <form method="post" style="display: inline;">
                        <input type="hidden" name="product_id" value="<?php echo $item['productID']; ?>">
                        <div class="quantity-controls">
                            <button type="button" class="qty-btn" onclick="updateQuantity(<?php echo $item['productID']; ?>, -1)">-</button>
                            <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                   min="1" max="10" class="qty-input" 
                                   onchange="updateCartItem(<?php echo $item['productID']; ?>, this.value)">
                            <button type="button" class="qty-btn" onclick="updateQuantity(<?php echo $item['productID']; ?>, 1)">+</button>
                        </div>
                    </form>
                    
                    <form method="post" style="display: inline;">
                        <input type="hidden" name="product_id" value="<?php echo $item['productID']; ?>">
                        <button type="submit" name="remove_item" class="remove-btn" 
                                onclick="return confirm('Remove this item from cart?')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
                <?php endwhile; ?>
            </div>

            <div class="cart-summary">
                <h3 class="summary-title">Order Summary</h3>
                
                <?php if($deliveryInfo['distance'] > 0): ?>
                <div class="delivery-info">
                    <h4><i class="fas fa-truck"></i> Delivery Information</h4>
                    <p><strong>Distance from Jaffna:</strong> <?php echo $deliveryInfo['distance']; ?> km</p>
                    <p><strong>Delivery Policy:</strong> Free delivery within 3km, Rs. 7/km beyond</p>
                </div>
                <?php endif; ?>
                
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span>Rs. <?php echo number_format($cartTotal, 2); ?></span>
                </div>
                
                <div class="summary-row">
                    <span>Delivery Charge:</span>
                    <span>Rs. <?php echo number_format($deliveryInfo['charge'], 2); ?></span>
                </div>
                
                <div class="summary-row">
                    <span>Total:</span>
                    <span>Rs. <?php echo number_format($cartTotal + $deliveryInfo['charge'], 2); ?></span>
                </div>
                
                <button class="checkout-btn" onclick="window.location.href='checkout.php'">
                    <i class="fas fa-credit-card"></i> Proceed to Checkout
                </button>
                
                <a href="index.php" class="continue-shopping">
                    <i class="fas fa-arrow-left"></i> Continue Shopping
                </a>
            </div>
        </div>
        <?php else: ?>
        <div class="empty-cart">
            <i class="fas fa-shopping-cart"></i>
            <h2>Your cart is empty</h2>
            <p>Add some delicious items to your cart and come back!</p>
            <a href="index.php" class="continue-shopping">
                <i class="fas fa-store"></i> Start Shopping
            </a>
        </div>
        <?php endif; ?>
    </div>

    <?php include('header&footer/footer.html'); ?>

    <script>
        function updateQuantity(productId, change) {
            const input = document.querySelector(`input[name="quantity"][form*="${productId}"], input[onchange*="${productId}"]`);
            if (input) {
                let newValue = parseInt(input.value) + change;
                if (newValue >= 1 && newValue <= 10) {
                    input.value = newValue;
                    updateCartItem(productId, newValue);
                }
            }
        }

        function updateCartItem(productId, quantity) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="product_id" value="${productId}">
                <input type="hidden" name="quantity" value="${quantity}">
                <input type="hidden" name="update_cart" value="1">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>
</html>