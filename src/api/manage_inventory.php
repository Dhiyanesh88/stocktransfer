<?php
require_once 'db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch($action) {
        case 'update_inventory':
            $product_id = $_POST['product_id'] ?? '';
            $branch_id = $_POST['branch_id'] ?? '';
            $quantity = $_POST['quantity'] ?? '';
            $current_barcode = $_POST['current_barcode'] ?? '';
            $status = $_POST['status'] ?? 'active';
            $notes = $_POST['notes'] ?? '';
            
            if (empty($product_id) || empty($branch_id) || empty($quantity) || empty($current_barcode)) {
                echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
                exit;
            }
            
            $result = updateInventory($product_id, $branch_id, $quantity, $current_barcode, $status, $notes);
            
            if ($result === true) {
                echo json_encode(['success' => true, 'message' => 'Inventory updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => $result]);
            }
            break;
            
        case 'get_inventory':
            $branch_id = $_POST['branch_id'] ?? null;
            $inventory = getInventory($branch_id);
            echo json_encode(['success' => true, 'data' => $inventory]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Only POST method allowed']);
}

function updateInventory($product_id, $branch_id, $quantity, $current_barcode, $status = 'active', $notes = ''){
    global $conn;
    $stmt = $conn->prepare("INSERT INTO Inventory(product_id, branch_id, quantity, current_barcode, status, notes)
        VALUES(?,?,?,?,?,?) ON DUPLICATE KEY UPDATE quantity=?, current_barcode=?, status=?, notes=?");
    if(!$stmt) return "Prepare failed: ".$conn->error;
    $stmt->bind_param("iiisssisss", $product_id, $branch_id, $quantity, $current_barcode, $status, $notes,
        $quantity, $current_barcode, $status, $notes);
    return $stmt->execute() ? true : $stmt->error;
}

function getInventory($branch_id = null){
    global $conn;
    $sql = "SELECT i.*, p.product_name, b.branch_name FROM Inventory i 
            JOIN Products p ON i.product_id = p.id
            JOIN Branches b ON i.branch_id = b.id";
    if($branch_id){
        $stmt = $conn->prepare($sql." WHERE i.branch_id = ?");
        $stmt->bind_param("i", $branch_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    $res = $conn->query($sql);
    return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}
?>