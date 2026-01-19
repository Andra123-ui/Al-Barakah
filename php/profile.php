<?php 
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';

// AMBIL DATA LENGKAP: NAMA, EMAIL, FULL_NAME, DLL → EMAIL PASTI MUNCUL!
$stmt = $conn->prepare("
    SELECT 
        u.name AS user_name,
        u.email AS user_email,
        b.full_name,
        b.whatsapp,
        b.address,
        b.city,
        b.province,
        b.postalcode,
        b.profile_pic
    FROM users u 
    LEFT JOIN user_biodata b ON u.id = b.user_id 
    WHERE u.id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Sync session supaya nama & foto selalu muncul di navbar
$_SESSION['name'] = $user['full_name'] ?? $user['user_name'] ?? 'User';

if (!empty($user['profile_pic'])) {
    // jika DB sudah punya foto → pakai DB
    $_SESSION['profile_pic'] = $user['profile_pic'];
} else {
    // jika DB kosong → tetap pakai SESSION kalau sebelumnya ada,
    // kalau tidak ada → pakai default
    $_SESSION['profile_pic'] = $_SESSION['profile_pic'] ?? 'images-user/default-avatar.jpg';
}


// Cek apakah biodata sudah lengkap (alamat diisi)
$biodata_exists = !empty($user['address']);

// === SIMPAN PERUBAHAN ===
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name  = trim($_POST['name']);
    $email      = trim($_POST['email']);
    $whatsapp   = trim($_POST['whatsapp']);
    $address    = trim($_POST['address']);
    $city       = trim($_POST['city']);
    $province   = trim($_POST['province']);
    $postalcode = trim($_POST['postalcode']);

    // Update tabel users (nama & email)
    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
    $stmt->bind_param("ssi", $full_name, $email, $user_id);
    $stmt->execute();
    $stmt->close();

    // Upload foto profil
    $profile_pic = $user['profile_pic'] ?? 'images-user/default-avatar.jpg';
    if (!empty($_FILES['profile_pic']['name']) && $_FILES['profile_pic']['error'] == 0) {
    $ext = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    if (in_array($ext, $allowed) && $_FILES['profile_pic']['size'] <= 2000000) {
        $new_name = "profile_$user_id.$ext";
        $dest = "images-user/$new_name";

        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $dest)) {
            $profile_pic = $dest;

            // 🔥 FIX: UPDATE SESSION SUPAYA MUNCUL LANGSUNG TANPA LOGIN ULANG
            $_SESSION['profile_pic'] = $profile_pic;
        }
    }
}



        // Simpan biodata (PASTI SIMPAN EMAIL JUGA!)
    if ($biodata_exists) {
        $stmt = $conn->prepare("UPDATE user_biodata SET 
            full_name=?, address=?, city=?, province=?, postalcode=?, whatsapp=?, email=?, profile_pic=? 
            WHERE user_id=?");
        $stmt->bind_param("ssssssssi", $full_name, $address, $city, $province, $postalcode, $whatsapp, $email, $profile_pic, $user_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO user_biodata 
            (user_id, full_name, address, city, province, postalcode, whatsapp, email, profile_pic) 
            VALUES (?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("issssssss", $user_id, $full_name, $address, $city, $province, $postalcode, $whatsapp, $email, $profile_pic);
    }
    $stmt->execute();
    $stmt->close();

    $message = "Profil berhasil diperbarui!";
    header("Location: profile.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - Al-Barakah</title>
    <link rel="stylesheet" href="css/MULTO.CSS">
    <style>
        .profile-page { padding: 6rem 2rem; max-width: 800px; margin: 0 auto; }
        .profile-card { background: var(--card-bg); border-radius: 20px; padding: 3rem; box-shadow: 0 15px 35px rgba(0,0,0,0.1); text-align: center; }
        .current-pic { width: 140px; height: 140px; border-radius: 50%; object-fit: cover; border: 6px solid var(--primary); margin-bottom: 1.5rem; }
        .welcome-name { font-size: 2.2rem; font-weight: 700; color: var(--primary); margin: 1rem 0; }
        .biodata-display { background: rgba(67,97,238,0.08); padding: 2rem; border-radius: 16px; margin: 2rem 0; text-align: left; }
        .biodata-row { display: flex; justify-content: space-between; padding: 1rem 0; border-bottom: 1px solid rgba(0,0,0,0.1); font-size: 1.1rem; }
        .biodata-row:last-child { border-bottom: none; }
        .biodata-label { font-weight: 600; color: var(--primary); width: 40%; }
        .biodata-value { width: 60%; text-align: right; }
        .btn-edit { background: var(--primary); color: white; padding: 14px 40px; border: none; border-radius: 50px; font-size: 1.1rem; cursor: pointer; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin: 2rem 0; }
        .form-group label { font-weight: 600; color: var(--primary); margin-bottom: 0.5rem; display: block; }
        .form-group input, .form-group textarea { width: 100%; padding: 14px; border: 1px solid #ddd; border-radius: 12px; font-size: 1rem; }
        .btn-save { background: var(--primary); color: white; padding: 16px 50px; border: none; border-radius: 50px; font-size: 1.2rem; cursor: pointer; }
        .success { background: #d4edda; color: #155724; padding: 1rem; border-radius: 12px; margin: 1rem 0; font-weight: bold; }
        @media (max-width: 768px) { .form-grid { grid-template-columns: 1fr; } .biodata-row { flex-direction: column; text-align: left; } .biodata-label, .biodata-value { width: 100%; } }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <div class="logo">🕌 Al-Barakah</div>
    <div class="nav-menu">
        <a href="multo.php">Beranda</a> 
            <a href="MULTO.php#products">Produk</a>
            <a href="MULTO.php#testimonials">Testimoni</a>
            <a href="MULTO.php#about">Tentang</a>
        <button class="theme-toggle" onclick="toggleTheme()">Tema</button>
        <div class="user-profile" onclick="toggleUserDropdown(event)">
            <img src="<?= htmlspecialchars($_SESSION['profile_pic']) ?>" class="profile-pic">
            <span class="username"><?= htmlspecialchars($_SESSION['name']) ?></span>
            <div class="dropdown-arrow">▼</div>
            <div class="user-dropdown" id="userDropdown">
                <a href="multo.php">Beranda</a>
                <hr>
                <a href="my_orders.php">Pesanan Saya</a> 
                <hr> 
                <a href="logout.php">Logout</a>
                
                    
        </div>
    </div>
</nav>

<div class="profile-page">
    <div class="profile-card">
        <h2>Profil Saya</h2>
        <?php if($message): ?><div class="success"><?= $message ?></div><?php endif; ?>

        <?php if ($biodata_exists): ?>
            <!-- BIODATA SUDAH LENGKAP -->
            <img src="<?= htmlspecialchars($_SESSION['profile_pic']) ?>" class="current-pic" alt="Profil">
            <div class="welcome-name">Selamat Datang, <?= htmlspecialchars($user['full_name'] ?? $user['user_name']) ?>!</div>

            <div class="biodata-display">
                <div class="biodata-row"><span class="biodata-label">Nama Lengkap</span><span class="biodata-value"><?= htmlspecialchars($user['full_name'] ?? $user['user_name']) ?></span></div>
                <div class="biodata-row"><span class="biodata-label">Email</span><span class="biodata-value"><?= htmlspecialchars($user['user_email'] ?? 'Belum diisi') ?></span></div>
                <div class="biodata-row"><span class="biodata-label">WhatsApp</span><span class="biodata-value"><?= htmlspecialchars($user['whatsapp'] ?? 'Belum diisi') ?></span></div>
                <div class="biodata-row"><span class="biodata-label">Alamat</span><span class="biodata-value"><?= nl2br(htmlspecialchars($user['address'] ?? 'Belum diisi')) ?></span></div>
                <div class="biodata-row"><span class="biodata-label">Kota</span><span class="biodata-value"><?= htmlspecialchars($user['city'] ?? 'Belum diisi') ?></span></div>
                <div class="biodata-row"><span class="biodata-label">Provinsi</span><span class="biodata-value"><?= htmlspecialchars($user['province'] ?? 'Belum diisi') ?></span></div>
                <div class="biodata-row"><span class="biodata-label">Kode Pos</span><span class="biodata-value"><?= htmlspecialchars($user['postalcode'] ?? 'Belum diisi') ?></span></div>
            </div>

            <button onclick="document.getElementById('editForm').style.display='block'; this.style.display='none';" class="btn-edit">
                Edit Biodata
            </button>

            <div id="editForm" style="display:none; margin-top:2rem;">
                <h3>Edit Biodata</h3>
                <form method="POST" enctype="multipart/form-data">
                    <div style="margin:1.5rem 0;"><input type="file" name="profile_pic" accept="image/*"><br><small>JPG/PNG/WEBP • Maks 2MB</small></div>
                    <div class="form-grid">
                        <div class="form-group"><label>Nama Lengkap</label><input type="text" name="name" value="<?= htmlspecialchars($user['full_name'] ?? $user['user_name']) ?>" required></div>
                        <div class="form-group"><label>Email</label><input type="email" name="email" value="<?= htmlspecialchars($user['user_email'] ?? '') ?>" required></div>
                        <div class="form-group"><label>WhatsApp</label><input type="tel" name="whatsapp" value="<?= htmlspecialchars($user['whatsapp'] ?? '') ?>" required></div>
                        <div class="form-group"><label>Kode Pos</label><input type="text" name="postalcode" value="<?= htmlspecialchars($user['postalcode'] ?? '') ?>" required></div>
                        <div class="form-group"><label>Kota</label><input type="text" name="city" value="<?= htmlspecialchars($user['city'] ?? '') ?>" required></div>
                        <div class="form-group"><label>Provinsi</label><input type="text" name="province" value="<?= htmlspecialchars($user['province'] ?? '') ?>" required></div>
                    </div>
                    <div class="form-group"><label>Alamat Lengkap</label><textarea name="address" required><?= htmlspecialchars($user['address'] ?? '') ?></textarea></div>
                    <button type="submit" class="btn-save">Simpan Perubahan</button>
                </form>
            </div>

        <?php else: ?>
            <!-- BELUM ISI BIODATA -->
            <img src="<?= htmlspecialchars($_SESSION['profile_pic']) ?>" class="current-pic" alt="Profil">
            <div class="welcome-name">Halo, <?= htmlspecialchars($user['user_name']) ?>!</div>
            <p style="margin:1.5rem 0; font-size:1.2rem;">Silakan lengkapi biodata Anda untuk melanjutkan belanja</p>

            <form method="POST" enctype="multipart/form-data">
                <div style="margin:1.5rem 0;"><input type="file" name="profile_pic" accept="image/*"><br><small>JPG/PNG/WEBP • Maks 2MB</small></div>
                <div class="form-grid">
                    <div class="form-group"><label>Nama Lengkap</label><input type="text" name="name" value="<?= htmlspecialchars($user['user_name']) ?>" required></div>
                    <div class="form-group"><label>Email</label><input type="email" name="email" value="<?= htmlspecialchars($user['user_email'] ?? '') ?>" required></div>
                    <div class="form-group"><label>WhatsApp</label><input type="tel" name="whatsapp" placeholder="08xxxxxxxxxx" required></div>
                    <div class="form-group"><label>Kode Pos</label><input type="text" name="postalcode" placeholder="Contoh: 60231" required></div>
                    <div class="form-group"><label>Kota</label><input type="text" name="city" placeholder="Contoh: Surabaya" required></div>
                    <div class="form-group"><label>Provinsi</label><input type="text" name="province" placeholder="Contoh: Jawa Timur" required></div>
                </div>
                <div class="form-group"><label>Alamat Lengkap</label><textarea name="address" rows="4" placeholder="Jalan, RT/RW, Kelurahan, Kecamatan" required></textarea></div>
                <button type="submit" class="btn-save">Lengkapi Biodata</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<script src="js/multo.js"></script>
</body>
</html>