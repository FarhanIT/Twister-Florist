<?php
require __DIR__ . '/config.php';
require_login();
$page_title = 'Penjualan'; $active = 'penjualan';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $pid = (int)($_POST['produk_id'] ?? 0);
    $jml = (int)($_POST['jumlah'] ?? 0);
    $tgl = $_POST['tanggal'] ?? date('Y-m-d');
    $cat = trim($_POST['catatan'] ?? '');
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl)) $tgl = date('Y-m-d');
    try {
        $pdo->beginTransaction();
        $st = $pdo->prepare('SELECT * FROM produk WHERE id = ? FOR UPDATE');
        $st->execute([$pid]);
        $p = $st->fetch();
        if (!$p || $jml < 1) throw new Exception('Data penjualan tidak valid.');
        if ($p['stok'] < $jml) throw new Exception('Stok tidak cukup. Sisa stok: ' . $p['stok']);
        $pdo->prepare('UPDATE produk SET stok = stok - ? WHERE id = ?')->execute([$jml, $pid]);
        $pdo->prepare('INSERT INTO penjualan (produk_id, nama_produk, jumlah, harga_satuan, total, tanggal, catatan, user_id) VALUES (?,?,?,?,?,?,?,?)')
            ->execute([$pid, $p['nama'], $jml, $p['harga'], $p['harga'] * $jml, $tgl, $cat ?: null, $_SESSION['user']['id']]);
        $pdo->commit();
        flash('Penjualan dicatat, stok otomatis berkurang.');
    } catch (Exception $ex) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        flash($ex->getMessage(), 'error');
    }
    redirect('penjualan.php');
}

$produk = $pdo->query('SELECT id, nama, stok, harga FROM produk WHERE stok > 0 ORDER BY nama')->fetchAll();
$riwayat = $pdo->query('SELECT * FROM penjualan ORDER BY id DESC LIMIT 15')->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<div class="page-head"><div><h1>Penjualan</h1><p class="muted">Catat penjualan, stok produk otomatis berkurang.</p></div></div>
<div class="grid-2 sales">
  <form method="post" class="card">
    <h3>Catat Penjualan</h3><?= csrf_field() ?>
    <label>Produk</label>
    <select name="produk_id" required><option value="">Pilih produk</option>
      <?php foreach ($produk as $p): ?><option value="<?= $p['id'] ?>"><?= e($p['nama']) ?> (stok <?= $p['stok'] ?>) - <?= rupiah($p['harga']) ?></option><?php endforeach; ?>
    </select>
    <label>Jumlah</label><input type="number" name="jumlah" min="1" value="1" required>
    <label>Tanggal</label><input type="date" name="tanggal" value="<?= date('Y-m-d') ?>" required>
    <label>Catatan (opsional)</label><input type="text" name="catatan" maxlength="255">
    <button class="btn btn-primary btn-block">Simpan Penjualan</button>
  </form>
  <div class="card">
    <h3>Riwayat Terbaru</h3>
    <div class="table-wrap"><table class="table"><thead><tr><th>Tanggal</th><th>Produk</th><th>Jml</th><th>Total</th></tr></thead><tbody>
    <?php foreach ($riwayat as $r): ?>
      <tr><td><?= date('d/m/Y', strtotime($r['tanggal'])) ?></td><td><?= e($r['nama_produk']) ?></td><td><?= $r['jumlah'] ?></td><td><?= rupiah($r['total']) ?></td></tr>
    <?php endforeach; ?>
    <?php if (!$riwayat): ?><tr><td colspan="4" class="center muted">Belum ada penjualan.</td></tr><?php endif; ?>
    </tbody></table></div>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
