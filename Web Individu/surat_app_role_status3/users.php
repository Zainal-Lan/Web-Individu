<?php
require 'config.php';
require_login();
$user = current_user();
if ($user['role'] !== 'admin') {
    die('Akses ditolak');
}

$action = $_GET['action'] ?? 'list';

if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare(
        'INSERT INTO users (username, password, nama_lengkap, role)
         VALUES (?,?,?,?)'
    );
    $stmt->execute([
        $_POST['username'],
        password_hash($_POST['password'], PASSWORD_DEFAULT),
        $_POST['nama_lengkap'],
        $_POST['role'],
    ]);
    header('Location: users.php');
    exit;
}

if ($action === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($id == $user['id']) {
        die('Tidak boleh menghapus diri sendiri');
    }
    $stmt = $pdo->prepare('DELETE FROM users WHERE id=?');
    $stmt->execute([$id]);
    header('Location: users.php');
    exit;
}

$rows = $pdo->query('SELECT * FROM users ORDER BY id ASC')->fetchAll();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Pengguna</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php include 'topnav_partial.php'; ?>
<main class="container">
  <h2>Pengguna</h2>

  <div class="card">
    <h3>Tambah Pengguna</h3>
    <form method="post" action="?action=add" class="form-grid">
      <label>Nama Lengkap
        <input name="nama_lengkap" required>
      </label>
      <label>Username
        <input name="username" required>
      </label>
      <label>Password
        <input type="password" name="password" required>
      </label>
      <label>Role
        <select name="role" required>
          <option value="admin">Admin</option>
          <option value="pegawai">Pegawai</option>
        </select>
      </label>
      <div style="grid-column:1/-1">
        <button class="btn" type="submit">Simpan</button>
      </div>
    </form>
  </div>

  <div class="card" style="margin-top:16px">
    <h3>Daftar Pengguna</h3>
    <table class="table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nama</th>
          <th>Username</th>
          <th>Role</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?=htmlspecialchars($r['id'])?></td>
          <td><?=htmlspecialchars($r['nama_lengkap'])?></td>
          <td><?=htmlspecialchars($r['username'])?></td>
          <td><?=htmlspecialchars($r['role'])?></td>
          <td>
            <?php if ($r['id'] != $user['id']): ?>
              <a class="link-danger"
                 href="?action=delete&id=<?=urlencode($r['id'])?>"
                 onclick="return confirm('Hapus pengguna ini?')">Hapus</a>
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
