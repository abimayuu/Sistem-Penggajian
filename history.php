<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Daftar bulan untuk looping filter
$daftar_bulan = [
    "Januari", "Februari", "Maret", "April", "Mei", "Juni", 
    "Juli", "Agustus", "September", "Oktober", "November", "Desember"
];

// Inisialisasi Filter
$where = "WHERE 1=1";
$bulan_pilihan = isset($_GET['bulan']) ? $_GET['bulan'] : '';
$tahun_pilihan = isset($_GET['tahun']) ? $_GET['tahun'] : '';

if (!empty($bulan_pilihan)) {
    $bulan = mysqli_real_escape_string($conn, $bulan_pilihan);
    $where .= " AND penggajian.bulan='$bulan'";
}

if (!empty($tahun_pilihan)) {
    $tahun = mysqli_real_escape_string($conn, $tahun_pilihan);
    $where .= " AND penggajian.tahun='$tahun'";
}

// Ambil data history penggajian
$data = mysqli_query($conn, "
    SELECT penggajian.*, karyawan.nama, karyawan.gaji_pokok 
    FROM penggajian 
    JOIN karyawan ON penggajian.id_karyawan = karyawan.id_karyawan
    $where
    ORDER BY id_gaji DESC
");

if (!$data) {
    die("Error ambil data: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Manajemen Penggajian - History</title>
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
                        <li><a class="dropdown-item" href="jurnal.php"><i class="fa-solid fa-book me-2"></i>Jurnal</a></li>
                        <li><a class="dropdown-item bg-primary text-white" href="history.php"><i class="fa-solid fa-clock-rotate-left me-2"></i>History</a></li>
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
                <h3 class="fw-bold text-dark mb-0 fs-4 fs-sm-3">Arsip & History Penggajian</h3>
                <span class="badge bg-secondary px-3 py-2 fs-7">Log Pencarian Cepat</span>
            </div>
            <hr>

            <div class="card shadow-sm mb-4 bg-white">
                <div class="card-body py-3">
                    <form method="GET" class="row g-3 align-items-center">
                        <div class="col-12 col-md-4">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-muted font-monospace small">Bulan</span>
                                <select name="bulan" class="form-select fw-bold">
                                    <option value="">-- Semua Bulan --</option>
                                    <?php foreach ($daftar_bulan as $bln) : ?>
                                        <option value="<?= $bln; ?>" <?= ($bulan_pilihan == $bln) ? 'selected' : ''; ?>><?= $bln; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-muted font-monospace small">Tahun</span>
                                <select name="tahun" class="form-select fw-bold">
                                    <option value="">-- Semua Tahun --</option>
                                    <?php for ($i = 2025; $i <= 2030; $i++) : ?>
                                        <option value="<?= $i; ?>" <?= ($tahun_pilihan == $i) ? 'selected' : ''; ?>><?= $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm px-3 fw-bold w-50">
                                <i class="fa-solid fa-magnifying-glass me-1"></i> Cari Data
                            </button>
                            <?php if (!empty($bulan_pilihan) || !empty($tahun_pilihan)): ?>
                                <a href="history.php" class="btn btn-outline-secondary btn-sm px-3 w-50">
                                    <i class="fa-solid fa-rotate-left me-1"></i> Reset
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm bg-white">
                <div class="card-header bg-dark text-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="fa-solid fa-clock-history me-2"></i>Log Riwayat Penerbitan Slip Gaji</h6>
                </div>
                <div class="card-body px-0 py-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead class="table-light border-bottom">
                                <tr>
                                    <th class="text-center" style="width: 5%">No</th>
                                    <th>Nama Karyawan</th>
                                    <th class="text-center" style="width: 20%">Periode Gaji</th>
                                    <th class="text-center" style="width: 20%">Tanggal Pembuatan</th>
                                    <th class="text-end" style="width: 20%">Total Gaji Bersih</th>
                                    <th class="text-center" style="width: 15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>

                            <?php if (mysqli_num_rows($data) > 0) : ?>
                                <?php $no = 1; ?>
                                <?php while ($row = mysqli_fetch_assoc($data)) : ?>
                                    <tr>
                                        <td class="text-center font-monospace text-muted"><?= $no++; ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($row['nama']); ?></strong>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border px-3 py-2">
                                                <i class="fa-regular fa-calendar-check me-1 text-primary"></i> 
                                                <?= htmlspecialchars($row['bulan'] . ' ' . $row['tahun']); ?>
                                            </span>
                                        </td>
                                        <td class="text-center text-muted font-monospace small">
                                            <?= date('d-m-Y H:i', strtotime($row['tanggal_input'])); ?>
                                        </td>
                                        <td class="text-end fw-bold text-success font-monospace">
                                            Rp <?= number_format($row['total_gaji'], 0, ',', '.'); ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="slip_gaji.php?id=<?= $row['id_gaji']; ?>" 
                                               class="btn btn-outline-primary btn-sm px-3" 
                                               target="_blank">
                                                <i class="fa-solid fa-print me-1"></i> Cetak Slip
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="fa-solid fa-folder-open d-block fs-3 mb-2 text-secondary"></i>
                                        Tidak ditemukan rekaman riwayat penggajian pada periode pencarian ini.
                                    </td>
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

</body>
</html>