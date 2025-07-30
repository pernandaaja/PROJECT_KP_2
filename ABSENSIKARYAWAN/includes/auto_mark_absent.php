<?php
require_once '../config/database.php';

function markAutoAbsent() {
    global $conn;
    
    // Set timezone
    date_default_timezone_set('Asia/Jakarta');
    
    // Ambil tanggal kemarin
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    
    // Query untuk mendapatkan karyawan yang belum absen kemarin
    $query = "INSERT INTO absensi (user_id, tanggal, status, created_at)
              SELECT u.id, '$yesterday', 'tidak hadir', NOW()
              FROM users u
              WHERE u.role = 'karyawan'
              AND NOT EXISTS (
                  SELECT 1 FROM absensi a 
                  WHERE a.user_id = u.id 
                  AND a.tanggal = '$yesterday'
              )";
              
    mysqli_query($conn, $query);
    
    // Log aktivitas auto marking
    $affected_rows = mysqli_affected_rows($conn);
    if($affected_rows > 0) {
        $log_query = "INSERT INTO activity_logs (activity, keterangan, waktu) 
                     VALUES ('auto_mark_absent', 
                             'Auto marked $affected_rows employees as absent for $yesterday', 
                             NOW())";
        mysqli_query($conn, $log_query);
    }
    
    return $affected_rows;
}