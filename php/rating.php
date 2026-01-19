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
<link rel="stylesheet" href="css/MULTO.CSS">

<!-- rating.php -->
<!-- Form Testimoni Baru -->
<section class="testimonial-form-section" id="testimonial-form">
    <h2>✍️ Bagikan Pengalaman Anda</h2>
    <div class="testimonial-form-container">
        <p style="text-align: center; margin-bottom: 2rem; color: var(--text-light);">
            Alhamdulillah, kami senang melayani Anda. Bagikan testimoni Anda untuk membantu calon pelanggan lainnya!
        </p>
        
        <form class="testimonial-form" id="testimonialForm" onsubmit="submitTestimonial(event)">
            <!-- BARIS INI WAJIB: Kirim order_id secara diam-diam -->
            <input type="hidden" name="order_id" value="<?= htmlspecialchars($_GET['order_id'] ?? 0) ?>">

            <div class="form-group">
                <label>Nama Lengkap *</label>
                <input type="text" id="testimonial_name" name="customer_name" required placeholder="Masukkan nama Anda">
            </div>
            
            <div class="form-group">
                <label>Kota/Lokasi</label>
                <input type="text" id="testimonial_location" name="location" placeholder="Contoh: Jakarta, Surabaya">
            </div>
            
            <div class="form-group">
                <label>Rating *</label>
                <div class="rating-input">
                    <input type="radio" name="rating" value="5" id="star5" checked><label for="star5">⭐⭐⭐⭐⭐</label>
                    <input type="radio" name="rating" value="4" id="star4"><label for="star4">⭐⭐⭐⭐</label>
                    <input type="radio" name="rating" value="3" id="star3"><label for="star3">⭐⭐⭐</label>
                    <input type="radio" name="rating" value="2" id="star2"><label for="star2">⭐⭐</label>
                    <input type="radio" name="rating" value="1" id="star1"><label for="star1">⭐</label>
                </div>
            </div>
            
            <div class="form-group">
                <label>Testimoni Anda *</label>
                <textarea id="testimonial_message" name="message" required rows="5" placeholder="Ceritakan pengalaman Anda berbelanja di Al-Barakah..."></textarea>
            </div>
            
            <div class="form-note">
                <small>📝 Testimoni Anda akan ditinjau oleh admin sebelum ditampilkan di halaman utama.</small>
            </div>
            
            <button type="submit" class="submit-testimonial-btn">Kirim Testimoni</button>
        </form>
        
        <!-- Area pesan sukses / error -->
        <div id="testimonialMessage" class="testimonial-message"></div>
    </div>
</section>

<script src="js/testimonial.js"></script>