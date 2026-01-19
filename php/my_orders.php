<?php 
session_start();
include 'config.php';
//my_orders.php
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Query yang benar: hanya ambil pesanan milik user yang login
$stmt = $conn->prepare("SELECT * FROM pesanan WHERE user_id = ? ORDER BY id ASC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders_result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🕌 Pesanan Saya - Al-Barakah</title>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/MULTO.CSS">
    <style>
        .orders-page{padding:6rem 2rem 4rem;background:var(--bg);}
        .orders-container{max-width:1100px;margin:0 auto;}
        .order-card{background:var(--card-bg);border-radius:12px;padding:1.5rem;margin-bottom:1.5rem;box-shadow:0 4px 15px rgba(0,0,0,0.1);}
        .order-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;}
        .order-id{font-size:1.3rem;font-weight:600;color:var(--primary);}
        .status-badge{padding:0.4rem 1rem;border-radius:50px;font-size:0.85rem;font-weight:600;}
        .status-pending{background:#fff3e0;color:#e65100;}
        .status-processing{background:#e3f2fd;color:#1565c0;}
        .status-shipped{background:#e8f5e9;color:#2e7d32;}
        .status-delivered{background:#f3e5f5;color:#7b1fa2;}
        .status-completed{background:#e0f2e9;color:#00695c;}
        .products-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1rem;margin:1rem 0;}
        .product-item{display:flex;align-items:center;gap:1rem;background:var(--bg);padding:0.8rem;border-radius:8px;}
        .product-item img{width:60px;height:60px;object-fit:cover;border-radius:8px;}
        .action-btn{margin-top:1rem;padding:0.8rem 1.5rem;background:var(--primary);color:white;border:none;border-radius:8px;cursor:pointer;}
        .action-btn:disabled{background:#999;cursor:not-allowed;}
        .action-btn.sent{background:#27ae60 !important;color:white !important;cursor:default;}
    </style>
    
</head>





<script src="js/multo.js"></script>
<body>

<!-- Navbar kamu -->
<nav class="navbar">
    <div class="logo">🕌 Al-Barakah</div>
    <div class="nav-menu">
        <a href="multo.php">Beranda</a> 
            <a href="MULTO.php#products">Produk</a>
            <a href="MULTO.php#testimonials">Testimoni</a>
            <a href="MULTO.php#about">Tentang</a>
        <button class="theme-toggle" onclick="toggleTheme()">🌙</button>
        <!-- Profil User dengan Dropdown -->
        <div class="user-profile" onclick="toggleUserDropdown(event)">
            <img src="<?php echo $_SESSION['profile_pic'] ?? 'images/default-avatar.jpg'; ?>" 
                 alt="Profil" class="profile-pic">
            <span class="username"><?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?></span>
            <div class="dropdown-arrow">▼</div>

            <div class="user-dropdown" id="userDropdown">
                <a href="profile.php">Profil Saya</a>
                <hr>
                <a href="multo.php">Beranda</a>
                <hr>
                <a href="logout.php" class="logout-link">Logout</a>
            </div>
        </div>
        </div>
</nav> 

<div class="orders-page">
    <div class="orders-container">
        <?php if(isset($_GET['success'])): ?>
<div style="background:#d4edda;color:#155724;padding:1.5rem;border-radius:12px;margin:2rem 0;text-align:center;font-weight:700;border:2px solid #c3e6cb;">
    Alhamdulillah! Pembayaran berhasil! Pesanan Anda <strong>langsung kami proses sekarang</strong>.
</div>
<?php endif; ?>
        <h1>Pesanan Saya</h1>
        <p style="margin-bottom:2rem;">Berikut adalah semua pesanan yang pernah Anda lakukan di Al-Barakah.</p>



<!-- ... HTML sebelumnya ... -->

<?php if ($orders_result->num_rows > 0): ?>
    <?php while($order = $orders_result->fetch_assoc()): 
    $products = json_decode($order['produk'], true);
    $testimonial_sent = $order['testimonial_sent'] ?? 0;
?>
    <div class="order-card" data-order-id="<?= htmlspecialchars($order['order_id']) ?>">
        <div class="order-header">
            <div class="order-id">Order <?= htmlspecialchars($order['order_id']) ?></div>
            <span class="status-badge status-<?= $order['status'] ?>">
                
    <?php
    $status_text = [
        'pending'     => 'Menunggu Pembayaran',
        'processing'  => 'Diproses',
        'shipped'     => 'Dikirim',
        'delivered'   => 'Sampai Tujuan',
        'completed'   => 'Selesai',
        'failed'      => 'Gagal / Kadaluarsa',
        'cancelled'   => 'Dibatalkan'
    ];

    echo $status_text[$order['status']] ?? ucfirst(str_replace('_', ' ', $order['status']));
    ?>
</span>

        </div>
        <!-- sisanya tetap sama -->

            <!-- sisanya tetap sama -->
                    <small>Tanggal: <?= date('d M Y H:i', strtotime($order['tanggal'])) ?></small>
                    <p><strong>Total:</strong> Rp <?= number_format($order['total'], 0, ',', '.') ?></p>

                    <div class="products-grid">
                        <?php foreach($products as $item): ?>
                            <div class="product-item">
                                <img src="<?= htmlspecialchars($item['image'] ?? 'images/no-image.jpg') ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                                <div>
                                    <div style="font-weight:600;"><?= htmlspecialchars($item['name']) ?></div>
                                    <small><?= $item['quantity'] ?> × Rp <?= number_format($item['price'], 0, ',', '.') ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($order['status'] == 'delivered' || $order['status'] == 'completed'): ?>
                        <?php if ($testimonial_sent == 1): ?>
                            <button class="action-btn sent" disabled>
                                Testimoni Sudah Terkirim — Terima Kasih!
                            </button>
                        <?php else: ?>
                            <a href="rating.php?order_id=<?= $order['id'] ?>">
                                <button class="action-btn">Beri Rating & Testimoni</button>
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <button class="action-btn" disabled>
                            Menunggu barang sampai untuk memberikan rating
                        </button>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align:center;padding:4rem;color:var(--text-light);font-size:1.1rem;">
                Belum ada pesanan. Yuk <a href="MULTO.php#products" style="color:var(--primary);font-weight:600;">belanja sekarang</a>!
            </p>
        <?php endif; ?>
    </div>
</div>

<!-- ===== FOOTER PREMIUM ===== -->
<footer class="footer">
    <div class="footer-container">
        <div class="footer-column">
            <h3>🕌 Al-Barakah</h3>
            <p>Toko online produk Islami berkualitas dengan keberkahan di setiap transaksi.</p>
            <p style="font-size: 0.9rem; margin-top: 1rem; opacity: 0.8;">
                "Allahumma barik lana fi tijaratina"<br>
                <em>— Ya Allah, berkahilah perdagangan kami</em>
            </p>
        </div>

        <div class="footer-column">
            <h4>Navigasi Cepat</h4>
            <ul>
                <li><a href="multo.php#home">Beranda</a></li>
                <li><a href="multo.php#products">Produk</a></li>
                <li><a href="multo.php#testimonials">Testimoni</a></li>
            </ul>
        </div>

        <div class="footer-column">
            <h4>Hubungi Kami</h4>
            <div class="contact-item">
                <span>WhatsApp</span>
                <a href="https://wa.me/6289523586766">+62 895-2358-66766</a>
            </div>
            <div class="contact-item">
                <span>Instagram</span>
                <a href="https://instagram.com/diandraalifianto">@diandraalifianto</a>
            </div>
            <div class="contact-item">
                <span>TikTok</span>
                <a href="https://tiktok.com/@ndraaygy2">@ndraaygy2</a>
            </div>
        </div>

        <div class="footer-column">
            <h4>Jam Operasional</h4>
            <p>Senin — Sabtu: 08:00 — 22:00 WIB<br>Minggu: 09:00 — 20:00 WIB</p>
            <div class="trust-badge">
                <p>100% Halal & Amanah</p>
                <p>Pengiriman Seluruh Indonesia</p>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <p>© 2025 <strong>Al-Barakah</strong>. Semua hak cipta dilindungi.</p>
        <p>Dibuat dengan cinta & doa untuk umat</p>
    </div>
</footer>

</body>
</html>

<?php 
$stmt->close();
$conn->close();
?>