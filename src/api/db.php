<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "stocks";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS stocks";
$conn->query($sql);

// Select the database
$conn->select_db($dbname);

// Create Branches table
$sql = "CREATE TABLE IF NOT EXISTS branches (
    branch_id INT AUTO_INCREMENT PRIMARY KEY,
    branch_name VARCHAR(100) NOT NULL,
    location TEXT,
    contact_number VARCHAR(15),
    email VARCHAR(100),
    manager_name VARCHAR(100)
)";
$conn->query($sql);

// Create Products table
$sql = "CREATE TABLE IF NOT EXISTS products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(100) NOT NULL,
    description TEXT,
    base_barcode VARCHAR(50) UNIQUE
)";
$conn->query($sql);

// Create Inventory table (added status and notes columns)
$sql = "CREATE TABLE IF NOT EXISTS inventory (
    inventory_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    branch_id INT,
    quantity INT NOT NULL CHECK (quantity >= 0),
    current_barcode VARCHAR(50) UNIQUE NOT NULL,
    status VARCHAR(20) DEFAULT 'active',
    notes TEXT,
    FOREIGN KEY (product_id) REFERENCES Products (product_id) ON DELETE CASCADE,
    FOREIGN KEY (branch_id) REFERENCES Branches (branch_id) ON DELETE CASCADE
)";
$conn->query($sql);

// Create Transfers table
$sql = "CREATE TABLE IF NOT EXISTS transfers (
    transfer_id INT AUTO_INCREMENT PRIMARY KEY,
    from_branch_id INT,
    to_branch_id INT,
    product_id INT,
    quantity INT NOT NULL CHECK (quantity > 0),
    transfer_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    barcode_changed BOOLEAN NOT NULL,
    old_barcode VARCHAR(50),
    new_barcode VARCHAR(50),
    transfer_reason VARCHAR(100),
    transfer_status VARCHAR(20) DEFAULT 'completed',
    transfer_notes TEXT,
    FOREIGN KEY (from_branch_id) REFERENCES Branches (branch_id) ON DELETE SET NULL,
    FOREIGN KEY (to_branch_id) REFERENCES Branches (branch_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES Products (product_id) ON DELETE CASCADE
)";
$conn->query($sql);
?>