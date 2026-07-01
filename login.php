<?php
session_start();
include 'koneksi.php';

$error_message = "";

if (isset($_POST['login'])) {
    // Menggunakan mysqli_real_escape_string untuk mengamankan input dari SQL Injection (Nilai Tambah untuk Sidang)
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = md5($_POST['password']);

    $query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' AND password='$password'");
    $cek = mysqli_num_rows($query);

    if ($cek > 0) {
        $data_user = mysqli_fetch_assoc($query);
        $_SESSION['login'] = true;
        $_SESSION['username'] = $data_user['username']; // Menyimpan session nama untuk di dashboard nanti
        header("Location: dashboard.php");
        exit;
    } else {
        $error_message = "Username atau Password yang Anda masukkan salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi Penggajian - Login</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <style>
        body {
            /* Background gradasi modern bertema korporat profesional */
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
            background: #ffffff;
            overflow: hidden;
        }
        .card-header-custom {
            background: #1e293b;
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
            border-bottom: none;
        }
        .brand-icon {
            font-size: 2.5rem;
            color: #38bdf8;
            margin-bottom: 10px;
        }
        .form-control:focus {
            border-color: #1e293b;
            box-shadow: 0 0 0 0.25rem rgba(30, 41, 59, 0.15);
        }
        .btn-login {
            background: #1e293b;
            color: #ffffff;
            font-weight: 600;
            padding: 11px;
            transition: all 0.3s;
            border: none;
        }
        .btn-login:hover {
            background: #0f172a;
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .input-group-text {
            background-color: #f8f9fa;
            color: #64748b;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-sm-10 col-md-7 col-lg-5 col-xl-4">
            
            <div class="card login-card">
                <div class="card-header-custom">
                    <div class="brand-icon">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                    <h5 class="fw-bold mb-1" style="letter-spacing: 0.5px;">E-PENGGAJIAN</h5>
                    <small class="text-white-50">UMKM. CIRENG BUNDA</small>
                </div>
                
                <div class="card-body p-4 pt-5">
                    
                    <?php if (!empty($error_message)) : ?>
                        <div class="alert alert-danger alert-dismissible fade show small py-2 mb-4" role="alert">
                            <i class="fa-solid fa-circle-exclamation me-2"></i> <?= $error_message; ?>
                            <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        
                        <div class="mb-4">
                            <label class="form-label small fw-semibold text-secondary">Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-user-tie"></i></span>
                                <input type="text" name="username" class="form-control" placeholder="Masukkan username Anda" autocomplete="off" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-semibold text-secondary">Password Security</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                            </div>
                        </div>

                        <div class="mt-4 mb-2">
                            <button type="submit" name="login" class="btn btn-login w-100 rounded-3">
                                <i class="fa-solid fa-right-to-bracket me-2"></i> Masuk ke Sistem
                            </button>
                        </div>
                        
                    </form>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <small class="text-white-50" style="font-size: 0.75rem;">
                    &copy; 2026 UMKM. CIRENG BUNDA. All Rights Reserved.
                </small>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>