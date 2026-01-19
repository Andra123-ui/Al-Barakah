<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

// Cek login admin
if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

// Ambil data form
$name        = $_POST['name'] ?? '';
$slug        = $_POST['slug'] ?? '';
$description = $_POST['description'] ?? '';
$price       = $_POST['price'] ?? '';
$category_id = $_POST['category_id'] ?? null;
$stock       = $_POST['stock'] ?? 0;
$image       = $_FILES['image'] ?? null;

// Validasi dasar
if (empty($name) || empty($price)) {
    echo json_encode(['success' => false, 'error' => 'Nama dan harga wajib diisi']);
    exit();
}

// Buat slug otomatis jika kosong
if (empty($slug)) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
}

// Deskripsi default
if (empty($description)) {
    $description = '-';
}

// Cek kategori valid jika ada
if (!empty($category_id)) {
    $check = $conn->prepare("SELECT id FROM categories WHERE id = ?");
    $check->bind_param("i", $category_id);
    $check->execute();

    if ($check->get_result()->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Kategori tidak ditemukan']);
        exit();
    }
}

// 🟤 --- UPLOAD GAMBAR --- 🟤
if (!$image || $image['error'] !== 0) {
    echo json_encode(['success' => false, 'error' => 'Gambar produk wajib diunggah']);
    exit();
}

// Pastikan folder images ada
$uploadDir = "images/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$ext = pathinfo($image['name'], PATHINFO_EXTENSION);
$imageName = "product_" . time() . "." . $ext;
$imagePathDB = $uploadDir . $imageName; // disimpan ke DB

// Upload ke folder
if (!move_uploaded_file($image['tmp_name'], $imagePathDB)) {
    echo json_encode(['success' => false, 'error' => 'Gagal upload gambar']);
    exit();
}

// 🟢 --- INSERT DATA --- 🟢
$stmt = $conn->prepare("
    INSERT INTO products (name, slug, description, price, category_id, image, stock)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "sssdisi",
    $name,
    $slug,
    $description,
    $price,
    $category_id,
    $imagePathDB, // isi DB = images/namafile.jpg
    $stock
);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Produk berhasil ditambahkan']);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}
$stmt->close();
$conn->close();