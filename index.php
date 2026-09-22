<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perpustakaan Digital</title>
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header class="topbar">
        <a class="brand" href="./index.php">
            <span class="brand-mark"><i class="fa-solid fa-book-open" aria-hidden="true"></i></span>
            <span>PERPUS</span>
        </a>
        <nav class="nav" aria-label="Navigasi utama">
            <a href="#" class="nav-item active">Beranda</a>
            <a href="#fitur" class="nav-item">Fitur</a>
            <a href="./auth/login.php" class="nav-item nav-login">Masuk</a>
            <a href="./auth/register.php" class="nav-item nav-register">Daftar</a>
        </nav>
    </header>

    <section class="hero">
        <div class="overlay"></div>
        <div class="hero-content">
            <p class="eyebrow">RUANG BACA DIGITAL</p>
            <h1>Temukan buku yang ingin kamu baca.</h1>
            <p>Jelajahi koleksi digital, baca preview gratis, dan nikmati akses penuh dengan akun premium.</p>
            <div class="hero-actions">
                <a href="./auth/register.php" class="button button-primary">Mulai Membaca <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                <a href="./auth/login.php" class="button button-ghost">Saya sudah punya akun</a>
            </div>
        </div>
    </section>

    <main class="container" id="fitur">
        <div class="section-heading">
            <p class="eyebrow">DIBUAT UNTUK PEMBACA</p>
            <h2>Satu tempat untuk perjalanan bacamu.</h2>
            <p>Mulai dari preview singkat sampai membaca buku secara penuh dengan pengalaman yang lebih nyaman.</p>
        </div>
        <div class="feature-grid">
            <article class="feature-card">
                <span class="feature-icon"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></span>
                <h3>Koleksi mudah ditemukan</h3>
                <p>Cari buku berdasarkan judul, penulis, atau kategori dalam katalog yang tertata.</p>
            </article>
            <article class="feature-card feature-card--accent">
                <span class="feature-icon"><i class="fa-solid fa-book-open-reader" aria-hidden="true"></i></span>
                <h3>Preview sebelum membaca</h3>
                <p>Lihat beberapa halaman pertama sebelum memilih buku yang ingin kamu baca.</p>
            </article>
            <article class="feature-card">
                <span class="feature-icon"><i class="fa-solid fa-crown" aria-hidden="true"></i></span>
                <h3>Akses premium penuh</h3>
                <p>Baca seluruh isi koleksi premium dengan paket yang sesuai kebutuhanmu.</p>
            </article>
        </div>
    </main>

    <?php include __DIR__ . "/user/partials/footer.php"; ?>
</body>
</html>
