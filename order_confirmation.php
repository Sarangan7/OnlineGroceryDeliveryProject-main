<?php
include('config.php');

if(!isset($_SESSION['AccountID']) || !isset($_GET['order_id'])) {
    header('location: index.php');
    exit();
}

$orderID = intval($_GET['order_id']);

// Get order details
$orderQuery = "SELECT o.*, c.firstName, c.lastName, a.AccEmail 
               FROM orders o 
               JOIN customer c ON o.customerID = c.customerID 
               JOIN account a ON c.AccID = a.AccID 
               WHERE o.orderID = ? AND c.AccID = ?";

$stmt = $con->prepare($orderQuery);
$stmt->bind_param("ii", $orderID, $_SESSION['AccountID']);
$stmt->execute();
$orderResult = $stmt->get_result();

if($orderResult->num_rows == 0) {
    header('location: index.php');
    exit();
}

$order = $orderResult->fetch_assoc();

// Get order items
$itemsQuery = "SELECT oi.*, p.productName, p.unit, p.imgName 
               FROM order_items oi 
               JOIN products p ON oi.productID = p.productID 
               WHERE oi.orderID = ?";

$stmt = $con->prepare($itemsQuery);
$stmt->bind_param("i", $orderID);
$stmt->execute();
$itemsResult = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - QuicKart</title>
    <link rel="icon" type="image/x-icon" href="images/logo-icon.jpeg">
    <link href="css/style1.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .confirmation-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            min-height: 80vh;
        }

        .success-header {
            text-align: center;
            padding: 40px 20px;
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            margin-bottom: 30px;
            border-radius: 15px;
        }

        .success-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }

        .order-details {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 30px;
        }

        .detail-section {
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid #f0f0f0;
        }

        .detail-section:last-child {
            border-bottom: none;
        }

        .detail-section h3 {
            color: #333;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .order-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #f8f9fa;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .order-item img {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            object-fit: cover;
            margin-right: 15px;
        }

        .item-details {
            flex: 1;
        }

        .item-details h5 {
            margin: 0 0 5px 0;
            color: #333;
        }

        .item-details p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }

        .item-total {
            font-weight: 600;
            color: #ff4444;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 10px 0;
        }

        .summary-row.total {
            border-top: 2px solid #ff4444;
            font-weight: 700;
            font-size: 18px;
            color: #ff4444;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .action-buttons {
            text-align: center;
            margin-top: 30px;
        }

        .btn {
            display: inline-block;
            padding: 12px 25px;
            margin: 0 10px;
            border: none;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-primary {
            background: linear-gradient(135deg, #ff4444, #e03333);
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .delivery-timeline {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }

        .timeline-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .timeline-item:last-child {
            margin-bottom: 0;
        }

        .timeline-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #28a745;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 12px;
        }

        .timeline-icon.pending {
            background: #ffc107;
        }

        .timeline-icon.inactive {
            background: #dee2e6;
            color: #6c757d;
        }
    </style>
</head>

<body>
    <?php include('header&footer/header.php'); ?>

    <div class="confirmation-container">
        <div class="success-header">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1>Order Placed Successfully!</h1>
            <p>Thank you for your order. We'll prepare it with care.</p>
            <h2>Order #<?php echo $orderID; ?></h2>
        </div>

        <div class="order-details">
            <div class="detail-section">
                <h3><i class="fas fa-info-circle"></i> Order Information</h3>
                <div class="summary-row">
                    <span>Order Date:</span>
                    <span><?php echo date('F j, Y g:i A', strtotime($order['order_date'])); ?></span>
                </div>
                <div class="summary-row">
                    <span>Order Status:</span>
                    <span><span class="status-badge status-pending"><?php echo ucfirst($order['order_status']); ?></span></span>
                </div>
                <div class="summary-row">
                    <span>Payment Method:</span>
                    <span><?php echo ucwords(str_replace('_', ' ', $order['payment_method'])); ?></span>
                </div>
                <div class="summary-row">
                    <span>Payment Status:</span>
                    <span><span class="status-badge status-pending"><?php echo ucfirst($order['payment_status']); ?></span></span>
                </div>
            </div>

            <div class="detail-section">
                <h3><i class="fas fa-map-marker-alt"></i> Delivery Information</h3>
                <p><strong>Address:</strong> <?php echo htmlspecialchars($order['delivery_address']); ?></p>
                <?php if($order['delivery_distance'] > 0): ?>
                <div class="summary-row">
                    <span>Distance from Jaffna:</span>
                    <span><?php echo number_format($order['delivery_distance'], 2); ?> km</span>
                </div>
                <?php endif; ?>
                <div class="summary-row">
                    <span>Delivery Charge:</span>
                    <span>Rs. <?php echo number_format($order['delivery_charge'], 2); ?></span>
                </div>
                <?php if($order['notes']): ?>
                <p><strong>Special Instructions:</strong> <?php echo htmlspecialchars($order['notes']); ?></p>
                <?php endif; ?>
            </div>

            <div class="detail-section">
                <h3><i class="fas fa-shopping-bag"></i> Order Items</h3>
                <?php while($item = $itemsResult->fetch_assoc()): ?>
                <div class="order-item">
                    <img src="images/products/<?php echo $item['imgName']; ?>" 
                         alt="<?php echo htmlspecialchars($item['productName']); ?>">
                    <div class="item-details">
                        <h5><?php echo htmlspecialchars($item['productName']); ?></h5>
                        <p><?php echo htmlspecialchars($item['unit']); ?> × <?php echo $item['quantity']; ?></p>
                        <p>Rs. <?php echo number_format($item['unit_price'], 2); ?> each</p>
                    </div>
                    <div class="item-total">
                        Rs. <?php echo number_format($item['total_price'], 2); ?>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

            <div class="detail-section">
                <h3><i class="fas fa-receipt"></i> Order Summary</h3>
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span>Rs. <?php echo number_format($order['total_amount'] - $order['delivery_charge'], 2); ?></span>
                </div>
                <div class="summary-row">
                    <span>Delivery Charge:</span>
                    <span>Rs. <?php echo number_format($order['delivery_charge'], 2); ?></span>
                </div>
                <div class="summary-row total">
                    <span>Total Amount:</span>
                    <span>Rs. <?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
            </div>

            <div class="delivery-timeline">
                <h4><i class="fas fa-truck"></i> Delivery Timeline</h4>
                <div class="timeline-item">
                    <div class="timeline-icon">
                        <i class="fas fa-check"></i>
                    </div>
                    <div>
                        <strong>Order Placed</strong>
                        <p>Your order has been received and is being processed.</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-icon pending">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <strong>Order Confirmed</strong>
                        <p>We'll confirm your order and start preparing it.</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-icon inactive">
                        <i class="fas fa-box"></i>
                    </div>
                    <div>
                        <strong>Preparing</strong>
                        <p>Your fresh items are being carefully selected and packed.</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-icon inactive">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div>
                        <strong>Out for Delivery</strong>
                        <p>Your order is on its way to your doorstep.</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-icon inactive">
                        <i class="fas fa-home"></i>
                    </div>
                    <div>
                        <strong>Delivered</strong>
                        <p>Enjoy your fresh groceries!</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="action-buttons">
            <a href="index.php" class="btn btn-primary">
                <i class="fas fa-store"></i> Continue Shopping
            </a>
            <a href="profile.php" class="btn btn-secondary">
                <i class="fas fa-user"></i> View Profile
            </a>
        </div>
    </div>

    <?php include('header&footer/footer.html'); ?>
</body>
</html>