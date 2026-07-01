<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("ID Transaksi tidak ditemukan.");
}

$id_gaji = mysqli_real_escape_string($conn, $_GET['id']);

// Ambil data gaji dan email karyawan
$query = mysqli_query($conn, "
    SELECT penggajian.*, karyawan.nama, karyawan.gaji_pokok, karyawan.email 
    FROM penggajian 
    JOIN karyawan ON penggajian.id_karyawan = karyawan.id_karyawan
    WHERE penggajian.id_gaji = '$id_gaji'
");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    die("Data tidak ditemukan.");
}

if (empty($data['email'])) {
    die("Gagal: Karyawan bernama " . $data['nama'] . " belum memiliki alamat email di database.");
}

// ==========================================
// PENGATURAN EMAIL (STANDAR AKUNTANSI FORMAL)
// ==========================================
$to      = $data['email'];
$subject = "Slip Gaji Resmi Periode " . $data['bulan'] . " " . $data['tahun'];
$from    = "admin@perusahaan.com"; // Ganti dengan nama domain perusahaan kamu nanti

// Membuat teks isi email (Format HTML)
$message = "
<html>
<head>
    <title>Slip Gaji</title>
</head>
<body>
    <p>Halo <b>" . htmlspecialchars($data['nama']) . "</b>,</p>
    <p>Berikut adalah rincian pembayaran gaji Anda untuk periode <b>" . $data['bulan'] . " " . $data['tahun'] . "</b>:</p>
    <table border='1' cellpadding='5' style='border-collapse: collapse; font-family: monospace;'>
        <tr bgcolor='#eee'><td><b>Komponen</b></td><td><b>Jumlah</b></td></tr>
        <tr><td>Gaji Pokok</td><td>Rp " . number_format($data['gaji_pokok'], 0, ',', '.') . "</td></tr>
        <tr><td>Bonus/Lembur</td><td>Rp " . number_format($data['lembur'], 0, ',', '.') . "</td></tr>
        <tr><td>Potongan</td><td style='color:red;'>- Rp " . number_format($data['potongan'], 0, ',', '.') . "</td></tr>
        <tr bgcolor='#ddd'><td><b>Total Diterima (Take Home Pay)</b></td><td><b>Rp " . number_format($data['total_gaji'], 0, ',', '.') . "</b></td></tr>
    </table>
    <br>
    <p><i>Gaji telah ditransfer langsung ke rekening Anda. Slip ini sah diterbitkan oleh Sistem Informasi Keuangan Perusahaan.</i></p>
    <p>Salam,<br><b>Manajemen Keuangan / Budi</b></p>
</body>
</html>
";

// Mengatur Header agar mendukung Format HTML murni
$headers  = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headers .= "From: PT. CAHAYA BARU UTAMA <" . $from . ">" . "\r\n";

// Eksekusi Pengiriman Surat Elektronik
if (mail($to, $subject, $message, $headers)) {
    // Jika sukses, kembali ke slip_gaji.php dengan status sukses
    header("Location: slip_gaji.php?id=$id_gaji&status=terkirim");
} else {
    // Jika gagal, kembali dengan status gagal
    header("Location: slip_gaji.php?id=$id_gaji&status=gagal");
}
exit;