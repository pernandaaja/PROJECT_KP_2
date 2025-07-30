<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'db_absensi';

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

function query($sql) {
    global $conn;
    $result = mysqli_query($conn, $sql);
    return $result;
}

function escape($string) {
    global $conn;
    return mysqli_real_escape_string($conn, $string);
}
?>