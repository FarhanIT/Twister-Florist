<?php
require __DIR__ . '/config.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('produk.php');
csrf_check();
$id = (int)($_POST['id'] ?? 0);
$st = $pdo->prepare('SELECT gambar FROM produk WHERE id = ?');
$st->execute([$id]);
if ($p = $st->fetch()) {
    $pdo->prepare('DELETE FROM produk WHERE id = ?')->execute([$id]);
    if ($p['gambar'] && file_exists(__DIR__ . '/uploads/' . $p['gambar'])) unlink(__DIR__ . '/uploads/' . $p['gambar']);
    flash('Produk berhasil dihapus.');
}
redirect('produk.php');
