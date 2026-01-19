<?php
// check_biodata.php — VERSI AMAN & SELALU JALAN
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['biodata_lengkap' => false]);
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT full_name, whatsapp, address, city, province, postalcode FROM user_biodata WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$biodata = $result->fetch_assoc();
$stmt->close();

$lengkap = $biodata && 
           !empty($biodata['whatsapp']) && 
           !empty($biodata['address']) && 
           !empty($biodata['city']) && 
           !empty($biodata['province']) && 
           !empty($biodata['postalcode']);

echo json_encode([
    'biodata_lengkap' => $lengkap,
    'biodata' => $lengkap ? [
        'full_name' => $biodata['full_name'] ?? $_SESSION['name'] ?? 'User',
        'whatsapp'  => $biodata['whatsapp'],
        'address'   => $biodata['address'],
        'city'      => $biodata['city'],
        'province'  => $biodata['province'],
        'postalcode'=> $biodata['postalcode']
    ] : null
]);
?>