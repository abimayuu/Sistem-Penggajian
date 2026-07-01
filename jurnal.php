<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// =====================
// HAPUS JURNAL
// =====================
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];

    if (!mysqli_query($conn, "DELETE FROM jurnal WHERE id_jurnal='$id'")) {
        die("Error hapus jurnal: " . mysqli_error($conn));
    }

    header("Location: jurnal.php?sukses_hapus=1");
    exit;
}

// =====================
// AMBIL DATA JURNAL
// =====================
$jurnal = mysqli_query($conn, "
    SELECT * FROM jurnal 
    ORDER BY id_jurnal DESC
");

if (!$jurnal) {
    die("Error ambil data: " . mysqli_error($conn));
}

$pesan_sukses = "";
if (isset($_GET['sukses_hapus'])) {
    $pesan_sukses = "Data rekaman jurnal akuntansi berhasil dihapus!";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Manajemen Keuangan - Jurnal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar-custom {
            background: linear-gradient(90deg, #1e1e2f, #2b2b55);
        }
        .card {
            border: none;
            border-radius: 8px;
        }
        .table th {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .table td {
            font-size: 0.9rem;
        }
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
                        <li><a class="dropdown-item" href="penggajian.php"><i class="fa-solid fa-money-check-dollar me-2"></i>Penggajian</a></li>
                        <li><a class="dropdown-item bg-primary text-white" href="jurnal.php"><i class="fa-solid fa-book me-2"></i>Jurnal</a></li>
                        <li><a class="dropdown-item" href="history.php"><i class="fa-solid fa-clock-rotate-left me-2"></i>History</a></li>
                        <li><a class="dropdown-item text-warning fw-bold" href="reset_sistem.php"><i class="fa-solid fa-dumpster me-2"></i>Reset Database</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container-fluid p-3 p-md-4">
    <div class="row">
        <div class="col-12">
            
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-3 gap-2">
                <h3 class="fw-bold text-dark mb-0 fs-4 fs-sm-3">Jurnal Penggajian Otomatis</h3>
                <span class="badge bg-secondary px-3 py-2 fs-7">Buku Besar Pembukuan</span>
            </div>
            <hr>

            <?php if (!empty($pesan_sukses)): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <strong>✨ Berhasil!</strong> <?= $pesan_sukses; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm bg-white">
                <div class="card-header bg-dark text-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="fa-solid fa-folder-open me-2"></i>Riwayat Transaksi Debit & Kredit</h6>
                </div>
                <div class="card-body px-0 py-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead class="table-light border-bottom">
                                <tr>
                                    <th class="text-center" style="width: 5%">No</th>
                                    <th class="text-center" style="width: 15%">Tanggal Input</th>
                                    <th>Keterangan Transaksi</th>
                                    <th class="text-end" style="width: 15%">Debit (+)</th>
                                    <th class="text-end" style="width: 15%">Kredit (-)</th>
                                    <th class="text-center" style="width: 10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>

                            <?php if (mysqli_num_rows($jurnal) > 0) : ?>
                                <?php $no = 1; ?>
                                <?php while ($row = mysqli_fetch_assoc($jurnal)) : ?>
                                    <tr>
                                        <td class="text-center font-monospace text-muted"><?= $no++; ?></td>
                                        <td class="text-center font-monospace small text-muted">
                                            <?= date('d-m-Y H:i', strtotime($row['tanggal'])); ?>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($row['keterangan']); ?></strong>
                                        </td>
                                        <td class="text-end fw-bold <?= ($row['debit'] > 0) ? 'text-success' : 'text-muted'; ?>">
                                            Rp <?= number_format($row['debit'], 0, ',', '.'); ?>
                                        </td>
                                        <td class="text-end fw-bold <?= ($row['kredit'] > 0) ? 'text-danger' : 'text-muted'; ?>">
                                            Rp <?= number_format($row['kredit'], 0, ',', '.'); ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="?hapus=<?= $row['id_jurnal']; ?>" 
                                               class="btn btn-outline-danger btn-sm"
                                               onclick="return confirm('Peringatan! Menghapus data jurnal secara manual dapat merusak keseimbangan (balance) laporan keuangan. Yakin tetap hapus?')">
                                                <i class="fa-solid fa-trash-can"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">Belum ada rekaman data entri jurnal akuntansi.</td>
                                </tr>
                            <?php endif; ?>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div> 
    </div> 
</div> 

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // URL Cleanup Script: Membersihkan parameter setelah sukses menghapus data
    if (window.location.search.indexOf('sukses_hapus=1') > -1) {
        window.history.replaceState({}, document.title, window.location.pathname);
    }
</script>

</body>
</html>