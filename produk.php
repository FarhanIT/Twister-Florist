<?php
require __DIR__ . '/config.php';
require_login();
$page_title = 'Manajemen Stok'; $active = 'stok';

$q = trim($_GET['q'] ?? '');
$kat = (int)($_GET['kategori'] ?? 0);
$page = max(1, (int)($_GET['page'] ?? 1));
$per = 8;

$where = '(p.nama LIKE ? OR k.nama LIKE ?)';
$params = ["%$q%", "%$q%"];
if ($kat) { $where .= ' AND p.kategori_id = ?'; $params[] = $kat; }

$st = $pdo->prepare("SELECT COUNT(*) FROM produk p JOIN kategori k ON k.id = p.kategori_id WHERE $where");
$st->execute($params);
$total = (int)$st->fetchColumn();
$pages = max(1, (int)ceil($total / $per));
$page = min($page, $pages);
$offset = ($page - 1) * $per;

$st = $pdo->prepare("SELECT p.*, k.nama AS kategori FROM produk p JOIN kategori k ON k.id = p.kategori_id WHERE $where ORDER BY p.id DESC LIMIT $per OFFSET $offset");
$st->execute($params);
$rows = $st->fetchAll();
$kats = $pdo->query('SELECT * FROM kategori ORDER BY nama')->fetchAll();
$qs = http_build_query(['q' => $q, 'kategori' => $kat]);

require __DIR__ . '/includes/header.php';
?>
<div class="page-head">
  <div><h1>Manajemen Stok</h1><p class="muted">Kelola data produk dan pantau ketersediaan stok barang.</p></div>
  <a href="produk_form.php" class="btn btn-primary">+ Tambah Produk</a>
</div>
<form class="filters" method="get">
  <input type="search" name="q" value="<?= e($q) ?>" placeholder="🔍 Cari nama produk atau kategori...">
  <select name="kategori" onchange="this.form.submit()">
    <option value="0">Semua Kategori</option>
    <?php foreach ($kats as $k): ?><option value="<?= $k['id'] ?>" <?= $kat == $k['id'] ? 'selected' : '' ?>><?= e($k['nama']) ?></option><?php endforeach; ?>
  </select>
</form>
<div class="card">
  <div class="table-wrap">
  <table class="table">
    <thead><tr><th>No</th><th>Gambar</th><th>Nama Produk</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Status</th><th>Aksi</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $i => $r): [$lbl, $cls] = status_stok($r['stok']); $img = foto($r['gambar']); ?>
      <tr>
        <td><?= $offset + $i + 1 ?></td>
        <td><?= $img ? '<img class="thumb" src="' . $img . '" alt="">' : '<div class="thumb ph">💐</div>' ?></td>
        <td><?= e($r['nama']) ?></td>
        <td><?= e($r['kategori']) ?></td>
        <td><?= rupiah($r['harga']) ?></td>
        <td><?= (int)$r['stok'] ?></td>
        <td><span class="badge b-<?= $cls ?>"><?= $lbl ?></span></td>
        <td class="actions">
          <a class="icon-btn" href="produk_form.php?id=<?= $r['id'] ?>" title="Edit">✏️</a>
          <form method="post" action="produk_hapus.php" onsubmit="return confirm('Hapus produk ini?')">
            <?= csrf_field() ?><input type="hidden" name="id" value="<?= $r['id'] ?>">
            <button class="icon-btn danger" title="Hapus">🗑️</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="8" class="center muted">Data produk tidak ditemukan.</td></tr><?php endif; ?>
    </tbody>
  </table>
  </div>
  <?php if ($pages > 1): ?>
  <div class="pager">
    <?php for ($p = 1; $p <= $pages; $p++): ?>
      <a class="<?= $p == $page ? 'on' : '' ?>" href="?<?= $qs ?>&page=<?= $p ?>"><?= $p ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
