<?php
session_start();
include "../config/koneksi.php";

if(isset($_POST['login'])){

    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = mysqli_query($koneksi,"SELECT * FROM tbl_user WHERE username='$username' AND password='$password'");

    if(mysqli_num_rows($query) > 0){

        $data = mysqli_fetch_assoc($query);

        $_SESSION['id_user'] = $data['id_user'];
        $_SESSION['fullname'] = $data['fullname'];
        $_SESSION['role'] = $data['role'];

        echo "<script>
                alert('Login Berhasil');
                window.location='../admin/dashboard.php';
              </script>";

    }else{

        echo "<script>
                alert('Username atau Password Salah');
              </script>";

    }

}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="css/loginregis.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>
<body>

<div class="container">

    <div class="card">

        <div class="logo">
            <img src="https://cdn-icons-png.flaticon.com/512/2232/2232688.png">
        </div>

        <div>
        <h2>PERPUSTAKAAN</h2>
        <p>SMK TARUNA BANGSA</p>
        </div>

        <form method="POST">

            <div class="input-group">

                <div class="input-box">
                    <i class="fa-solid fa-user-tag"></i>
                    <input type="text" name="username" placeholder="Masukkan Username" required>
                </div>
            </div>

            <div class="input-group">

                <div class="password-box">

                    <i class="fa-solid fa-lock left-icon"></i>

                    <input type="password"
                        id="password"
                        name="password"
                        placeholder="Masukkan Password"
                        required>

                    <i class="fa-solid fa-eye right-icon"
                        id="togglePassword"></i>

                </div>
            </div>

            <button type="submit" name="login">Masuk</button>

        </form>

        <div class="divider">atau</div>

        <div class="link">
            Belum punya akun?
            <a href="regis.php">Daftar</a>
        </div>

    </div>

</div>

<script>
const password = document.getElementById("password");
const toggle = document.getElementById("togglePassword");

toggle.addEventListener("click", function(){

    if(password.type === "password"){

        password.type = "text";
        toggle.classList.remove("fa-eye");
        toggle.classList.add("fa-eye-slash");

    }else{

        password.type = "password";
        toggle.classList.remove("fa-eye-slash");
        toggle.classList.add("fa-eye");

    }

});
</script>

</body>
</html>