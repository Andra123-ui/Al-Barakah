<?php
// get_testimonials.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$host = "localhost";
$user = "root";
$password = "";
$database = "users_db";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed']));
}

// Ambil hanya testimoni yang sudah diapprove
$sql = "SELECT customer_name, location, message, rating, created_at 
        FROM testimonials 
        WHERE is_approved = 1 
        ORDER BY created_at DESC 
        LIMIT 10";

$result = $conn->query($sql);

$testimonials = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $testimonials[] = [
            'customer_name' => htmlspecialchars($row['customer_name']),
            'location' => $row['location'] ? htmlspecialchars($row['location']) : '',
            'message' => htmlspecialchars($row['message']),
            'rating' => (int)$row['rating'],
            'created_at' => $row['created_at']
        ];
    }
}

echo json_encode([
    'success' => true,
    'testimonials' => $testimonials
]);

$conn->close();
?>