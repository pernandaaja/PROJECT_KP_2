<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Cek login
cek_login();

$user_id = $_SESSION['user_id'];
$tanggal = date('Y-m-d');

// Ambil data karyawan
$query_user = "SELECT u.*, d.nama_divisi, j.nama_shift, j.jam_masuk, j.jam_pulang 
               FROM users u 
               JOIN divisi d ON u.divisi_id = d.id 
               JOIN jam_kerja j ON u.jam_kerja_id = j.id 
               WHERE u.id = $user_id";
$result_user = query($query_user);
$user = mysqli_fetch_assoc($result_user);

// Cek absensi hari ini
$query_absen = "SELECT * FROM absensi WHERE user_id = $user_id AND tanggal = '$tanggal'";
$result_absen = query($query_absen);
$absen_hari_ini = mysqli_fetch_assoc($result_absen);

// Riwayat absensi 7 hari terakhir
$query_riwayat = "SELECT * FROM absensi 
                  WHERE user_id = $user_id 
                  ORDER BY tanggal DESC LIMIT 7";
$result_riwayat = query($query_riwayat);
?>

<?php include '../includes/header_karyawan.php'; ?>


<div class="col-md-9 col-lg-10 py-3">
    <div class="container">
        <h2 class="mb-4">Informasi Karyawan</h2>

        <!-- Info Karyawan -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="card-title">Informasi Karyawan</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td width="150">NIK</td>
                                <td>: <?= $user['nik'] ?></td>
                            </tr>
                            <tr>
                                <td>Nama</td>
                                <td>: <?= $user['nama'] ?></td>
                            </tr>
                            <tr>
                                <td>Divisi</td>
                                <td>: <?= $user['nama_divisi'] ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5 class="card-title">Jadwal Kerja</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td width="150">Shift</td>
                                <td>: <?= $user['nama_shift'] ?></td>
                            </tr>
                            <tr>
                                <td>Jam Masuk</td>
                                <td>: <?= date('H:i', strtotime($user['jam_masuk'])) ?></td>
                            </tr>
                            <tr>
                                <td>Jam Pulang</td>
                                <td>: <?= date('H:i', strtotime($user['jam_pulang'])) ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

      
            </div>
        </div>
    </div>
</div>
</div>
</div>
</div>