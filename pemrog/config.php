<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "";
$DB_NAME = "lab_kep";

/*
InfinityFree contoh:
$DB_HOST = "sqlXXX.infinityfree.com";
$DB_USER = "if0_xxxxxxxx";
$DB_PASS = "xxxxxx";
$DB_NAME = "if0_xxxxxxxx_lab_peminjaman";
*/

/** penting:
 * - Kalau kamu taruh file langsung di public_html -> BASE_URL = ""
 * - Kalau taruh di public_html/lab-tools -> BASE_URL = "/lab-tools"
 */
// AUTO detect base folder (aman untuk XAMPP & hosting)
$dir = str_replace('\\','/', realpath(__DIR__));
$root = str_replace('\\','/', realpath($_SERVER['DOCUMENT_ROOT']));
$BASE_URL = "/pemrog";
if (strpos($dir, $root) === 0) {
  $BASE_URL = substr($dir, strlen($root));
}
$BASE_URL = rtrim($BASE_URL, '/');


$koneksi = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if (!$koneksi) die("Koneksi DB gagal: " . mysqli_connect_error());
mysqli_set_charset($koneksi, "utf8mb4");
