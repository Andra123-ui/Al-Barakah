<?php
require_once 'config.php';

// Ambil JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!empty($data['order_id'])) {
    $order_id = $data['order_id'];

    // Langsung update status jadi "processing"
    $stmt = $conn->prepare("UPDATE pesanan SET status = 'processing' WHERE order_id = ?");
    $stmt->bind_param("s", $order_id);
    $stmt->execute();
    $stmt->close();

    // Event SSE
    $event = ['order_id' => $order_id, 'status' => 'processing'];
file_put_contents(__DIR__.'/webhook_events.json', json_encode($event)."\n", FILE_APPEND | LOCK_EX);

}

echo "OK";
