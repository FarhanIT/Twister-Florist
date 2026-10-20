<?php
// ====== Konfigurasi database (sesuaikan) ======
const DB_HOST = 'localhost';
const DB_NAME = 'twister_florist';
const DB_USER = 'root';
const DB_PASS = '';
const STOK_MENIPIS = 5; // stok <= angka ini dianggap menipis

session_start();

try {
    $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4', DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $ex) {
    die('Koneksi database gagal. Pastikan database sudah di-import dan config.php benar.');
}

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function rupiah($n) { return 'Rp ' . number_format((int)$n, 0, ',', '.'); }
function redirect($url) { header('Location: ' . $url); exit; }

function status_stok($stok) {
    if ($stok <= 0) return ['Habis', 'habis'];
    if ($stok <= STOK_MENIPIS) return ['Menipis', 'menipis'];
    return ['Tersedia', 'tersedia'];
}

function foto($f) {
    return ($f && file_exists(__DIR__ . '/uploads/' . $f)) ? 'uploads/' . rawurlencode($f) : null;
}

function require_login() {
    if (empty($_SESSION['user'])) redirect('login.php');
}

function csrf_token() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function csrf_field() { return '<input type="hidden" name="csrf" value="' . csrf_token() . '">'; }
function csrf_check() {
    if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'])) {
        http_response_code(419);
        die('Sesi tidak valid. Silakan kembali dan coba lagi.');
    }
}

function flash($msg = null, $type = 'success') {
    if ($msg !== null) { $_SESSION['flash'] = [$msg, $type]; return; }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

// Simpan gambar upload; otomatis diperkecil (maks 800px) supaya ringan & tampil proporsional
function simpan_gambar($tmp, $ext, $tujuan, $maks = 800) {
    $info = @getimagesize($tmp);
    if (!$info || !function_exists('imagecreatetruecolor')) return move_uploaded_file($tmp, $tujuan);
    [$w, $h] = $info;
    $src = $info[2] === IMAGETYPE_PNG ? @imagecreatefrompng($tmp) : @imagecreatefromjpeg($tmp);
    if (!$src) return move_uploaded_file($tmp, $tujuan);
    $skala = min(1, $maks / max($w, $h));
    $nw = max(1, (int)round($w * $skala)); $nh = max(1, (int)round($h * $skala));
    $dst = imagecreatetruecolor($nw, $nh);
    if ($info[2] === IMAGETYPE_PNG) {
        imagealphablending($dst, false); imagesavealpha($dst, true);
        imagefill($dst, 0, 0, imagecolorallocatealpha($dst, 0, 0, 0, 127));
    }
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
    $ok = $info[2] === IMAGETYPE_PNG ? imagepng($dst, $tujuan, 7) : imagejpeg($dst, $tujuan, 85);
    imagedestroy($src); imagedestroy($dst);
    return $ok;
}
