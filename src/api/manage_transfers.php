<?php
require_once 'db.php';
header('Content-Type: application/json');
// FUNCTION DECLARATIONS
function transferProduct($from_branch_id, $to_branch_id, $product_id, $quantity){
    global $conn;
    $conn->begin_transaction();
    try{
        $stmt = $conn->prepare("SELECT quantity, current_barcode FROM inventory WHERE product_id = ? AND branch_id = ?");
        $stmt->bind_param("ii", $product_id, $from_branch_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        if(!$res || $res['quantity'] < $quantity) throw new Exception("Insufficient quantity");
        $current_qty = $res['quantity'];
        $old_barcode = $res['current_barcode'];
        $barcode_changed = false;
        $new_barcode = $old_barcode;
        if($quantity != $current_qty){
            $new_barcode .= '_' . time();
            $barcode_changed = true;
        }
        // Update source branch inventory
        $stmt = $conn->prepare("UPDATE inventory SET quantity = ? WHERE product_id = ? AND branch_id = ?");
        $new_qty = $current_qty - $quantity;
        $stmt->bind_param("iii", $new_qty, $product_id, $from_branch_id);
        $stmt->execute();
        // Update or insert destination branch inventory
        $stmt = $conn->prepare("INSERT INTO inventory(product_id, branch_id, quantity, current_barcode)
            VALUES(?,?,?,?) ON DUPLICATE KEY UPDATE quantity = quantity + ?, current_barcode = ?");
        $stmt->bind_param("iiisis", $product_id, $to_branch_id, $quantity, $new_barcode, $quantity, $new_barcode);
        $stmt->execute();
        // Record the transfer
        $stmt = $conn->prepare("INSERT INTO transfers(from_branch_id, to_branch_id, product_id, quantity, barcode_changed, old_barcode, new_barcode, transfer_reason, transfer_status)
            VALUES(?,?,?,?,?,?,?,?,?)");
        $barcode_changed_int = $barcode_changed ? 1 : 0;
        $transfer_reason = "Stock transfer";
        $transfer_status = "completed";
        $stmt->bind_param("iiiisssss", $from_branch_id, $to_branch_id, $product_id, $quantity, $barcode_changed_int, $old_barcode, $new_barcode, $transfer_reason, $transfer_status);
        $stmt->execute();
        $conn->commit();
        return true;
    } catch(Exception $e) {
        $conn->rollback();
        return $e->getMessage();
    }
}
function getTransfers(){
    global $conn;
    $res = $conn->query("SELECT t.*, p.product_name, fb.branch_name AS from_branch, tb.branch_name AS to_branch
        FROM transfers t
        JOIN products p ON t.product_id = p.product_id
        LEFT JOIN branches fb ON t.from_branch_id = fb.branch_id
        JOIN branches tb ON t.to_branch_id = tb.branch_id
        ORDER BY t.transfer_date DESC");
    return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}
// MAIN REQUEST HANDLER
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    switch($action) {
        case 'transfer_product':
            $from_branch_id = $_POST['from_branch_id'] ?? '';
            $to_branch_id = $_POST['to_branch_id'] ?? '';
            $product_id = $_POST['product_id'] ?? '';
            $quantity = $_POST['quantity'] ?? '';
            if (empty($from_branch_id) || empty($to_branch_id) || empty($product_id) || empty($quantity)) {
                echo json_encode(['success' => false, 'message' => 'All fields are required: from_branch_id, to_branch_id, product_id, quantity']);
                exit;
            }
            if (!is_numeric($quantity) || $quantity <= 0) {
                echo json_encode(['success' => false, 'message' => 'Quantity must be a positive number']);
                exit;
            }
            $result = transferProduct($from_branch_id, $to_branch_id, $product_id, $quantity);
            if ($result === true) {
                echo json_encode(['success' => true, 'message' => 'Product transferred successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => $result]);
            }
            break;
        case 'get_transfers':
            $transfers = getTransfers();
            echo json_encode(['success' => true, 'data' => $transfers]);
            break;
        default:
            // Handle direct transfer without action parameter (your second file functionality)
            $from_branch_id = $_POST['from_branch_id'] ?? null;
            $to_branch_id = $_POST['to_branch_id'] ?? null;
            $product_id = $_POST['product_id'] ?? null;
            $quantity = $_POST['quantity'] ?? null;
            // Validate required parameters
            if (!$from_branch_id || !$to_branch_id || !$product_id || !$quantity) {
                echo json_encode(['success' => false, 'message' => 'Missing parameters']);
                exit;
            }
            // Validate quantity is numeric and positive
            if (!is_numeric($quantity) || $quantity <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid quantity']);
                exit;
            }
            try {
                $result = transferProduct($from_branch_id, $to_branch_id, $product_id, $quantity);
                // Handle different return types from transferProduct function
                if ($result === true) {
                    echo json_encode(['success' => true, 'message' => 'Transfer successful']);
                } elseif ($result === false) {
                    echo json_encode(['success' => false, 'message' => 'Transfer failed']);
                } elseif (is_string($result)) {
                    // If it returns an error message string
                    echo json_encode(['success' => false, 'message' => $result]);
                } else {
                    // For any other case, assume success
                    echo json_encode(['success' => true, 'message' => 'Transfer completed']);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
            }
            break;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Handle GET request for transfers (your third file functionality)
    $transfers = getTransfers();
    echo json_encode([
        'success' => true,
        'data' => $transfers
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Only POST and GET methods allowed']);
}
?>
