<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

/* ======================
   QUERY STATISTIK
====================== */
$karyawan = mysqli_query($conn, "SELECT COUNT(*) as total FROM karyawan WHERE status='Aktif'");
$data_karyawan = mysqli_fetch_assoc($karyawan);

$gaji = mysqli_query($conn, "SELECT COUNT(*) as total FROM penggajian");
$data_gaji = mysqli_fetch_assoc($gaji);

$total_pengeluaran = mysqli_query($conn, "SELECT SUM(total_gaji) as total FROM penggajian");
$data_pengeluaran = mysqli_fetch_assoc($total_pengeluaran);
$grand_total_gaji = $data_pengeluaran['total'] ? $data_pengeluaran['total'] : 0;

/* ======================
   QUERY GRAFIK
====================== */
$grafik = mysqli_query($conn, "
    SELECT bulan, tahun, SUM(total_gaji) as total 
    FROM penggajian 
    GROUP BY tahun, bulan
    ORDER BY id_gaji ASC LIMIT 12
");

$bulan = [];
$total = [];
while($row = mysqli_fetch_assoc($grafik)){
    $bulan[] = $row['bulan'] . ' ' . $row['tahun'];
    $total[] = $row['total'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Manajemen Penggajian - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar-custom {
            background: linear-gradient(90deg, #1e1e2f, #2b2b55);
        }
        .card-stat {
            border: none;
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        .card-stat:hover {
            transform: translateY(-5px);
        }
        .chart-card {
            border: none;
            border-radius: 8px;
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
                    <a class="nav-link active fw-bold" href="dashboard.php"><i class="fa-solid fa-gauge me-1"></i> Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="karyawan.php"><i class="fa-solid fa-users me-1"></i> Data Karyawan</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-folder-open me-1"></i> Fitur Lainnya
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownMenuLink">
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

<div class="container-fluid p-3 p-md-4">
    <div class="row">
        <div class="col-12">
            
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
                <h3 class="fw-bold text-dark mb-0 fs-4 fs-sm-3">Ringkasan Sistem Informasi</h3>
                <span class="badge bg-primary px-3 py-2 font-monospace"><?= date('d F Y'); ?></span>
            </div>
            <hr>

            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="card card-stat shadow-sm border-start border-primary border-4 bg-white">
                        <div class="card-body d-flex justify-content-between align-items-center p-4">
                            <div>
                                <h6 class="text-muted text-uppercase small fw-bold mb-1">Karyawan Aktif</h6>
                                <h2 class="fw-bold mb-0 text-dark"><?= $data_karyawan['total']; ?> <span class="fs-6 text-muted fw-normal">Orang</span></h2>
                            </div>
                            <div class="bg-primary-subtle p-3 rounded-circle text-primary">
                                <i class="fa-solid fa-users fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-4">
                    <div class="card card-stat shadow-sm border-start border-success border-4 bg-white">
                        <div class="card-body d-flex justify-content-between align-items-center p-4">
                            <div>
                                <h6 class="text-muted text-uppercase small fw-bold mb-1">Total Transaksi Slip</h6>
                                <h2 class="fw-bold mb-0 text-dark"><?= $data_gaji['total']; ?> <span class="fs-6 text-muted fw-normal">Kali</span></h2>
                            </div>
                            <div class="bg-success-subtle p-3 rounded-circle text-success">
                                <i class="fa-solid fa-receipt fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="card card-stat shadow-sm border-start border-danger border-4 bg-white">
                        <div class="card-body d-flex justify-content-between align-items-center p-4">
                            <div>
                                <h6 class="text-muted text-uppercase small fw-bold mb-1">Kas Gaji Terbayar</h6>
                                <h3 class="fw-bold mb-0 text-dark fs-4 fs-sm-3">Rp <?= number_format($grand_total_gaji, 0, ',', '.'); ?></h3>
                            </div>
                            <div class="bg-danger-subtle p-3 rounded-circle text-danger">
                                <i class="fa-solid fa-wallet fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card chart-card shadow-sm border-0 bg-white">
                <div class="card-header bg-dark text-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="fa-solid fa-chart-bar me-2"></i>Grafik Total Pengeluaran Gaji per Periode</h6>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div style="position: relative; height:340px; width: 100%;">
                        <canvas id="chartGaji"></canvas>
                    </div>
                </div>
            </div>

        </div> 
    </div> 
</div> 

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
var ctx = document.getElementById('chartGaji').getContext('2d');
var chart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($bulan); ?>,
        datasets: [{
            label: 'Anggaran Keluar (Rp)',
            data: <?= json_encode($total); ?>,
            backgroundColor: 'rgba(78, 115, 223, 0.85)',
            borderColor: 'rgba(78, 115, 223, 1)',
            borderWidth: 1,
            borderRadius: 6,
            barThickness: window.innerWidth < 768 ? 15 : 35
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(0, 0, 0, 0.05)' },
                ticks: {
                    font: { family: 'monospace', size: window.innerWidth < 768 ? 10 : 12 },
                    callback: function(value) {
                        if (window.innerWidth < 768 && value >= 1000000) {
                            return 'Rp ' + (value / 1000000).toFixed(0) + 'jt';
                        }
                        return 'Rp ' + value.toLocaleString('id-ID');
                    }
                }
            }
        },
        plugins: {
            legend: {
                display: true,
                position: 'top',
                labels: { font: { weight: 'bold', size: window.innerWidth < 768 ? 11 : 12 } }
            }
        }
    }
});
</script>
</body>
</html>