<?php
include('../config.php');

if(!isset($_SESSION['email']) || $_SESSION['AccountID'] != "Admin"){
    exit('Unauthorized');
}

if(!isset($_GET['customer_id'])) {
    exit('Customer ID not provided');
}

$customerId = intval($_GET['customer_id']);

// Get customer details with order information
$customerQuery = "SELECT c.*, a.AccEmail, a.AccPassword,
                  COUNT(o.orderID) as total_orders,
                  COALESCE(SUM(o.total_amount), 0) as total_spent,
                  MAX(o.order_date) as last_order_date
                  FROM customer c 
                  LEFT JOIN account a ON c.AccID = a.AccID 
                  LEFT JOIN orders o ON c.customerID = o.customerID
                  WHERE c.AccID = ?
                  GROUP BY c.customerID";

$stmt = $con->prepare($customerQuery);
$stmt->bind_param("i", $customerId);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0) {
    echo '<p style="color: red;">Customer not found.</p>';
    exit();
}

$customer = $result->fetch_assoc();

// Get recent orders
$ordersQuery = "SELECT orderID, order_date, total_amount, order_status, payment_status 
                FROM orders 
                WHERE customerID = ? 
                ORDER BY order_date DESC 
                LIMIT 5";

$stmt = $con->prepare($ordersQuery);
$stmt->bind_param("i", $customer['customerID']);
$stmt->execute();
$ordersResult = $stmt->get_result();
?>

<style>
.customer-detail-section {
    margin-bottom: 25px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 10px;
    border-left: 4px solid #ff4444;
}

.customer-detail-section h3 {
    color: #ff4444;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #dee2e6;
}

.detail-item:last-child {
    border-bottom: none;
}

.detail-label {
    font-weight: 600;
    color: #333;
}

.detail-value {
    color: #666;
}

.orders-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

.orders-table th,
.orders-table td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid #dee2e6;
}

.orders-table th {
    background: #ff4444;
    color: white;
    font-weight: 600;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-confirmed {
    background: #d4edda;
    color: #155724;
}

.status-delivered {
    background: #d1ecf1;
    color: #0c5460;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    margin-top: 15px;
}

.stat-card {
    background: white;
    padding: 15px;
    border-radius: 8px;
    text-align: center;
    border: 1px solid #dee2e6;
}

.stat-number {
    font-size: 24px;
    font-weight: 700;
    color: #ff4444;
}

.stat-label {
    font-size: 12px;
    color: #666;
    text-transform: uppercase;
}
</style>

<div class="customer-detail-section">
    <h3><i class="fas fa-user"></i> Personal Information</h3>
    <div class="detail-grid">
        <div class="detail-item">
            <span class="detail-label">Customer ID:</span>
            <span class="detail-value"><?php echo $customer['customerID']; ?></span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Account ID:</span>
            <span class="detail-value"><?php echo $customer['AccID']; ?></span>
        </div>
        <div class="detail-item">
            <span class="detail-label">First Name:</span>
            <span class="detail-value"><?php echo htmlspecialchars($customer['firstName']); ?></span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Last Name:</span>
            <span class="detail-value"><?php echo htmlspecialchars($customer['lastName'] ?? 'N/A'); ?></span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Email:</span>
            <span class="detail-value"><?php echo htmlspecialchars($customer['AccEmail']); ?></span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Phone:</span>
            <span class="detail-value"><?php echo htmlspecialchars($customer['phone'] ?? 'N/A'); ?></span>
        </div>
    </div>
</div>

<div class="customer-detail-section">
    <h3><i class="fas fa-map-marker-alt"></i> Location Information</h3>
    <div class="detail-grid">
        <div class="detail-item">
            <span class="detail-label">Address:</span>
            <span class="detail-value"><?php echo htmlspecialchars($customer['address'] ?? 'N/A'); ?></span>
        </div>
        <div class="detail-item">
            <span class="detail-label">City:</span>
            <span class="detail-value"><?php echo htmlspecialchars($customer['city'] ?? 'N/A'); ?></span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Postal Code:</span>
            <span class="detail-value"><?php echo htmlspecialchars($customer['postal_code'] ?? 'N/A'); ?></span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Coordinates:</span>
            <span class="detail-value">
                <?php 
                if($customer['latitude'] && $customer['longitude']) {
                    echo number_format($customer['latitude'], 6) . ', ' . number_format($customer['longitude'], 6);
                } else {
                    echo 'N/A';
                }
                ?>
            </span>
        </div>
    </div>
</div>

<div class="customer-detail-section">
    <h3><i class="fas fa-chart-bar"></i> Order Statistics</h3>
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?php echo $customer['total_orders']; ?></div>
            <div class="stat-label">Total Orders</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">Rs. <?php echo number_format($customer['total_spent'], 2); ?></div>
            <div class="stat-label">Total Spent</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">
                <?php 
                if($customer['last_order_date']) {
                    echo date('M j', strtotime($customer['last_order_date']));
                } else {
                    echo 'Never';
                }
                ?>
            </div>
            <div class="stat-label">Last Order</div>
        </div>
    </div>
</div>

<?php if($ordersResult->num_rows > 0): ?>
<div class="customer-detail-section">
    <h3><i class="fas fa-shopping-bag"></i> Recent Orders</h3>
    <table class="orders-table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Date</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Payment</th>
            </tr>
        </thead>
        <tbody>
            <?php while($order = $ordersResult->fetch_assoc()): ?>
            <tr>
                <td>#<?php echo $order['orderID']; ?></td>
                <td><?php echo date('M j, Y', strtotime($order['order_date'])); ?></td>
                <td>Rs. <?php echo number_format($order['total_amount'], 2); ?></td>
                <td>
                    <span class="status-badge status-<?php echo $order['order_status']; ?>">
                        <?php echo ucfirst($order['order_status']); ?>
                    </span>
                </td>
                <td>
                    <span class="status-badge status-<?php echo $order['payment_status']; ?>">
                        <?php echo ucfirst($order['payment_status']); ?>
                    </span>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<div class="customer-detail-section">
    <h3><i class="fas fa-shopping-bag"></i> Order History</h3>
    <p style="text-align: center; color: #666; padding: 20px;">
        <i class="fas fa-inbox" style="font-size: 2rem; display: block; margin-bottom: 10px; color: #ddd;"></i>
        This customer hasn't placed any orders yet.
    </p>
</div>
<?php endif; ?>

<?php mysqli_close($con); ?>