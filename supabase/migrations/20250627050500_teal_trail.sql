/*
  # Enhanced Database Schema for Shopping Cart and Location Features

  1. New Tables
    - `cart` - Shopping cart items for each customer
    - `orders` - Customer orders with delivery information
    - `order_items` - Individual items in each order
    - Enhanced `customer` table with location fields

  2. Location Features
    - Customer address with coordinates
    - Distance calculation for delivery charges
    - Delivery zone management

  3. Order Management
    - Complete order tracking
    - Payment status
    - Delivery status
*/

-- Add location fields to customer table
ALTER TABLE customer 
ADD COLUMN latitude DECIMAL(10, 8) DEFAULT NULL,
ADD COLUMN longitude DECIMAL(11, 8) DEFAULT NULL,
ADD COLUMN postal_code VARCHAR(10) DEFAULT NULL,
ADD COLUMN city VARCHAR(50) DEFAULT 'Jaffna';

-- Create cart table
CREATE TABLE IF NOT EXISTS cart (
    cartID int NOT NULL AUTO_INCREMENT,
    customerID int NOT NULL,
    productID int NOT NULL,
    quantity int NOT NULL DEFAULT 1,
    added_date timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (cartID),
    FOREIGN KEY (customerID) REFERENCES customer(customerID) ON DELETE CASCADE,
    FOREIGN KEY (productID) REFERENCES products(productID) ON DELETE CASCADE,
    UNIQUE KEY unique_cart_item (customerID, productID)
);

-- Create orders table
CREATE TABLE IF NOT EXISTS orders (
    orderID int NOT NULL AUTO_INCREMENT,
    customerID int NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    delivery_charge DECIMAL(10, 2) DEFAULT 0.00,
    delivery_distance DECIMAL(5, 2) DEFAULT 0.00,
    delivery_address TEXT NOT NULL,
    delivery_latitude DECIMAL(10, 8) DEFAULT NULL,
    delivery_longitude DECIMAL(11, 8) DEFAULT NULL,
    order_status ENUM('pending', 'confirmed', 'preparing', 'out_for_delivery', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    payment_method ENUM('cash_on_delivery', 'card', 'bank_transfer') DEFAULT 'cash_on_delivery',
    order_date timestamp DEFAULT CURRENT_TIMESTAMP,
    delivery_date datetime DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    PRIMARY KEY (orderID),
    FOREIGN KEY (customerID) REFERENCES customer(customerID) ON DELETE CASCADE
);

-- Create order_items table
CREATE TABLE IF NOT EXISTS order_items (
    order_item_id int NOT NULL AUTO_INCREMENT,
    orderID int NOT NULL,
    productID int NOT NULL,
    quantity int NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    PRIMARY KEY (order_item_id),
    FOREIGN KEY (orderID) REFERENCES orders(orderID) ON DELETE CASCADE,
    FOREIGN KEY (productID) REFERENCES products(productID) ON DELETE CASCADE
);

-- Insert some sample products if they don't exist
INSERT IGNORE INTO products (productName, unit, price, category, imgName) VALUES
('Fresh Tomatoes', '500g', 150.00, 'Vegetables', 'Tomatoes500g.jpg'),
('Green Beans', '250g', 120.00, 'Vegetables', 'beans250g.jpg'),
('Carrots', '500g', 100.00, 'Vegetables', 'carrots500g.jpg'),
('Fresh Spinach', '250g', 80.00, 'Vegetables', 'spinach250g.jpg'),
('Red Onions', '1kg', 200.00, 'Vegetables', 'onions1kg.jpg'),
('Fresh Apples', '1kg', 350.00, 'Fruits', 'Apple1.jpg'),
('Bananas', '500g', 180.00, 'Fruits', 'Banana500g.jpg'),
('Fresh Grapes', '500g', 450.00, 'Fruits', 'Grapes100g.jpg'),
('Pineapple', '1 piece', 250.00, 'Fruits', 'Pineapple1.jpeg'),
('Fresh Milk', '1L', 180.00, 'Dairy Products', 'milk1L.jpg'),
('Cheese Slices', '200g', 320.00, 'Dairy Products', 'Cheese300g pack.jpg'),
('Greek Yogurt', '150g', 150.00, 'Dairy Products', 'yogurt150g.jpg'),
('Coca Cola', '2L', 280.00, 'Beverages', 'CocaCola2L.jpg'),
('7UP', '2L', 260.00, 'Beverages', '7up2L.jpg'),
('Orange Juice', '1L', 220.00, 'Beverages', 'orange_juice1L.jpg'),
('Potato Chips', '150g', 120.00, 'Snacks', 'chips150g.jpg'),
('Chocolate Cookies', '200g', 180.00, 'Snacks', 'cookies200g.jpg'),
('Mixed Nuts', '100g', 250.00, 'Snacks', 'nuts100g.jpg');