<?php
require 'config.php';
require_login();
$user = current_user();

$action     = $_GET['action'] ?? 'list';
$upload_dir = __DIR__.'/uploads';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// generator nomor surat keluar
function generate_nomor_keluar($pdo){
    $last = (int)$pdo->query('SELECT id FROM mail_out ORDER BY id DESC LIMIT 1')->fetchColumn();
    $next = $last + 1;
    $num  = str_pad($next, 3, '0', STR_PAD_LEFT);
    $year = date('Y');
    return "{$num}/SK/ADM/{$year}";
}

if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $file = null;
    if (!empty($_FILES['file']['name'])) {
        $ext      = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        $safeName = 'mailout_'.time().'_'.mt_rand(1000,9999).'.'.$ext;
        if (move_uploaded_file($_FILES['file']['tmp_name'], $upload_dir.'/'.$safeName)) {
            $file = $safeName;
        }
    }

    $nomor = $_POST['nomor_surat'] ?? '';
    if ($nomor === '') {
        $nomor = generate_nomor_keluar($pdo);
    }

    $stmt = $pdo->prepare(
        'INSERT INTO mail_out (no_agenda, nomor_surat, tujuan, perihal, tgl_surat, file, status, created_by, created_at)
         VALUES (?,?,?,?,?,?,?,?,NOW())'
    );
    $stmt->execute([
        $_POST['no_agenda'] ?: null,
        $nomor,
        $_POST['tujuan']    ?: null,
        $_POST['perihal']   ?: null,
        $_POST['tgl_surat'] ?: null,
        $file,
        'belum_dibaca',
        $user['id'],
    ]);

    header('Location: mail_out.php');
    exit;
}

// update status surat keluar
if ($action === 'update_status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id     = (int)($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? 'belum_dibaca';
    if (!in_array($status, ['belum_dibaca','proses','selesai'], true)) {
        $status = 'belum_dibaca';
    }
    $stmt = $pdo->prepare('UPDATE mail_out SET status = ? WHERE id = ?');
    $stmt->execute([$status, $id]);
    header('Location: mail_out.php');
    exit;
}

if ($action === 'delete' && isset($_GET['id'])) {
    if ($user['role'] !== 'admin') {
        die('Akses ditolak');
    }
    $id = (int)$_GET['id'];

    $stmt = $pdo->prepare('SELECT file FROM mail_out WHERE id=?');
    $stmt->execute([$id]);
    $old = $stmt->fetch();
    if ($old && $old['file'] && is_file($upload_dir.'/'.$old['file'])) {
        @unlink($upload_dir.'/'.$old['file']);
    }

    $stmt = $pdo->prepare('DELETE FROM mail_out WHERE id=?');
    $stmt->execute([$id]);

    header('Location: mail_out.php');
    exit;
}

$rows = $pdo->query('SELECT m.*, u.nama_lengkap AS pembuat
                     FROM mail_out m
                     LEFT JOIN users u ON m.created_by = u.id
                     ORDER BY m.id DESC')->fetchAll();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Surat Keluar</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php include 'topnav_partial.php'; ?>
<main class="container">
  <div class="page-header">
    <div>
      <h2>Surat Keluar</h2>
      <p class="page-subtitle">Kelola arsip surat keluar dan statusnya (Belum dibaca → Proses → Selesai).</p>
    </div>
  </div>

  <div class="card">
    <h3>Tambah Surat Keluar</h3>
    <form method="post" enctype="multipart/form-data" action="?action=add" class="form-grid">
      <label>No Agenda
        <input name="no_agenda">
      </label>
      <label>Nomor Surat (kosongkan untuk auto)
        <input name="nomor_surat">
      </label>
      <label>Tujuan
        <input name="tujuan">
      </label>
      <label>Perihal
        <input name="perihal">
      </label>
      <label>Tanggal Surat
        <input type="date" name="tgl_surat">
      </label>
      <label>Dokumen (PDF/JPG/PNG)
        <input type="file" name="file">
      </label>
      <div style="grid-column:1/-1">
        <button class="btn" type="submit">Simpan</button>
      </div>
    </form>
  </div>

  <div class="card" style="margin-top:16px">
    <h3>Daftar Surat Keluar</h3>
    <table class="table">
      <thead>
        <tr>
          <th>ID</th>
          <th>No Agenda</th>
          <th>Nomor</th>
          <th>Tujuan</th>
          <th>Perihal</th>
          <th>Tgl Surat</th>
          <th>Dokumen</th>
          <th>Status</th>
          <th>Dibuat Oleh</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?=htmlspecialchars($r['id'])?></td>
          <td><?=htmlspecialchars($r['no_agenda'])?></td>
          <td><?=htmlspecialchars($r['nomor_surat'])?></td>
          <td><?=htmlspecialchars($r['tujuan'])?></td>
          <td><?=htmlspecialchars($r['perihal'])?></td>
          <td><?=htmlspecialchars($r['tgl_surat'])?></td>
          <td>
            <?php if (!empty($r['file'])): ?>
              <a href="uploads/<?=urlencode($r['file'])?>" target="_blank">Lihat</a>
            <?php else: ?>
              -
            <?php endif; ?>
          </td>
          <td>
            <form method="post" action="?action=update_status" class="inline-form">
              <input type="hidden" name="id" value="<?=htmlspecialchars($r['id'])?>">
              <select name="status" class="select-small">
                <option value="belum_dibaca" <?= $r['status']==='belum_dibaca' ? 'selected' : '' ?>>Belum dibaca</option>
                <option value="proses"       <?= $r['status']==='proses'       ? 'selected' : '' ?>>Proses</option>
                <option value="selesai"      <?= $r['status']==='selesai'      ? 'selected' : '' ?>>Selesai</option>
              </select>
              <button class="btn-small" type="submit">OK</button>
            </form>
          </td>
          <td><?=htmlspecialchars($r['pembuat'] ?? '-')?></td>
          <td>
            <?php if ($user['role'] === 'admin'): ?>
              <a class="link-danger"
                 href="?action=delete&id=<?=urlencode($r['id'])?>"
                 onclick="return confirm('Hapus surat ini?')">Hapus</a>
            <?php else: ?>
              -
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>
</body>
</html>
