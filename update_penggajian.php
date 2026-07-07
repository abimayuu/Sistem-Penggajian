<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$pesan_error = "";
$pesan_sukses = "";

// Cek apakah ada ID Gaji yang dikirim di URL
if (!isset($_GET['id'])) {
    header("Location: penggajian.php");
    exit;
}

$id_gaji = mysqli_real_escape_string($conn, $_GET['id']);

// 1. AMBIL DATA LAMA YANG AKAN DI-UPDATE (Dihubungkan dengan tabel karyawan via JOIN)
$query_ambil = "
    SELECT penggajian.*, karyawan.nama, karyawan.gaji_pokok 
    FROM penggajian 
    JOIN karyawan ON penggajian.id_karyawan = karyawan.id_karyawan 
    WHERE penggajian.id_gaji = '$id_gaji'
";
$eksekusi_ambil = mysqli_query($conn, $query_ambil);
$data_lama      = mysqli_fetch_assoc($eksekusi_ambil);

// Jika ID gaji tidak ditemukan di database, tendang kembali ke halaman utama
if (!$data_lama) {
    header("Location: penggajian.php");
    exit;
}


if (isset($_POST['update'])) {
    // Ambil input baru (hanya lembur dan potongan yang boleh diedit)
    $lembur_baru   = isset($_POST['lembur']) ? (int)$_POST['lembur'] : 0;
    $potongan_baru = isset($_POST['potongan']) ? (int)$_POST['potongan'] : 0;
    
    // Ambil gaji pokok lama dari hasil query di atas
    $gaji_pokok = (int)$data_lama['gaji_pokok'];

    // Hitung ulang Total Gaji Bersih yang baru
    $total_gaji_baru = $gaji_pokok + $lembur_baru - $potongan_baru;

    // 2. UPDATE TABEL PENGGAJIAN
    $query_update_gaji = "
        UPDATE penggajian 
        SET lembur = '$lembur_baru', 
            potongan = '$potongan_baru', 
            total_gaji = '$total_gaji_baru' 
        WHERE id_gaji = '$id_gaji'
    ";

    if (mysqli_query($conn, $query_update_gaji)) {
        
        // 3. SINKRONISASI OTOMATIS KE TABEL JURNAL (Paling Penting untuk Akuntansi!)
        // Update nominal sisi Debit (Akun Beban Gaji)
        mysqli_query($conn, "
            UPDATE jurnal 
            SET debit = '$total_gaji_baru' 
            WHERE id_gaji = '$id_gaji' AND debit > 0
        ");

        // Update nominal sisi Kredit (Akun Kas/Utang Gaji)
        mysqli_query($conn, "
            UPDATE jurnal 
            SET kredit = '$total_gaji_baru' 
            WHERE id_gaji = '$id_gaji' AND kredit > 0
        ");

        // Set notifikasi sukses dan redirect kembali ke halaman utama penggajian
        header("Location: penggajian.php?sukses_update=1");
        exit;
    } else {
        $pesan_error = "Gagal memperbarui data penggajian: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Data Penggajian</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .navbar-custom {
            background: linear-gradient(90deg, #1e1e2f, #2b2b55);
        }
        .card { border: none; border-radius: 8px; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom shadow-sm sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="#">
            <i class="fa-solid fa-user-gear me-2"></i>PANEL ADMIN
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php"><i class="fa-solid fa-gauge me-1"></i> Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="karyawan.php"><i class="fa-solid fa-users me-1"></i> Data Karyawan</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle active fw-bold" href="#" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-folder-open me-1"></i> Fitur Lainnya
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownMenuLink">
                        <li><a class="dropdown-item bg-primary text-white" href="penggajian.php"><i class="fa-solid fa-money-check-dollar me-2"></i>Penggajian</a></li>
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

<div class="container-fluid p-3 p-md-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="fw-bold text-dark mb-0 fs-4 fs-sm-3">Koreksi Data Penggajian</h3>
                <a href="penggajian.php" class="btn btn-secondary btn-sm"><i class="fa-solid fa-arrow-left me-2"></i>Kembali</a>
            </div>
            <hr>

            <?php if (!empty($pesan_error)): ?>
                <div class="alert alert-danger shadow-sm" role="alert">
                    <strong>⚠️ Error:</strong> <?= $pesan_error; ?>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm bg-white">
                <div class="card-header bg-dark text-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="fa-solid fa-pen-to-square me-2"></i>Form Koreksi Nominal Slip Gaji</h6>
                </div>
                <div class="card-body p-3 p-sm-4">
                    <form method="POST">
                        
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold font-monospace">Nama Karyawan (Permanen)</label>
                            <input type="text" class="form-control bg-light fw-bold" value="<?= htmlspecialchars($data_lama['nama']); ?>" readonly disabled>
                        </div>

                        <div class="row mb-3 g-3">
                            <div class="col-12 col-sm-6">
                                <label class="form-label text-muted small fw-bold font-monospace">Periode Bulan</label>
                                <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($data_lama['bulan']); ?>" readonly disabled>
                            </div>
                            <div class="col-12 col-sm-6">
                                <label class="form-label text-muted small fw-bold font-monospace">Periode Tahun</label>
                                <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($data_lama['tahun']); ?>" readonly disabled>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold font-monospace">Gaji Pokok Master (Rp)</label>
                            <input type="text" class="form-control bg-light text-success fw-semibold" value="Rp <?= number_format($data_lama['gaji_pokok'], 0, ',', '.'); ?>" readonly disabled>
                        </div>

                        <hr class="my-4">

                        <div class="row mb-4 g-3">
                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-bold text-primary"><i class="fa-solid fa-circle-plus me-1"></i> Bonus Lembur Baru (Rp)</label>
                                <input type="number" name="lembur" class="form-control form-control-lg border-primary" value="<?= $data_lama['lembur']; ?>" min="0" required>
                                <div class="form-text">Masukkan angka murni saja tanpa titik/Rp.</div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <label class="form-label fw-bold text-danger"><i class="fa-solid fa-circle-minus me-1"></i> Potongan Gaji Baru (Rp)</label>
                                <input type="number" name="potongan" class="form-control form-control-lg border-danger" value="<?= $data_lama['potongan']; ?>" min="0" required>
                                <div class="form-text">Potongan keterlambatan, kasbon, atau absensi.</div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" name="update" class="btn btn-warning btn-lg fw-bold text-dark">
                                <i class="fa-solid fa-rotate me-2"></i>Simpan Perubahan & Sinkron Jurnal
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
