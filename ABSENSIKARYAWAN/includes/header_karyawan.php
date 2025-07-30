<?php
// Cek jika belum login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Ambil data user dari database
$user_id = $_SESSION['user_id'];
$query = "SELECT u.*, d.nama_divisi, j.nama_shift 
          FROM users u 
          LEFT JOIN divisi d ON u.divisi_id = d.id 
          LEFT JOIN jam_kerja j ON u.jam_kerja_id = j.id 
          WHERE u.id = $user_id";
$result = mysqli_query($conn, $query);
$user_data = mysqli_fetch_assoc($result);
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

        .divisi-info {
            font-size: 0.8rem;
            color: #6c757d;
            margin-top: 5px;
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
                    <h5 class="mb-1"><?= htmlspecialchars($user_data['nama']) ?></h5>
                    <p class="mb-0 text-muted">NIK: <?= htmlspecialchars($user_data['nik']) ?></p>
                    <div class="divisi-info">
                        <p class="mb-0">Divisi: <?= htmlspecialchars($user_data['nama_divisi']) ?></p>
                        <p class="mb-0">Shift: <?= htmlspecialchars($user_data['nama_shift']) ?></p>
                    </div>
                    <small class="current-time d-block mt-2">
                        <?= date('l, d F Y') ?><br>
                        <span id="current-time"></span>
                    </small>
                </div>
                
                <nav class="nav flex-column">
                    <a class="nav-link <?= str_contains($_SERVER['PHP_SELF'], 'dashboard.php') ? 'active' : '' ?>" 
                       href="dashboard.php">
                        <i class='bx bxs-dashboard'></i> Dashboard
                    </a>
                    
                    <a class="nav-link <?= str_contains($_SERVER['PHP_SELF'], 'riwayat.php') ? 'active' : '' ?>" 
                       href="riwayat.php">
                        <i class='bx bx-history'></i> Riwayat Absensi
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
</script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>