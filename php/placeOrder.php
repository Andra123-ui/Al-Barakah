<?php
// placeOrder.php — Kirim alamat + item detail ke Midtrans
ob_start();
header('Content-Type: application/json');

try {
    session_start();
    require_once 'config.php';

    if (!isset($_POST['order_id'])) {
        throw new Exception('Data pesanan tidak ditemukan');
    }

    $order_id = $_POST['order_id'];

    // Cek apakah pesanan ada dan masih pending
    $check = $conn->prepare("SELECT * FROM pesanan WHERE order_id = ?");
    $check->bind_param("s", $order_id);
    $check->execute();
    $order = $check->get_result()->fetch_assoc();
    $check->close();

    if (!$order) throw new Exception('Pesanan tidak ditemukan');
    if ($order['status'] !== 'pending') throw new Exception('Pesanan sudah diproses sebelumnya');



    // Ambil biodata user
    $bio_stmt = $conn->prepare("SELECT full_name, whatsapp, email, address, city, province, postalcode 
                                FROM user_biodata WHERE user_id = ?");
    $bio_stmt->bind_param("i", $order['user_id']);
    $bio_stmt->execute();
    $bio = $bio_stmt->get_result()->fetch_assoc();
    $bio_stmt->close();

    // Data customer
    $customer_name = $bio['full_name'] ?? $order['nama'];
    $phone         = $bio['whatsapp'] ?? $order['whatsapp'];
    $email_raw     = $bio['email'] ?? $order['email'];
    $email         = filter_var($email_raw, FILTER_VALIDATE_EMAIL) ? $email_raw : "customer_{$order_id}@multo-toko.com";

    // Alamat customer
    $billing_address = [
        "first_name"   => $customer_name,
        "phone"        => $phone,
        "address"      => $bio['address']   ?? '',
        "city"         => $bio['city']      ?? '',
        "postal_code"  => $bio['postalcode'] ?? '',
        "country_code" => "IDN"
    ];

    $shipping_address = $billing_address;

    // PRODUK (keranjang)
    $cart_items = json_decode($order['produk'], true);

    $item_details = [];
    foreach ($cart_items as $item) {
        $item_details[] = [
            "id"       => $item['id'],
            "price"    => (int)$item['price'],
            "quantity" => (int)$item['quantity'],
            "name"     => $item['name']
        ];
    }

    // Payload Midtrans
    $payload = [
        "transaction_details" => [
            "order_id"     => $order_id,
            "gross_amount" => (int)$order['total']
        ],
        "customer_details" => [
            "first_name"       => $customer_name,
            "email"            => $email,
            "phone"            => $phone,
            "billing_address"  => $billing_address,
            "shipping_address" => $shipping_address
        ],
        "item_details" => $item_details,
        "enabled_payments" => [
            "gopay", "shopeepay", "bca_va", "bni_va",
            "bri_va", "qris", "indomaret", "alfamart"
        ]
    ];

    // Kirim ke Midtrans
    $server_key = "Mid-server-g5a0DI75ttD83Z6BA4HDtEz3";

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => "https://app.sandbox.midtrans.com/snap/v1/transactions",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => [
            "Content-Type: application/json",
            "Accept: application/json",
            "Authorization: Basic " . base64_encode($server_key . ":")
        ]
    ]);

    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpcode !== 201) {
        $err = json_decode($response, true);
        throw new Exception($err['error_messages'][0] ?? 'Gagal koneksi ke Midtrans');
    }

    $json = json_decode($response, true);

    ob_end_clean();
    echo json_encode([
        'success'    => true,
        'snap_token' => $json['token'],
        'order_id'   => $order_id
    ]);

} catch (Throwable $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
exit;
?>
