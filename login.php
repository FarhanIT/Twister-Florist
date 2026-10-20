<?php
require __DIR__ . '/config.php';
if (!empty($_SESSION['user'])) redirect('index.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = trim($_POST['username'] ?? '');
    $st = $pdo->prepare('SELECT * FROM users WHERE username = ? OR email = ?');
    $st->execute([$username, $username]);
    $u = $st->fetch();
    if ($u && password_verify($_POST['password'] ?? '', $u['password'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => $u['id'], 'nama' => $u['nama'], 'role' => $u['role']];
        redirect('index.php');
    }
    $error = 'Username atau password salah.';
}
$page_title = 'Login';
?>
<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login - Twister Florist</title><link rel="stylesheet" href="assets/css/style.css">
<script>
/* Intro tampil sekali setiap kali web dibuka (per tab). Paksa tampil lagi: login.php?intro=1 */
try{
  if(location.search.indexOf('intro=1')>-1||!sessionStorage.getItem('tf_intro')){
    document.documentElement.classList.add('intro-on');
    sessionStorage.setItem('tf_intro','1');
  }
}catch(e){}
</script>
</head>
<body class="auth-body">
<div class="intro" aria-hidden="true">
  <div class="curtain curtain-top"></div>
  <div class="curtain curtain-bottom"></div>
  <div class="intro-logo">
    <div class="intro-mark"><span>T</span><span>F</span></div>
    <div class="intro-name">Twister Florist</div>
    <div class="intro-sub">TOKO BUNGA LUMAJANG</div>
  </div>
</div>
<div class="auth-card">
  <div class="auth-logo">тƒ</div>
  <h1>Twister Florist</h1>
  <p class="muted">Masuk untuk mengelola stok toko bunga</p>
  <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
  <?php if ($f = flash()): ?><div class="alert alert-<?= e($f[1]) ?>"><?= e($f[0]) ?></div><?php endif; ?>
  <form method="post">
    <?= csrf_field() ?>
    <label>Username / Email</label>
    <input type="text" name="username" required autofocus>
    <label>Password</label>
    <input type="password" name="password" required>
    <button class="btn btn-primary btn-block">Masuk</button>
  </form>
  <p class="auth-foot">Belum punya akun? <a href="register.php">Daftar</a></p>
</div>
<script src="assets/js/app.js"></script>
</body></html>
