<?php
require_once __DIR__ . "/../../middleware/user.php";
require_once __DIR__ . "/../../config/koneksi.php";

$idUser = (int)$_SESSION["id_user"];
$packageOptions = get_premium_packages();
$selectedPackageKey = $_POST["paket_premium"] ?? "1-bulan";
$selectedPackage = null;
foreach ($packageOptions as $package) {
    if ($package["id"] === $selectedPackageKey) {
        $selectedPackage = $package;
        break;
    }
}
if ($selectedPackage === null) {
    $selectedPackage = $packageOptions[0];
}
$nominal = (int)$selectedPackage["price"];
$durasi = (int)$selectedPackage["days"];

// cek status premium saat ini
$stmt = mysqli_prepare($koneksi, "SELECT is_premium, tanggal_premium_hingga FROM tbl_user WHERE id_user=?");
mysqli_stmt_bind_param($stmt, "i", $idUser);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

$isPremium = false;
$premiumExpiry = null;
$premiumExpiryDisplay = null;
if ($user && $user["is_premium"]) {
    $today = new DateTime("today");
    $expiryDate = new DateTime($user["tanggal_premium_hingga"]);
    if ($today <= $expiryDate) {
        $isPremium = true;
        $premiumExpiry = $user["tanggal_premium_hingga"];
        $bulanIndonesia = [1 => "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
        $premiumExpiryDisplay = $expiryDate->format("d") . " " . $bulanIndonesia[(int)$expiryDate->format("n")] . " " . $expiryDate->format("Y");
    }
}

// proses pembayaran
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["proses_pembayaran"])) {
    mysqli_begin_transaction($koneksi);
    try {
        $paketPilihan = trim($_POST["paket_premium"] ?? "");
        $availablePackageIds = array_map(static fn($package) => $package["id"], $packageOptions);
        if (!in_array($paketPilihan, $availablePackageIds, true)) {
            throw new Exception("Pilih paket premium terlebih dahulu.");
        }

        $selectedPackage = null;
        foreach ($packageOptions as $package) {
            if ($package["id"] === $paketPilihan) {
                $selectedPackage = $package;
                break;
            }
        }

        if ($selectedPackage === null) {
            throw new Exception("Paket yang dipilih tidak valid.");
        }

        $metodePilihan = trim($_POST["metode_pembayaran"] ?? "");
        $metodeTersedia = ["DANA", "OVO", "GoPay", "ShopeePay"];
        if (!in_array($metodePilihan, $metodeTersedia, true)) {
            throw new Exception("Pilih metode pembayaran terlebih dahulu.");
        }

        $nominal = (int)$selectedPackage["price"];
        $durasi = (int)$selectedPackage["days"];
        $metode = $metodePilihan;
        $status = "pending";
        $stmt = mysqli_prepare($koneksi, "INSERT INTO tbl_pembayaran (id_user, nominal, metode_pembayaran, status, durasi_hari) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iissi", $idUser, $nominal, $metode, $status, $durasi);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        log_activity($koneksi, $idUser, null, "pembayaran_pending", "Inisiasi pembayaran premium - Rp " . number_format($nominal) . " (menunggu konfirmasi)");

        mysqli_commit($koneksi);
        flash("Permintaan pembayaran Anda telah dibuat. Silakan transfer sesuai instruksi dan tunggu approval admin.", "success");
        redirect("user/pembayaran/index.php");
    } catch (Throwable $e) {
        mysqli_rollback($koneksi);
        flash("Gagal memproses pembayaran: " . $e->getMessage(), "error");
    }
}
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Beli Premium</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/app.css?v=<?= filemtime(__DIR__ . "/../../css/app.css") ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/footer.css?v=<?= filemtime(__DIR__ . "/../../css/footer.css") ?>">
    <style>
        .premium-container { max-width: 820px; margin: 28px auto 44px; padding: 0 18px; }
        .premium-container > h1 { margin: 0 0 22px; color: #173042; font-size: clamp(1.7rem, 3vw, 2.3rem); }
        .premium-card { background: #176b69; color: white; padding: 26px 30px; border-radius: 12px; margin-bottom: 18px; box-shadow: 0 12px 28px rgba(23, 107, 105, 0.18); }
        .premium-card h2 { margin: 0 0 12px; font-size: 1.45rem; }
        .features { list-style: none; padding: 0; margin: 0; }
        .features li { padding: 8px 0; border-bottom: 1px solid rgba(255,255,255,0.18); }
        .features li:before { content: "✓"; margin-right: 10px; font-weight: bold; }
        .price { font-size: 1.8em; margin: 18px 0 4px; font-weight: 700; }
        .info-box { background: #f4f7f7; padding: 14px 16px; border-radius: 7px; margin: 14px 0; line-height: 1.55; }
        .package-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; margin: 18px 0 20px; }
        .package-option { position: relative; display: flex; min-height: 120px; flex-direction: column; justify-content: space-between; gap: 6px; padding: 12px; border: 1px solid #d6e0df; border-radius: 10px; background: #fff; color: #173042; cursor: pointer; transition: all .2s ease; box-shadow: 0 6px 14px rgba(18, 26, 34, 0.04); }
        .package-option:hover { border-color: #176b69; transform: translateY(-1px); }
        .package-option input { position: absolute; opacity: 0; pointer-events: none; }
        .package-option.selected { border-color: #176b69; background: #edf8f7; box-shadow: 0 12px 22px rgba(23, 107, 105, 0.12); }
        .package-option__header { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
        .package-option__label { font-size: .9rem; font-weight: 700; }
        .package-option__discount { display: inline-flex; padding: 3px 6px; border-radius: 999px; background: #d7f5ec; color: #0d5d52; font-size: .65rem; font-weight: 700; }
        .package-option__price { font-size: .95rem; font-weight: 800; color: #173042; }
        .package-option__meta { color: #5b6f7d; font-size: .7rem; }
        @media (max-width: 520px) { .package-grid { grid-template-columns: 1fr; } }
        .premium-container form.card { padding: 22px; }
        .premium-container form.card > div { margin-top: 14px !important; margin-bottom: 14px !important; }
        .premium-container form.card > div:first-child { padding: 14px !important; }
        .premium-container form.card button { background: #176b69 !important; border-radius: 7px; border: 0; }
        .premium-container form.card button:hover { background: #125654 !important; }
        .premium-btn,
        .premium-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 10px 18px;
            border: 0;
            border-radius: 7px;
            font: inherit;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: background-color .2s ease, transform .2s ease, box-shadow .2s ease;
        }
        .premium-btn {
            width: 100%;
            color: #fff;
            background: #176b69 !important;
        }
        .premium-btn:hover {
            background: #125654 !important;
            transform: translateY(-1px);
            box-shadow: 0 5px 14px rgba(23, 107, 105, .2);
        }
        .premium-link {
            color: #52636c;
            background: #edf2f1;
            border: 1px solid #d5e0de;
        }
        .premium-link:hover { color: #173042; background: #e2ebea; }
        .premium-actions { display: flex; justify-content: center; margin-top: 16px; }
        .payment-method-preview { color: #176b69; font-weight: 700; }
        .payment-modal[hidden] { display: none; }
        .payment-modal { position: fixed; inset: 0; z-index: 20; display: grid; place-items: center; padding: 18px; background: rgba(23, 48, 66, .48); }
        .payment-dialog { width: min(420px, 100%); padding: 24px; background: #fff; border-radius: 12px; box-shadow: 0 20px 50px rgba(23, 48, 66, .25); }
        .payment-dialog h2 { margin: 0 0 6px; color: #173042; font-size: 1.35rem; }
        .payment-dialog p { margin: 0 0 18px; color: #667780; }
        .method-list { display: grid; gap: 9px; }
        .method-option { display: flex; align-items: center; gap: 10px; padding: 12px 14px; border: 1px solid #d5e0de; border-radius: 8px; cursor: pointer; color: #173042; }
        .method-option:hover, .method-option:has(input:checked) { border-color: #176b69; background: #edf7f5; }
        .method-option input { accent-color: #176b69; }
        .modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px; }
        .modal-actions .premium-btn { width: auto; }
        @media (max-width: 600px) {
            .premium-container { margin-top: 22px; padding: 0 14px; }
            .premium-card { padding: 22px 20px; }
            .premium-container form.card { padding: 16px; }
        }
    </style>
</head>

<body class="user-page">
    <?php render_flash(); ?>
    <?php include __DIR__ . "/../partials/header.php"; ?>
    
    <main class="premium-container">
        <div class="premium-card">
            <h2>Akses Tanpa Batas</h2>
            <ul class="features">
                <li>Baca semua buku tanpa batasan halaman</li>
                <li>Akses 1000+ buku dalam koleksi</li>
                <li>Download PDF untuk dibaca offline</li>
                <li>Dukungan pelanggan prioritas</li>
                <li>Akses selamanya selama aktif</li>
            </ul>
            <div class="price">Rp <?= number_format($nominal) ?></div>
            <small>Durasi: <?= $durasi ?> hari</small>
        </div>
        
        <form method="POST" class="card">
            <div class="package-grid">
                <?php foreach ($packageOptions as $package): ?>
                    <?php $isSelected = $package["id"] === $selectedPackage["id"]; ?>
                    <label class="package-option <?= $isSelected ? 'selected' : '' ?>" data-package-id="<?= e($package["id"]) ?>">
                        <input type="radio" name="paket_premium" value="<?= e($package["id"]) ?>" <?= $isSelected ? 'checked' : '' ?>>
                        <span class="package-option__header">
                            <span class="package-option__label"><?= e($package["label"]) ?></span>
                            <?php if ((int)$package["discount_percent"] > 0): ?>
                                <span class="package-option__discount">Hemat <?= (int)$package["discount_percent"] ?>%</span>
                            <?php endif; ?>
                        </span>
                        <span class="package-option__price">Rp <?= number_format((int)$package["price"]) ?></span>
                        <span class="package-option__meta"><?= (int)$package["days"] ?> hari</span>
                    </label>
                <?php endforeach; ?>
            </div>
            <button type="button" class="premium-btn" id="openPaymentModal">
                Bayar Sekarang - Rp <?= number_format($nominal) ?>
            </button>
            
            <div class="premium-actions">
                <a href="<?= BASE_URL ?>/user/buku/index.php" class="premium-link">Kembali ke Katalog</a>
            </div>
        </form>

        <div class="payment-modal" id="paymentModal" hidden>
            <div class="payment-dialog" role="dialog" aria-modal="true" aria-labelledby="paymentTitle">
                <h2 id="paymentTitle">Pilih metode pembayaran</h2>
                <p>Total Rp <?= number_format($nominal) ?> untuk <?= $durasi ?> hari. Pilih e-wallet yang akan digunakan.</p>
                <div class="method-list">
                    <label class="method-option"><input type="radio" name="payment_method_choice" value="DANA"> DANA</label>
                    <label class="method-option"><input type="radio" name="payment_method_choice" value="OVO"> OVO</label>
                    <label class="method-option"><input type="radio" name="payment_method_choice" value="GoPay"> GoPay</label>
                    <label class="method-option"><input type="radio" name="payment_method_choice" value="ShopeePay"> ShopeePay</label>
                </div>
                <p class="payment-dialog__instruction">Transfer ke <strong><?= ADMIN_EWALLET ?></strong> setelah melanjutkan.</p>
                <div class="modal-actions">
                    <button type="button" class="premium-link" id="closePaymentModal">Batal</button>
                    <button type="button" class="premium-btn" id="confirmPaymentMethod">Lanjutkan</button>
                </div>
            </div>
        </div>
        
    </main>
    
    <?php include __DIR__ . "/../partials/footer.php"; ?>
    <script>
        const paymentForm = document.querySelector('form[method="POST"]');
        const paymentModal = document.getElementById("paymentModal");
        const packageOptions = document.querySelectorAll('.package-option');
        const priceDisplay = document.querySelector('.price');
        const buyButton = document.getElementById('openPaymentModal');
        const modalSummary = document.querySelector('#paymentModal p');

        const packageMap = {
            '1-bulan': { price: 14999, days: 30 },
            '3-bulan': { price: 39999, days: 90 },
            '6-bulan': { price: 59999, days: 180 },
            '12-bulan': { price: 79999, days: 365 }
        };

        const updateSelection = (selectedId) => {
            const chosen = packageMap[selectedId] || packageMap['1-bulan'];
            priceDisplay.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(chosen.price);
            buyButton.textContent = 'Bayar Sekarang - Rp ' + new Intl.NumberFormat('id-ID').format(chosen.price);
            if (modalSummary) {
                modalSummary.textContent = 'Total Rp ' + new Intl.NumberFormat('id-ID').format(chosen.price) + ' untuk ' + chosen.days + ' hari. Pilih e-wallet yang akan digunakan.';
            }
            packageOptions.forEach((option) => {
                option.classList.toggle('selected', option.dataset.packageId === selectedId);
            });
        };

        packageOptions.forEach((option) => {
            option.addEventListener('click', () => {
                const radio = option.querySelector('input[name="paket_premium"]');
                if (radio) {
                    radio.checked = true;
                    updateSelection(option.dataset.packageId);
                }
            });
        });

        document.querySelectorAll('input[name="paket_premium"]').forEach((radio) => {
            radio.addEventListener('change', () => {
                const selectedPackage = radio.closest('.package-option');
                if (selectedPackage) {
                    updateSelection(selectedPackage.dataset.packageId);
                }
            });
        });

        document.getElementById("openPaymentModal").addEventListener("click", () => {
            paymentModal.hidden = false;
        });

        document.getElementById("closePaymentModal").addEventListener("click", () => {
            paymentModal.hidden = true;
        });

        document.getElementById("confirmPaymentMethod").addEventListener("click", () => {
            const selected = document.querySelector('input[name="payment_method_choice"]:checked');
            if (!selected) {
                alert("Silakan pilih metode pembayaran.");
                return;
            }

            const hiddenInput = document.createElement("input");
            hiddenInput.type = "hidden";
            hiddenInput.name = "metode_pembayaran";
            hiddenInput.value = selected.value;
            paymentForm.appendChild(hiddenInput);
            paymentModal.hidden = true;

            const submitInput = document.createElement("input");
            submitInput.type = "hidden";
            submitInput.name = "proses_pembayaran";
            submitInput.value = "1";
            paymentForm.appendChild(submitInput);
            paymentForm.submit();
        });

        paymentModal.addEventListener("click", (event) => {
            if (event.target === paymentModal) paymentModal.hidden = true;
        });
    </script>
</body>

</html>
