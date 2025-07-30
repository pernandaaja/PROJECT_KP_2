<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Absensi Karyawan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <style>
        .welcome-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            padding: 30px;
        }
        .hero-section {
            min-height: 100vh;
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('assets/images/bg-office.jpg');
            background-size: cover;
            background-position: center;
        }
        .btn-custom {
            padding: 15px 30px;
            font-size: 1.2rem;
            margin: 10px;
            width: 200px;
        }
    </style>
</head>
<body>
    <div class="hero-section d-flex align-items-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="welcome-card text-center">
                        <h1 class="mb-4">Selamat Datang</h1>
                        <h3 class="mb-4">Sistem Absensi Karyawan</h3>
                        <div class="d-flex justify-content-center">
                            <a href="quick_absensi.php" class="btn btn-primary btn-custom">
                                <i class='bx bx-time-five'></i> Absensi
                            </a>
                            <a href="login.php" class="btn btn-success btn-custom">
                                <i class='bx bx-log-in'></i> Login
                            </a>
                        </div>
                        <div class="text-center mt-3">
                            <a href="register.php">Daftar Akun Baru</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>