<?php
require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../config/app.php";
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullname = trim($_POST["fullname"] ?? "");
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm = $_POST["confirm"] ?? "";
    if ($password !== $confirm) {
        $error = "Konfirmasi password tidak cocok.";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter.";
    } else {
        $stmt = mysqli_prepare($koneksi, "SELECT id_user FROM tbl_user WHERE username=?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $exists = mysqli_stmt_get_result($stmt)->num_rows;
        mysqli_stmt_close($stmt);
        if ($exists) {
            $error = "Username sudah digunakan.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($koneksi, "INSERT INTO tbl_user(fullname,username,password,role,tanggal_daftar) VALUES(?,?,?,'user',CURDATE())");
            mysqli_stmt_bind_param($stmt, "sss", $fullname, $username, $hash);
            mysqli_stmt_execute($stmt);
            $idUser = mysqli_insert_id($koneksi);
            mysqli_stmt_close($stmt);
            log_activity($koneksi, $idUser, null, "register", "Pengguna mendaftarkan akun dengan username " . $username);
            redirect("auth/login.php");
        }
    }
}
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Daftar</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/app.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>

<body class="auth">
    <div class="container">
        <div class="card">
            <div class="logo">
                <img src="https://cdn-icons-png.flaticon.com/512/2232/2232688.png" alt="Logo perpustakaan">
            </div>
            <h2>DAFTAR</h2>
            <p>SMK TARUNA BANGSA</p>

            <?php if ($error): ?>
                <p class="error"><?= e($error) ?></p>
            <?php endif; ?>

            <form method="post" class="auth-form">
                <div class="input-group">
                    <div class="input-box">
                        <i class="fa-solid fa-user"></i>
                        <input type="text" name="fullname" placeholder="Masukkan Nama Lengkap" required>
                    </div>
                </div>

                <div class="input-group">
                    <div class="input-box">
                        <i class="fa-solid fa-user-tag"></i>
                        <input type="text" name="username" placeholder="Masukkan Username" required>
                    </div>
                </div>

                <div class="input-group">
                    <div class="password-box">
                        <i class="fa-solid fa-lock left-icon"></i>
                        <input type="password" id="password" name="password" placeholder="Masukkan Password" required>
                        <i class="fa-solid fa-eye right-icon" id="togglePassword"></i>
                    </div>
                </div>

                <div class="input-group">
                    <div class="password-box">
                        <i class="fa-solid fa-lock left-icon"></i>
                        <input type="password" id="confirmPassword" name="confirm" placeholder="Konfirmasi Password" required>
                    </div>
                </div>

                <button type="submit">
                    <i class="fa-solid fa-user-plus"></i>
                    Daftar
                </button>
            </form>

            <div class="divider">atau</div>

            <div class="link">
                Sudah punya akun?
                <a href="login.php">Login</a>
            </div>

            <div class="footer">
                © 2026 Perpustakaan SMK Taruna Bangsa
            </div>
        </div>
    </div>

    <script>
        const password = document.getElementById("password");
        const confirmPassword = document.getElementById("confirmPassword");
        const toggle = document.getElementById("togglePassword");

        if (toggle && password) {
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

        if (confirmPassword && password) {
            password.addEventListener("input", function() {
                confirmPassword.setAttribute("data-password", password.value);
            });
        }
    </script>
</body>

</html>