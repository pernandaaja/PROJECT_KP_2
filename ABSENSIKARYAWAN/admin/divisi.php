<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Cek login dan role admin
cek_login();
cek_admin();

// Proses tambah divisi
if (isset($_POST['tambah'])) {
    $nama_divisi = validate_input($_POST['nama_divisi']);
    
    if (!empty($nama_divisi)) {
        $query = "INSERT INTO divisi (nama_divisi) VALUES ('$nama_divisi')";
        if (mysqli_query($conn, $query)) {
            set_alert('success', 'Divisi berhasil ditambahkan');
        } else {
            set_alert('danger', 'Gagal menambahkan divisi');
        }
    }
    header('Location: divisi.php');
    exit();
}

// Proses edit divisi
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $nama_divisi = validate_input($_POST['nama_divisi']);
    
    if (!empty($nama_divisi)) {
        $query = "UPDATE divisi SET nama_divisi = '$nama_divisi' WHERE id = $id";
        if (mysqli_query($conn, $query)) {
            set_alert('success', 'Divisi berhasil diupdate');
        } else {
            set_alert('danger', 'Gagal mengupdate divisi');
        }
    }
    header('Location: divisi.php');
    exit();
}

// Proses hapus divisi
if (isset($_POST['hapus'])) {
    $id = $_POST['id'];
    
    // Cek apakah divisi masih digunakan
    $query_cek = "SELECT COUNT(*) as total FROM users WHERE divisi_id = $id";
    $result_cek = mysqli_query($conn, $query_cek);
    $data_cek = mysqli_fetch_assoc($result_cek);
    
    if ($data_cek['total'] > 0) {
        set_alert('danger', 'Divisi tidak dapat dihapus karena masih digunakan');
    } else {
        $query = "DELETE FROM divisi WHERE id = $id";
        if (mysqli_query($conn, $query)) {
            set_alert('success', 'Divisi berhasil dihapus');
        } else {
            set_alert('danger', 'Gagal menghapus divisi');
        }
    }
    header('Location: divisi.php');
    exit();
}

// Ambil data divisi
$query = "SELECT * FROM divisi ORDER BY nama_divisi ASC";
$result = mysqli_query($conn, $query);
?>

<?php include '../includes/header_admin.php'; ?>


<div class="col-md-9 col-lg-10 py-3">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Data Divisi</h2>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class='bx bx-plus'></i> Tambah Divisi
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
                                <th>Nama Divisi</th>
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
                                <td><?= $row['nama_divisi'] ?></td>
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
                                            <h5 class="modal-title">Edit Divisi</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                <div class="mb-3">
                                                    <label class="form-label">Nama Divisi</label>
                                                    <input type="text" class="form-control" name="nama_divisi" 
                                                           value="<?= $row['nama_divisi'] ?>" required>
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
                                            Apakah Anda yakin ingin menghapus divisi "<?= $row['nama_divisi'] ?>"?
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
                <h5 class="modal-title">Tambah Divisi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Divisi</label>
                        <input type="text" class="form-control" name="nama_divisi" required>
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

