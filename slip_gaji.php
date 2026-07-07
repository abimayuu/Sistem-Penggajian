<?php
session_start();
include 'koneksi.php';

// Proteksi Halaman: Wajib Login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Validasi Parameter ID Gaji dari URL
if (!isset($_GET['id'])) {
    die("<div class='container mt-5 alert alert-danger'>Error: Parameter ID Transaksi tidak ditemukan.</div>");
}

$id_gaji = mysqli_real_escape_string($conn, $_GET['id']);

// Query Relasi Tabel Penggajian & Karyawan (Sudah Ditambahkan kolom no_hp)
$query = mysqli_query($conn, "
    SELECT penggajian.*, karyawan.nama, karyawan.gaji_pokok, karyawan.email, karyawan.no_hp 
    FROM penggajian 
    JOIN karyawan ON penggajian.id_karyawan = karyawan.id_karyawan
    WHERE penggajian.id_gaji = '$id_gaji'
");
$data = mysqli_fetch_assoc($query);

// Jika Data ID tidak ada di database
if (!$data) {
    die("<div class='container mt-5 alert alert-danger'>Error: Data rekaman slip gaji tidak ditemukan di sistem.</div>");
}


$no_hp = $data['no_hp'];

// Cek jika nomor HP kosong
if (empty($no_hp)) {
    $url_wa = "#";
    $wa_onclick = "alert('Gagal: Nomor WhatsApp karyawan ini belum diisi di database!'); return false;";
} else {
    // Paksa format nomor HP agar diawali angka 62 (Standar Internasional)
    if (substr($no_hp, 0, 1) === '0') {
        $no_hp = '62' . substr($no_hp, 1);
    } elseif (substr($no_hp, 0, 2) !== '62') {
        $no_hp = '62' . $no_hp;
    }
    
    $wa_onclick = "";

    // Menyusun isi nota teks WhatsApp secara otomatis
    $teks_pesan = "Halo *" . $data['nama'] . "*,\n\n";
    $teks_pesan .= "Berikut rincian *Slip Gaji Resmi* Anda periode *" . $data['bulan'] . " " . $data['tahun'] . "*:\n";
    $teks_pesan .= "----------------------------------------\n";
    $teks_pesan .= " Gaji Pokok  : Rp " . number_format($data['gaji_pokok'], 0, ',', '.') . "\n";
    $teks_pesan .= " Lembur/Bonus: Rp " . number_format($data['lembur'], 0, ',', '.') . "\n";
    $teks_pesan .= " Potongan    : -Rp " . number_format($data['potongan'], 0, ',', '.') . "\n";
    $teks_pesan .= "----------------------------------------\n";
    $teks_pesan .= "*TAKE HOME PAY (Bersih) : Rp " . number_format($data['total_gaji'], 0, ',', '.') . "*\n\n";
    $teks_pesan .= "Dana hak kerja Anda telah ditransfer via Bank Pemindahbukuan.\n";
    $teks_pesan .= "_PT. CAHAYA BARU UTAMA_";

    // Encode teks agar aman dibaca URL Browser
    $url_wa = "https://api.whatsapp.com/send?phone=" . $no_hp . "&text=" . urlencode($teks_pesan);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Gaji Resmi - <?= htmlspecialchars($data['nama']); ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <style>
        body { 
            background-color: #f8f9fa; 
            font-family: 'Courier New', Courier, monospace; 
            color: #000;
        }
        .slip-box { 
            border: 2px dashed #000; 
            padding: 35px; 
            max-width: 850px; 
            margin: 20px auto; 
            background: #fff; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .line-double { 
            border-top: 3px double #000; 
            margin-top: 10px; 
            margin-bottom: 20px; 
            opacity: 1;
        }
        .line-single { 
            border-top: 1px solid #000; 
            margin: 15px 0; 
            opacity: 1;
        }
        .bg-sub {
            background-color: #f2f2f2 !important;
            border: 1px solid #000 !important;
        }
        @media print { 
            .btn-area { display: none; } 
            .slip-box { border: none; box-shadow: none; margin: 0; padding: 0; } 
        }
    </style>
</head>
<body>

<div class="container text-center mt-4 btn-area">
    <div class="btn-group shadow-sm">
        <a href="history.php" class="btn btn-outline-secondary btn-sm fw-bold">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke History
        </a>
        <button onclick="window.print()" class="btn btn-light btn-sm fw-bold border">
            <i class="fa-solid fa-print me-1"></i> Cetak Kertas (Print)
        </button>
        <button onclick="unduhPDF()" class="btn btn-primary btn-sm fw-bold">
            <i class="fa-solid fa-file-pdf me-1"></i> Unduh File PDF
        </button>
        
        <a href="<?= $url_wa; ?>" onclick="<?= $wa_onclick; ?>" target="_blank" class="btn btn-success btn-sm fw-bold">
            <i class="fa-brands fa-whatsapp me-1"></i> Kirim via WhatsApp
        </a>
    </div>
</div>

<div id="capture-area-slip" class="slip-box">
    
    <div class="row">
        <div class="col-7 text-start">
            <h4 class="fw-bold mb-0" style="letter-spacing: 0.5px;">UMKM. CIRENG BUNDA</h4>
            <small class="text-muted d-block" style="font-size: 0.75rem; font-family: sans-serif;">
                Jl. Sukarela, Depan Mushola Babusalam, Jl. Batujajar Lrg. Sejambu 1, Kota Palembang, Sumatera Selatan 30152
            </small>
        </div>
        <div class="col-5 text-end">
            <h4 class="fw-bold mb-0 text-decoration-underline" style="letter-spacing: 0.5px;">SLIP GAJI KARYAWAN</h4>
            <small class="font-monospace text-muted">No. Referensi: #SG-202600<?= $data['id_gaji']; ?></small>
        </div>
    </div>
    
    <div class="line-double"></div>

    <div class="row text-start small mb-4">
        <div class="col-6">
            <table class="table table-borderless table-sm mb-0">
                <tr><td style="width: 35%">Nama Karyawan</td><td>: <strong><?= htmlspecialchars($data['nama']); ?></strong></td></tr>
                <tr><td>ID Pegawai</td><td>: EMP-00<?= $data['id_karyawan']; ?></td></tr>
                <tr><td>No. WhatsApp</td><td>: <?= htmlspecialchars($data['no_hp'] ? $data['no_hp'] : '-'); ?></td></tr>
            </table>
        </div>
        <div class="col-6">
            <table class="table table-borderless table-sm mb-0">
                <tr><td style="width: 40%">Periode Penggajian</td><td>: <strong><?= htmlspecialchars($data['bulan'] . ' ' . $data['tahun']); ?></strong></td></tr>
                <tr><td>Metode Bayar</td><td>: Transfer Bank Pemindahbukuan</td></tr>
                <tr><td>Tanggal Rilis</td><td>: <?= date('d-m-Y H:i', strtotime($data['tanggal_input'])); ?></td></tr>
            </table>
        </div>
    </div>

    <div class="row text-start small">
        
        <div class="col-6 border-end border-dark">
            <p class="fw-bold bg-sub p-1 text-center border mb-2">I. PENDAPATAN (EARNINGS)</p>
            <table class="table table-borderless table-sm">
                <tr>
                    <td>Gaji Pokok Berjalan</td>
                    <td class="text-end">Rp <?= number_format($data['gaji_pokok'], 0, ',', '.'); ?></td>
                </tr>
                <tr>
                    <td>Upah Lembur / Bonus Insentif</td>
                    <td class="text-end text-primary">+ Rp <?= number_format($data['lembur'], 0, ',', '.'); ?></td>
                </tr>
                <tr><td>&nbsp;</td><td></td></tr>
                <tr class="fw-bold border-top border-dark">
                    <td>Total Pendapatan Kotor</td>
                    <td class="text-end">Rp <?= number_format($data['gaji_pokok'] + $data['lembur'], 0, ',', '.'); ?></td>
                </tr>
            </table>
        </div>

        <div class="col-6">
            <p class="fw-bold bg-sub p-1 text-center border mb-2">II. POTONGAN (DEDUCTIONS)</p>
            <table class="table table-borderless table-sm">
                <tr>
                    <td>Potongan Ketidakhadiran / Absen</td>
                    <td class="text-end text-danger">- Rp <?= number_format($data['potongan'], 0, ',', '.'); ?></td>
                </tr>
                <tr>
                    <td>Pajak Penghasilan Resmi (PPh 21)</td>
                    <td class="text-end text-muted">- Rp 0</td>
                </tr>
                <tr><td>&nbsp;</td><td></td></tr>
                <tr class="fw-bold border-top border-dark">
                    <td>Total Potongan Gaji</td>
                    <td class="text-end text-danger">Rp <?= number_format($data['potongan'], 0, ',', '.'); ?></td>
                </tr>
            </table>
        </div>

    </div>

    <div class="line-single"></div>

    <div class="p-3 border border-dark bg-sub d-flex justify-content-between align-items-center mb-4">
        <h6 class="mb-0 fw-bold font-monospace">TOTAL GAJI BERSIH DITERIMA (TAKE HOME PAY):</h6>
        <h4 class="mb-0 fw-bold text-success font-monospace">Rp <?= number_format($data['total_gaji'], 0, ',', '.'); ?></h4>
    </div>

    <div class="row text-start small mt-5 pt-3">
        <div class="col-6 text-center">
            <p class="mb-5">Karyawan Bersangkutan,</p>
            <p class="mb-0 fw-bold">( <?= htmlspecialchars($data['nama']); ?> )</p>
            <small class="text-muted d-block" style="font-size: 11px; font-family:sans-serif;">Tanda Tangan Penerima</small>
        </div>
        <div class="col-6 text-center">
            <p class="mb-5">Palembang, <?= date('d-m-Y', strtotime($data['tanggal_input'])); ?><br>Otorisasi Keuangan Perusahaan,</p>
            <p class="mb-0 fw-bold text-decoration-underline">BUDI</p>
            <small class="text-muted d-block" style="font-size: 11px; font-family:sans-serif;">Finance Officer / Owner</small>
        </div>
    </div>
</div>

<script>
function unduhPDF() {
    const element = document.getElementById('capture-area-slip');
    const namaPegawai = "<?= htmlspecialchars($data['nama']); ?>";
    const bulanTahun = "<?= $data['bulan'].'_'.$data['tahun']; ?>";
    
    const configurationOptions = {
        margin:       15,
        filename:     `Slip_Gaji_${namaPegawai}_${bulanTahun}.pdf`,
        image:        { type: 'jpeg', quality: 0.99 },
        html2canvas:  { scale: 2, useCORS: true },
        jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };

    html2pdf().set(configurationOptions).from(element).save();
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
