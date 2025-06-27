<?php
include('config.php');
include('cart_functions.php');

if(!isset($_SESSION['AccountID'])) {
    header('location: login.php');
    exit();
}

// Get customer details
$customerQuery = "SELECT c.*, a.AccEmail FROM customer c 
                  JOIN account a ON c.AccID = a.AccID 
                  WHERE c.AccID = ?";
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

// Get cart items and totals
$cartItems = getCartItems($customerID);
$cartTotal = getCartTotal($customerID);

if($cartTotal == 0) {
    header('location: cart.php');
    exit();
}

$deliveryInfo = getDeliveryChargeForCustomer($customerID);

// Handle order placement
if(isset($_POST['place_order'])) {
    $deliveryAddress = mysqli_real_escape_string($con, $_POST['delivery_address']);
    $deliveryLat = floatval($_POST['delivery_lat']) ?: null;
    $deliveryLon = floatval($_POST['delivery_lon']) ?: null;
    $paymentMethod = mysqli_real_escape_string($con, $_POST['payment_method']);
    $notes = mysqli_real_escape_string($con, $_POST['notes']);
    
    // Calculate delivery distance and charge
    $deliveryDistance = 0;
    $deliveryCharge = 0;
    
    if($deliveryLat && $deliveryLon) {
        $deliveryDistance = calculateDistance(JAFFNA_LAT, JAFFNA_LON, $deliveryLat, $deliveryLon);
        $deliveryCharge = calculateDeliveryCharge($deliveryDistance);
    }
    
    $totalAmount = $cartTotal + $deliveryCharge;
    
    // Start transaction
    $con->begin_transaction();
    
    try {
        // Insert order
        $orderQuery = "INSERT INTO orders (customerID, total_amount, delivery_charge, delivery_distance, 
                       delivery_address, delivery_latitude, delivery_longitude, payment_method, notes) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $con->prepare($orderQuery);
        $stmt->bind_param("idddsddss", $customerID, $totalAmount, $deliveryCharge, $deliveryDistance,
                         $deliveryAddress, $deliveryLat, $deliveryLon, $paymentMethod, $notes);
        $stmt->execute();
        
        $orderID = $con->insert_id;
        
        // Insert order items
        $cartItemsForOrder = getCartItems($customerID);
        while($item = $cartItemsForOrder->fetch_assoc()) {
            $itemTotal = $item['quantity'] * $item['price'];
            
            $orderItemQuery = "INSERT INTO order_items (orderID, productID, quantity, unit_price, total_price) 
                              VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $con->prepare($orderItemQuery);
            $stmt->bind_param("iiidd", $orderID, $item['productID'], $item['quantity'], $item['price'], $itemTotal);
            $stmt->execute();
        }
        
        // Clear cart
        clearCart($customerID);
        
        // Update customer location if provided
        if($deliveryLat && $deliveryLon) {
            $updateLocationQuery = "UPDATE customer SET latitude = ?, longitude = ? WHERE customerID = ?";
            $stmt = $con->prepare($updateLocationQuery);
            $stmt->bind_param("ddi", $deliveryLat, $deliveryLon, $customerID);
            $stmt->execute();
        }
        
        $con->commit();
        
        // Redirect to order confirmation
        header("location: order_confirmation.php?order_id=" . $orderID);
        exit();
        
    } catch (Exception $e) {
        $con->rollback();
        $error = "Error placing order. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - QuicKart</title>
    <link rel="icon" type="image/x-icon" href="images/logo-icon.jpeg">
    <link href="css/style1.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .checkout-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            min-height: 80vh;
        }

        .checkout-header {
            text-align: center;
            padding: 40px 20px;
            background: linear-gradient(135deg, #ff4444, #ff6666);
            color: white;
            margin-bottom: 30px;
            border-radius: 15px;
        }

        .checkout-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .checkout-form {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        .form-section {
            margin-bottom: 30px;
        }

        .form-section h3 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ff4444;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #ff4444;
            box-shadow: 0 0 0 3px rgba(255, 68, 68, 0.1);
        }

        .location-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 10px;
            transition: all 0.3s ease;
        }

        .location-btn:hover {
            background: #218838;
        }

        .order-summary {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            padding: 30px;
            height: fit-content;
            position: sticky;
            top: 20px;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .summary-item:last-child {
            border-bottom: 2px solid #ff4444;
            font-weight: 700;
            font-size: 18px;
            color: #ff4444;
        }

        .order-items {
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: 20px;
        }

        .order-item {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f8f9fa;
        }

        .order-item img {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            object-fit: cover;
            margin-right: 15px;
        }

        .item-info {
            flex: 1;
        }

        .item-info h5 {
            margin: 0 0 5px 0;
            font-size: 14px;
            color: #333;
        }

        .item-info p {
            margin: 0;
            font-size: 12px;
            color: #666;
        }

        .place-order-btn {
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

        .place-order-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 68, 68, 0.3);
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

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 768px) {
            .checkout-content {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <?php include('header&footer/header.php'); ?>

    <div class="checkout-container">
        <div class="checkout-header">
            <h1><i class="fas fa-credit-card"></i> Checkout</h1>
            <p>Complete your order details</p>
        </div>

        <?php if(isset($error)): ?>
        <div class="error-message">
            <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <form method="post" class="checkout-content">
            <div class="checkout-form">
                <div class="form-section">
                    <h3><i class="fas fa-user"></i> Customer Information</h3>
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" value="<?php echo htmlspecialchars($customer['firstName'] . ' ' . $customer['lastName']); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" value="<?php echo htmlspecialchars($customer['AccEmail']); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" value="<?php echo htmlspecialchars($customer['phone'] ?? 'Not provided'); ?>" readonly>
                    </div>
                </div>

                <div class="form-section">
                    <h3><i class="fas fa-map-marker-alt"></i> Delivery Information</h3>
                    
                    <div class="delivery-info">
                        <h4><i class="fas fa-info-circle"></i> Delivery Policy</h4>
                        <p><strong>Free delivery</strong> within 3km from Jaffna town center</p>
                        <p><strong>Rs. 7 per km</strong> for deliveries beyond 3km</p>
                    </div>

                    <div class="form-group">
                        <label for="delivery_address">Delivery Address *</label>
                        <textarea name="delivery_address" id="delivery_address" rows="3" required 
                                  placeholder="Enter your complete delivery address"><?php echo htmlspecialchars($customer['address'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <button type="button" class="location-btn" onclick="getCurrentLocation()">
                            <i class="fas fa-location-arrow"></i> Get My Location
                        </button>
                        <p style="font-size: 12px; color: #666; margin-top: 5px;">
                            This helps us calculate accurate delivery charges
                        </p>
                    </div>

                    <input type="hidden" name="delivery_lat" id="delivery_lat" value="<?php echo $customer['latitude'] ?? ''; ?>">
                    <input type="hidden" name="delivery_lon" id="delivery_lon" value="<?php echo $customer['longitude'] ?? ''; ?>">

                    <div id="location_status" style="margin-top: 10px; font-size: 14px;"></div>
                </div>

                <div class="form-section">
                    <h3><i class="fas fa-credit-card"></i> Payment Method</h3>
                    <div class="form-group">
                        <select name="payment_method" required>
                            <option value="">Select Payment Method</option>
                            <option value="cash_on_delivery">Cash on Delivery</option>
                            <option value="card">Credit/Debit Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>
                </div>

                <div class="form-section">
                    <h3><i class="fas fa-sticky-note"></i> Additional Notes</h3>
                    <div class="form-group">
                        <textarea name="notes" rows="3" 
                                  placeholder="Any special instructions for delivery..."></textarea>
                    </div>
                </div>
            </div>

            <div class="order-summary">
                <h3><i class="fas fa-receipt"></i> Order Summary</h3>
                
                <div class="order-items">
                    <?php 
                    $cartItemsDisplay = getCartItems($customerID);
                    while($item = $cartItemsDisplay->fetch_assoc()): 
                    ?>
                    <div class="order-item">
                        <img src="images/products/<?php echo $item['imgName']; ?>" 
                             alt="<?php echo htmlspecialchars($item['productName']); ?>">
                        <div class="item-info">
                            <h5><?php echo htmlspecialchars($item['productName']); ?></h5>
                            <p><?php echo $item['quantity']; ?> × Rs. <?php echo number_format($item['price'], 2); ?></p>
                        </div>
                        <span>Rs. <?php echo number_format($item['total_price'], 2); ?></span>
                    </div>
                    <?php endwhile; ?>
                </div>

                <div class="summary-item">
                    <span>Subtotal:</span>
                    <span>Rs. <?php echo number_format($cartTotal, 2); ?></span>
                </div>

                <div class="summary-item">
                    <span>Delivery Charge:</span>
                    <span id="delivery_charge_display">Rs. <?php echo number_format($deliveryInfo['charge'], 2); ?></span>
                </div>

                <div class="summary-item">
                    <span>Total Amount:</span>
                    <span id="total_amount_display">Rs. <?php echo number_format($cartTotal + $deliveryInfo['charge'], 2); ?></span>
                </div>

                <button type="submit" name="place_order" class="place-order-btn">
                    <i class="fas fa-check-circle"></i> Place Order
                </button>
            </div>
        </form>
    </div>

    <?php include('header&footer/footer.html'); ?>

    <script>
        function getCurrentLocation() {
            const statusDiv = document.getElementById('location_status');
            
            if (navigator.geolocation) {
                statusDiv.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Getting your location...';
                
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const lat = position.coords.latitude;
                        const lon = position.coords.longitude;
                        
                        document.getElementById('delivery_lat').value = lat;
                        document.getElementById('delivery_lon').value = lon;
                        
                        // Calculate delivery charge
                        calculateDeliveryCharge(lat, lon);
                        
                        statusDiv.innerHTML = '<i class="fas fa-check-circle" style="color: green;"></i> Location obtained successfully!';
                    },
                    function(error) {
                        statusDiv.innerHTML = '<i class="fas fa-exclamation-triangle" style="color: red;"></i> Unable to get location. Please enter address manually.';
                        console.error('Geolocation error:', error);
                    }
                );
            } else {
                statusDiv.innerHTML = '<i class="fas fa-times-circle" style="color: red;"></i> Geolocation is not supported by this browser.';
            }
        }

        function calculateDeliveryCharge(lat, lon) {
            // Jaffna town center coordinates
            const jaffnaLat = 9.6615;
            const jaffnaLon = 80.0255;
            
            // Calculate distance using Haversine formula
            const R = 6371; // Earth's radius in km
            const dLat = (lat - jaffnaLat) * Math.PI / 180;
            const dLon = (lon - jaffnaLon) * Math.PI / 180;
            
            const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                      Math.cos(jaffnaLat * Math.PI / 180) * Math.cos(lat * Math.PI / 180) *
                      Math.sin(dLon/2) * Math.sin(dLon/2);
            
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            const distance = R * c;
            
            // Calculate delivery charge
            let deliveryCharge = 0;
            if (distance > 3) {
                deliveryCharge = (distance - 3) * 7;
            }
            
            // Update display
            const subtotal = <?php echo $cartTotal; ?>;
            document.getElementById('delivery_charge_display').textContent = 'Rs. ' + deliveryCharge.toFixed(2);
            document.getElementById('total_amount_display').textContent = 'Rs. ' + (subtotal + deliveryCharge).toFixed(2);
            
            // Update location status
            const statusDiv = document.getElementById('location_status');
            statusDiv.innerHTML = `<i class="fas fa-map-marker-alt" style="color: blue;"></i> Distance: ${distance.toFixed(2)} km from Jaffna center`;
        }

        // Auto-get location if customer has saved coordinates
        <?php if($customer['latitude'] && $customer['longitude']): ?>
        window.onload = function() {
            calculateDeliveryCharge(<?php echo $customer['latitude']; ?>, <?php echo $customer['longitude']; ?>);
            document.getElementById('location_status').innerHTML = '<i class="fas fa-check-circle" style="color: green;"></i> Using saved location';
        };
        <?php endif; ?>
    </script>
</body>
</html>