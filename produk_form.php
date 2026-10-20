<?php
require __DIR__ . '/config.php';
require_login();
$id = (int)($_GET['id'] ?? 0);
$produk = ['nama' => '', 'kategori_id' => '', 'harga' => '', 'stok' => '', 'gambar' => null];
if ($id) {
    $st = $pdo->prepare('SELECT * FROM produk WHERE id = ?');
    $st->execute([$id]);
    $produk = $st->fetch();
    if (!$produk) { flash('Produk tidak ditemukan.', 'error'); redirect('produk.php'); }
}
$kats = $pdo->query('SELECT * FROM kategori ORDER BY nama')->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $data = [
        'nama' => trim($_POST['nama'] ?? ''),
        'kategori_id' => (int)($_POST['kategori_id'] ?? 0),
        'harga' => (int)preg_replace('/\D/', '', $_POST['harga'] ?? '0'),
        'stok' => (int)($_POST['stok'] ?? -1),
    ];
    if ($data['nama'] === '') $errors[] = 'Nama produk wajib diisi.';
    if (!in_array($data['kategori_id'], array_column($kats, 'id'))) $errors[] = 'Pilih kategori.';
    if ($data['harga'] <= 0) $errors[] = 'Harga harus lebih dari 0.';
    if ($data['stok'] < 0) $errors[] = 'Stok tidak boleh negatif.';

    $gambar = $produk['gambar'];
    $newfile = null;
    if (!$errors && !empty($_FILES['gambar']['name'])) {
        $f = $_FILES['gambar'];
        $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        if ($f['error'] !== UPLOAD_ERR_OK) $errors[] = 'Upload gambar gagal.';
        elseif (!in_array($ext, ['jpg', 'jpeg', 'png'])) $errors[] = 'Gambar harus JPG atau PNG.';
        elseif ($f['size'] > 2 * 1024 * 1024) $errors[] = 'Ukuran gambar maksimal 2MB.';
        elseif (!@getimagesize($f['tmp_name'])) $errors[] = 'File bukan gambar yang valid.';
        else {
            $newfile = uniqid('p_') . '.' . $ext;
            simpan_gambar($f['tmp_name'], $ext, __DIR__ . '/uploads/' . $newfile);
            $gambar = $newfile;
        }
    }
    if (!$errors) {
        if ($id) {
            $pdo->prepare('UPDATE produk SET nama=?, kategori_id=?, harga=?, stok=?, gambar=? WHERE id=?')
                ->execute([$data['nama'], $data['kategori_id'], $data['harga'], $data['stok'], $gambar, $id]);
            if ($newfile && $produk['gambar'] && file_exists(__DIR__ . '/uploads/' . $produk['gambar'])) unlink(__DIR__ . '/uploads/' . $produk['gambar']);
            flash('Produk berhasil diperbarui.');
        } else {
            $pdo->prepare('INSERT INTO produk (nama, kategori_id, harga, stok, gambar) VALUES (?,?,?,?,?)')
                ->execute([$data['nama'], $data['kategori_id'], $data['harga'], $data['stok'], $gambar]);
            flash('Produk berhasil ditambahkan.');
        }
        redirect('produk.php');
    }
    $produk = array_merge($produk, $data);
}

$page_title = $id ? 'Edit Produk' : 'Tambah Produk'; $active = 'stok';
require __DIR__ . '/includes/header.php';
$img = foto($produk['gambar']);
?>
<div class="page-head"><div><h1><?= $page_title ?></h1><p class="muted"><?= $id ? 'Perbarui informasi produk yang sudah ada.' : 'Masukkan data produk baru ke dalam sistem.' ?></p></div></div>
<?php foreach ($errors as $er): ?><div class="alert alert-error"><?= e($er) ?></div><?php endforeach; ?>
<form method="post" enctype="multipart/form-data" class="card form-grid">
  <?= csrf_field() ?>
  <div class="upload">
    <div class="preview" id="preview"><?= $img ? '<img src="' . $img . '" alt="">' : '<div class="ph-big">📷<b>Unggah Gambar Produk</b><small>Klik untuk memilih gambar<br>(JPG, PNG, max 2MB)</small></div>' ?></div>
    <label class="btn btn-ghost">🖼 <?= $id ? 'Ubah Gambar' : 'Pilih Gambar' ?><input type="file" name="gambar" accept=".jpg,.jpeg,.png" hidden></label>
  </div>
  <div>
    <label>Nama Produk *</label><input type="text" name="nama" value="<?= e($produk['nama']) ?>" placeholder="Masukkan nama produk" required>
    <label>Kategori *</label>
    <select name="kategori_id" required><option value="">Pilih kategori</option>
      <?php foreach ($kats as $k): ?><option value="<?= $k['id'] ?>" <?= $produk['kategori_id'] == $k['id'] ? 'selected' : '' ?>><?= e($k['nama']) ?></option><?php endforeach; ?>
    </select>
    <label>Harga *</label>
    <div class="input-pre"><span>Rp</span><input type="number" name="harga" min="1" value="<?= e($produk['harga']) ?>" placeholder="Masukkan harga" required></div>
    <label>Stok *</label><input type="number" name="stok" min="0" value="<?= e($produk['stok']) ?>" placeholder="Masukkan jumlah stok" required>
    <div class="form-actions"><a href="produk.php" class="btn btn-ghost">Batal</a><button class="btn btn-primary"><?= $id ? 'Simpan Perubahan' : 'Simpan' ?></button></div>
  </div>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>
