<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$pesan_sukses = "";
$pesan_error = "";

if (isset($_POST['kosongkan_data'])) {
    // Matikan foreign key check sementara agar proses hapus lancar tanpa error relasi
    mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 0");

    // Daftar tabel yang akan DIKOSONGKAN TOTAL (Data transaksi & operasional)
    $tabel_transaksi = ['jurnal', 'penggajian', 'karyawan'];
    
    $berhasil = true;
    foreach ($tabel_transaksi as $tabel) {
        if (!mysqli_query($conn, "TRUNCATE TABLE $tabel")) {
            $berhasil = false;
            $pesan_error = "Gagal mengosongkan tabel $tabel: " . mysqli_error($conn);
            break;
        }
    }

    // Hidupkan kembali foreign key check setelah selesai
    mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 1");

    if ($berhasil) {
        $pesan_sukses = "Sistem berhasil dibersihkan! Semua data karyawan, penggajian, dan jurnal telah dikosongkan kembali ke angka 0.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Manajemen - Reset</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .navbar-custom { background: linear-gradient(90deg, #1e1e2f, #2b2b55); }
        .card { border: none; border-radius: 8px; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom shadow-sm sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="#"><i class="fa-solid fa-user-gear me-2"></i>PANEL ADMIN</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="fa-solid fa-gauge me-1"></i> Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="karyawan.php"><i class="fa-solid fa-users me-1"></i> Data Karyawan</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle active fw-bold" href="#" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown"><i class="fa-solid fa-folder-open me-1"></i> Fitur Lainnya</a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="penggajian.php"><i class="fa-solid fa-money-check-dollar me-2"></i>Penggajian</a></li>
                        <li><a class="dropdown-item" href="jurnal.php"><i class="fa-solid fa-book me-2"></i>Jurnal</a></li>
                        <li><a class="dropdown-item" href="history.php"><i class="fa-solid fa-clock-rotate-left me-2"></i>History</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container p-4">
    <div class="row justify-content-center">
        <div class="col-md-7">
            
            <h3 class="fw-bold text-dark mb-3"><i class="fa-solid fa-triangle-exclamation text-danger me-2"></i>Pembersihan Database</h3>
            <hr>

            <?php if (!empty($pesan_sukses)): ?>
                <div class="alert alert-success shadow-sm mb-4">
                    <strong>✨ Berhasil!</strong> <?= $pesan_sukses; ?>
                    <div class="mt-2"><a href="dashboard.php" class="btn btn-sm btn-success">Ke Dashboard</a></div>
                </div>
            <?php endif; ?>

            <?php if (!empty($pesan_error)): ?>
                <div class="alert alert-danger shadow-sm">
                    <strong>⚠️ Gagal:</strong> <?= $pesan_error; ?>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm border-start border-danger border-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-danger mb-3">Zona Bahaya (Reset Sistem Baru)</h5>
                    <p class="text-muted small">
                        Fitur ini digunakan sebelum menyerahkan aplikasi ke pemilik asli. Menekan tombol di bawah akan menghapus secara permanen:
                    </p>
                    <ul class="text-muted small">
                        <li>Semua <strong>Data Karyawan</strong> master.</li>
                        <li>Semua <strong>Riwayat Transaksi Penggajian</strong>.</li>
                        <li>Semua log otomatis <strong>Jurnal Akuntansi (Debit/Kredit)</strong>.</li>
                    </ul>
                    <p class="text-dark fw-bold small">Akun Login Admin tidak akan dihapus agar pemilik tetap bisa masuk.</p>
                    
                    <form method="POST" onsubmit="return confirm('APAKAH ANDA YAKIN? Tindakan ini menghapus seluruh data transaksi selamanya dan tidak bisa dibatalkan!')">
                        <div class="d-grid mt-4">
                            <button type="submit" name="kosongkan_data" class="btn btn-danger btn-lg fw-bold">
                                <i class="fa-solid fa-trash-glow me-2"></i>Kosongkan Semua Data & Transaksi
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>