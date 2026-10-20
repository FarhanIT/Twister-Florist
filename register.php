<?php
require __DIR__ . '/config.php';
if (!empty($_SESSION['user'])) redirect('index.php');
$errors = [];
$old = ['nama' => '', 'username' => '', 'email' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $old = ['nama' => trim($_POST['nama'] ?? ''), 'username' => trim($_POST['username'] ?? ''), 'email' => trim($_POST['email'] ?? '')];
    $pass = $_POST['password'] ?? '';
    if ($old['nama'] === '') $errors[] = 'Nama wajib diisi.';
    if (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $old['username'])) $errors[] = 'Username 3-30 karakter (huruf, angka, underscore).';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Email tidak valid.';
    if (strlen($pass) < 6) $errors[] = 'Password minimal 6 karakter.';
    if ($pass !== ($_POST['password2'] ?? '')) $errors[] = 'Konfirmasi password tidak sama.';
    if (!$errors) {
        $st = $pdo->prepare('SELECT 1 FROM users WHERE username = ? OR email = ?');
        $st->execute([$old['username'], $old['email']]);
        if ($st->fetch()) {
            $errors[] = 'Username atau email sudah terdaftar.';
        } else {
            $pdo->prepare('INSERT INTO users (nama, username, email, password) VALUES (?,?,?,?)')
                ->execute([$old['nama'], $old['username'], $old['email'], password_hash($pass, PASSWORD_DEFAULT)]);
            flash('Registrasi berhasil, silakan login.');
            redirect('login.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Register - Twister Florist</title><link rel="stylesheet" href="assets/css/style.css"></head>
<body class="auth-body">
<div class="auth-card">
  <div class="auth-logo">тƒ</div>
  <h1>Buat Akun</h1>
  <p class="muted">Daftar untuk mengakses sistem stok</p>
  <?php foreach ($errors as $er): ?><div class="alert alert-error"><?= e($er) ?></div><?php endforeach; ?>
  <form method="post">
    <?= csrf_field() ?>
    <label>Nama Lengkap</label><input type="text" name="nama" value="<?= e($old['nama']) ?>" required>
    <label>Username</label><input type="text" name="username" value="<?= e($old['username']) ?>" required>
    <label>Email</label><input type="email" name="email" value="<?= e($old['email']) ?>" required>
    <label>Password</label><input type="password" name="password" required>
    <label>Konfirmasi Password</label><input type="password" name="password2" required>
    <button class="btn btn-primary btn-block">Daftar</button>
  </form>
  <p class="auth-foot">Sudah punya akun? <a href="login.php">Login</a></p>
</div>
<script src="assets/js/app.js"></script>
</body></html>
