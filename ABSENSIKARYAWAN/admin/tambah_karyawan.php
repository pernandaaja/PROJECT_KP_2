<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Cek login dan role admin
cek_login();
cek_admin();

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
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $divisi_id = (int)$_POST['divisi_id'];
    $jam_kerja_id = (int)$_POST['jam_kerja_id'];
    
    // Validasi NIK unik
    $check_nik = mysqli_query($conn, "SELECT id FROM users WHERE nik = '$nik'");
    if(mysqli_num_rows($check_nik) > 0) {
        set_alert('danger', 'NIK sudah digunakan');
    } else {
        $query = "INSERT INTO users (nik, nama, email, password, divisi_id, jam_kerja_id, role) 
                  VALUES ('$nik', '$nama', '$email', '$password', $divisi_id, $jam_kerja_id, 'karyawan')";
        
        if(mysqli_query($conn, $query)) {
            set_alert('success', 'Data karyawan berhasil ditambahkan');
            header('Location: karyawan.php');
            exit;
        } else {
            set_alert('danger', 'Gagal menambahkan data karyawan');
        }
    }
}
?>

<?php include '../includes/header_admin.php'; ?>

<div class="container-fluid py-4">
    

        <!-- Content -->
        <div class="col-md-9 col-lg-10">
            <?php show_alert(); ?>
            
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Tambah Karyawan</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="nik" class="form-label">NIK</label>
                            <input type="text" class="form-control" id="nik" name="nik" required>
                        </div>
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama</label>
                            <input type="text" class="form-control" id="nama" name="nama" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="divisi_id" class="form-label">Divisi</label>
                            <select class="form-select" id="divisi_id" name="divisi_id" required>
                                <option value="">Pilih Divisi</option>
                                <?php while($divisi = mysqli_fetch_assoc($result_divisi)): ?>
                                    <option value="<?= $divisi['id'] ?>">
                                        <?= htmlspecialchars($divisi['nama_divisi']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="jam_kerja_id" class="form-label">Jam Kerja</label>
                            <select class="form-select" id="jam_kerja_id" name="jam_kerja_id" required>
                                <option value="">Pilih Jam Kerja</option>
                                <?php while($jam_kerja = mysqli_fetch_assoc($result_jam_kerja)): ?>
                                    <option value="<?= $jam_kerja['id'] ?>">
                                        <?= htmlspecialchars($jam_kerja['nama_shift']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class='bx bx-save'></i> Simpan
                            </button>
                            <a href="karyawan.php" class="btn btn-secondary">
                                <i class='bx bx-arrow-back'></i> Kembali
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
