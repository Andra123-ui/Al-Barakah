<?php 
session_start();

//multo.php

// Periksa apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    // Jika belum login, arahkan ke halaman login
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🕌 Al-Barakah | E-commerce Islami Modern</title>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/MULTO.CSS">
</head>
<body>
    <!-- ===== NAVBAR DENGAN FOTO PROFIL & DROPDOWN ===== -->
<nav class="navbar">
    <div class="logo">🕌 Al-Barakah</div>
    <div class="nav-menu">
        <a href="#home">Beranda</a>
        <a href="#products">Produk</a>
        <a href="#testimonials">Testimoni</a>
        <a href="#about">Tentang</a>
        
        <!-- Tombol Keranjang -->
        <button class="cart-icon" onclick="toggleCart()">🛒
            <span class="cart-badge" id="cartBadge">0</span>
        </button>

        <!-- Tombol Tema -->
        <button class="theme-toggle" onclick="toggleTheme()">☀️</button>

        <!-- Profil User dengan Dropdown -->
        <div class="user-profile" onclick="toggleUserDropdown(event)">
            <img src="<?php echo $_SESSION['profile_pic'] ?? 'images/default-avatar.jpg'; ?>" 
                 alt="Profil" class="profile-pic">
            <span class="username"><?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?></span>
            <div class="dropdown-arrow">▼</div>

            <div class="user-dropdown" id="userDropdown">
                <a href="profile.php">Profil Saya</a>
                <hr>
                <a href="my_orders.php">Pesanan Saya</a>
                <hr>
                <a href="logout.php" class="logout-link">Logout</a>
            </div>
        </div>
    </div>
</nav>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-content">
            <h1>Berdagang dengan Kejujuran,<br>Berkah Mengalir Tanpa Batas</h1>
            <p>Temukan Produk Islami yang Membawa Berkah untuk Hidupmu</p>
            <button class="cta-btn" onclick="scrollToProducts()">Belanja Sekarang</button>
        </div>
    </section>

    <!-- Search Bar -->
    <section class="search-container">
        <div class="search-box">
            <input type="text" class="search-input" id="searchInput" placeholder="Cari produk Islami..." onkeyup="searchProducts()">
            <button class="search-btn">🔍</button>
        </div>
    </section>

    <!-- Carousel Banner -->
    <!-- Carousel Banner -->
<section class="carousel">
    <h2>✨ Produk Unggulan</h2>
    <div class="carousel-container">
        <!-- Semua Produk -->
        <div class="carousel-item" data-category="all" onclick="filterByCategory('all')">
            <div style="font-size: 3rem;">🛍️</div>
            <h3>Semua Produk</h3>
        </div>

        <!-- Sajadah -->
        <div class="carousel-item" data-category="sajadah" oncli="filterByCategory('sajadah')">
            <div style="font-size: 3rem;">🕋</div>
            <h3>Sajadah Premium</h3>
            <p>Empuk & berkualitas</p>
        </div>

        <!-- Baju Koko -->
        <div class="carousel-item" data-category="pakaian" onclick="filterByCategory('pakaian')">
            <div style="font-size: 3rem;">👔</div>
            <h3>Baju Koko Eksklusif</h3>
            <p>Elegan & nyaman</p>
        </div>

        <!-- Mukena -->
        <div class="carousel-item" data-category="mukena" onclick="filterByCategory('mukena')">
            <div style="font-size: 3rem;">🧕</div>
            <h3>Mukena Cantik</h3>
            <p>Design modern</p>
        </div>

        <!-- Tasbih Digital -->
        <div class="carousel-item" data-category="tasbih" onclick="filterByCategory('tasbih')">
            <div style="font-size: 3rem;">📿</div>
            <h3>Tasbih Digital</h3>
            <p>Praktis & modern</p>
        </div>

        <!-- Parfum -->
        <div class="carousel-item" data-category="parfum" onclick="filterByCategory('parfum')">
            <div style="font-size: 3rem;">🌸</div>
            <h3>Parfum Non-Alkohol</h3>
            <p>Wangi tahan lama</p>
        </div>

        <!-- Al-Quran -->
        <div class="carousel-item" data-category="alquran" onclick="filterByCategory('alquran')">
            <div style="font-size: 3rem;">📖</div>
            <h3>Al-Quran Tajwid Warna</h3>
            <p>Mudah dipahami</p>
        </div>

        <!-- Peci -->
        <div class="carousel-item" data-category="peci" onclick="filterByCategory('peci')">
            <div style="font-size: 3rem;">🧢</div>
            <h3>Peci Rajut Premium</h3>
            <p>Nyaman & berkualitas</p>
        </div>

        <!-- Sarung -->
        <div class="carousel-item" data-category="sarung" onclick="filterByCategory('sarung')">
            <div style="font-size: 3rem;">🧣</div>
            <h3>Sarung Wadimor Eksklusif</h3>
            <p>Motif elegan & lembut</p>
        </div>

        <!-- Jubah -->
        <div class="carousel-item" data-category="jubah" onclick="filterByCategory('jubah')">
            <div style="font-size: 3rem;">🧣</div>
            <h3>Jubah Eksklusif</h3>
            <p>Motif elegan & lembut</p>
        </div>
    </div>
</section>


<!-- Products Section -->
<section class="products" id="products">
    <h2>🛍️ Koleksi Produk Berkah</h2>

    <div class="product-grid">
        <?php
        include 'config.php';

        // Ambil products + kategori
        $result = $conn->query("
            SELECT p.*, c.name AS category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id
            ORDER BY p.id ASC
        ");

        if ($result->num_rows > 0):
            while ($row = $result->fetch_assoc()):
                $img = $row['image'] ? $row['image'] : 'images/no-image.png';


        ?>
        
        <div class="product-card"
             data-name="<?= htmlspecialchars($row['name']) ?>"
             data-price="<?= $row['price'] ?>"
             data-category="<?= strtolower($row['category_name']) ?>">

            <img src="<?= $img ?>" class="product-image">

            <h3><?= htmlspecialchars($row['name']) ?></h3>
            <p><?= htmlspecialchars($row['description']) ?></p>

            <div class="product-price">Rp<?= number_format($row['price'], 0, ',', '.') ?></div>

            <button class="add-to-cart"
                onclick="addToCart(
                    '<?= htmlspecialchars($row['name']) ?>',
                    <?= $row['price'] ?>,
                    '<?= $img ?>',
                    <?= $row['id'] ?>
                )">
                Tambah ke Keranjang
            </button>
        </div>

        <?php endwhile; ?>

        <?php else: ?>
            <p style="grid-column:1/-1; text-align:center; opacity:0.7;">Belum ada produk ditambahkan.</p>
        <?php endif; ?>
    </div>
</section>

    <!-- Cart Modal -->
    <div class="cart-modal" id="cartModal">
        <div class="cart-header">
            <h2>🛒 Keranjang Belanja</h2>
            <button class="close-cart" onclick="toggleCart()">×</button>
        </div>
        <div class="cart-items" id="cartItems">
            <div class="empty-cart">
                <p>Keranjang Anda masih kosong</p>
                <p>Yuk mulai belanja! 🛍️</p>
            </div>
        </div>
        <div class="cart-summary" id="cartSummary" style="display: none;">
            <div class="cart-total">
                <span>Total:</span>
                <span id="cartTotal">Rp 0</span>
            </div>
            <button class="checkout-btn" onclick="openCheckout()">Checkout Sekarang</button>
        </div>
    </div>

    <!-- Checkout Modal — WAJIB LENGKAP BIODATA DULU! -->
<div class="checkout-overlay" id="checkoutOverlay" onclick="closeCheckout()"></div>
<div class="checkout-modal" id="checkoutModal">
    <div id="checkoutContent">
        <div style="text-align:center; padding:4rem;">
            <div style="width:60px; height:60px; border:6px solid #f0f0f0; border-top:6px solid #4361ee; border-radius:50%; animation:spin 1s linear infinite; margin:0 auto 1rem;"></div>
            <p style="font-size:1.2rem;">Memeriksa biodata Anda...</p>
        </div>
    </div>
</div>

<style>
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
.checkout-modal, .checkout-overlay { display: none; }
.checkout-modal.active, .checkout-overlay.active { display: block; }
</style>

    <!-- Testimonials -->
    <section class="testimonials" id="testimonials">
        <h2>💬 Testimoni Pelanggan</h2>
        <div class="testimonial-grid">
            <div class="testimonial-card">
                <p>"Alhamdulillah, produknya berkualitas dan pelayanannya sangat baik. Sajadah yang saya beli sangat nyaman untuk shalat. Barakallah!"</p>
                <div class="testimonial-author">— Fatimah Azzahra, Jakarta ⭐⭐⭐⭐⭐</div>
            </div>
            <div class="testimonial-card">
                <p>"Baju koko dari Al-Barakah sangat elegan dan bahannya adem. Cocok banget buat ke masjid dan acara formal. Recommended!"</p>
                <div class="testimonial-author">— Ahmad Fauzi, Bandung ⭐⭐⭐⭐⭐</div>
            </div>
            <div class="testimonial-card">
                <p>"Parfumnya wangi banget dan halal. Seneng deh belanja di Al-Barakah, semua produknya Islami dan berkualitas."</p>
                <div class="testimonial-author">— Siti Nurhaliza, Surabaya ⭐⭐⭐⭐⭐</div>
            </div>
        </div>
         <div style="text-align: center; margin-top: 3rem;">
        <a href="all_testimonials.php" class="cta-btn">Lihat Semua Testimoni 📝</a>
    </div>
    </section>

    <!-- Tambahkan section ini SETELAH section testimonials di HTML Anda -->


    <!-- About Section -->
    <section class="about" id="about">
        <h2>Tentang Al-Barakah</h2>
        <div class="about-content">
            <p>
                <strong>Al-Barakah</strong> adalah toko online yang menyediakan berbagai produk Islami berkualitas tinggi. 
                Kami berkomitmen untuk menyediakan produk yang tidak hanya berkualitas, tetapi juga membawa berkah dalam setiap transaksi.
            </p>
            <p style="margin-top: 1rem;">
                Visi kami adalah menjadi platform e-commerce Islami terpercaya yang mengedepankan kejujuran, amanah, dan pelayanan terbaik 
                untuk umat Muslim di seluruh Indonesia. Setiap produk yang kami jual telah melewati seleksi ketat untuk memastikan kehalalannya.
            </p>
            <p style="margin-top: 1rem; color: var(--primary); font-weight: 600;">
                "خَيْرُ النَّاسِ أَنْفَعُهُمْ لِلنَّاسِ"<br>
                <em>"Sebaik-baik manusia adalah yang paling bermanfaat bagi orang lain"</em>
            </p>
        </div>
    </section>

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
                <li><a href="#home">Beranda</a></li>
                <li><a href="#products">Produk</a></li>
                <li><a href="#testimonials">Testimoni</a></li>
                <li><a href="my_orders.php">Pesanan Saya</a></li>
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

    <!-- Scroll to Top -->
    <div class="scroll-top" onclick="scrollToTop()">🌙</div>

    <!-- Promo Popup -->
    <div class="popup-overlay" onclick="closePromoPopup()"></div>
    <div class="promo-popup">
        <button class="close-popup" onclick="closePromoPopup()">×</button>
        <h2 style="color: var(--primary); margin-bottom: 1rem;">🌙 Promo Spesial</h2>
        <p>Dapatkan diskon hingga <strong style="color: var(--secondary);">30%</strong> untuk semua produk!</p>
        <div class="countdown" id="countdown">Loading...</div>
        <button class="cta-btn" onclick="closePromoPopup(); scrollToProducts();">Belanja Sekarang</button>
    </div>
<script src="js/multo.js"></script>
    
</body>
</html>

