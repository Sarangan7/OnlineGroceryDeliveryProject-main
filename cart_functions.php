<?php
include('config.php');

// Function to add item to cart
function addToCart($customerID, $productID, $quantity = 1) {
    global $con;
    
    // Check if item already exists in cart
    $checkQuery = "SELECT * FROM cart WHERE customerID = ? AND productID = ?";
    $stmt = $con->prepare($checkQuery);
    $stmt->bind_param("ii", $customerID, $productID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update quantity if item exists
        $updateQuery = "UPDATE cart SET quantity = quantity + ? WHERE customerID = ? AND productID = ?";
        $stmt = $con->prepare($updateQuery);
        $stmt->bind_param("iii", $quantity, $customerID, $productID);
        return $stmt->execute();
    } else {
        // Insert new item
        $insertQuery = "INSERT INTO cart (customerID, productID, quantity) VALUES (?, ?, ?)";
        $stmt = $con->prepare($insertQuery);
        $stmt->bind_param("iii", $customerID, $productID, $quantity);
        return $stmt->execute();
    }
}

// Function to get cart items for a customer
function getCartItems($customerID) {
    global $con;
    
    $query = "SELECT c.*, p.productName, p.unit, p.price, p.imgName, p.category,
              (c.quantity * p.price) as total_price
              FROM cart c 
              JOIN products p ON c.productID = p.productID 
              WHERE c.customerID = ? 
              ORDER BY c.added_date DESC";
    
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $customerID);
    $stmt->execute();
    return $stmt->get_result();
}

// Function to get cart total
function getCartTotal($customerID) {
    global $con;
    
    $query = "SELECT SUM(c.quantity * p.price) as total 
              FROM cart c 
              JOIN products p ON c.productID = p.productID 
              WHERE c.customerID = ?";
    
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $customerID);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total'] ?? 0;
}

// Function to update cart item quantity
function updateCartQuantity($customerID, $productID, $quantity) {
    global $con;
    
    if ($quantity <= 0) {
        return removeFromCart($customerID, $productID);
    }
    
    $query = "UPDATE cart SET quantity = ? WHERE customerID = ? AND productID = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("iii", $quantity, $customerID, $productID);
    return $stmt->execute();
}

// Function to remove item from cart
function removeFromCart($customerID, $productID) {
    global $con;
    
    $query = "DELETE FROM cart WHERE customerID = ? AND productID = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("ii", $customerID, $productID);
    return $stmt->execute();
}

// Function to clear entire cart
function clearCart($customerID) {
    global $con;
    
    $query = "DELETE FROM cart WHERE customerID = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $customerID);
    return $stmt->execute();
}

// Function to calculate delivery charge based on distance
function calculateDeliveryCharge($distance) {
    // Free delivery within 3km from Jaffna town
    if ($distance <= 3) {
        return 0;
    }
    
    // Rs. 7 per km beyond 3km
    $extraDistance = $distance - 3;
    return $extraDistance * 7;
}

// Function to calculate distance between two coordinates (Haversine formula)
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371; // Earth's radius in kilometers
    
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    
    $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    
    return $earthRadius * $c;
}

// Jaffna town center coordinates (approximate)
define('JAFFNA_LAT', 9.6615);
define('JAFFNA_LON', 80.0255);

// Function to get delivery charge for customer location
function getDeliveryChargeForCustomer($customerID) {
    global $con;
    
    $query = "SELECT latitude, longitude FROM customer WHERE customerID = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $customerID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $customer = $result->fetch_assoc();
        if ($customer['latitude'] && $customer['longitude']) {
            $distance = calculateDistance(JAFFNA_LAT, JAFFNA_LON, $customer['latitude'], $customer['longitude']);
            return [
                'distance' => round($distance, 2),
                'charge' => calculateDeliveryCharge($distance)
            ];
        }
    }
    
    return ['distance' => 0, 'charge' => 0];
}
?>