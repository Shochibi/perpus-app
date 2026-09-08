<?php
require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../config/app.php";
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (isset($_SESSION["id_user"])) redirect($_SESSION["role"] === "admin" ? "admin/dashboard.php" : "user/dashboard.php");
$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM tbl_user WHERE username=? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    if ($user && password_verify($password, $user["password"])) {
        session_regenerate_id(true);
        $_SESSION["id_user"] = (int)$user["id_user"];
        $_SESSION["fullname"] = $user["fullname"];
        $_SESSION["role"] = $user["role"];
        log_activity($koneksi, (int)$user["id_user"], null, "login", "Pengguna berhasil login");
        redirect($user["role"] === "admin" ? "admin/dashboard.php" : "user/dashboard.php");
    }
    $error = "Username atau password salah.";
}
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Login</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/app.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>

<body class="auth">
    <div class="container">
        <div class="card">
            <div class="logo">
                <img src="https://cdn-icons-png.flaticon.com/512/2232/2232688.png" alt="Logo perpustakaan">
            </div>
            <h2>PERPUSTAKAAN</h2>
            <p>SMK TARUNA BANGSA</p>

            <?php if ($error): ?>
                <p class="error"><?= e($error) ?></p>
            <?php endif; ?>

            <form method="post" class="auth-form">
                <div class="input-group">
                    <div class="input-box">
                        <i class="fa-solid fa-user-tag"></i>
                        <input type="text" name="username" placeholder="Masukkan Username" required autofocus>
                    </div>
                </div>

                <div class="input-group">
                    <div class="password-box">
                        <i class="fa-solid fa-lock left-icon"></i>
                        <input type="password" id="password" name="password" placeholder="Masukkan Password" required>
                        <i class="fa-solid fa-eye right-icon" id="togglePassword"></i>
                    </div>
                </div>

                <button type="submit">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    Login
                </button>
            </form>

            <div class="divider">atau</div>

            <div class="link">
                Belum punya akun?
                <a href="register.php">Daftar</a>
            </div>

            <div class="footer">
                © 2026 Perpustakaan SMK Taruna Bangsa
            </div>
        </div>
    </div>

    <script>
        const password = document.getElementById("password");
        const toggle = document.getElementById("togglePassword");

        if (password && toggle) {
            toggle.addEventListener("click", function() {
                if (password.type === "password") {
                    password.type = "text";
                    toggle.classList.remove("fa-eye");
                    toggle.classList.add("fa-eye-slash");
                } else {
                    password.type = "password";
                    toggle.classList.remove("fa-eye-slash");
                    toggle.classList.add("fa-eye");
                }
            });
        }
    </script>
</body>

</html>