<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
// Ganti require autoload dengan require FPDF langsung
require_once '../vendor/fpdf/fpdf.php';

// Cek login dan role admin
cek_login();
cek_admin();

// Set default filter bulan dan tahun
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
$divisi_id = isset($_GET['divisi_id']) ? $_GET['divisi_id'] : '';

// Query untuk mengambil daftar divisi
$query_divisi = "SELECT * FROM divisi ORDER BY nama_divisi";
$result_divisi = mysqli_query($conn, $query_divisi);

// Query data absensi
$query = "SELECT a.*, u.nama, u.nik, d.nama_divisi, j.nama_shift, 
          j.jam_masuk as jam_kerja_masuk, j.jam_pulang as jam_kerja_pulang 
          FROM absensi a
          JOIN users u ON a.user_id = u.id
          JOIN divisi d ON u.divisi_id = d.id
          JOIN jam_kerja j ON u.jam_kerja_id = j.id
          WHERE MONTH(a.tanggal) = $bulan 
          AND YEAR(a.tanggal) = $tahun";

if ($divisi_id != '') {
    $query .= " AND u.divisi_id = $divisi_id";
}

$query .= " ORDER BY a.tanggal DESC, u.nama ASC";
$result = mysqli_query($conn, $query);

// Hitung statistik
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

<?php include '../includes/header_admin.php'; ?>



<!-- Content -->
<div class="col-md-9 col-lg-10">
    <div class="card">
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
                        <select name="divisi_id" class="form-select">
                            <option value="">Semua Divisi</option>
                            <?php while ($divisi = mysqli_fetch_assoc($result_divisi)): ?>
                                <option value="<?= $divisi['id'] ?>" <?= $divisi_id == $divisi['id'] ? 'selected' : '' ?>>
                                    <?= $divisi['nama_divisi'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">
                            <i class='bx bx-filter-alt'></i> Filter
                        </button>
                    </div>
                    <div class="col-auto">
                        <a href="cetak_laporan.php?bulan=<?= $bulan ?>&tahun=<?= $tahun ?>&divisi_id=<?= $divisi_id ?>"
                            class="btn btn-success" target="_blank">
                            <i class='bx bxs-file-pdf'></i> Cetak PDF
                        </a>
                    </div>
                </div>
            </form>

            <!-- Statistik Cards -->
            <div class="row mb-4">
                <div class="col-md-2 mb-3">
                    <div class="card bg-success text-white h-100">
                        <div class="card-body">
                            <h6 class="card-title">Sudah Absen</h6>
                            <h2 class="mb-0"><?= $total_hadir ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 mb-3">
                    <div class="card bg-warning text-dark h-100">
                        <div class="card-body">
                            <h6 class="card-title">Terlambat</h6>
                            <h2 class="mb-0"><?= $total_terlambat ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 mb-3">
                    <div class="card bg-info text-white h-100">
                        <div class="card-body">
                            <h6 class="card-title">Izin</h6>
                            <h2 class="mb-0"><?= $total_izin ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 mb-3">
                    <div class="card bg-primary text-white h-100">
                        <div class="card-body">
                            <h6 class="card-title">Sakit</h6>
                            <h2 class="mb-0"><?= $total_sakit ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 mb-3">
                    <div class="card bg-danger text-white h-100">
                        <div class="card-body">
                            <h6 class="card-title">Tidak Hadir</h6>
                            <h2 class="mb-0"><?= $total_tidak_hadir ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabel Laporan -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>NIK</th>
                            <th>Nama</th>
                            <th>Divisi</th>
                            <th>Status</th>
                            <th>Jam Masuk</th>
                            <th>Jam Pulang</th>
                            <th>Keterlambatan</th>
                            <th>Alasan Terlambat</th>
                            <th>Alasan Pulang Cepat</th>
                            <th>Keterangan</th>
                            <th>Bukti</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        foreach ($absensi_data as $row):
                            // Hitung keterlambatan
                            $keterlambatan = '';
                            if ($row['status'] == 'hadir' && $row['status_keterlambatan']) {
                                $jam_masuk = strtotime($row['jam_masuk']);
                                $jam_kerja = strtotime($row['jam_kerja_masuk']);
                                $selisih = round(($jam_masuk - $jam_kerja) / 60);
                                $keterlambatan = $selisih . ' menit';
                            }
                        ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= tanggal_indonesia($row['tanggal']) ?></td>
                                <td><?= $row['nik'] ?></td>
                                <td><?= $row['nama'] ?></td>
                                <td><?= $row['nama_divisi'] ?></td>
                                <td><?= format_status_absensi($row['status'], $row['status_keterlambatan']) ?></td>
                                <td>
                                    <?php if ($row['jam_masuk']): ?>
                                        <?= date('H:i', strtotime($row['jam_masuk'])) ?>
                                        <br>
                                        <small class="text-muted">
                                            (<?= date('H:i', strtotime($row['jam_kerja_masuk'])) ?>)
                                        </small>
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
                                <td><?= $keterlambatan ? $keterlambatan : '-' ?></td>
                                <td><?= $row['catatan_masuk'] ? htmlspecialchars($row['catatan_masuk']) : '-' ?></td>
                                <td><?= $row['catatan_pulang'] ? htmlspecialchars($row['catatan_pulang']) : '-' ?></td>
                                <td>
                                    <?php if ($row['status'] == 'izin' || $row['status'] == 'sakit'): ?>
                                        <?= ucfirst($row['status']) ?>
                                    <?php elseif ($row['status_keterlambatan']): ?>
                                        Terlambat
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (($row['status'] == 'izin' || $row['status'] == 'sakit') && $row['bukti']): ?>
                                        <button type="button"
                                            class="btn btn-sm btn-info"
                                            data-bs-toggle="modal"
                                            data-bs-target="#buktiModal<?= $row['id'] ?>">
                                            <i class="bx bx-file"></i>
                                        </button>

                                        <!-- Modal Bukti -->
                                        <div class="modal fade" id="buktiModal<?= $row['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Bukti <?= ucfirst($row['status']) ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <?php
                                                        $file_ext = strtolower(pathinfo($row['bukti'], PATHINFO_EXTENSION));
                                                        if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                                                            <img src="../uploads/<?= htmlspecialchars($row['bukti']) ?>"
                                                                class="img-fluid"
                                                                alt="Bukti <?= ucfirst($row['status']) ?>">
                                                        <?php elseif ($file_ext == 'pdf'): ?>
                                                            <div class="ratio ratio-16x9">
                                                                <iframe src="../uploads/<?= htmlspecialchars($row['bukti']) ?>"
                                                                    title="Bukti <?= ucfirst($row['status']) ?>"></iframe>
                                                            </div>
                                                        <?php else: ?>
                                                            <p class="text-center">File tidak dapat ditampilkan</p>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <a href="../uploads/<?= htmlspecialchars($row['bukti']) ?>"
                                                            class="btn btn-primary"
                                                            download>
                                                            Download File
                                                        </a>
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                            Tutup
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
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