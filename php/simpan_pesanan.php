<?php
include 'config.php';
//simpan_pesanan.php

header('Content-Type: application/json');
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["success" => false, "error" => "No data received"]);
    exit;
}

$nama = $data['nama'];
$whatsapp = $data['whatsapp'];
$email = $data['email'];
$alamat = $data['alamat'];
$kota = $data['kota'];
$provinsi = $data['provinsi'];
$kodepos = $data['kodepos'];
$pembayaran = $data['pembayaran'];
$catatan = $data['catatan'];
$cart = json_encode($data['cart']);
$total = 0;

foreach ($data['cart'] as $item) {
    $total += $item['price'] * $item['quantity'];
}

$query = "INSERT INTO pesanan (nama, whatsapp, email, alamat, kota, provinsi, kodepos, pembayaran, catatan, produk, total)
          VALUES ('$nama', '$whatsapp', '$email', '$alamat', '$kota', '$provinsi', '$kodepos', '$pembayaran', '$catatan', '$cart', '$total')";

if ($conn->query($query)) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => $conn->error]);
}
?>
