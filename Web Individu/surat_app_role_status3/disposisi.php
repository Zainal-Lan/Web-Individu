<?php
require 'config.php';
require_login();
$user = current_user();

$action = $_GET['action'] ?? 'list';

// ambil daftar surat untuk dropdown tambah disposisi
$mail_in  = $pdo->query('SELECT id, nomor_surat, perihal FROM mail_in ORDER BY id DESC')->fetchAll();
$mail_out = $pdo->query('SELECT id, nomor_surat, perihal FROM mail_out ORDER BY id DESC')->fetchAll();

// tambah disposisi
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare(
        'INSERT INTO disposisi (mail_type, mail_id, kepada, pesan, status, catatan, created_by, created_at)
         VALUES (?,?,?,?,?,?,?,NOW())'
    );
    $stmt->execute([
        $_POST['mail_type'] ?? 'masuk',
        $_POST['mail_id']   ?? null,
        $_POST['kepada']    ?? null,
        $_POST['pesan']     ?? null,
        'belum_dibaca',
        $_POST['catatan']   ?? null,
        $user['id'],
    ]);
    header('Location: disposisi.php');
    exit;
}

// update status & catatan disposisi
if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id      = (int)($_POST['id'] ?? 0);
    $status  = $_POST['status'] ?? 'belum_dibaca';
    $catatan = $_POST['catatan'] ?? null;

    if (!in_array($status, ['belum_dibaca','proses','selesai'], true)) {
        $status = 'belum_dibaca';
    }

    $stmt = $pdo->prepare('SELECT * FROM disposisi WHERE id=?');
    $stmt->execute([$id]);
    $dis = $stmt->fetch();

    if (!$dis) {
        die('Data tidak ditemukan');
    }

    $boleh = false;

    if ($user['role'] === 'admin') {
        $boleh = true;
    } else {
        $kepada = strtolower($dis['kepada'] ?? '');
        $nama   = strtolower($user['nama'] ?? '');
        $uname  = strtolower($user['username'] ?? '');

        if ($kepada && ($nama && strpos($kepada, $nama) !== false ||
                        $uname && strpos($kepada, $uname) !== false)) {
            $boleh = true;
        }
    }

    if (!$boleh) {
        die('Anda tidak berhak mengubah disposisi ini');
    }

    $stmt = $pdo->prepare('UPDATE disposisi SET status = ?, catatan = ? WHERE id = ?');
    $stmt->execute([$status, $catatan, $id]);

    header('Location: disposisi.php');
    exit;
}

// hapus disposisi (hanya admin)
if ($action === 'delete' && isset($_GET['id'])) {
    if ($user['role'] !== 'admin') {
        die('Akses ditolak');
    }
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare('DELETE FROM disposisi WHERE id=?');
    $stmt->execute([$id]);
    header('Location: disposisi.php');
    exit;
}

// list disposisi
$rows = $pdo->query(
"SELECT d.*, u.nama_lengkap AS pembuat
 FROM disposisi d
 LEFT JOIN users u ON d.created_by = u.id
 ORDER BY d.id DESC"
)->fetchAll();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Disposisi</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php include 'topnav_partial.php'; ?>
<main class="container">
  <div class="page-header">
    <div>
      <h2>Disposisi</h2>
      <p class="page-subtitle">Buat disposisi dan pantau progresnya (Belum dibaca → Proses → Selesai).</p>
    </div>
  </div>

  <div class="card" id="buat-disposisi">
    <h3>Buat Disposisi Baru</h3>
    <form method="post" action="?action=add" class="form-grid">
      <label>Jenis Surat
        <select name="mail_type" required>
          <option value="masuk">Surat Masuk</option>
          <option value="keluar">Surat Keluar</option>
        </select>
      </label>

      <label>Surat Terkait
        <select name="mail_id" required>
          <option value="">-- pilih surat --</option>
          <optgroup label="Surat Masuk">
            <?php foreach ($mail_in as $m): ?>
              <option value="<?=htmlspecialchars($m['id'])?>">
                [M<?=$m['id']?>] <?=htmlspecialchars($m['nomor_surat'])?> - <?=htmlspecialchars($m['perihal'])?>
              </option>
            <?php endforeach; ?>
          </optgroup>
          <optgroup label="Surat Keluar">
            <?php foreach ($mail_out as $m): ?>
              <option value="<?=htmlspecialchars($m['id'])?>">
                [K<?=$m['id']?>] <?=htmlspecialchars($m['nomor_surat'])?> - <?=htmlspecialchars($m['perihal'])?>
              </option>
            <?php endforeach; ?>
          </optgroup>
        </select>
      </label>

      <label>Kepada (nama / username)
        <input type="text" name="kepada" placeholder="Mis: Kepala Bagian, pegawai">
      </label>

      <label>Pesan Disposisi
        <input type="text" name="pesan" placeholder="Instruksi singkat">
      </label>

      <label>Catatan Awal (opsional)
        <input type="text" name="catatan" placeholder="Catatan tambahan">
      </label>

      <div style="grid-column:1/-1">
        <button class="btn" type="submit">Simpan Disposisi</button>
      </div>
    </form>
  </div>

  <div class="card" style="margin-top:16px">
    <h3>Daftar Disposisi</h3>
    <table class="table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Jenis Surat</th>
          <th>ID Surat</th>
          <th>Kepada</th>
          <th>Pesan</th>
          <th>Status</th>
          <th>Catatan</th>
          <th>Dibuat Oleh</th>
          <th>Tanggal</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($rows as $r): 
        $isTarget = false;
        $kepada = strtolower($r['kepada'] ?? '');
        $nama   = strtolower($user['nama'] ?? '');
        $uname  = strtolower($user['username'] ?? '');
        if ($kepada && (($nama && strpos($kepada, $nama) !== false) ||
                        ($uname && strpos($kepada, $uname) !== false))) {
            $isTarget = true;
        }
      ?>
        <tr>
          <td><?=htmlspecialchars($r['id'])?></td>
          <td><?=htmlspecialchars($r['mail_type'])?></td>
          <td><?=htmlspecialchars($r['mail_id'])?></td>
          <td><?=htmlspecialchars($r['kepada'])?></td>
          <td><?=htmlspecialchars($r['pesan'])?></td>
          <td>
            <?php if ($isTarget || $user['role'] === 'admin'): ?>
              <form method="post" action="?action=update" class="inline-form">
                <input type="hidden" name="id" value="<?=htmlspecialchars($r['id'])?>">
                <select name="status" class="select-small">
                  <option value="belum_dibaca" <?= $r['status']==='belum_dibaca' ? 'selected' : '' ?>>Belum dibaca</option>
                  <option value="proses"       <?= $r['status']==='proses'       ? 'selected' : '' ?>>Proses</option>
                  <option value="selesai"      <?= $r['status']==='selesai'      ? 'selected' : '' ?>>Selesai</option>
                </select>
            <?php else: ?>
              <span class="badge-status badge-<?=htmlspecialchars($r['status'] ?? 'belum_dibaca')?>"><?=htmlspecialchars($r['status'] ?? 'belum_dibaca')?></span>
            <?php endif; ?>
          </td>
          <td>
            <?php if ($isTarget || $user['role'] === 'admin'): ?>
                <input type="text" name="catatan" class="input-small" placeholder="Catatan"
                       value="<?=htmlspecialchars($r['catatan'] ?? '')?>">
                <button class="btn-small" type="submit">Simpan</button>
              </form>
            <?php else: ?>
              <?=htmlspecialchars($r['catatan'] ?? '-')?>
            <?php endif; ?>
          </td>
          <td><?=htmlspecialchars($r['pembuat'] ?? '-')?></td>
          <td><?=htmlspecialchars($r['created_at'])?></td>
          <td>
            <?php if ($user['role'] === 'admin'): ?>
              <a class="link-danger"
                 href="?action=delete&id=<?=urlencode($r['id'])?>"
                 onclick="return confirm('Hapus disposisi ini?')">Hapus</a>
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
