<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

// Kalau sudah login → redirect ke dashboard sesuai role
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'];
    header("Location: " . APP_URL . "/$role/dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = trim($_POST['password'] ?? '');
    
    if ($email && $pass) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if ($user && password_verify($pass, $user['password'])) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama']    = $user['nama'];
            $_SESSION['email']   = $user['email'];
            $_SESSION['role']    = $user['role'];

            // Log aktivitas
            log_activity($conn, $user['id'], 'Login', 'Login berhasil');

            // Redirect ke dashboard sesuai role
            header("Location: " . APP_URL . "/{$user['role']}/dashboard.php");
            exit;
        } else {
            $error = 'Email atau password salah.';
        }
    } else {
        $error = 'Mohon isi semua field.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login – <?= APP_NAME ?></title>

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
:root {
  --cream:#fdf6ec;--orange:#e8620a;--orange-deep:#c04f06;--brown:#3d1f0a;
  --brown-mid:#7a3b10;--gray:#6b6b6b;
}
*{box-sizing:border-box;margin:0;padding:0;}
body{
  font-family:'DM Sans',sans-serif;
  background: linear-gradient(135deg, #3d1f0a 0%, #7a3b10 50%, #c04f06 100%);
  min-height:100vh;display:flex;align-items:center;justify-content:center;
  padding:20px;
}
.login-wrap{
  display:grid;grid-template-columns:1fr 1fr;
  max-width:900px;width:100%;
  background:#fff;border-radius:20px;overflow:hidden;
  box-shadow:0 25px 60px rgba(0,0,0,0.3);
}
.login-left{
  background: linear-gradient(160deg, #3d1f0a, #e8620a);
  padding:50px 40px;
  display:flex;flex-direction:column;justify-content:center;
}
.login-left h1{
  font-family:'Playfair Display',serif;
  color:#fff;font-size:2rem;margin-bottom:12px;
}
.login-left p{color:rgba(255,255,255,0.75);}
.login-right{padding:50px 44px;}
.login-right h2{
  font-family:'Playfair Display',serif;
  color:var(--brown);font-size:1.7rem;margin-bottom:6px;
}
.login-right p{color:var(--gray);margin-bottom:25px;}
.form-group{margin-bottom:16px;}
label{font-size:0.85rem;font-weight:600;}
input{
  width:100%;padding:10px;
  border:1px solid #ccc;border-radius:8px;
}
.btn-login{
  width:100%;padding:12px;
  background:var(--orange);color:#fff;
  border:none;border-radius:8px;
  cursor:pointer;font-weight:600;
}
.alert-error{
  background:#fee2e2;color:#7f1d1d;
  padding:10px;border-radius:8px;
  margin-bottom:15px;
}
.register-link{text-align:center;margin-top:15px;}
</style>
</head>

<body>
<div class="login-wrap">

  <div class="login-left">
    <h1>Sewa Alat Masak</h1>
    <p>Login untuk mengakses sistem.</p>
  </div>

  <div class="login-right">
    <h2>Login</h2>
    <p>Masukkan email & password</p>

    <?php if ($error): ?>
      <div class="alert-error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>

      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" required>
      </div>

      <button type="submit" class="btn-login">Login</button>
    </form>

    <div class="register-link">
      Belum punya akun? <a href="register.php">Daftar</a>
    </div>

  </div>

</div>
</body>
</html>