<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Cek login dan role admin
cek_login();
cek_admin();

// Proses tambah jam kerja
if (isset($_POST['tambah'])) {
    $nama_shift = validate_input($_POST['nama_shift']);
    $jam_masuk = validate_input($_POST['jam_masuk']);
    $jam_pulang = validate_input($_POST['jam_pulang']);
    
    if (!empty($nama_shift) && !empty($jam_masuk) && !empty($jam_pulang)) {
        $query = "INSERT INTO jam_kerja (nama_shift, jam_masuk, jam_pulang) 
                 VALUES ('$nama_shift', '$jam_masuk', '$jam_pulang')";
        if (mysqli_query($conn, $query)) {
            set_alert('success', 'Jam kerja berhasil ditambahkan');
        } else {
            set_alert('danger', 'Gagal menambahkan jam kerja');
        }
    }
    header('Location: jam_kerja.php');
    exit();
}

// Proses edit jam kerja
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $nama_shift = validate_input($_POST['nama_shift']);
    $jam_masuk = validate_input($_POST['jam_masuk']);
    $jam_pulang = validate_input($_POST['jam_pulang']);
    
    if (!empty($nama_shift) && !empty($jam_masuk) && !empty($jam_pulang)) {
        $query = "UPDATE jam_kerja SET 
                 nama_shift = '$nama_shift',
                 jam_masuk = '$jam_masuk',
                 jam_pulang = '$jam_pulang'
                 WHERE id = $id";
        if (mysqli_query($conn, $query)) {
            set_alert('success', 'Jam kerja berhasil diupdate');
        } else {
            set_alert('danger', 'Gagal mengupdate jam kerja');
        }
    }
    header('Location: jam_kerja.php');
    exit();
}

// Proses hapus jam kerja
if (isset($_POST['hapus'])) {
    $id = $_POST['id'];
    
    // Cek apakah jam kerja masih digunakan
    $query_cek = "SELECT COUNT(*) as total FROM users WHERE jam_kerja_id = $id";
    $result_cek = mysqli_query($conn, $query_cek);
    $data_cek = mysqli_fetch_assoc($result_cek);
    
    if ($data_cek['total'] > 0) {
        set_alert('danger', 'Jam kerja tidak dapat dihapus karena masih digunakan');
    } else {
        $query = "DELETE FROM jam_kerja WHERE id = $id";
        if (mysqli_query($conn, $query)) {
            set_alert('success', 'Jam kerja berhasil dihapus');
        } else {
            set_alert('danger', 'Gagal menghapus jam kerja');
        }
    }
    header('Location: jam_kerja.php');
    exit();
}

// Ambil data jam kerja
$query = "SELECT * FROM jam_kerja ORDER BY jam_masuk ASC";
$result = mysqli_query($conn, $query);
?>

<?php include '../includes/header_admin.php'; ?>


<div class="col-md-9 col-lg-10 py-3">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Data Jam Kerja</h2>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class='bx bx-plus'></i> Tambah Jam Kerja
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
                                <th>Nama Shift</th>
                                <th>Jam Masuk</th>
                                <th>Jam Pulang</th>
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
                                <td><?= $row['nama_shift'] ?></td>
                                <td><?= date('H:i', strtotime($row['jam_masuk'])) ?></td>
                                <td><?= date('H:i', strtotime($row['jam_pulang'])) ?></td>
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
                                            <h5 class="modal-title">Edit Jam Kerja</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                <div class="mb-3">
                                                    <label class="form-label">Nama Shift</label>
                                                    <input type="text" class="form-control" name="nama_shift" 
                                                           value="<?= $row['nama_shift'] ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Jam Masuk</label>
                                                    <input type="time" class="form-control" name="jam_masuk" 
                                                           value="<?= date('H:i', strtotime($row['jam_masuk'])) ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Jam Pulang</label>
                                                    <input type="time" class="form-control" name="jam_pulang" 
                                                           value="<?= date('H:i', strtotime($row['jam_pulang'])) ?>" required>
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
                                            Apakah Anda yakin ingin menghapus jam kerja "<?= $row['nama_shift'] ?>"?
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
                <h5 class="modal-title">Tambah Jam Kerja</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Shift</label>
                        <input type="text" class="form-control" name="nama_shift" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jam Masuk</label>
                        <input type="time" class="form-control" name="jam_masuk" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jam Pulang</label>
                        <input type="time" class="form-control" name="jam_pulang" required>
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

