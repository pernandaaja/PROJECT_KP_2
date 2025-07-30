<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Cek login dan role admin
cek_login();
cek_admin();

// Ambil data karyawan
$id = (int)$_GET['id'];
$query = "SELECT * FROM users WHERE id = $id AND role = 'karyawan'";
$result = mysqli_query($conn, $query);
$karyawan = mysqli_fetch_assoc($result);

if(!$karyawan) {
    set_alert('danger', 'Data karyawan tidak ditemukan');
    header('Location: karyawan.php');
    exit;
}

// Query untuk mengambil data divisi
$query_divisi = "SELECT * FROM divisi ORDER BY nama_divisi";
$result_divisi = mysqli_query($conn, $query_divisi);

// Query untuk mengambil data jam kerja
$query_jam_kerja = "SELECT * FROM jam_kerja ORDER BY nama_shift";
$result_jam_kerja = mysqli_query($conn, $query_jam_kerja);

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nik = mysqli_real_escape_string($conn, $_POST['nik']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $divisi_id = (int)$_POST['divisi_id'];
    $jam_kerja_id = (int)$_POST['jam_kerja_id'];
    
    // Cek NIK unik kecuali untuk karyawan yang sedang diedit
    $check_nik = mysqli_query($conn, "SELECT id FROM users WHERE nik = '$nik' AND id != $id");
    if(mysqli_num_rows($check_nik) > 0) {
        set_alert('danger', 'NIK sudah digunakan');
    } else {
        $query = "UPDATE users SET 
                  nik = '$nik',
                  nama = '$nama',
                  email = '$email',
                  divisi_id = $divisi_id,
                  jam_kerja_id = $jam_kerja_id";
        
        // Update password jika diisi
        if(!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $query .= ", password = '$password'";
        }
        
        $query .= " WHERE id = $id";
        
        if(mysqli_query($conn, $query)) {
            set_alert('success', 'Data karyawan berhasil diupdate');
            header('Location: karyawan.php');
            exit;
        } else {
            set_alert('danger', 'Gagal mengupdate data karyawan');
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2">
            <div class="sidebar">
                <!-- Sama seperti di karyawan.php -->
            </div>
        </div>

        <!-- Content -->
        <div class="col-md-9 col-lg-10">
            <?php show_alert(); ?>
            
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Edit Karyawan</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="nik" class="form-label">NIK</label>
                            <input type="text" class="form-control" id="nik" name="nik" 
                                   value