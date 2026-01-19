<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

require_once 'config.php';

$customer_name = trim($_POST['customer_name'] ?? '');
$location      = trim($_POST['location'] ?? '');
$message       = trim($_POST['message'] ?? '');
$rating        = (int)($_POST['rating'] ?? 5);
$order_id      = (int)($_POST['order_id'] ?? 0);  // Ambil order_id

if (empty($customer_name) || empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Nama dan testimoni wajib diisi!']);
    exit;
}

// Simpan testimoni + order_id
$stmt = $conn->prepare("INSERT INTO testimonials 
    (order_id, customer_name, location, message, rating, is_approved, created_at) 
    VALUES (?, ?, ?, ?, ?, 0, NOW())");
$stmt->bind_param("isssi", $order_id, $customer_name, $location, $message, $rating);

if ($stmt->execute()) {
    // Update status testimonial_sent di tabel pesanan
    if ($order_id > 0) {
        $update = $conn->prepare("UPDATE pesanan SET testimonial_sent = 1 WHERE id = ?");
        $update->bind_param("i", $order_id);
        $update->execute();
        $update->close();
    }

    echo json_encode([
        'success' => true,
        'message' => 'Alhamdulillah, testimoni Anda berhasil dikirim! ❤️'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal menyimpan testimoni.'
    ]);
}

$stmt->close();
$conn->close();
exit;
?>