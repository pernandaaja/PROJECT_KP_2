<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

if (isset($_POST['nik'])) {
    $nik = validate_input($_POST['nik']);
    
    $query = "SELECT j.jam_masuk, j.jam_pulang 
              FROM users u 
              JOIN jam_kerja j ON u.jam_kerja_id = j.id 
              WHERE u.nik = '$nik'";
              
    $result = mysqli_query($conn, $query);
    
    if ($row = mysqli_fetch_assoc($result)) {
        echo json_encode([
            'jam_masuk' => $row['jam_masuk'],
            'jam_pulang' => $row['jam_pulang']
        ]);
    } else {
        echo json_encode(['error' => 'NIK tidak ditemukan']);
    }
}
?>