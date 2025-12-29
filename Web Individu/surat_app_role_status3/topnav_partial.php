<?php
if (!isset($user)) {
    require_once 'config.php';
    require_login();
    $user = current_user();
}
?>
<header class="topbar">
  <div class="topbar-title">Aplikasi Surat</div>
  <div class="topbar-right">
    <nav class="nav-inline">
      <a href="index.php">Dashboard</a>
      <a href="mail_in.php">Surat Masuk</a>
      <a href="mail_out.php">Surat Keluar</a>
      <a href="disposisi.php">Disposisi</a>
      <?php if ($user['role'] === 'admin'): ?>
        <a href="users.php">Pengguna</a>
      <?php endif; ?>
    </nav>
    <span><?=htmlspecialchars($user['nama'] ?? $user['username'])?> (<?=htmlspecialchars($user['role'])?>)</span>
    <a class="btn-outline" href="logout.php">Logout</a>
  </div>
</header>
