<?php
date_default_timezone_set('Asia/Jakarta');

// Fungsi untuk mengecek login
function cek_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit();
    }
}

// Fungsi untuk mengecek role admin
function cek_admin() {
    if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
        header("Location: ../index.php");
        exit();
    }
}


// Fungsi untuk validasi login
function validate_login($email, $password) {
    global $conn;
    $query = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['role'] = $user['role'];
        return true;
    }
    return false;
}


function clean($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return mysqli_real_escape_string($conn, $data);
}


// Fungsi format tanggal Indonesia
function tanggal_indonesia($tanggal) {
    $bulan = array(
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    
    $split = explode('-', $tanggal);
    return $split[2] . ' ' . $bulan[(int)$split[1]] . ' ' . $split[0];
}

// Fungsi format waktu 24 jam
function format_waktu($waktu) {
    return date('H:i', strtotime($waktu));
}

// Fungsi untuk mengecek keterlambatan
function cek_keterlambatan($jam_masuk, $jam_kerja) {
    return strtotime($jam_masuk) > strtotime($jam_kerja);
}

// Fungsi untuk mengecek pulang cepat
function cek_pulang_cepat($jam_pulang, $jam_kerja) {
    return strtotime($jam_pulang) < strtotime($jam_kerja);
}

// Fungsi untuk upload file
function upload_file($file, $jenis) {
    // Gunakan __DIR__ untuk mendapatkan path absolut
    $target_dir = __DIR__ . "/../uploads/";
    
    // Buat direktori jika belum ada
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    // Bersihkan nama file
    $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $new_filename = date('Ymd_His') . "_" . $jenis . "_" . uniqid() . "." . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    // Cek ukuran file (max 5MB)
    if ($file["size"] > 5000000) {
        return false;
    }
    
    // Cek tipe file
    $allowed = array("jpg", "jpeg", "png", "pdf");
    if (!in_array($file_extension, $allowed)) {
        return false;
    }
    
    // Upload file
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        // Kembalikan hanya nama file untuk disimpan di database
        return $new_filename;
    }
    
    return false;
}

// Fungsi untuk mendapatkan status absensi
function get_status_absensi($user_id, $tanggal) {
    global $conn;
    $query = "SELECT * FROM absensi WHERE user_id = $user_id AND tanggal = '$tanggal'";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}

// Fungsi untuk mendapatkan jam kerja karyawan
function get_jam_kerja($jam_kerja_id) {
    global $conn;
    $query = "SELECT * FROM jam_kerja WHERE id = $jam_kerja_id";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}

// Fungsi untuk validasi input
function validate_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return mysqli_real_escape_string($conn, $data);
}

// Fungsi untuk mendapatkan data karyawan
function get_karyawan($user_id) {
    global $conn;
    $query = "SELECT u.*, d.nama_divisi, j.nama_shift, j.jam_masuk, j.jam_pulang 
              FROM users u 
              JOIN divisi d ON u.divisi_id = d.id 
              JOIN jam_kerja j ON u.jam_kerja_id = j.id 
              WHERE u.id = $user_id";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}

// Fungsi untuk mendapatkan statistik absensi
function get_statistik_absensi($tanggal) {
    global $conn;
    
    // Total karyawan
    $query_karyawan = "SELECT COUNT(*) as total FROM users WHERE role = 'karyawan'";
    $total_karyawan = mysqli_fetch_assoc(mysqli_query($conn, $query_karyawan))['total'];
    
    // Hadir hari ini
    $query_hadir = "SELECT COUNT(*) as total FROM absensi 
                    WHERE tanggal = '$tanggal' AND status = 'hadir'";
    $hadir = mysqli_fetch_assoc(mysqli_query($conn, $query_hadir))['total'];
    
    // Izin/Sakit hari ini
    $query_izin = "SELECT COUNT(*) as total FROM absensi 
                   WHERE tanggal = '$tanggal' AND (status = 'izin' OR status = 'sakit')";
    $izin = mysqli_fetch_assoc(mysqli_query($conn, $query_izin))['total'];
    
    // Tidak hadir
    $tidak_hadir = $total_karyawan - ($hadir + $izin);
    
    return array(
        'total_karyawan' => $total_karyawan,
        'hadir' => $hadir,
        'izin' => $izin,
        'tidak_hadir' => $tidak_hadir
    );
}

// Fungsi untuk mendapatkan riwayat absensi
function get_riwayat_absensi($user_id, $limit = null) {
    global $conn;
    $query = "SELECT a.*, u.nama, u.nik, d.nama_divisi, j.nama_shift 
              FROM absensi a 
              JOIN users u ON a.user_id = u.id 
              JOIN divisi d ON u.divisi_id = d.id 
              JOIN jam_kerja j ON u.jam_kerja_id = j.id 
              WHERE a.user_id = $user_id 
              ORDER BY a.tanggal DESC";
    
    if ($limit) {
        $query .= " LIMIT $limit";
    }
    
    return mysqli_query($conn, $query);
}

// Fungsi untuk mendapatkan absensi yang perlu diverifikasi
function get_pending_verifikasi() {
    global $conn;
    $query = "SELECT a.*, u.nama, u.nik, d.nama_divisi, j.nama_shift 
              FROM absensi a 
              JOIN users u ON a.user_id = u.id 
              JOIN divisi d ON u.divisi_id = d.id 
              JOIN jam_kerja j ON u.jam_kerja_id = j.id 
              WHERE a.admin_verifikasi_masuk = 0 
              OR a.admin_verifikasi_pulang = 0 
              ORDER BY a.tanggal DESC";
    return mysqli_query($conn, $query);
}

// Fungsi untuk memverifikasi absensi
function verifikasi_absensi($absensi_id, $tipe, $status, $catatan) {
    global $conn;
    $catatan = validate_input($catatan);
    
    if ($tipe == 'masuk') {
        $query = "UPDATE absensi SET 
                  admin_verifikasi_masuk = $status,
                  catatan_masuk = '$catatan'
                  WHERE id = $absensi_id";
    } else {
        $query = "UPDATE absensi SET 
                  admin_verifikasi_pulang = $status,
                  catatan_pulang = '$catatan'
                  WHERE id = $absensi_id";
    }
    
    return mysqli_query($conn, $query);
}
// Fungsi untuk mendapatkan status verifikasi
function get_status_verifikasi($status) {
    switch ($status) {
        case 0:
            return '<span class="badge bg-warning text-dark">Menunggu Verifikasi</span>';
        case 1:
            return '<span class="badge bg-success">Disetujui</span>';
        case 2:
            return '<span class="badge bg-danger">Ditolak</span>';
        default:
            return '<span class="badge bg-secondary">Tidak Ada</span>';
    }
}



// Fungsi untuk generate laporan
function generate_laporan($bulan, $tahun, $divisi_id = null) {
    global $conn;
    
    $query = "SELECT a.*, u.nama, u.nik, d.nama_divisi, j.nama_shift 
              FROM absensi a 
              JOIN users u ON a.user_id = u.id 
              JOIN divisi d ON u.divisi_id = d.id 
              JOIN jam_kerja j ON u.jam_kerja_id = j.id 
              WHERE MONTH(a.tanggal) = $bulan 
              AND YEAR(a.tanggal) = $tahun";
    
    if ($divisi_id) {
        $query .= " AND u.divisi_id = $divisi_id";
    }
    
    $query .= " ORDER BY a.tanggal DESC, u.nama ASC";
    
    return mysqli_query($conn, $query);
}

// Fungsi untuk mendapatkan daftar divisi
function get_divisi() {
    global $conn;
    $query = "SELECT * FROM divisi ORDER BY nama_divisi ASC";
    return mysqli_query($conn, $query);
}

// Fungsi untuk mendapatkan daftar jam kerja
function get_jam_kerja_all() {
    global $conn;
    $query = "SELECT * FROM jam_kerja ORDER BY jam_masuk ASC";
    return mysqli_query($conn, $query);
}

// Fungsi untuk alert message
function set_alert($type, $message) {
    $_SESSION['alert'] = array(
        'type' => $type,
        'message' => $message
    );
}

// Fungsi untuk menampilkan alert
function show_alert() {
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        $type = $alert['type'];
        $message = $alert['message'];
        
        echo "<div class='alert alert-$type alert-dismissible fade show' role='alert'>
                $message
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
              </div>";
        
        unset($_SESSION['alert']);
    }
}

// Fungsi untuk format status absensi
function format_status_absensi($status, $keterlambatan = false) {
    $badge_class = 'bg-success';
    $text = ucfirst($status);
    
    if ($status == 'izin') {
        $badge_class = 'bg-warning';
    } elseif ($status == 'sakit') {
        $badge_class = 'bg-info';
    } elseif ($keterlambatan) {
        $badge_class = 'bg-danger';
        $text = 'Terlambat';
    }
    
    return "<span class='badge $badge_class'>$text</span>";
}

// Fungsi untuk cek duplikat NIK
function cek_duplikat_nik($nik, $user_id = null) {
    global $conn;
    $query = "SELECT id FROM users WHERE nik = '$nik'";
    if ($user_id) {
        $query .= " AND id != $user_id";
    }
    $result = mysqli_query($conn, $query);
    return mysqli_num_rows($result) > 0;
}

// Fungsi untuk cek duplikat email
function cek_duplikat_email($email, $user_id = null) {
    global $conn;
    $query = "SELECT id FROM users WHERE email = '$email'";
    if ($user_id) {
        $query .= " AND id != $user_id";
    }
    $result = mysqli_query($conn, $query);
    return mysqli_num_rows($result) > 0;
}

// Fungsi untuk get current datetime
function get_current_datetime() {
    date_default_timezone_set('Asia/Jakarta');
    return date('Y-m-d H:i:s');
}


// Fungsi untuk get current date dalam format UTC
function get_current_date() {
    date_default_timezone_set('Asia/Jakarta');
    return date('Y-m-d');
}

// Fungsi untuk get current time dalam format UTC
function get_current_time() {
     date_default_timezone_set('Asia/Jakarta');
    return date('H:i:s');
}

// Fungsi untuk format datetime
function format_datetime($datetime) {
    return date('d/m/Y H:i', strtotime($datetime));
}

// Fungsi untuk cek status absensi hari ini
function cek_status_absensi_hari_ini($user_id) {
    global $conn;
    $tanggal = date('Y-m-d');
    
    $query = "SELECT * FROM absensi WHERE user_id = $user_id AND tanggal = '$tanggal'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $absensi = mysqli_fetch_assoc($result);
        if ($absensi['jam_pulang'] === null) {
            return 'belum_pulang';
        }
        return 'sudah_lengkap';
    }
    return 'belum_absen';
}
?>