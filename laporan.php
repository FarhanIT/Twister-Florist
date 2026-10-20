<?php
require __DIR__ . '/config.php';
require_login();
$page_title = 'Laporan'; $active = 'laporan';
$tab = ($_GET['tab'] ?? 'stok') === 'penjualan' ? 'penjualan' : 'stok';
$kats = $pdo->query('SELECT * FROM kategori ORDER BY nama')->fetchAll();

if ($tab === 'stok') {
    $kat = (int)($_GET['kategori'] ?? 0);
    $sql = 'SELECT p.*, k.nama AS kategori FROM produk p JOIN kategori k ON k.id = p.kategori_id' . ($kat ? ' WHERE p.kategori_id = ?' : '') . ' ORDER BY k.nama, p.nama';
    $st = $pdo->prepare($sql);
    $st->execute($kat ? [$kat] : []);
    $rows = $st->fetchAll();
    $total_stok = array_sum(array_column($rows, 'stok'));
    $nilai = array_sum(array_map(fn($r) => $r['stok'] * $r['harga'], $rows));
} else {
    $dari = $_GET['dari'] ?? date('Y-m-01');
    $sampai = $_GET['sampai'] ?? date('Y-m-d');
    $st = $pdo->prepare('SELECT * FROM penjualan WHERE tanggal BETWEEN ? AND ? ORDER BY tanggal DESC, id DESC');
    $st->execute([$dari, $sampai]);
    $rows = $st->fetchAll();
    $total_item = array_sum(array_column($rows, 'jumlah'));
    $pendapatan = array_sum(array_column($rows, 'total'));
}
require __DIR__ . '/includes/header.php';
?>
<div class="page-head"><div><h1>Laporan</h1><p class="muted">Laporan stok dan penjualan Twister Florist.</p></div>
  <button class="btn btn-primary no-print" onclick="window.print()">🖨 Cetak</button></div>
<div class="tabs no-print">
  <a class="<?= $tab === 'stok' ? 'on' : '' ?>" href="?tab=stok">Laporan Stok</a>
  <a class="<?= $tab === 'penjualan' ? 'on' : '' ?>" href="?tab=penjualan">Laporan Penjualan</a>
</div>

<?php if ($tab === 'stok'): ?>
<form class="filters no-print" method="get"><input type="hidden" name="tab" value="stok">
  <select name="kategori" onchange="this.form.submit()"><option value="0">Semua Kategori</option>
    <?php foreach ($kats as $k): ?><option value="<?= $k['id'] ?>" <?= $kat == $k['id'] ? 'selected' : '' ?>><?= e($k['nama']) ?></option><?php endforeach; ?>
  </select>
</form>
<div class="summary"><div><small>Total Item Stok</small><b><?= $total_stok ?></b></div><div><small>Nilai Persediaan</small><b><?= rupiah($nilai) ?></b></div><div><small>Jumlah Produk</small><b><?= count($rows) ?></b></div></div>
<div class="card"><div class="table-wrap"><table class="table">
  <thead><tr><th>No</th><th>Produk</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Nilai</th><th>Status</th></tr></thead><tbody>
  <?php foreach ($rows as $i => $r): [$lbl, $cls] = status_stok($r['stok']); ?>
    <tr><td><?= $i + 1 ?></td><td><?= e($r['nama']) ?></td><td><?= e($r['kategori']) ?></td><td><?= rupiah($r['harga']) ?></td><td><?= $r['stok'] ?></td><td><?= rupiah($r['stok'] * $r['harga']) ?></td><td><span class="badge b-<?= $cls ?>"><?= $lbl ?></span></td></tr>
  <?php endforeach; ?>
  </tbody></table></div></div>

<?php else: ?>
<form class="filters no-print" method="get"><input type="hidden" name="tab" value="penjualan">
  <label>Dari</label><input type="date" name="dari" value="<?= e($dari) ?>">
  <label>Sampai</label><input type="date" name="sampai" value="<?= e($sampai) ?>">
  <button class="btn btn-primary">Tampilkan</button>
</form>
<div class="summary"><div><small>Jumlah Transaksi</small><b><?= count($rows) ?></b></div><div><small>Item Terjual</small><b><?= $total_item ?></b></div><div><small>Total Pendapatan</small><b><?= rupiah($pendapatan) ?></b></div></div>
<div class="card"><div class="table-wrap"><table class="table">
  <thead><tr><th>No</th><th>Tanggal</th><th>Produk</th><th>Jumlah</th><th>Harga</th><th>Total</th><th>Catatan</th></tr></thead><tbody>
  <?php foreach ($rows as $i => $r): ?>
    <tr><td><?= $i + 1 ?></td><td><?= date('d/m/Y', strtotime($r['tanggal'])) ?></td><td><?= e($r['nama_produk']) ?></td><td><?= $r['jumlah'] ?></td><td><?= rupiah($r['harga_satuan']) ?></td><td><?= rupiah($r['total']) ?></td><td><?= e($r['catatan']) ?></td></tr>
  <?php endforeach; ?>
  <?php if (!$rows): ?><tr><td colspan="7" class="center muted">Tidak ada penjualan pada periode ini.</td></tr><?php endif; ?>
  </tbody></table></div></div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
