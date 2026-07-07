<?php
// Aktifkan pelaporan error internal untuk mencegah halaman putih jika ada masalah server
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$pesan_error = "";
$pesan_sukses = "";

// Pilihan daftar bulan Indonesia
$daftar_bulan = [
    1 => "Januari", 2 => "Februari", 3 => "Maret", 4 => "April", 
    5 => "Mei", 6 => "Juni", 7 => "Juli", 8 => "Agustus", 
    9 => "September", 10 => "Oktober", 11 => "November", 12 => "Desember"
];

// Mengambil filter periode dari URL (GET). Jika tidak ada, default ke bulan & tahun ini dalam format TEKS.
if (isset($_GET['filter_bulan'])) {
    $bulan_aktif = $_GET['filter_bulan'];
} else {
    $angka_bulan = (int)date('n');
    $bulan_aktif = $daftar_bulan[$angka_bulan]; // Menghasilkan teks: "Januari", "Februari", dll.
}
$tahun_aktif = isset($_GET['filter_tahun']) ? $_GET['filter_tahun'] : date('Y');


if (isset($_POST['simpan'])) {
    $id_karyawan = mysqli_real_escape_string($conn, $_POST['id_karyawan']);
    $bulan       = mysqli_real_escape_string($conn, $_POST['bulan']);
    $tahun       = mysqli_real_escape_string($conn, $_POST['tahun']);
    $lembur      = isset($_POST['lembur']) ? (int)$_POST['lembur'] : 0;
    $potongan    = isset($_POST['potongan']) ? (int)$_POST['potongan'] : 0;

    // Cek Duplikasi Periode
    $cek_duplikat = mysqli_query($conn, "SELECT * FROM penggajian WHERE id_karyawan='$id_karyawan' AND bulan='$bulan' AND tahun='$tahun'");
    
    if (mysqli_num_rows($cek_duplikat) > 0) {
        $pesan_error = "Input Gaji Ditolak! Karyawan tersebut sudah menerima gaji pada periode $bulan $tahun.";
    } else {
        // VALIDASI KETAT ENUM: Hanya memproses karyawan yang berstatus murni 'Aktif'
        $karyawan_query = mysqli_query($conn, "SELECT * FROM karyawan WHERE id_karyawan='$id_karyawan' AND status = 'Aktif'");
        $data_karyawan  = mysqli_fetch_assoc($karyawan_query);

        if ($data_karyawan) {
            $gaji_pokok = (int)$data_karyawan['gaji_pokok'];
            $total_gaji = $gaji_pokok + $lembur - $potongan;

            $query_gaji = "INSERT INTO penggajian (id_karyawan, bulan, tahun, lembur, potongan, total_gaji, tanggal_input)
                           VALUES ('$id_karyawan','$bulan','$tahun','$lembur','$potongan','$total_gaji', NOW())";
            
            if (mysqli_query($conn, $query_gaji)) {
                $id_gaji = mysqli_insert_id($conn);
                $ket = "Gaji $bulan $tahun - " . mysqli_real_escape_string($conn, $data_karyawan['nama']);
                mysqli_query($conn, "INSERT INTO jurnal (id_gaji,tanggal,keterangan,debit,kredit) VALUES ('$id_gaji',NOW(),'$ket','$total_gaji',0)");
                mysqli_query($conn, "INSERT INTO jurnal (id_gaji,tanggal,keterangan,debit,kredit) VALUES ('$id_gaji',NOW(),'$ket',0,'$total_gaji')");
                
                header("Location: penggajian.php?filter_bulan=$bulan&filter_tahun=$tahun&sukses=1");
                exit;
            }
        } else {
            $pesan_error = "Gagal memproses transaksi. Karyawan berstatus Nonaktif tidak dapat diberi gaji.";
        }
    }
}

if (isset($_GET['sukses'])) $pesan_sukses = "Data penggajian berhasil disimpan!";
if (isset($_GET['sukses_update'])) $pesan_sukses = "Data penggajian berhasil diperbarui!";


if (isset($_GET['hapus'])) {
    $id = mysqli_real_escape_string($conn, $_GET['hapus']);
    mysqli_query($conn, "DELETE FROM jurnal WHERE id_gaji='$id'");
    mysqli_query($conn, "DELETE FROM penggajian WHERE id_gaji='$id'");
    header("Location: penggajian.php?filter_bulan=$bulan_aktif&filter_tahun=$tahun_aktif");
    exit;
}

// 1. FILTER UNTUK BOX ATAS & DROPDOWN FORM: Hanya memuat karyawan yang saat ini berstatus ENUM 'Aktif'
$karyawan_all = mysqli_query($conn, "SELECT id_karyawan, nama, gaji_pokok FROM karyawan WHERE status = 'Aktif' ORDER BY nama ASC");
$sudah_gajian_list = [];
$belum_gajian_list = [];

while($k = mysqli_fetch_assoc($karyawan_all)) {
    $id_k = $k['id_karyawan'];
    // Mencari riwayat berdasarkan teks nama bulan yang aktif
    $cek = mysqli_query($conn, "SELECT id_gaji FROM penggajian WHERE id_karyawan='$id_k' AND bulan='$bulan_aktif' AND tahun='$tahun_aktif'");
    if (mysqli_num_rows($cek) > 0) {
        $sudah_gajian_list[] = $k;
    } else {
        $belum_gajian_list[] = $k;
    }
}

// 2. FILTER UNTUK TABEL RIWAYAT TRANSAKSI: Saring ketat agar riwayat karyawan 'Nonaktif' tidak dimuat ulang di halaman kerja ini
$data_tabel = mysqli_query($conn, "
    SELECT penggajian.*, karyawan.nama, karyawan.gaji_pokok 
    FROM penggajian 
    JOIN karyawan ON penggajian.id_karyawan = karyawan.id_karyawan
    WHERE penggajian.bulan = '$bulan_aktif' 
    AND penggajian.tahun = '$tahun_aktif'
    AND karyawan.status = 'Aktif'
    ORDER BY id_gaji DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Manajemen Penggajian - Fitur Penggajian</title>
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
            
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
                <h3 class="fw-bold text-dark mb-0 fs-4 fs-sm-3">Status Penggajian Karyawan</h3>
                <form method="GET" class="d-flex gap-2 w-100 w-sm-auto">
                    <select name="filter_bulan" class="form-select form-select-sm fw-bold border-primary" onchange="this.form.submit()">
                        <?php foreach($daftar_bulan as $bln): ?>
                            <option value="<?= $bln; ?>" <?= ($bulan_aktif == $bln) ? 'selected' : ''; ?>><?= $bln; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="filter_tahun" class="form-select form-select-sm fw-bold border-primary" onchange="this.form.submit()">
                        <?php for($i=2025; $i<=2030; $i++): ?>
                            <option value="<?= $i; ?>" <?= ($tahun_aktif == $i) ? 'selected' : ''; ?>><?= $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </form>
            </div>
            <hr>

            <?php if (!empty($pesan_error)): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <strong>⚠️ Gagal!</strong> <?= $pesan_error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($pesan_sukses)): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <strong>✨ Berhasil!</strong> <?= $pesan_sukses; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row g-3 mb-4">
                <div class="col-12 col-md-6">
                    <div class="card shadow-sm border-start border-warning border-4 bg-white">
                        <div class="card-header bg-white py-2 fw-bold text-warning border-0">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i>Belum Digaji Periode <?= $bulan_aktif; ?> <?= $tahun_aktif; ?>
                        </div>
                        <div class="card-body py-2 d-flex flex-wrap gap-2">
                            <?php if(empty($belum_gajian_list)): ?>
                                <span class="text-muted small">🎉 Semua karyawan aktif sudah menerima gaji.</span>
                            <?php else: ?>
                                <?php foreach($belum_gajian_list as $bg): ?>
                                    <span class="badge bg-warning-subtle text-warning border border-warning px-3 py-2 rounded-pill font-monospace" style="font-size: 0.85rem;">
                                        ⚠️ <?= htmlspecialchars($bg['nama']); ?>
                                    </span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="card shadow-sm border-start border-success border-4 bg-white">
                        <div class="card-header bg-white py-2 fw-bold text-success border-0">
                            <i class="fa-solid fa-circle-check me-2"></i>Sudah Digaji Periode <?= $bulan_aktif; ?> <?= $tahun_aktif; ?>
                        </div>
                        <div class="card-body py-2 d-flex flex-wrap gap-2">
                            <?php if(empty($sudah_gajian_list)): ?>
                                <span class="text-muted small">Belum ada transaksi gaji masuk bulan ini.</span>
                            <?php else: ?>
                                <?php foreach($sudah_gajian_list as $sg): ?>
                                    <span class="badge bg-success-subtle text-success border border-success px-3 py-2 rounded-pill font-monospace" style="font-size: 0.85rem;">
                                        ✅ <?= htmlspecialchars($sg['nama']); ?>
                                    </span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4 bg-white">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold text-primary"><i class="fa-solid fa-file-invoice-dollar me-2"></i>Form Pembuatan Slip Gaji (Periode: <?= $bulan_aktif; ?> <?= $tahun_aktif; ?>)</h6>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="bulan" value="<?= $bulan_aktif; ?>">
                        <input type="hidden" name="tahun" value="<?= $tahun_aktif; ?>">

                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label class="form-label font-monospace small text-muted mb-1">Pilih Karyawan</label>
                                <select name="id_karyawan" class="form-select" required>
                                    <option value="">-- Pilih Karyawan --</option>
                                    <?php if(empty($belum_gajian_list)): ?>
                                        <option value="" disabled>Semua sudah digaji untuk periode ini</option>
                                    <?php else: ?>
                                        <?php foreach($belum_gajian_list as $k_belum): ?>
                                            <option value="<?= $k_belum['id_karyawan']; ?>">
                                                🟢 <?= htmlspecialchars($k_belum['nama']); ?> (Rp <?= number_format($k_belum['gaji_pokok'], 0, ',', '.'); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3">
                                <label class="form-label font-monospace small text-muted mb-1">Bonus Lembur (Rp)</label>
                                <input type="number" name="lembur" class="form-control" value="0" min="0">
                            </div>
                            <div class="col-12 col-sm-6 col-md-3">
                                <label class="form-label font-monospace small text-muted mb-1">Potongan Gaji (Rp)</label>
                                <input type="number" name="potongan" class="form-control" value="0" min="0">
                            </div>
                            <div class="col-12 col-md-2 d-flex align-items-end">
                                <button type="submit" name="simpan" class="btn btn-success w-100 fw-bold">
                                    <i class="fa-solid fa-floppy-disk me-2"></i>Simpan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm bg-white">
                <div class="card-header bg-dark text-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="fa-solid fa-list me-2"></i>Rincian Penggajian Bulan: <?= $bulan_aktif; ?> <?= $tahun_aktif; ?></h6>
                </div>
                <div class="card-body px-0 py-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead class="table-light border-bottom">
                                <tr>
                                    <th class="text-center" style="width: 5%">No</th>
                                    <th>Nama Karyawan</th>
                                    <th class="text-center">Periode Gaji</th>
                                    <th class="text-center">Tanggal Input</th>
                                    <th class="text-end">Gaji Pokok</th>
                                    <th class="text-end">Lembur (+)</th>
                                    <th class="text-end">Potongan (-)</th>
                                    <th class="text-end">Total Bersih</th>
                                    <th class="text-center" style="width: 20%">Aksi Kontrol</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1; 
                                while($row = mysqli_fetch_assoc($data_tabel)): 
                                ?>
                                <tr>
                                    <td class="text-center font-monospace text-muted"><?= $no++; ?></td>
                                    <td><strong><?= htmlspecialchars($row['nama']); ?></strong></td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border"><?= htmlspecialchars($row['bulan'] . ' ' . $row['tahun']); ?></span>
                                    </td>
                                    <td class="text-center text-muted font-monospace small">
                                        <?= date('d-m-Y H:i', strtotime($row['tanggal_input'])); ?>
                                    </td>
                                    <td class="text-end text-muted">Rp <?= number_format($row['gaji_pokok'], 0, ',', '.'); ?></td>
                                    <td class="text-end text-primary">+ Rp <?= number_format($row['lembur'], 0, ',', '.'); ?></td>
                                    <td class="text-end text-danger">- Rp <?= number_format($row['potongan'], 0, ',', '.'); ?></td>
                                    <td class="text-end fw-bold text-success">Rp <?= number_format($row['total_gaji'], 0, ',', '.'); ?></td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="slip_gaji.php?id=<?= $row['id_gaji']; ?>" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-print"></i> Slip</a>
                                            <a href="update_penggajian.php?id=<?= $row['id_gaji']; ?>" class="btn btn-outline-warning btn-sm"><i class="fa-solid fa-pen-to-square"></i> Update</a>
                                            <a href="?hapus=<?= $row['id_gaji']; ?>&filter_bulan=<?= $bulan_aktif; ?>&filter_tahun=<?= $tahun_aktif; ?>" onclick="return confirm('Hapus data ini?')" class="btn btn-outline-danger btn-sm"><i class="fa-solid fa-trash-can"></i> Hapus</a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                                
                                <?php if (mysqli_num_rows($data_tabel) == 0): ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">Tidak ada data transaksi penggajian pada periode terpilih.</td>
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
    if (window.location.search.indexOf('sukses=1') > -1 || window.location.search.indexOf('sukses_update=1') > -1) {
        const urlParams = new URLSearchParams(window.location.search);
        const bln = urlParams.get('filter_bulan');
        const thn = urlParams.get('filter_tahun');
        window.history.replaceState({}, document.title, window.location.pathname + `?filter_bulan=${bln}&filter_tahun=${thn}`);
    }
</script>
</body>
</html>
