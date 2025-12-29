<?php
require 'config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role']     = $user['role'];
        $_SESSION['nama']     = $user['nama_lengkap'];
        header('Location: index.php');
        exit;
    } else {
        $error = 'Username atau password salah';
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Login - Aplikasi Surat</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body class="login-body">
  <div class="login-card">
    <h2>Aplikasi Surat</h2>
    <?php if ($error): ?>
      <div class="alert"><?=htmlspecialchars($error)?></div>
    <?php endif; ?>
    <form method="post">
      <label>Username</label>
      <input type="text" name="username" required>
      <label>Password</label>
      <input type="password" name="password" required>
      <button class="btn" type="submit">Login</button>
    </form>
  </div>
</body>
</html>
