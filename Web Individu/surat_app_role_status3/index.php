<?php
require 'config.php';
require_login();
$user = current_user();

$in  = (int)$pdo->query('SELECT COUNT(*) FROM mail_in')->fetchColumn();
$out = (int)$pdo->query('SELECT COUNT(*) FROM mail_out')->fetchColumn();
$dis = (int)$pdo->query('SELECT COUNT(*) FROM disposisi')->fetchColumn();

$latest_in  = $pdo->query('SELECT nomor_surat, perihal, tgl_terima FROM mail_in ORDER BY id DESC LIMIT 5')->fetchAll();
$latest_out = $pdo->query('SELECT nomor_surat, perihal, tgl_surat FROM mail_out ORDER BY id DESC LIMIT 5')->fetchAll();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Dashboard - Aplikasi Surat</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php include 'topnav_partial.php'; ?>
<main class="container">
  <section class="dashboard-hero">
    <div>
      <h1>Selamat datang, <?=htmlspecialchars($user['nama'] ?? $user['username'])?> 👋</h1>
      <p class="hero-subtitle">
        Anda login sebagai <strong><?=htmlspecialchars($user['role'])?></strong>. 
        Kelola surat masuk, surat keluar, dan disposisi dengan lebih cepat dan rapi.
      </p>
      <div style="margin-top:10px">
        <a class="btn" href="disposisi.php#buat-disposisi">+ Buat Disposisi</a>
      </div>
    </div>
    <div class="hero-tag">
      <span class="hero-dot"></span>
      Sistem Surat Internal Aktif
    </div>
  </section>

  <section class="stat-grid">
    <div class="stat-card">
      <div class="stat-icon">📥</div>
      <div>
        <div class="stat-label">Surat Masuk</div>
        <div class="stat-value"><?=$in?></div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">📤</div>
      <div>
        <div class="stat-label">Surat Keluar</div>
        <div class="stat-value"><?=$out?></div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">📝</div>
      <div>
        <div class="stat-label">Disposisi</div>
        <div class="stat-value"><?=$dis?></div>
      </div>
    </div>
  </section>

  <section class="dashboard-grid">
    <div class="card">
      <h3>Surat Masuk Terbaru</h3>
      <?php if ($latest_in): ?>
        <ul class="list-recent">
          <?php foreach ($latest_in as $row): ?>
            <li>
              <div class="recent-title"><?=htmlspecialchars($row['nomor_surat'] ?? '-')?></div>
              <div class="recent-sub"><?=htmlspecialchars($row['perihal'] ?? '-')?></div>
              <div class="recent-meta">Diterima: <?=htmlspecialchars($row['tgl_terima'] ?? '-')?></div>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p class="empty-state">Belum ada surat masuk.</p>
      <?php endif; ?>
      <a class="link-inline" href="mail_in.php">Lihat semua surat masuk →</a>
    </div>

    <div class="card">
      <h3>Surat Keluar Terbaru</h3>
      <?php if ($latest_out): ?>
        <ul class="list-recent">
          <?php foreach ($latest_out as $row): ?>
            <li>
              <div class="recent-title"><?=htmlspecialchars($row['nomor_surat'] ?? '-')?></div>
              <div class="recent-sub"><?=htmlspecialchars($row['perihal'] ?? '-')?></div>
              <div class="recent-meta">Tanggal: <?=htmlspecialchars($row['tgl_surat'] ?? '-')?></div>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p class="empty-state">Belum ada surat keluar.</p>
      <?php endif; ?>
      <a class="link-inline" href="mail_out.php">Lihat semua surat keluar →</a>
    </div>
  </section>

  <section class="card role-hint">
    <h3>Panduan Singkat Hak Akses</h3>
    <p>
      <strong>Admin</strong> dapat mengelola pengguna, menghapus surat, dan menghapus disposisi.
      <br>
      <strong>Pegawai</strong> dapat menginput surat, membuat disposisi dari dashboard, 
      dan mengubah status disposisi yang ditujukan kepadanya.
    </p>
  </section>
</main>
</body>
</html>
