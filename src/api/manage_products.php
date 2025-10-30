<?php
require_once 'db.php';
header('Content-Type: application/json');

/**
 * =============================
 *   FUNCTION DEFINITIONS
 * =============================
 */

function addProduct($product_name, $description, $base_barcode) {
    global $conn;

    // Insert into products table
    $stmt = $conn->prepare("INSERT INTO products (product_name, description, base_barcode) VALUES (?, ?, ?)");
    if (!$stmt) return "Product insert failed: " . $conn->error;

    $stmt->bind_param("sss", $product_name, $description, $base_barcode);
    if ($stmt->execute()) {
        $product_id = $conn->insert_id;

        // Add to default branch (branch_id = 1)
        $branch_id = 1;
        $stmt2 = $conn->prepare("INSERT INTO inventory (product_id, branch_id, quantity, current_barcode) VALUES (?, ?, 0, ?)");
        if (!$stmt2) return "Inventory insert failed: " . $conn->error;

        $stmt2->bind_param("iis", $product_id, $branch_id, $base_barcode);
        if ($stmt2->execute()) {
            return true;
        } else {
            return "Inventory insert failed: " . $conn->error;
        }
    }

    return "Product insert failed: " . $conn->error;
}

function getProducts() {
    global $conn;
    $res = $conn->query("SELECT * FROM products");
    return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}

function getInventoryQuantity($from_branch_id, $product_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT quantity FROM inventory WHERE product_id = ? AND branch_id = ?");
    $stmt->bind_param("ii", $product_id, $from_branch_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    return $res ? (int)$res['quantity'] : 0;
}

/**
 * =============================
 *   API HANDLER SECTION
 * =============================
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitize incoming POST keys
    $clean_post = [];
    foreach ($_POST as $key => $value) {
        $clean_post[trim($key)] = trim($value);
    }

    $action = $clean_post['action'] ?? '';

    switch ($action) {

        /**
         * ➕ ADD PRODUCT
         */
        case 'add_product':
            $product_name = $clean_post['product_name'] ?? '';
            $description  = $clean_post['description'] ?? '';
            $base_barcode = $clean_post['base_barcode'] ?? '';

            if (!empty($product_name) && !empty($base_barcode)) {
                $result = addProduct($product_name, $description, $base_barcode);

                if ($result === true) {
                    echo json_encode(['success' => true, 'message' => 'Product added successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => $result]);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Product name and barcode are required']);
            }
            break;

        /**
         * 📦 GET ALL PRODUCTS
         */
        case 'get_products':
            $products = getProducts();
            echo json_encode(['success' => true, 'data' => $products]);
            break;

        /**
         * 🔍 CHECK INVENTORY QUANTITY
         */
        case 'check_quantity':
            $from_branch_id = $clean_post['from_branch_id'] ?? null;
            $product_id     = $clean_post['product_id'] ?? null;

            if (!$from_branch_id || !$product_id) {
                echo json_encode(['success' => false, 'message' => 'Missing parameters']);
                exit;
            }

            $quantity = getInventoryQuantity($from_branch_id, $product_id);
            echo json_encode([
                'success' => true,
                'from_branch_id' => (int)$from_branch_id,
                'product_id' => (int)$product_id,
                'quantity' => $quantity
            ]);
            break;

        /**
         * ❌ INVALID ACTION
         */
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid or missing action']);
            break;
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Only POST method allowed']);
}
?>
