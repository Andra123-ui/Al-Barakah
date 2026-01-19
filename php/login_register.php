<?php
session_start();
require_once 'config.php';

// login_register.php — 

if (isset($_POST['register'])) {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role     = 'user';

    // Cek email sudah ada?
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $_SESSION['register_error'] = 'Email sudah terdaftar!';
        $_SESSION['active_form'] = 'register';
    } else {
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $password, $role);
        $stmt->execute();
        $stmt->close();

        $_SESSION['success'] = 'Registrasi berhasil! Silakan login.';
        $_SESSION['active_form'] = 'login';
    }
    $check->close();
    header("Location: index.php");
    exit();
}

if (isset($_POST['login'])) {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // INI YANG PENTING — SESSION HARUS PAKAI KEY YANG SAMA DENGAN NAVBAR & PROFILE
            $_SESSION['user_id']     = $user['id'];
            $_SESSION['name']        = $user['name'];                    // PAKAI 'name'
            $_SESSION['user_email']  = $email;
            $_SESSION['user_role']   = 'user';

            // Ambil biodata (kalau ada)
            $bio_stmt = $conn->prepare("SELECT full_name, profile_pic FROM user_biodata WHERE user_id = ?");
            $bio_stmt->bind_param("i", $user['id']);
            $bio_stmt->execute();
            $bio = $bio_stmt->get_result()->fetch_assoc();
            $bio_stmt->close();

            // Prioritas: full_name dari biodata > name dari users
            $_SESSION['name']        = $bio['full_name'] ?? $user['name'] ?? 'User';
            $_SESSION['profile_pic'] = $bio['profile_pic'] ?? 'images-user/default-avatar.jpg';

            header("Location: multo.php");
            exit();
        }
    }
    $_SESSION['login_error'] = 'Email atau password salah!';
    $_SESSION['active_form'] = 'login';
    header("Location: index.php");
    exit();
}
?>