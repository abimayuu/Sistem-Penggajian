<?php
include 'koneksi.php';

$id = $_GET['id'];

$data = mysqli_query($conn,
"SELECT * FROM karyawan WHERE id_karyawan='$id'");

$row = mysqli_fetch_assoc($data);

// UPDATE DATA
if(isset($_POST['update'])){

    $nama = $_POST['nama'];
    $jabatan = $_POST['jabatan'];
    $kontak = $_POST['kontak'];
    $email = $_POST['email'];
    $alamat = $_POST['alamat'];
    $gaji_pokok = $_POST['gaji_pokok'];
    $tanggal_masuk = $_POST['tanggal_masuk'];

    mysqli_query($conn,"
    UPDATE karyawan SET
        nama='$nama',
        jabatan='$jabatan',
        kontak='$kontak',
        email='$email',
        alamat='$alamat',
        gaji_pokok='$gaji_pokok',
        tanggal_masuk='$tanggal_masuk'
    WHERE id_karyawan='$id'
    ");

    header("Location:karyawan.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Karyawan</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">

<div class="card shadow">

<div class="card-header bg-warning">
    <h4>Edit Data Karyawan</h4>
</div>

<div class="card-body">

<form method="POST">

<div class="mb-3">
<label>Nama Karyawan</label>
<input type="text"
name="nama"
class="form-control"
value="<?= $row['nama']; ?>"
required>
</div>

<div class="mb-3">
<label>Jabatan</label>
<input type="text"
name="jabatan"
class="form-control"
value="<?= $row['jabatan']; ?>"
required>
</div>

<div class="mb-3">
<label>Kontak</label>
<input type="text"
name="kontak"
class="form-control"
value="<?= $row['kontak']; ?>">
</div>

<div class="mb-3">
<label>Email</label>
<input type="email"
name="email"
class="form-control"
value="<?= $row['email']; ?>">
</div>

<div class="mb-3">
<label>Alamat</label>
<textarea
name="alamat"
class="form-control"
rows="3"><?= $row['alamat']; ?></textarea>
</div>

<div class="mb-3">
<label>Gaji Pokok</label>
<input type="number"
name="gaji_pokok"
class="form-control"
value="<?= $row['gaji_pokok']; ?>"
required>
</div>

<div class="mb-3">
<label>Tanggal Masuk</label>
<input type="date"
name="tanggal_masuk"
class="form-control"
value="<?= $row['tanggal_masuk']; ?>">
</div>

<div class="mb-3">
<label>Status</label>
<input type="text"
class="form-control"
value="<?= $row['status']; ?>"
readonly>
</div>

<button type="submit"
name="update"
class="btn btn-success">
Update
</button>

<a href="karyawan.php"
class="btn btn-secondary">
Kembali
</a>

</form>

</div>
</div>

</div>

</body>
</html>