<?php
$user = $_SESSION['user'];
$active = $active ?? '';
$menu = [
  'dashboard' => ['index.php', '🏠', 'Dashboard'],
  'stok'      => ['produk.php', '📦', 'Manajemen Stok'],
  'kategori'  => ['kategori.php', '🏷️', 'Kategori Produk'],
  'penjualan' => ['penjualan.php', '💐', 'Penjualan'],
  'laporan'   => ['laporan.php', '📄', 'Laporan'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($page_title ?? 'Twister Florist') ?> - Twister Florist</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app">
  <aside class="sidebar" id="sidebar">
    <div class="brand"><span class="brand-icon">тƒ</span><div><b>Twister Florist</b><small>Toko Bunga Lumajang</small></div></div>
    <nav>
      <?php foreach ($menu as $key => [$href, $icon, $label]): ?>
        <a href="<?= $href ?>" class="<?= $active === $key ? 'active' : '' ?>"><span><?= $icon ?></span><?= $label ?></a>
      <?php endforeach; ?>
    </nav>
    <div class="sidebar-bottom">
      <a href="logout.php"><span>↩</span>Logout</a>
    </div>
  </aside>
  <div class="main">
    <header class="topbar">
      <button class="burger" id="burger" aria-label="Menu">☰</button>
      <div class="user"><span class="avatar"><?= e(strtoupper(substr($user['nama'], 0, 1))) ?></span><?= e($user['nama']) ?></div>
    </header>
    <main class="content">
<?php if ($f = flash()): ?>
      <div class="alert alert-<?= e($f[1]) ?>"><?= e($f[0]) ?></div>
<?php endif; ?>
