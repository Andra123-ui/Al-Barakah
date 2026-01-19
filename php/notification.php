<?php
// notification.php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo "Webhook active";
    exit;
}

$SERVER_KEY = "Mid-server-g5a0DI75ttD83Z6BA4HDtEz3"; // ganti dengan server key sandbox kamu

$json = file_get_contents("php://input");
file_put_contents("midtrans_log.txt", date('Y-m-d H:i:s') . " => " . $json . "\n", FILE_APPEND);

$data = json_decode($json, true);
if (!$data || !isset($data['order_id'])) {
    http_response_code(400);
    echo "Invalid callback";
    exit;
}

$order_id = $data['order_id'];
$status_code = $data['status_code'] ?? '';
$gross_amount = $data['gross_amount'] ?? '';
$signature_key = $data['signature_key'] ?? '';

// validasi signature
$computed = hash('sha512', $order_id . $status_code . $gross_amount . $SERVER_KEY);
if ($signature_key !== $computed) {
    http_response_code(401);
    echo "Invalid Signature";
    exit;
}

// logika status
$status = $data['transaction_status'] ?? '';
$fraud = $data['fraud_status'] ?? null;

switch ($status) {
    case "capture":
        $new_status = ($fraud === "challenge") ? "pending" : "processing";
        break;
    case "settlement":
        $new_status = "processing";
        break;
    case "pending":
        $new_status = "pending";
        break;
    case "deny":
    case "cancel":
    case "expire":
        $new_status = "failed";
        break;
    default:
        $new_status = "pending";
        break;
}

// update DB
$stmt = $conn->prepare("UPDATE pesanan SET status = ? WHERE order_id = ?");
$stmt->bind_param("ss", $new_status, $order_id);
$stmt->execute();
$stmt->close();

echo "OK";
