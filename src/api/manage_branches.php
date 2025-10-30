<?php
require_once 'db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch($action) {
        case 'add_branch':
            $branch_name = $_POST['branch_name'] ?? '';
            $location = $_POST['location'] ?? '';
            $contact_number = $_POST['contact_number'] ?? '';
            $email = $_POST['email'] ?? '';
            $manager_name = $_POST['manager_name'] ?? '';
            
            if (empty($branch_name) || empty($location)) {
                echo json_encode(['success' => false, 'message' => 'Branch name and location are required']);
                exit;
            }
            
            $result = addBranch($branch_name, $location, $contact_number, $email, $manager_name);
            
            if ($result === true) {
                echo json_encode(['success' => true, 'message' => 'Branch added successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to add branch']);
            }
            break;
            
        case 'get_branches':
            $branches = getBranches();
            echo json_encode(['success' => true, 'data' => $branches]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Only POST method allowed']);
}

function addBranch($branch_name, $location, $contact_number = '', $email = '', $manager_name = '') {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO branches(branch_name, location, contact_number, email, manager_name) VALUES(?,?,?,?,?)");
    if(!$stmt) return false;
    $stmt->bind_param("sssss", $branch_name, $location, $contact_number, $email, $manager_name);
    return $stmt->execute();
}

function getBranches() {
    global $conn;
    $res = $conn->query("SELECT * FROM branches");
    return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}
?>