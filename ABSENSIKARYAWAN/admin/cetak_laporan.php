<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../vendor/fpdf/fpdf.php';

// Cek login dan role admin
cek_login();
cek_admin();

// Ambil parameter
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
$divisi_id = isset($_GET['divisi_id']) ? $_GET['divisi_id'] : '';

class PDF extends FPDF {
    // Header
    function Header() {
        // Logo (jika ada)
        // $this->Image('logo.png', 10, 10, 30);
        
        // Kop Surat
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 8, 'NANJAYA', 0, 1, 'C');
        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 6, 'Jl. Jupiter No. 83, Tambun Selatan', 0, 1, 'C');
        $this->Cell(0, 6, 'Telp: +628829825416 | Email: nanjaya@company.com', 0, 1, 'C');
        $this->Cell(0, 6, 'Website: www.nanjaya.com', 0, 1, 'C');
        
        // Garis pembatas
        $this->SetLineWidth(1);
        $this->Line(10, 40, 287, 40);
        $this->SetLineWidth(0.2);
        $this->Line(10, 41, 287, 41);
        
        // Jarak
        $this->Ln(10);

        // Judul Laporan
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, 'LAPORAN ABSENSI KARYAWAN', 0, 1, 'C');
        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 10, 'Periode: ' . date('F Y', strtotime($_GET['tahun'].'-'.$_GET['bulan'].'-01')), 0, 1, 'C');
        
        if(isset($_GET['divisi_id']) && $_GET['divisi_id'] != '') {
            global $conn;
            $divisi_id = $_GET['divisi_id'];
            $query_divisi = "SELECT nama_divisi FROM divisi WHERE id = $divisi_id";
            $result_divisi = mysqli_query($conn, $query_divisi);
            $divisi = mysqli_fetch_assoc($result_divisi);
            $this->Cell(0, 10, 'Divisi: ' . $divisi['nama_divisi'], 0, 1, 'C');
        }
        
        $this->Ln(5);
    }

    // Footer tetap sama
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Halaman ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}
// Inisialisasi PDF
$pdf = new PDF('L', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->AddPage();



//  mengurutkan dari yang terbaru
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

$query .= " ORDER BY a.tanggal DESC, a.created_at DESC, u.nama ASC"; // Ubah ordering ke DESC
$result = mysqli_query($conn, $query);

// Header tabel
$pdf->SetFont('Arial', 'B', 8); // Kecilkan font agar muat
$pdf->Cell(8, 10, 'No', 1, 0, 'C');
$pdf->Cell(20, 10, 'Tanggal', 1, 0, 'C');
$pdf->Cell(15, 10, 'NIK', 1, 0, 'C');
$pdf->Cell(30, 10, 'Nama', 1, 0, 'C');
$pdf->Cell(25, 10, 'Divisi', 1, 0, 'C');
$pdf->Cell(20, 10, 'Status', 1, 0, 'C');
$pdf->Cell(20, 10, 'Jam Masuk', 1, 0, 'C');
$pdf->Cell(20, 10, 'Jam Pulang', 1, 0, 'C');
$pdf->Cell(20, 10, 'Keterlambatan', 1, 0, 'C');
$pdf->Cell(40, 10, 'Alasan Terlambat', 1, 0, 'C');
$pdf->Cell(40, 10, 'Alasan Pulang Cepat', 1, 1, 'C');

// Isi tabel
$pdf->SetFont('Arial', '', 8);
$no = 1;
while ($row = mysqli_fetch_assoc($result)) {
    $currentY = $pdf->GetY();
    if ($currentY > 180) { // Cek jika mendekati batas bawah halaman
        $pdf->AddPage();
    }

    // Hitung keterlambatan
    $keterlambatan = '-';
    if ($row['status'] == 'hadir' && $row['status_keterlambatan']) {
        $jam_masuk = strtotime($row['jam_masuk']);
        $jam_kerja = strtotime($row['jam_kerja_masuk']);
        $selisih = round(($jam_masuk - $jam_kerja) / 60);
        $keterlambatan = $selisih . ' menit';
    }

    $cellHeight = 10; // Tinggi default cell

    // Cek panjang teks alasan untuk menentukan tinggi cell
    $alasanTerlambat = $row['catatan_masuk'] ? $row['catatan_masuk'] : '-';
    $alasanPulang = $row['catatan_pulang'] ? $row['catatan_pulang'] : '-';
    
    // Hitung berapa baris yang dibutuhkan
    $maxLength = 30; // Maksimal karakter per baris
    $rowsTerlambat = ceil(strlen($alasanTerlambat) / $maxLength);
    $rowsPulang = ceil(strlen($alasanPulang) / $maxLength);
    $maxRows = max($rowsTerlambat, $rowsPulang, 1);
    $cellHeight = $maxRows * 5; // 5mm per baris

    $pdf->Cell(8, $cellHeight, $no++, 1, 0, 'C');
    $pdf->Cell(20, $cellHeight, tanggal_indonesia($row['tanggal']), 1, 0, 'C');
    $pdf->Cell(15, $cellHeight, $row['nik'], 1, 0, 'C');
    $pdf->Cell(30, $cellHeight, $row['nama'], 1, 0, 'L');
    $pdf->Cell(25, $cellHeight, $row['nama_divisi'], 1, 0, 'C');
    $pdf->Cell(20, $cellHeight, ucfirst($row['status']), 1, 0, 'C');
    $pdf->Cell(20, $cellHeight, $row['jam_masuk'] ? date('H:i', strtotime($row['jam_masuk'])) : '-', 1, 0, 'C');
    $pdf->Cell(20, $cellHeight, $row['jam_pulang'] ? date('H:i', strtotime($row['jam_pulang'])) : '-', 1, 0, 'C');
    $pdf->Cell(20, $cellHeight, $keterlambatan, 1, 0, 'C');

    // Alasan dengan text wrapping
    $x = $pdf->GetX();
    $y = $pdf->GetY();
    $pdf->MultiCell(40, 5, $alasanTerlambat, 1, 'L');
    $pdf->SetXY($x + 40, $y);
    $x = $pdf->GetX();
    $pdf->MultiCell(40, 5, $alasanPulang, 1, 'L');
    $pdf->SetY($y + $cellHeight);
}

// Output PDF
$pdf->Output('Laporan_Absensi_'.date('Y-m', strtotime($tahun.'-'.$bulan.'-01')).'.pdf', 'D');
?>