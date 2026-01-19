<?php
// submit_order.php → VERSI FINAL + STOK BERKURANG OTOMATIS
ob_start();
header('Content-Type: application/json');

try {
    session_start();
    require_once 'config.php';

    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Silakan login terlebih dahulu');
    }

    $cart_json = $_POST['cart'] ?? '';
    if (empty($cart_json)) throw new Exception('Keranjang kosong');

    $cart = json_decode($cart_json, true);
    if (!is_array($cart) || empty($cart)) throw new Exception('Format keranjang salah');

    // Hitung total & validasi stok
    $total = 0;
    foreach ($cart as $item) {
        $id       = (int)($item['id'] ?? 0);
        $quantity = (int)($item['quantity'] ?? 0);
        $price    = (int)($item['price'] ?? 0);

        if ($id <= 0 || $quantity <= 0) continue;

        $total += $price * $quantity;

        // Cek stok cukup?
        $check = $conn->prepare("SELECT stock FROM products WHERE id = ?");
        $check->bind_param("i", $id);
        $check->execute();
        $stock = $check->get_result()->fetch_assoc()['stock'] ?? 0;
        $check->close();

        if ($stock < $quantity) {
            throw new Exception("Stok {$item['name']} tidak cukup! Tersedia: $stock");
        }
    }

    // MULAI TRANSAKSI — AMAN KALAU ADA ERROR
    $conn->begin_transaction();

    // KURANGI STOK
    foreach ($cart as $item) {
        $id       = (int)($item['id'] ?? 0);
        $quantity = (int)($item['quantity'] ?? 0);
        if ($id > 0 && $quantity > 0) {
            $update = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            $update->bind_param("ii", $quantity, $id);
            $update->execute();
            $update->close();
        }
    }

    // Simpan pesanan
   // Jadi gini (taruh setelah transaksi sukses, sebelum INSERT):
$seq_stmt = $conn->prepare("SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pesanan'");
$seq_stmt->execute();
$seq_result = $seq_stmt->get_result()->fetch_assoc();
$next_global_id = $seq_result['AUTO_INCREMENT'] ?? 1; // ini id selanjutnya yang akan dipakai
$seq_stmt->close();

$order_id = 'ORD-' . strtoupper(uniqid()); // Contoh: ORD-67A1F3B456C89


    $stmt = $conn->prepare("INSERT INTO pesanan 
        (user_id, order_id, nama, whatsapp, email, alamat, kota, provinsi, kodepos, pembayaran, catatan, produk, total, status, tanggal)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");

    // Ambil data dari biodata (bukan dari form!)
    $bio_stmt = $conn->prepare("SELECT full_name, whatsapp, email, address, city, province, postalcode 
                            FROM user_biodata WHERE user_id = ?");
    $bio_stmt->bind_param("i", $_SESSION['user_id']);
    $bio_stmt->execute();
    $bio = $bio_stmt->get_result()->fetch_assoc();
    $bio_stmt->close();

    $nama       = $bio['full_name'] ?? $_SESSION['name'] ?? 'Customer';
    $whatsapp   = $bio['whatsapp'] ?? '';
    $email      = $bio['email'] ?? '';
    $alamat     = $bio['address'] ?? '';
    $kota       = $bio['city'] ?? '';
    $provinsi   = $bio['province'] ?? '';
    $kodepos    = $bio['postalcode'] ?? '';
    $pembayaran = $_POST['payment'] ?? 'transfer';
    $catatan    = $_POST['notes'] ?? '';

    $stmt->bind_param("isssssssssssi",
        $_SESSION['user_id'],
        $order_id,
        $nama,
        $whatsapp,
        $email,
        $alamat,
        $kota,
        $provinsi,
        $kodepos,
        $pembayaran,
        $catatan,
        $cart_json,
        $total
    );

    if (!$stmt->execute()) {
        $conn->rollback();
        throw new Exception('Gagal menyimpan pesanan');
    }
    $stmt->close();

    // SUKSES → COMMIT
    $conn->commit();

    ob_end_clean();
    echo json_encode([
        'success'  => true,
        'order_id' => $order_id,
        'total'    => $total
    ]);

} catch (Throwable $e) {
    // ADA ERROR → ROLLBACK (stok kembali!)
    if ($conn->errno) $conn->rollback();
    
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
exit;
?>