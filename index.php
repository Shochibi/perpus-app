<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perpustakaan Digital</title>
    <link rel="stylesheet" href="./css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<!-- ================= NAVBAR ================= -->
<div class="topbar">
    <div class="brand">
        <img src="img/logo.png" alt="Logo">
        <h1>Perpustakaan Online</h1>
    </div>

    <div class="nav">
        <a href="#" class="nav-item active">Beranda</a>
        <a href="./auth/login.php" class="nav-item">Login</a>
    </div>
</div>

<!-- ================= HERO ================= -->
<section class="hero" style="background-image: url('./img/bg-library.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat;">
    <div class="overlay"></div>
    <div class="hero-content">
        <h1>Selamat Datang di Perpustakaan Online</h1>
        <p>Perpustakaan Online adalah platform modern untuk menemukan koleksi buku, informasi perpustakaan, dan literasi digital tanpa fitur interaktif tambahan.</p>
    </div>
</section>

<!-- ================= ABOUT ================= -->
<div class="container">
    <div class="judul-section">Tentang Perpustakaan</div>
    <div class="book-grid">
        <div class="book-card">
            <h3>Visi</h3>
            <p>Menghadirkan pengalaman literasi yang mudah diakses dan informatif untuk semua pembaca.</p>
        </div>
        <div class="book-card">
            <h3>Misi</h3>
            <p>Menyediakan akses informasi perpustakaan digital dengan tampilan yang bersih dan menarik.</p>
        </div>
        <div class="book-card">
            <h3>Manfaat</h3>
            <p>Memberi tahu pengunjung tentang koleksi, layanan, dan kegiatan perpustakaan lewat UI yang sederhana.</p>
        </div>
        <div class="book-card">
            <h3>Tujuan</h3>
            <p>Meningkatkan minat baca dan memudahkan pengguna menemukan informasi perpustakaan.</p>
        </div>
    </div>
</div>
</body>
<?php include __DIR__ . "/user/partials/footer.php"; ?>
</html>
