<?php
require __DIR__ . '/config.php';
require_login();
$page_title = 'Dashboard'; $active = 'dashboard';

$total_produk = (int)$pdo->query('SELECT COUNT(*) FROM produk')->fetchColumn();
$menipis = (int)$pdo->query('SELECT COUNT(*) FROM produk WHERE stok > 0 AND stok <= ' . STOK_MENIPIS)->fetchColumn();
$habis = (int)$pdo->query('SELECT COUNT(*) FROM produk WHERE stok = 0')->fetchColumn();
$total_kat = (int)$pdo->query('SELECT COUNT(*) FROM kategori')->fetchColumn();

$grafik = $pdo->query('SELECT k.nama, COALESCE(SUM(p.stok),0) AS total FROM kategori k LEFT JOIN produk p ON p.kategori_id = k.id GROUP BY k.id ORDER BY total DESC')->fetchAll();
$max = max(array_merge([1], array_column($grafik, 'total')));

$low = $pdo->query('SELECT p.nama, p.stok, k.nama AS kategori FROM produk p JOIN kategori k ON k.id = p.kategori_id WHERE p.stok <= ' . STOK_MENIPIS . ' ORDER BY p.stok ASC, p.nama LIMIT 5')->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<section class="hero">
  <div><h1>Selamat Datang, <?= e($_SESSION['user']['nama']) ?></h1>
  <p>Berikut adalah ringkasan persediaan stok barang di Twister Florist.</p></div>
  <div class="hero-flower">💐</div>
</section>

<div class="stats">
  <div class="stat"><div class="ico ico-green">🌿</div><div><small>Total Produk</small><b data-count="<?= $total_produk ?>"><?= $total_produk ?></b><em>varian buket &amp; bunga</em></div></div>
  <div class="stat stat-warn"><div class="ico ico-red">⚠️</div><div><small>Stok Menipis</small><b data-count="<?= $menipis ?>"><?= $menipis ?></b><em>perlu segera ditambah</em></div></div>
  <div class="stat"><div class="ico ico-purple">📦</div><div><small>Stok Habis</small><b data-count="<?= $habis ?>"><?= $habis ?></b><em>produk</em></div></div>
  <div class="stat"><div class="ico ico-green">🌱</div><div><small>Total Kategori</small><b data-count="<?= $total_kat ?>"><?= $total_kat ?></b><em>kategori produk</em></div></div>
</div>

<div class="grid-2">
  <div class="card">
    <h3>Grafik Persediaan Stok</h3>
    <div class="chart">
      <?php foreach ($grafik as $g): ?>
        <div class="bar-col">
          <span class="bar-val"><?= (int)$g['total'] ?></span>
          <div class="bar" style="height: <?= round($g['total'] / $max * 100) ?>%"></div>
          <span class="bar-label"><?= e($g['nama']) ?></span>
        </div>
      <?php endforeach; ?>
      <?php if (!$grafik): ?><p class="muted">Belum ada data.</p><?php endif; ?>
    </div>
  </div>
  <div class="card">
    <div class="card-head"><h3>Produk dengan Stok Menipis</h3><a href="produk.php" class="link">Lihat Semua →</a></div>
    <div class="table-wrap"><table class="table">
      <thead><tr><th>No</th><th>Nama Produk</th><th>Kategori</th><th>Sisa</th><th>Status</th></tr></thead>
      <tbody>
      <?php foreach ($low as $i => $r): [$lbl, $cls] = status_stok($r['stok']); ?>
        <tr><td><?= $i + 1 ?></td><td><?= e($r['nama']) ?></td><td><?= e($r['kategori']) ?></td><td><?= (int)$r['stok'] ?></td><td><span class="badge b-<?= $cls ?>"><?= $lbl ?></span></td></tr>
      <?php endforeach; ?>
      <?php if (!$low): ?><tr><td colspan="5" class="muted center">Semua stok aman 🌸</td></tr><?php endif; ?>
      </tbody>
    </table></div>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
