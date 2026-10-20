<?php
require __DIR__ . '/config.php';
require_login();
$page_title = 'Kategori Produk'; $active = 'kategori';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $aksi = $_POST['aksi'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    $nama = trim($_POST['nama'] ?? '');
    try {
        if ($aksi === 'tambah' && $nama !== '') {
            $pdo->prepare('INSERT INTO kategori (nama) VALUES (?)')->execute([$nama]);
            flash('Kategori ditambahkan.');
        } elseif ($aksi === 'edit' && $nama !== '' && $id) {
            $pdo->prepare('UPDATE kategori SET nama = ? WHERE id = ?')->execute([$nama, $id]);
            flash('Kategori diperbarui.');
        } elseif ($aksi === 'hapus' && $id) {
            $c = $pdo->prepare('SELECT COUNT(*) FROM produk WHERE kategori_id = ?');
            $c->execute([$id]);
            if ($c->fetchColumn() > 0) flash('Kategori tidak bisa dihapus karena masih dipakai produk.', 'error');
            else { $pdo->prepare('DELETE FROM kategori WHERE id = ?')->execute([$id]); flash('Kategori dihapus.'); }
        }
    } catch (PDOException $ex) {
        flash('Nama kategori sudah ada.', 'error');
    }
    redirect('kategori.php');
}

$edit = (int)($_GET['edit'] ?? 0);
$kats = $pdo->query('SELECT k.*, COUNT(p.id) AS jml, (SELECT gambar FROM produk WHERE kategori_id = k.id AND gambar IS NOT NULL LIMIT 1) AS gambar FROM kategori k LEFT JOIN produk p ON p.kategori_id = k.id GROUP BY k.id ORDER BY k.nama')->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<div class="page-head"><div><h1>Kategori Produk</h1><p class="muted">Kelola kategori produk buket dan bunga.</p></div></div>
<div class="cat-grid">
<?php foreach ($kats as $k): $img = foto($k['gambar']); ?>
  <div class="cat-card">
    <div class="cat-img"><?= $img ? '<img src="' . $img . '" alt="" loading="lazy">' : '<div class="ph-cat">💐</div>' ?></div>
    <?php if ($edit == $k['id']): ?>
      <form method="post" class="inline-form"><?= csrf_field() ?><input type="hidden" name="aksi" value="edit"><input type="hidden" name="id" value="<?= $k['id'] ?>">
        <input type="text" name="nama" value="<?= e($k['nama']) ?>" required><button class="btn btn-primary btn-sm">Simpan</button></form>
    <?php else: ?>
      <b><?= e($k['nama']) ?></b><small class="muted"><?= (int)$k['jml'] ?> produk</small>
      <div class="cat-actions">
        <a class="btn btn-ghost btn-sm" href="?edit=<?= $k['id'] ?>">✏️ Edit</a>
        <form method="post" onsubmit="return confirm('Hapus kategori ini?')"><?= csrf_field() ?><input type="hidden" name="aksi" value="hapus"><input type="hidden" name="id" value="<?= $k['id'] ?>"><button class="btn btn-ghost btn-sm danger">🗑️</button></form>
      </div>
    <?php endif; ?>
  </div>
<?php endforeach; ?>
  <div class="cat-card cat-add">
    <div class="plus">+</div>
    <form method="post" class="inline-form"><?= csrf_field() ?><input type="hidden" name="aksi" value="tambah">
      <input type="text" name="nama" placeholder="Nama kategori baru" required><button class="btn btn-primary btn-sm">Tambah Kategori</button></form>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
