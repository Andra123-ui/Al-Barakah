<?php

session_start();

//index.php

$errors = [
   'login' => $_SESSION['login_error'] ?? '',
   'register' => $_SESSION['register_error'] ?? '',
   'admin' => $_SESSION['admin_error'] ?? ''
];
$activeForm = $_SESSION['active_form'] ?? 'login';

session_unset();

function showError($error) {
    return !empty($error) ? "<p class='error-message'>$error</p>" : '';
}

function isActiveForm($formName, $activeForm) {
    return $formName === $activeForm ? 'active' : '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/login.css">

</head>
<body>
    <div class="container">
        <div class="form-box <?= isActiveForm('login', $activeForm); ?>" id="login-form">
            <form action="login_register.php" method="post">
                <h2>Login</h2>
                <?= showError($errors['login']); ?>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="login">Login</button>
                <p>Don't have an account? <a href="#" onclick="showForm('register-form')">Register</a></p>
                <p class="admin-link"><a href="#" onclick="showForm('admin-form')">Admin Login</a></p>
            </form>
        </div>

        <div class="form-box <?= isActiveForm('register', $activeForm); ?>" id="register-form">
            <form action="login_register.php" method="post">
                <h2>Register</h2>
                <?= showError($errors['register']); ?>
                <input type="text" name="name" placeholder="Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <select name="role" required>
                    <option value="user">User</option>               
                 </select>
                <button type="submit" name="register">Register</button>
                <p>Already have an account? <a href="#" onclick="showForm('login-form')">Login</a></p>
            </form>
        </div>

        <div class="form-box <?= isActiveForm('admin', $activeForm); ?>" id="admin-form">
            <form action="admin_login.php" method="post">
                <h2>Admin Login</h2>
                <?= showError($errors['admin']); ?>
                <input type="password" name="admin_password" placeholder="Admin Password" required>
                <button type="submit" name="admin_login">Access Admin Panel</button>
                <p><a href="#" onclick="showForm('login-form')">Back to Login</a></p>
            </form>
        </div>
    </div>

    <script src="js/login.js"></script>
</body>
</html>