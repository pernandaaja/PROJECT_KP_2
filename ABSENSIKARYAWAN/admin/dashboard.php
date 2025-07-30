<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Cek login dan role admin
cek_login();
cek_admin();

// Fungsi untuk menandai tidak hadir manual
if(isset($_POST['mark_absent'])) {
    if(isset($_POST['user_ids']) && is_array($_POST['user_ids'])) {
        $tanggal = date('Y-m-d');
        $admin_id = $_SESSION['user_id'];
        $success = true;
        
        // Mulai transaction
        mysqli_begin_transaction($conn);
        
        try {
            foreach($_POST['user_ids'] as $user_id) {
                $user_id = (int)$user_id; // Sanitize input
                
                // Cek apakah sudah ada absensi
                $check_query = "SELECT id FROM absensi 
                              WHERE user_id = $user_id 
                              AND tanggal = '$tanggal'";
                $check_result = mysqli_query($conn, $check_query);
                
                if(mysqli_num_rows($check_result) > 0) {
                    // Update absensi yang sudah ada
                    $query = "UPDATE absensi 
                             SET status = 'tidak hadir',
                                 admin_verifikasi = $admin_id
                             WHERE user_id = $user_id 
                             AND tanggal = '$tanggal'";
                } else {
                    // Insert absensi baru
                    $query = "INSERT INTO absensi 
                             (user_id, tanggal, status, admin_verifikasi) 
                             VALUES ($user_id, '$tanggal', 'tidak hadir', $admin_id)";
                }
                
                if(!mysqli_query($conn, $query)) {
                    throw new Exception("Error processing user ID: $user_id");
                }
            }
            
            // Commit jika semua berhasil
            mysqli_commit($conn);
            set_alert('success', 'Berhasil menandai karyawan tidak hadir');
            
        } catch (Exception $e) {
            // Rollback jika terjadi error
            mysqli_rollback($conn);
            set_alert('danger', 'Gagal menandai karyawan tidak hadir: ' . $e->getMessage());
        }
        
        // Redirect kembali ke dashboard
        header('Location: dashboard.php');
        exit;
    } else {
        set_alert('warning', 'Pilih minimal satu karyawan');
    }
}

// Query untuk mendapatkan karyawan yang belum absen hari ini
$today = date('Y-m-d');
$query_not_present = "SELECT u.* 
                     FROM users u 
                     WHERE u.role = 'karyawan'
                     AND NOT EXISTS (
                         SELECT 1 FROM absensi a 
                         WHERE a.user_id = u.id 
                         AND a.tanggal = '$today'
                     )
                     ORDER BY u.nik";
$result_not_present = mysqli_query($conn, $query_not_present);

// Hitung total karyawan
$query_total = "SELECT COUNT(*) as total FROM users WHERE role = 'karyawan'";
$result_total = mysqli_query($conn, $query_total);
$total_karyawan = mysqli_fetch_assoc($result_total)['total'];

// Hitung yang sudah absen
$query_present = "SELECT COUNT(DISTINCT user_id) as total 
                 FROM absensi 
                 WHERE tanggal = '$today'";
$result_present = mysqli_query($conn, $query_present);
$total_present = mysqli_fetch_assoc($result_present)['total'];

?>

<?php include '../includes/header_admin.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
       

        <!-- Content -->
        <div class="col-md-9 col-lg-10">
            <?php show_alert(); ?>
            
            <!-- Info Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total Karyawan</h5>
                            <h2><?= $total_karyawan ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5 class="card-title">Sudah Absen</h5>
                            <h2><?= $total_present ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-warning text-dark">
                        <div class="card-body">
                            <h5 class="card-title">Belum Absen</h5>
                            <h2><?= $total_karyawan - $total_present ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monitoring Absensi -->
            <div class="card">
                <div class="card-header bg-warning">
                    <h5 class="card-title mb-0">Monitoring Absensi Hari Ini</h5>
                </div>
                <div class="card-body">
                    <?php if(mysqli_num_rows($result_not_present) > 0): ?>
                        <form method="POST" id="formAbsent">
                            <div class="alert alert-warning">
                                <h6>Karyawan yang belum absen:</h6>
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="checkAll">
                                        <label class="form-check-label" for="checkAll">
                                            Pilih Semua
                                        </label>
                                    </div>
                                </div>
                                <ul class="list-unstyled">
                                    <?php while($row = mysqli_fetch_assoc($result_not_present)): ?>
                                        <li>
                                            <div class="form-check">
                                                <input class="form-check-input user-check" type="checkbox" 
                                                       name="user_ids[]" value="<?= $row['id'] ?>" 
                                                       id="check_<?= $row['id'] ?>">
                                                <label class="form-check-label" for="check_<?= $row['id'] ?>">
                                                    <?= htmlspecialchars($row['nama']) ?> 
                                                    (<?= htmlspecialchars($row['nik']) ?>)
                                                </label>
                                            </div>
                                        </li>
                                    <?php endwhile; ?>
                                </ul>
                                <button type="submit" name="mark_absent" class="btn btn-danger" 
                                        onclick="return confirm('Apakah Anda yakin ingin menandai karyawan yang dipilih sebagai tidak hadir?')">
                                    <i class='bx bx-x-circle'></i> Tandai Tidak Hadir
                                </button>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-success">
                            Semua karyawan sudah melakukan absensi hari ini.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const formAbsent = document.getElementById('formAbsent');
    const checkAll = document.getElementById('checkAll');
    const userChecks = document.getElementsByClassName('user-check');

    if(checkAll) {
        checkAll.addEventListener('change', function() {
            Array.from(userChecks).forEach(check => {
                check.checked = this.checked;
            });
        });
    }

    if(formAbsent) {
        formAbsent.addEventListener('submit', function(e) {
            const checked = formAbsent.querySelectorAll('input[name="user_ids[]"]:checked');
            if(checked.length === 0) {
                e.preventDefault();
                alert('Pilih minimal satu karyawan!');
            }
        });
    }
});
</script>
