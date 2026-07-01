<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// ==========================================
// PROSES TAMBAH DATA (SECURE INTEGRATION)
// ==========================================
if (isset($_POST['simpan'])) {
    // Mengamankan input data dari karakter berbahaya (SQL Injection)
    $nama          = mysqli_real_escape_string($conn, $_POST['nama']);
    $jabatan       = mysqli_real_escape_string($conn, $_POST['jabatan']);
    $kontak        = mysqli_real_escape_string($conn, $_POST['kontak']);
    $email         = mysqli_real_escape_string($conn, $_POST['email']);
    $alamat        = mysqli_real_escape_string($conn, $_POST['alamat']);
    $gaji_pokok    = (int)$_POST['gaji_pokok'];
    $tanggal_masuk = mysqli_real_escape_string($conn, $_POST['tanggal_masuk']);

    $query_tambah = "INSERT INTO karyawan 
        (nama, jabatan, kontak, email, alamat, gaji_pokok, status, tanggal_masuk)
        VALUES 
        ('$nama', '$jabatan', '$kontak', '$email', '$alamat', '$gaji_pokok', 'Aktif', '$tanggal_masuk')";

    mysqli_query($conn, $query_tambah);
    header("Location: karyawan.php");
    exit;
}

// ==========================================
// PROSES NONAKTIFKAN KARYAWAN
// ==========================================
if (isset($_GET['nonaktif'])) {
    $id = intval($_GET['nonaktif']);

    mysqli_query($conn, "UPDATE karyawan 
        SET status='Nonaktif', tanggal_keluar=CURDATE() 
        WHERE id_karyawan='$id'");

    header("Location: karyawan.php");
    exit;
}

// ==========================================
// PROSES AKTIFKAN KEMBALI
// ==========================================
if (isset($_GET['aktif'])) {
    $id = intval($_GET['aktif']);

    mysqli_query($conn, "UPDATE karyawan 
        SET status='Aktif', tanggal_keluar=NULL 
        WHERE id_karyawan='$id'");

    header("Location: karyawan.php");
    exit;
}

// AMBIL DATA UTAMA
$data = mysqli_query($conn, "SELECT * FROM karyawan ORDER BY id_karyawan DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Manajemen Karyawan</title>
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
        /* Responsivitas ekstra untuk tabel di HP */
        @media (max-width: 576px) {
            .table th, .table td {
                font-size: 0.8rem !important;
                padding: 8px 4px !important;
            }
            .btn-sm-custom {
                padding: 0.25rem 0.4rem;
                font-size: 0.75rem;
            }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom shadow-sm sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="#">
            <i class="fa-solid fa-user-gear me-2"></i>PANEL ADMIN
        </a>
        <button class="navbar-toggler" type="text/all" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php"><i class="fa-solid fa-gauge me-1"></i> Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active fw-bold" href="karyawan.php"><i class="fa-solid fa-users me-1"></i> Data Karyawan</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-folder-open me-1"></i> Fitur Lainnya
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownMenuLink">
                        <li><a class="dropdown-item" href="penggajian.php"><i class="fa-solid fa-money-check-dollar me-2"></i>Penggajian</a></li>
                        <li><a class="dropdown-item" href="jurnal.php"><i class="fa-solid fa-book me-2"></i>Jurnal</a></li>
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
                <h3 class="fw-bold text-dark mb-0 fs-4 fs-sm-3">Manajemen Data Karyawan</h3>
                <span class="badge bg-secondary px-3 py-2">Otomatis / Real-time</span>
            </div>
            <hr>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold text-primary"><i class="fa-solid fa-user-plus me-2"></i>Form Tambah Karyawan Baru</h6>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label class="form-label font-monospace small text-muted mb-1">Nama Lengkap</label>
                                <input type="text" name="nama" class="form-control" placeholder="Contoh: Ahmad Subarjo" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label font-monospace small text-muted mb-1">Jabatan / Posisi</label>
                                <input type="text" name="jabatan" class="form-control" placeholder="Contoh: Pegawai" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label font-monospace small text-muted mb-1">Nomor HP / WhatsApp</label>
                                <input type="text" name="kontak" class="form-control" placeholder="Contoh: 081234567xx" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label font-monospace small text-muted mb-1">Alamat Email</label>
                                <input type="email" name="email" class="form-control" placeholder="nama@perusahaan.com">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label font-monospace small text-muted mb-1">Gaji Pokok bulanan (Rp)</label>
                                <input type="number" name="gaji_pokok" class="form-control" placeholder="Nilai murni angka saja" required min="0">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label font-monospace small text-muted mb-1">Tanggal Mulai Kerja</label>
                                <input type="date" name="tanggal_masuk" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label font-monospace small text-muted mb-1">Alamat Tinggal Sekarang</label>
                                <textarea name="alamat" class="form-control" rows="2" placeholder="Tuliskan nama jalan, RT/RW, Kecamatan, Kota/Kabupaten..."></textarea>
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" name="simpan" class="btn btn-primary w-100 w-sm-auto px-4">
                                    <i class="fa-solid fa-floppy-disk me-2"></i>Simpan Registrasi
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="fa-solid fa-list me-2"></i>Daftar Seluruh Karyawan</h6>
                </div>
                <div class="card-body px-0 py-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead class="table-light border-bottom">
                                <tr>
                                    <th class="text-center" style="width: 5%">No</th>
                                    <th>Nama Karyawan</th>
                                    <th>Jabatan</th>
                                    <th>Kontak Informasi</th>
                                    <th>Alamat</th>
                                    <th class="text-end">Gaji Pokok</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Tgl Masuk</th>
                                    <th class="text-center">Tgl Keluar</th>
                                    <th class="text-center" style="width: 15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1; 
                                while($row = mysqli_fetch_assoc($data)): 
                                ?>
                                <tr>
                                    <td class="text-center font-monospace text-muted"><?= $no++; ?></td>
                                    <td><strong><?= htmlspecialchars($row['nama']); ?></strong></td>
                                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($row['jabatan']); ?></span></td>
                                    <td>
                                        <small class="d-block text-nowrap"><i class="fa-solid fa-phone text-muted me-1"></i> <?= htmlspecialchars($row['kontak']); ?></small>
                                        <small class="d-block text-muted text-nowrap"><i class="fa-solid fa-envelope text-muted me-1"></i> <?= htmlspecialchars($row['email'] ? $row['email'] : '-'); ?></small>
                                    </td>
                                    <td><span class="text-truncate d-inline-block" style="max-width: 120px;"><?= htmlspecialchars($row['alamat'] ? $row['alamat'] : '-'); ?></span></td>
                                    <td class="text-end fw-semibold text-success text-nowrap">Rp <?= number_format($row['gaji_pokok'], 0, ',', '.'); ?></td>
                                    <td class="text-center">
                                        <?php if($row['status'] == "Aktif"): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Aktif</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">Nonaktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center font-monospace text-muted small text-nowrap"><?= $row['tanggal_masuk']; ?></td>
                                    <td class="text-center font-monospace text-muted small text-nowrap"><?= $row['tanggal_keluar'] ? $row['tanggal_keluar'] : '-'; ?></td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="edit_karyawan.php?id=<?= $row['id_karyawan']; ?>" class="btn btn-outline-warning btn-sm btn-sm-custom" title="Edit Data">
                                                <i class="fa-solid fa-pen-to-square"></i> <span class="d-none d-sm-inline">Edit</span>
                                            </a>
                                            <?php if($row['status'] == "Aktif"): ?>
                                                <a href="?nonaktif=<?= $row['id_karyawan']; ?>" class="btn btn-outline-danger btn-sm btn-sm-custom" onclick="return confirm('Apakah Anda yakin ingin menonaktifkan karyawan ini?')" title="Nonaktifkan">
                                                    <i class="fa-solid fa-user-slash"></i> <span class="d-none d-sm-inline">Off</span>
                                                </a>
                                            <?php else: ?>
                                                <a href="?aktif=<?= $row['id_karyawan']; ?>" class="btn btn-outline-success btn-sm btn-sm-custom" title="Aktifkan Kembali">
                                                    <i class="fa-solid fa-user-check"></i> <span class="d-none d-sm-inline">On</span>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                                
                                <?php if(mysqli_num_rows($data) == 0): ?>
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">Belum ada data rekaman karyawan saat ini.</td>
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