<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$error = '';
$success = '';

if (isset($_POST['submit'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $nik = mysqli_real_escape_string($conn, $_POST['nik']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $divisi_id = !empty($_POST['divisi_id']) ? (int)$_POST['divisi_id'] : NULL;
    $jam_kerja_id = !empty($_POST['jam_kerja_id']) ? (int)$_POST['jam_kerja_id'] : NULL;
    $role = 'karyawan';

    // Cek field yang wajib diisi
    if (empty($nama) || empty($nik) || empty($email) || empty($password)) {
        $error = "Nama, NIK, Email dan Password harus diisi!";
    } else {
        // Cek NIK duplikat
        $check_nik = mysqli_query($conn, "SELECT nik FROM users WHERE nik = '$nik'");
        if (mysqli_num_rows($check_nik) > 0) {
            $error = "NIK sudah terdaftar!";
        } else {
            // Cek email duplikat
            $check_email = mysqli_query($conn, "SELECT email FROM users WHERE email = '$email'");
            if (mysqli_num_rows($check_email) > 0) {
                $error = "Email sudah terdaftar!";
            } else {
                // Insert data
                $query = "INSERT INTO users (nama, nik, email, password, divisi_id, jam_kerja_id, role) 
                         VALUES ('$nama', '$nik', '$email', '$password', " . 
                         ($divisi_id === NULL ? "NULL" : $divisi_id) . ", " . 
                         ($jam_kerja_id === NULL ? "NULL" : $jam_kerja_id) . ", '$role')";
                
                if (mysqli_query($conn, $query)) {
                    $success = "Registrasi berhasil! Silakan login.";
                } else {
                    $error = "Error: " . mysqli_error($conn);
                }
            }
        }
    }
}

// Get data divisi
$query_divisi = mysqli_query($conn, "SELECT * FROM divisi ORDER BY nama_divisi");

// Get data jam kerja
$query_jam_kerja = mysqli_query($conn, "SELECT * FROM jam_kerja ORDER BY nama_shift");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Sistem Absensi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h4 class="mb-0">Register</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error) : ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>

                        <?php if ($success) : ?>
                            <div class="alert alert-success">
                                <?= $success ?>
                                <br>
                                <a href="login.php" class="btn btn-primary btn-sm mt-2">Login Sekarang</a>
                            </div>
                        <?php else : ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama" name="nama" 
                                       value="<?= isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : '' ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="nik" class="form-label">NIK <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nik" name="nik"
                                       value="<?= isset($_POST['nik']) ? htmlspecialchars($_POST['nik']) : '' ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email"
                                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="divisi_id" class="form-label">Divisi</label>
                                <select class="form-select" id="divisi_id" name="divisi_id">
                                    <option value="">Pilih Divisi</option>
                                    <?php while ($divisi = mysqli_fetch_assoc($query_divisi)) : ?>
                                        <option value="<?= $divisi['id'] ?>" 
                                                <?= (isset($_POST['divisi_id']) && $_POST['divisi_id'] == $divisi['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($divisi['nama_divisi']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="jam_kerja_id" class="form-label">Jam Kerja</label>
                                <select class="form-select" id="jam_kerja_id" name="jam_kerja_id">
                                    <option value="">Pilih Jam Kerja</option>
                                    <?php while ($jam_kerja = mysqli_fetch_assoc($query_jam_kerja)) : ?>
                                        <option value="<?= $jam_kerja['id'] ?>"
                                                <?= (isset($_POST['jam_kerja_id']) && $_POST['jam_kerja_id'] == $jam_kerja['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($jam_kerja['nama_shift']) ?> 
                                            (<?= $jam_kerja['jam_masuk'] ?> - <?= $jam_kerja['jam_pulang'] ?>)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" name="submit" class="btn btn-primary">Register</button>
                                <a href="index.php" class="btn btn-secondary">Kembali</a>
                            </div>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>