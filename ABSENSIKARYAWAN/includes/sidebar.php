<?php
$role = $_SESSION['role'];
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="col-md-3 col-lg-2 sidebar py-3">
    <div class="text-center mb-4">
        <i class='bx bxs-user-circle' style="font-size: 4rem;"></i>
        <h6 class="mt-2"><?= $_SESSION['nama'] ?></h6>
        <small class="text-muted"><?= ucfirst($role) ?></small>
    </div>
    
    <ul class="nav flex-column">
        <?php if ($role == 'admin'): ?>
            <li class="nav-item">
                <a class="nav-link <?= $current_page == 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">
                    <i class='bx bxs-dashboard'></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $current_page == 'karyawan.php' ? 'active' : '' ?>" href="karyawan.php">
                    <i class='bx bxs-user-detail'></i> Data Karyawan
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $current_page == 'divisi.php' ? 'active' : '' ?>" href="divisi.php">
                    <i class='bx bxs-building'></i> Data Divisi
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $current_page == 'jam_kerja.php' ? 'active' : '' ?>" href="jam_kerja.php">
                    <i class='bx bxs-time'></i> Jam Kerja
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $current_page == 'verifikasi.php' ? 'active' : '' ?>" href="verifikasi.php">
                    <i class='bx bxs-check-circle'></i> Verifikasi Absensi
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $current_page == 'laporan.php' ? 'active' : '' ?>" href="laporan.php">
                    <i class='bx bxs-report'></i> Laporan
                </a>
            </li>
        <?php else: ?>
            <li class="nav-item">
                <a class="nav-link <?= $current_page == 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">
                    <i class='bx bxs-dashboard'></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $current_page == 'riwayat.php' ? 'active' : '' ?>" href="riwayat.php">
                    <i class='bx bxs-history'></i> Riwayat Absensi
                </a>
            </li>
        <?php endif; ?>
        <li class="nav-item mt-3">
            <a class="nav-link text-danger" href="../logout.php">
                <i class='bx bxs-log-out'></i> Logout
            </a>
        </li>
    </ul>
</div>