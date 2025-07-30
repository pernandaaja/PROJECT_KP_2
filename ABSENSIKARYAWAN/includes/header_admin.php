<?php
// Cek jika belum login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Absensi Karyawan</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Box Icons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    
    <!-- Custom CSS -->
    <style>
        .sidebar {
            min-height: 100vh;
            background: #f8f9fa;
            padding: 20px;
            border-right: 1px solid #dee2e6;
        }
        
        .sidebar .nav-link {
            color: #333;
            padding: 8px 16px;
            margin-bottom: 5px;
            border-radius: 4px;
        }
        
        .sidebar .nav-link:hover {
            background: #e9ecef;
        }
        
        .sidebar .nav-link.active {
            background: #0d6efd;
            color: white;
        }
        
        .sidebar .nav-link i {
            margin-right: 10px;
        }
        
        .content {
            padding: 20px;
        }
        
        .user-info {
            padding: 15px;
            margin-bottom: 20px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .current-time {
            font-size: 0.9rem;
            color: #6c757d;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 px-0">
            <div class="sidebar">
                <div class="user-info">
                    <h5 class="mb-1">Administrator</h5>
                    <p class="mb-2 text-muted"><?= $_SESSION['nama'] ?? 'Admin' ?></p>
                    <small class="current-time d-block">
                        <?= date('l, d F Y') ?><br>
                        <span id="current-time"></span>
                    </small>
                </div>
                
                <nav class="nav flex-column">
                    <a class="nav-link <?= str_contains($_SERVER['PHP_SELF'], 'dashboard.php') ? 'active' : '' ?>" 
                       href="dashboard.php">
                        <i class='bx bxs-dashboard'></i> Dashboard
                    </a>
                    <a class="nav-link <?= str_contains($_SERVER['PHP_SELF'], 'karyawan.php') ? 'active' : '' ?>" 
                       href="karyawan.php">
                        <i class='bx bxs-user-detail'></i> Data Karyawan
                    </a>
                    <a class="nav-link <?= str_contains($_SERVER['PHP_SELF'], 'divisi.php') ? 'active' : '' ?>" 
                       href="divisi.php">
                        <i class='bx bxs-building'></i> Data Divisi
                    </a>
                    <a class="nav-link <?= str_contains($_SERVER['PHP_SELF'], 'jam_kerja.php') ? 'active' : '' ?>" 
                       href="jam_kerja.php">
                        <i class='bx bx-time-five'></i> Jam Kerja
                    </a>
                    <a class="nav-link <?= str_contains($_SERVER['PHP_SELF'], 'laporan.php') ? 'active' : '' ?>" 
                       href="laporan.php">
                        <i class='bx bxs-report'></i> Laporan
                    </a>
                    <a class="nav-link text-danger" href="../logout.php" 
                       onclick="return confirm('Apakah Anda yakin ingin keluar?')">
                        <i class='bx bx-log-out'></i> Keluar
                    </a>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <main class="col-md-9 col-lg-10 ms-sm-auto px-md-4">
            <!-- Content akan ditampilkan di sini -->

<!-- JavaScript untuk waktu real-time -->
<script>
function updateTime() {
    const timeElement = document.getElementById('current-time');
    const now = new Date();
    const timeString = now.toLocaleTimeString('en-US', { 
        hour12: false, 
        hour: '2-digit', 
        minute: '2-digit', 
        second: '2-digit' 
    });
    timeElement.textContent = timeString;
}

// Update setiap detik
setInterval(updateTime, 1000);
updateTime(); // Initial call

// Bootstrap JS dan Popper
document.write('<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"><\/script>');
</script>