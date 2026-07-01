<?php
session_start();

// Cek apakah user sudah memiliki session login aktif
if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
    // Jika sudah login, alihkan langsung ke halaman Dashboard
    header("Location: dashboard.php");
    exit;
} else {
    // Jika belum login, alihkan ke halaman Login
    header("Location: login.php");
    exit;
}
?>