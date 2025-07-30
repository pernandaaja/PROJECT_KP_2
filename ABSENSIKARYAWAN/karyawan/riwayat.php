<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Cek login
cek_login();

// Ambil user_id dari session
$user_id = $_SESSION['user_id'];

// Set default filter bulan dan tahun
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

// Query untuk mengambil data absensi
$query = "SELECT a.*, u.nama, u.nik, d.nama_divisi, j.nama_shift, 
          j.jam_masuk as jam_kerja_masuk, j.jam_pulang as jam_kerja_pulang,
          a.catatan_admin 
          FROM absensi a
          JOIN users u ON a.user_id = u.id
          JOIN divisi d ON u.divisi_id = d.id
          JOIN jam_kerja j ON u.jam_kerja_id = j.id
          WHERE a.user_id = $user_id 
          AND MONTH(a.tanggal) = $bulan 
          AND YEAR(a.tanggal) = $tahun
          ORDER BY a.tanggal DESC";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Error: " . mysqli_error($conn));
}

// Hitung statistik
$total_hari = date('t', strtotime("$tahun-$bulan-01"));
$total_hadir = 0;
$total_terlambat = 0;
$total_izin = 0;
$total_sakit = 0;
$total_tidak_hadir = 0;

$absensi_data = array();
while ($row = mysqli_fetch_assoc($result)) {
    $absensi_data[] = $row;
    if ($row['status'] == 'hadir') {
        $total_hadir++;
        if ($row['status_keterlambatan']) {
            $total_terlambat++;
        }
    } elseif ($row['status'] == 'izin') {
        $total_izin++;
    } elseif ($row['status'] == 'sakit') {
        $total_sakit++;
    } elseif ($row['status'] == 'tidak hadir') {
        $total_tidak_hadir++;
    }
}
?>

<?php include '../includes/header_karyawan.php'; ?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Riwayat Absensi</h5>
                </div>
                <div class="card-body">
                    <!-- Filter -->
                    <form method="GET" class="mb-4">
                        <div class="row g-3 align-items-center">
                            <div class="col-auto">
                                <label class="col-form-label">Filter:</label>
                            </div>
                            <div class="col-auto">
                                <select name="bulan" class="form-select">
                                    <?php for ($i = 1; $i <= 12; $i++): ?>
                                        <option value="<?= sprintf('%02d', $i) ?>" <?= $bulan == sprintf('%02d', $i) ? 'selected' : '' ?>>
                                            <?= date('F', mktime(0, 0, 0, $i, 1)) ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-auto">
                                <select name="tahun" class="form-select">
                                    <?php for ($i = date('Y'); $i >= date('Y') - 5; $i--): ?>
                                        <option value="<?= $i ?>" <?= $tahun == $i ? 'selected' : '' ?>>
                                            <?= $i ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary">
                                    <i class='bx bx-filter-alt'></i> Filter
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Statistik Cards -->
                    <div class="row mb-4">
                        <div class="col-md-2 mb-3">
                            <div class="card bg-success text-white h-100">
                                <div class="card-body">
                                    <h6 class="card-title">Hadir</h6>
                                    <h2 class="mb-0"><?= $total_hadir ?></h2>
                                    <small>dari <?= $total_hari ?> hari</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 mb-3">
                            <div class="card bg-warning text-dark h-100">
                                <div class="card-body">
                                    <h6 class="card-title">Terlambat</h6>
                                    <h2 class="mb-0"><?= $total_terlambat ?></h2>
                                    <small>kali</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 mb-3">
                            <div class="card bg-info text-white h-100">
                                <div class="card-body">
                                    <h6 class="card-title">Izin</h6>
                                    <h2 class="mb-0"><?= $total_izin ?></h2>
                                    <small>hari</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 mb-3">
                            <div class="card bg-primary text-white h-100">
                                <div class="card-body">
                                    <h6 class="card-title">Sakit</h6>
                                    <h2 class="mb-0"><?= $total_sakit ?></h2>
                                    <small>hari</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 mb-3">
                            <div class="card bg-danger text-white h-100">
                                <div class="card-body">
                                    <h6 class="card-title">Tidak Hadir</h6>
                                    <h2 class="mb-0"><?= $total_tidak_hadir ?></h2>
                                    <small>hari</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabel Riwayat -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th>Jam Masuk</th>
                                    <th>Jam Pulang</th>
                                    <th>Bukti</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($absensi_data as $row):
                                    // Hitung keterlambatan
                                    $keterlambatan = '';
                                    if ($row['status'] == 'hadir' && $row['jam_masuk'] && $row['jam_kerja_masuk']) {
                                        $jam_masuk = strtotime($row['jam_masuk']);
                                        $jam_kerja = strtotime($row['jam_kerja_masuk']);

                                        // Cek jika jam masuk lebih dari jam kerja
                                        if ($jam_masuk > $jam_kerja) {
                                            $selisih = round(($jam_masuk - $jam_kerja) / 60);
                                            $keterlambatan = $selisih . ' menit';

                                            // Update status keterlambatan
                                            $row['status_keterlambatan'] = true;
                                        }
                                    }
                                ?>
                                    <tr>
                                        <td><?= tanggal_indonesia($row['tanggal']) ?></td>
                                        <td>
                                            <?php if ($row['status'] == 'hadir'): ?>
                                                <?php if ($row['status_keterlambatan']): ?>
                                                    <span class="badge bg-warning">Terlambat</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">Hadir</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <?= format_status_absensi($row['status'], $row['status_keterlambatan']) ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($row['jam_masuk']): ?>
                                                <?= date('H:i', strtotime($row['jam_masuk'])) ?>
                                                <br>
                                                <small class="text-muted">
                                                    (<?= date('H:i', strtotime($row['jam_kerja_masuk'])) ?>)
                                                </small>
                                                <?php if ($row['status_keterlambatan']): ?>
                                                    <br>
                                                    <span class="badge bg-warning">Terlambat <?= $keterlambatan ?></span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($row['jam_pulang']): ?>
                                                <?= date('H:i', strtotime($row['jam_pulang'])) ?>
                                                <br>
                                                <small class="text-muted">
                                                    (<?= date('H:i', strtotime($row['jam_kerja_pulang'])) ?>)
                                                </small>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        
                                       
                                        <td>
                                            <?php if ($row['bukti']): ?>
                                                <a href="../uploads/bukti/<?= $row['bukti'] ?>"
                                                    class="btn btn-sm btn-info"
                                                    target="_blank">
                                                    <i class='bx bx-file'></i> Lihat
                                                </a>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>