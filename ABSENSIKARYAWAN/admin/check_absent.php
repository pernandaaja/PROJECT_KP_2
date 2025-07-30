<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Cek login dan role admin
cek_login();
cek_admin();

$tanggal = isset($_POST['tanggal']) ? $_POST['tanggal'] : date('Y-m-d');

// Query untuk mendapatkan karyawan yang tidak absen
$query = "SELECT u.* 
          FROM users u 
          WHERE u.role = 'karyawan' 
          AND NOT EXISTS (
              SELECT 1 
              FROM absensi a 
              WHERE a.user_id = u.id 
              AND a.tanggal = '$tanggal'
          )";

$result = mysqli_query($conn, $query);
$tidak_absen = [];

while ($row = mysqli_fetch_assoc($result)) {
    $tidak_absen[] = $row;
}

// Jika ada yang belum absen dan request untuk menandai
if (!empty($tidak_absen) && isset($_POST['mark_absent'])) {
    foreach ($tidak_absen as $karyawan) {
        $user_id = $karyawan['id'];
        $query_insert = "INSERT INTO absensi (user_id, tanggal, status, jenis_absen, created_at) 
                        VALUES ($user_id, '$tanggal', 'tidak hadir', 'masuk', NOW())";
        mysqli_query($conn, $query_insert);
    }
    echo json_encode(['status' => 'success', 'message' => 'Berhasil menandai karyawan yang tidak absen']);
    exit;
}

echo json_encode(['status' => 'info', 'data' => $tidak_absen]);
?>