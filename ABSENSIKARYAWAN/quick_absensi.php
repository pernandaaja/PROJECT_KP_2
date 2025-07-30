<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Set timezone
date_default_timezone_set('Asia/Jakarta');

$error = "";
$success = "";

// Inisialisasi variabel
$jenis_absen = isset($_POST['jenis_absen']) ? validate_input($_POST['jenis_absen']) : '';
$nik = isset($_POST['nik']) ? validate_input($_POST['nik']) : '';

// Inisialisasi variabel tanggal dan waktu
$today = date('Y-m-d');
$now = date('H:i:s');

if (isset($_POST['submit'])) {
    if (empty($nik)) {
        $error = "NIK harus diisi!";
    } elseif (empty($jenis_absen)) {
        $error = "Jenis absen harus dipilih!";
    } else {
        // Cek NIK karyawan
        $query = "SELECT * FROM users WHERE nik = '$nik'";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);
            $user_id = $user['id'];

            // Ambil informasi jam kerja
            $query = "SELECT j.* FROM users u 
                     JOIN jam_kerja j ON u.jam_kerja_id = j.id 
                     WHERE u.id = $user_id";
            $result = mysqli_query($conn, $query);
            $jam_kerja = mysqli_fetch_assoc($result);

            // Cek apakah sudah absen hari ini
            $query_check = "SELECT * FROM absensi WHERE user_id = $user_id AND tanggal = '$today'";
            $result_check = mysqli_query($conn, $query_check);
            $absensi = mysqli_fetch_assoc($result_check);

            if ($jenis_absen == 'masuk') {
                if (!$absensi) {
                    // Absen Masuk
                    $status = 'hadir';
                    $status_keterlambatan = 0;
                    $catatan_masuk = '';

                    // Cek keterlambatan
                    if (strtotime($now) > strtotime($jam_kerja['jam_masuk'])) {
                        $status_keterlambatan = 1;
                        
                        // Validasi alasan terlambat
                        if (empty($_POST['alasan_terlambat'])) {
                            $error = "Anda terlambat! Harap isi alasan keterlambatan.";
                        } else {
                            $catatan_masuk = mysqli_real_escape_string($conn, $_POST['alasan_terlambat']);
                        }
                    }

                    if (!$error) {
                        $query = "INSERT INTO absensi (
                            user_id, 
                            tanggal, 
                            jam_masuk, 
                            status, 
                            jenis_absen,
                            status_keterlambatan, 
                            catatan_masuk,
                            created_at,
                            updated_at
                        ) VALUES (
                            $user_id, 
                            '$today', 
                            '$now', 
                            '$status', 
                            'masuk',
                            $status_keterlambatan, 
                            '$catatan_masuk',
                            NOW(),
                            NOW()
                        )";

                        if (mysqli_query($conn, $query)) {
                            $success = 'Absen masuk berhasil' . ($status_keterlambatan ? ' (Terlambat)' : '');
                        } else {
                            $error = 'Gagal melakukan absen masuk: ' . mysqli_error($conn);
                        }
                    }
                } else {
                    $error = "Anda sudah absen masuk hari ini!";
                }
            } elseif ($jenis_absen == 'pulang') {
                if ($absensi) {
                    // Cek jika sudah absen pulang
                    if ($absensi['jam_pulang']) {
                        $error = "Anda sudah absen pulang hari ini!";
                    }
                    // Jika belum absen pulang
                    else {
                        $status = 'hadir';
                        $catatan_pulang = '';

                        // Cek pulang lebih awal
                        if (strtotime($now) < strtotime($jam_kerja['jam_pulang'])) {
                            // Validasi alasan pulang cepat
                            if (empty($_POST['alasan_pulang_cepat'])) {
                                $error = "Anda pulang lebih awal! Harap isi alasan pulang cepat.";
                            } else {
                                $catatan_pulang = mysqli_real_escape_string($conn, $_POST['alasan_pulang_cepat']);
                            }
                        }

                        if (!$error) {
                            $query = "UPDATE absensi SET 
                                jam_pulang = '$now',
                                status = '$status',
                                catatan_pulang = '$catatan_pulang',
                                updated_at = NOW()
                             WHERE id = " . $absensi['id'];

                            if (mysqli_query($conn, $query)) {
                                $success = 'Absen pulang berhasil' . ($catatan_pulang ? ' (Pulang cepat)' : '');
                            } else {
                                $error = 'Gagal melakukan absen pulang: ' . mysqli_error($conn);
                            }
                        }
                    }
                } else {
                    $error = "Anda belum absen masuk hari ini!";
                }
            } elseif (in_array($jenis_absen, ['izin', 'sakit'])) {
                if (!$absensi) {
                    // Upload bukti
                    $bukti = "";
                    if (isset($_FILES['bukti']) && $_FILES['bukti']['error'] == 0) {
                        $bukti = upload_file($_FILES['bukti'], $jenis_absen);
                        if (!$bukti) {
                            $error = "Gagal mengupload bukti! Pastikan file adalah JPG/PNG/PDF dan ukuran max 5MB.";
                        }
                    } else {
                        $error = "Bukti wajib diupload untuk izin/sakit!";
                    }

                    if (!$error) {
                        $query = "INSERT INTO absensi (
                            user_id, 
                            tanggal,
                            jam_masuk,
                            jam_pulang,
                            status, 
                            jenis_absen, 
                            bukti,
                            status_keterlambatan,
                            catatan_masuk,
                            catatan_pulang,
                            created_at,
                            updated_at
                        ) VALUES (
                            $user_id, 
                            '$today',
                            NULL,
                            NULL,
                            '$jenis_absen',
                            '$jenis_absen',
                            '$bukti',
                            0,
                            NULL,
                            NULL,
                            NOW(),
                            NOW()
                        )";

                        if (mysqli_query($conn, $query)) {
                            $success = "Pengajuan " . ucfirst($jenis_absen) . " berhasil disubmit!";
                        } else {
                            $error = "Gagal mengajukan " . $jenis_absen . ": " . mysqli_error($conn);
                        }
                    }
                } else {
                    $error = "Anda sudah melakukan absensi hari ini!";
                }
            }
        } else {
            $error = "NIK tidak ditemukan!";
        }
    }
}
?>

<!-- HTML dan JavaScript sama seperti sebelumnya, hanya perlu menghapus pesan "Menunggu verifikasi admin" -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi Cepat - Sistem Absensi Karyawan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title text-center mb-4">Absensi Cepat</h3>

                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <?= $error ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <?= $success ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                <br>
                                <a href="index.php" class="btn btn-sm btn-primary mt-2">Kembali ke Beranda</a>
                            </div>
                        <?php else: ?>
                            <form method="POST" enctype="multipart/form-data" id="absensiForm">
                                <div class="mb-3">
                                    <label for="nik" class="form-label">NIK</label>
                                    <input type="text" class="form-control" id="nik" name="nik" value="<?= htmlspecialchars($nik) ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="jenis_absen" class="form-label">Jenis Absensi</label>
                                    <select class="form-select" id="jenis_absen" name="jenis_absen" required>
                                        <option value="">Pilih Jenis Absensi</option>
                                        <option value="masuk" <?= $jenis_absen === 'masuk' ? 'selected' : '' ?>>Masuk</option>
                                        <option value="pulang" <?= $jenis_absen === 'pulang' ? 'selected' : '' ?>>Pulang</option>
                                        <option value="izin" <?= $jenis_absen === 'izin' ? 'selected' : '' ?>>Izin</option>
                                        <option value="sakit" <?= $jenis_absen === 'sakit' ? 'selected' : '' ?>>Sakit</option>
                                    </select>
                                </div>

                                <!-- Field untuk alasan terlambat -->
                                <div class="mb-3 d-none" id="alasan_terlambat_wrapper">
                                    <label for="alasan_terlambat" class="form-label">Alasan Terlambat</label>
                                    <textarea class="form-control" id="alasan_terlambat" name="alasan_terlambat" rows="3"></textarea>
                                    <div class="form-text text-danger">Anda terlambat. Harap isi alasan keterlambatan.</div>
                                </div>

                                <!-- Field untuk alasan pulang cepat -->
                                <div class="mb-3 d-none" id="alasan_pulang_cepat_wrapper">
                                    <label for="alasan_pulang_cepat" class="form-label">Alasan Pulang Cepat</label>
                                    <textarea class="form-control" id="alasan_pulang_cepat" name="alasan_pulang_cepat" rows="3"></textarea>
                                    <div class="form-text text-danger">Anda pulang lebih awal. Harap isi alasan.</div>
                                </div>

                                <!-- Field untuk upload bukti -->
                                <div id="bukti_wrapper" class="mb-3 d-none">
                                    <label for="bukti" class="form-label">Upload Bukti</label>
                                    <input type="file" class="form-control" id="bukti" name="bukti">
                                    <small class="text-muted">Format: JPG, JPEG, PNG, PDF (Max. 5MB)</small>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" name="submit" class="btn btn-primary">Submit Absensi</button>
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
    <script>
    $(document).ready(function() {
        function checkAbsensiTime() {
            const nik = $('#nik').val();
            const jenis = $('#jenis_absen').val();
            
            if (!nik) return;

            $.ajax({
                url: 'check_jam_kerja.php',
                method: 'POST',
                data: { nik: nik },
                success: function(response) {
                    try {
                        const data = JSON.parse(response);
                        const now = new Date();
                        const currentTime = now.getHours().toString().padStart(2, '0') + ':' + 
                                          now.getMinutes().toString().padStart(2, '0') + ':' + 
                                          now.getSeconds().toString().padStart(2, '0');
                        
                        if (jenis === 'masuk') {
                            if (currentTime > data.jam_masuk) {
                                $('#alasan_terlambat_wrapper').removeClass('d-none');
                                $('#alasan_terlambat').prop('required', true);
                            } else {
                                $('#alasan_terlambat_wrapper').addClass('d-none');
                                $('#alasan_terlambat').prop('required', false);
                            }
                        } else if (jenis === 'pulang') {
                            if (currentTime < data.jam_pulang) {
                                $('#alasan_pulang_cepat_wrapper').removeClass('d-none');
                                $('#alasan_pulang_cepat').prop('required', true);
                            } else {
                                $('#alasan_pulang_cepat_wrapper').addClass('d-none');
                                $('#alasan_pulang_cepat').prop('required', false);
                            }
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                    }
                }
            });
        }

        // Event listeners
        $('#nik').on('change keyup', checkAbsensiTime);
        $('#jenis_absen').on('change', function() {
            const jenis = $(this).val();
            
            // Reset semua wrapper
            $('#alasan_terlambat_wrapper, #alasan_pulang_cepat_wrapper, #bukti_wrapper').addClass('d-none');
            $('#alasan_terlambat, #alasan_pulang_cepat, #bukti').prop('required', false);

            if (jenis === 'izin' || jenis === 'sakit') {
                $('#bukti_wrapper').removeClass('d-none');
                $('#bukti').prop('required', true);
            } else {
                checkAbsensiTime();
            }
        });

        // Form validation
        $('#absensiForm').on('submit', function(e) {
            const jenis = $('#jenis_absen').val();
            
            if ($('#alasan_terlambat').prop('required') && !$('#alasan_terlambat').val()) {
                e.preventDefault();
                alert('Harap isi alasan keterlambatan!');
                return false;
            }
            
            if ($('#alasan_pulang_cepat').prop('required') && !$('#alasan_pulang_cepat').val()) {
                e.preventDefault();
                alert('Harap isi alasan pulang cepat!');
                return false;
            }
        });

        // Initial check
        checkAbsensiTime();
    });
    </script>
</body>
</html>