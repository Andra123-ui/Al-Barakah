<?php 
session_start();
include 'config.php';

//all_testimonials.php

// Ambil semua testimoni approved
$testimonials_query = "SELECT * FROM testimonials WHERE is_approved = 1 ORDER BY created_at DESC";
$testimonials_result = $conn->query($testimonials_query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semua Testimoni - Al-Barakah</title>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/MULTO.CSS">
    <style>
        .testimonials-page {
            min-height: 100vh;
            padding: 6rem 2rem 4rem;
            background: var(--bg);
        }
        
        .testimonials-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .page-header h1 {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }
        
        .back-btn {
            display: inline-block;
            padding: 0.8rem 2rem;
            background: var(--primary);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 2rem;
            transition: all 0.3s;
        }
        
        .back-btn:hover {
            background: var(--secondary);
            transform: translateY(-2px);
        }
        
        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .no-testimonials {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-light);
        }
    </style>
</head>

    <script src="js/multo.js"></script>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="logo">🕌 Al-Barakah</div>
        <div class="nav-menu">
            <a href="MULTO.php">Beranda</a>
            <a href="MULTO.php#products">Produk</a>
            <a href="MULTO.php#testimonials">Testimoni</a>
            <a href="MULTO.php#about">Tentang</a>
            <button class="theme-toggle" onclick="toggleTheme()">🌙</button>
        </div>
    </nav>

    <div class="testimonials-page">
        <div class="testimonials-container">
            <a href="MULTO.php" class="back-btn">← Kembali ke Beranda</a>
            
            <div class="page-header">
                <h1>💬 Semua Testimoni Pelanggan</h1>
                <p>Alhamdulillah, terima kasih atas kepercayaan dan testimoni dari pelanggan setia kami</p>
            </div>

            <div class="testimonials-grid">
                <?php if ($testimonials_result && $testimonials_result->num_rows > 0): ?>
                    <?php while($testimonial = $testimonials_result->fetch_assoc()): ?>
                        <div class="testimonial-card">
                            <p>"<?= htmlspecialchars($testimonial['message']) ?>"</p>
                            <div class="testimonial-author">
                                — <?= htmlspecialchars($testimonial['customer_name']) ?>, 
                                <?= htmlspecialchars($testimonial['location']) ?> 
                                <?php for($i = 0; $i < $testimonial['rating']; $i++): ?>⭐<?php endfor; ?>
                            </div>
                            <small style="display: block; margin-top: 1rem; color: var(--text-light);">
                                <?= date('d M Y', strtotime($testimonial['created_at'])) ?>
                            </small>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-testimonials">
                        <h3>Belum ada testimoni</h3>
                        <p>Jadilah yang pertama memberikan testimoni!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-dua">اللهُمَّ بَارِكْ لَنَا فِي تِجَارَتِنَا</div>
        <p>"Ya Allah, berkahilah perdagangan kami"</p>
        <p style="margin-top: 2rem; opacity: 0.8;">© 2025 Al-Barakah. Semua hak dilindungi.</p>
    </footer>

</body>
</html>