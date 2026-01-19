<?php
session_start();
include 'config.php';

//admin_actions.php

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['action'])) {
    echo json_encode(['success' => false, 'error' => 'No action specified']);
    exit();
}

$action = $data['action'];
$id = $data['id'] ?? null;

switch ($action) {

    // ============================ DELETE USER ============================
    case 'delete_user':
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'No ID provided']);
            break;
        }
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        echo json_encode(['success' => $stmt->execute()]);
        break;


    // ============================ DELETE ORDER ============================
    case 'delete_order':
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'No ID provided']);
            break;
        }
        $stmt = $conn->prepare("DELETE FROM pesanan WHERE id = ?");
        $stmt->bind_param("i", $id);
        echo json_encode(['success' => $stmt->execute()]);
        break;


    // ============================ VIEW ORDER ============================
    case 'view_order':
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'No ID provided']);
            break;
        }
        $stmt = $conn->prepare("SELECT * FROM pesanan WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();
        echo ($result->num_rows > 0)
            ? json_encode(['success' => true, 'order' => $result->fetch_assoc()])
            : json_encode(['success' => false, 'error' => 'Order not found']);
        break;


    // ============================ APPROVE TESTIMONIAL ============================
    case 'approve_testimonial':
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'No ID provided']);
            break;
        }

        $stmt = $conn->prepare("UPDATE testimonials SET is_approved = 1 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $stmt = $conn->prepare("SELECT * FROM testimonials WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        echo json_encode(['success' => true, 'testimonial' => $stmt->get_result()->fetch_assoc()]);
        break;


    // ============================ REJECT TESTIMONIAL ============================
    case 'reject_testimonial':
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'No ID provided']);
            break;
        }

        $stmt = $conn->prepare("UPDATE testimonials SET is_approved = -1 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $stmt = $conn->prepare("SELECT * FROM testimonials WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        echo json_encode(['success' => true, 'testimonial' => $stmt->get_result()->fetch_assoc()]);
        break;


    // ============================ DELETE TESTIMONIAL ============================
    case 'delete_testimonial':
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'No ID provided']);
            break;
        }
        $stmt = $conn->prepare("DELETE FROM testimonials WHERE id = ?");
        $stmt->bind_param("i", $id);
        echo json_encode(['success' => $stmt->execute()]);
        break;


    // ============================ UPDATE ORDER STATUS ============================
    case 'update_order_status':
        if (!$id || !isset($data['status']) || !in_array($data['status'], ['pending','processing','shipped','completed'])) {
            echo json_encode(['success' => false, 'error' => 'Invalid data']);
            break;
        }

        $stmt = $conn->prepare("UPDATE pesanan SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $data['status'], $id);
        echo json_encode(['success' => $stmt->execute(), 'new_status' => $data['status']]);
        break;


    // ============================ UPDATE PRODUCT STOCK ============================
case 'update_product_stock':
    if (!$id || !isset($data['stock']) || !is_numeric($data['stock']) || $data['stock'] < 0) {
        echo json_encode(['success' => false, 'error' => 'Stok tidak valid']);
        break;
    }

    $newStock = intval($data['stock']);
    $stmt = $conn->prepare("UPDATE products SET stock = ? WHERE id = ?");
    $stmt->bind_param("ii", $newStock, $id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'new_stock' => $newStock
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Gagal update database: ' . $stmt->error
        ]);
    }
    $stmt->close();
    break;


    // ============================ DELETE PRODUCT ============================
    case 'delete_product':
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'No ID provided']);
            break;
        }
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        echo json_encode(['success' => $stmt->execute()]);
        break;


    // ============================ INVALID ============================
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}

$conn->close();
?>
