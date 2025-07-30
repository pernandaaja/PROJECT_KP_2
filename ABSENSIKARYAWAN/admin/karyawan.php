<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Cek login dan role admin
cek_login();
cek_admin();

// Proses tambah karyawan
if (isset($_POST['tambah'])) {
    $nik = validate_input($_POST['nik']);
    $nama = validate_input($_POST['nama']);
    $email = validate_input($_POST['email']);
    $password = validate_input($_POST['password']);
    $divisi_id = validate_input($_POST['divisi_id']);
    $jam_kerja_id = validate_input($_POST['jam_kerja_id']);
    
    // Cek duplikat NIK dan email
    if (cek_duplikat_nik($nik)) {
        set_alert('danger', 'NIK sudah digunakan');
    } elseif (cek_duplikat_email($email)) {
        set_alert('danger', 'Email sudah digunakan');
    } else {
        $query = "INSERT INTO users (nik, nama, email, password, divisi_id, jam_kerja_id, role) 
                 VALUES ('$nik', '$nama', '$email', '$password', $divisi_id, $jam_kerja_id, 'karyawan')";
        if (mysqli_query($conn, $query)) {
            set_alert('success', 'Karyawan berhasil ditambahkan');
        } else {
            set_alert('danger', 'Gagal menambahkan karyawan');
        }
    }
    header('Location: karyawan.php');
    exit();
}

// Proses edit karyawan
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $nik = validate_input($_POST['nik']);
    $nama = validate_input($_POST['nama']);
    $email = validate_input($_POST['email']);
    $divisi_id = validate_input($_POST['divisi_id']);
    $jam_kerja_id = validate_input($_POST['jam_kerja_id']);
    
    // Cek duplikat NIK dan email
    if (cek_duplikat_nik($nik, $id)) {
        set_alert('danger', 'NIK sudah digunakan');
    } elseif (cek_duplikat_email($email, $id)) {
        set_alert('danger', 'Email sudah digunakan');
    } else {
        $password_query = "";
        if (!empty($_POST['password'])) {
            $password = validate_input($_POST['password']);
            $password_query = ", password = '$password'";
        }
        
        $query = "UPDATE users SET 
                 nik = '$nik',
                 nama = '$nama',
                 email = '$email',
                 divisi_id = $divisi_id,
                 jam_kerja_id = $jam_kerja_id
                 $password_query
                 WHERE id = $id";
        
        if (mysqli_query($conn, $query)) {
            set_alert('success', 'Data karyawan berhasil diupdate');
        } else {
            set_alert('danger', 'Gagal mengupdate data karyawan');
        }
    }
    header('Location: karyawan.php');
    exit();
}

// Proses hapus karyawan
if (isset($_POST['hapus'])) {
    $id = $_POST['id'];
    
    // Cek apakah ada data absensi
    $query_cek = "SELECT COUNT(*) as total FROM absensi WHERE user_id = $id";
    $result_cek = mysqli_query($conn, $query_cek);
    $data_cek = mysqli_fetch_assoc($result_cek);
    
    if ($data_cek['total'] > 0) {
        set_alert('danger', 'Karyawan tidak dapat dihapus karena memiliki data absensi');
    } else {
        $query = "DELETE FROM users WHERE id = $id";
        if (mysqli_query($conn, $query)) {
            set_alert('success', 'Data karyawan berhasil dihapus');
        } else {
            set_alert('danger', 'Gagal menghapus data karyawan');
        }
    }
    header('Location: karyawan.php');
    exit();
}

// Ambil data divisi
$query_divisi = "SELECT * FROM divisi ORDER BY nama_divisi ASC";
$result_divisi = mysqli_query($conn, $query_divisi);
$divisi_options = array();
while ($row = mysqli_fetch_assoc($result_divisi)) {
    $divisi_options[$row['id']] = $row['nama_divisi'];
}

// Ambil data jam kerja
$query_jam_kerja = "SELECT * FROM jam_kerja ORDER BY jam_masuk ASC";
$result_jam_kerja = mysqli_query($conn, $query_jam_kerja);
$jam_kerja_options = array();
while ($row = mysqli_fetch_assoc($result_jam_kerja)) {
    $jam_kerja_options[$row['id']] = $row['nama_shift'] . ' (' . 
                                    date('H:i', strtotime($row['jam_masuk'])) . ' - ' . 
                                    date('H:i', strtotime($row['jam_pulang'])) . ')';
}

// Ambil data karyawan
$query = "SELECT u.*, d.nama_divisi, j.nama_shift, j.jam_masuk, j.jam_pulang 
          FROM users u 
          LEFT JOIN divisi d ON u.divisi_id = d.id 
          LEFT JOIN jam_kerja j ON u.jam_kerja_id = j.id 
          WHERE u.role = 'karyawan' 
          ORDER BY u.nama ASC";
$result = mysqli_query($conn, $query);
?>

<?php include '../includes/header_admin.php'; ?>

<div class="col-md-9 col-lg-10 py-3">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Data Karyawan</h2>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class='bx bx-plus'></i> Tambah Karyawan
            </button>
        </div>

        <?php show_alert(); ?>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NIK</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Divisi</th>
                                <th>Jam Kerja</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            while ($row = mysqli_fetch_assoc($result)): 
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= $row['nik'] ?></td>
                                <td><?= $row['nama'] ?></td>
                                <td><?= $row['email'] ?></td>
                                <td><?= $row['nama_divisi'] ?></td>
                                <td>
                                    <?= $row['nama_shift'] ?><br>
                                    <small class="text-muted">
                                        <?= date('H:i', strtotime($row['jam_masuk'])) ?> - 
                                        <?= date('H:i', strtotime($row['jam_pulang'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-warning" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalEdit<?= $row['id'] ?>">
                                        <i class='bx bx-edit'></i> Edit
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalHapus<?= $row['id'] ?>">
                                        <i class='bx bx-trash'></i> Hapus
                                    </button>
                                </td>
                            </tr>

                            <!-- Modal Edit -->
                            <div class="modal fade" id="modalEdit<?= $row['id'] ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Karyawan</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                <div class="mb-3">
                                                    <label class="form-label">NIK</label>
                                                    <input type="text" class="form-control" name="nik" 
                                                           value="<?= $row['nik'] ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Nama</label>
                                                    <input type="text" class="form-control" name="nama" 
                                                           value="<?= $row['nama'] ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Email</label>
                                                    <input type="email" class="form-control" name="email" 
                                                           value="<?= $row['email'] ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Password Baru (kosongkan jika tidak diubah)</label>
                                                    <input type="password" class="form-control" name="password">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Divisi</label>
                                                    <select class="form-select" name="divisi_id" required>
                                                        <?php foreach ($divisi_options as $id => $nama): ?>
                                                        <option value="<?= $id ?>" <?= $row['divisi_id'] == $id ? 'selected' : '' ?>>
                                                            <?= $nama ?>
                                                        </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Jam Kerja</label>
                                                    <select class="form-select" name="jam_kerja_id" required>
                                                        <?php foreach ($jam_kerja_options as $id => $nama): ?>
                                                        <option value="<?= $id ?>" <?= $row['jam_kerja_id'] == $id ? 'selected' : '' ?>>
                                                            <?= $nama ?>
                                                        </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" name="edit" class="btn btn-primary">Simpan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Hapus -->
                            <div class="modal fade" id="modalHapus<?= $row['id'] ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Konfirmasi Hapus</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            Apakah Anda yakin ingin menghapus karyawan "<?= $row['nama'] ?>"?
                                        </div>
                                        <form method="POST">
                                            <div class="modal-footer">
                                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" name="hapus" class="btn btn-danger">Hapus</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Karyawan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">NIK</label>
                        <input type="text" class="form-control" name="nik" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama</label>
                        <input type="text" class="form-control" name="nama" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Divisi</label>
                        <select class="form-select" name="divisi_id" required>
                            <?php foreach ($divisi_options as $id => $nama): ?>
                            <option value="<?= $id ?>"><?= $nama ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jam Kerja</label>
                        <select class="form-select" name="jam_kerja_id" required>
                            <?php foreach ($jam_kerja_options as $id => $nama): ?>
                            <option value="<?= $id ?>"><?= $nama ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

