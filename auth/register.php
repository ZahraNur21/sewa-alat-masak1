<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once __DIR__ . '/../includes/config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: " . APP_URL . "/{$_SESSION['role']}/dashboard.php");
    exit;
}

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama    = trim($_POST['nama'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $pass    = $_POST['password'] ?? '';
    $telepon = trim($_POST['telepon'] ?? '');
    $alamat  = trim($_POST['alamat'] ?? '');

    if (!$nama || !$email || !$pass) {
        $error = 'Nama, email, dan password wajib diisi.';
    } elseif (strlen($pass) < 6) {
        $error = 'Password minimal 6 karakter.';
    } else {

        $check = $conn->prepare("SELECT id FROM users WHERE email=?");
        if (!$check) die("ERROR: " . $conn->error);

        $check->bind_param("s", $email);
        $check->execute();

        if ($check->get_result()->num_rows > 0) {
            $error = 'Email sudah terdaftar.';
        } else {
            $hashed = password_hash($pass, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO users (nama,email,password,role,telepon,alamat) VALUES (?,?,?,'peminjam',?,?)");
            if (!$stmt) die("ERROR: " . $conn->error);

            $stmt->bind_param("sssss", $nama, $email, $hashed, $telepon, $alamat);

            if ($stmt->execute()) {
                $success = 'Akun berhasil dibuat! Silakan login.';
            } else {
                $error = 'Gagal membuat akun.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar – <?= APP_NAME ?></title>

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
:root{--cream:#fdf6ec;--orange:#e8620a;--orange-deep:#c04f06;--brown:#3d1f0a;--gray:#6b6b6b;}
*{box-sizing:border-box;margin:0;padding:0;}
body{
  font-family:'DM Sans',sans-serif;
  background:linear-gradient(135deg,#3d1f0a,#c04f06);
  min-height:100vh;display:flex;align-items:center;justify-content:center;
}
.wrap{
  background:#fff;border-radius:20px;
  max-width:500px;width:100%;padding:44px;
  box-shadow:0 25px 60px rgba(0,0,0,0.3);
}
.logo{text-align:center;margin-bottom:24px;}
.logo span{font-size:2.5rem;}
.logo h2{font-family:'Playfair Display',serif;color:var(--brown);}
.logo p{color:var(--gray);font-size:0.9rem;}

.form-group{margin-bottom:14px;}
label{font-size:0.8rem;font-weight:600;color:var(--brown);}
.iw{position:relative;}
.iw i{position:absolute;left:10px;top:50%;transform:translateY(-50%);}
input,textarea{
  width:100%;padding:10px 10px 10px 34px;
  border-radius:10px;border:1px solid #ddd;
}
.btn{
  width:100%;padding:12px;
  background:var(--orange);color:#fff;
  border:none;border-radius:10px;
  cursor:pointer;font-weight:600;
}
.btn:hover{background:var(--orange-deep);}
.alert-e{background:#fee2e2;color:#7f1d1d;padding:10px;border-radius:8px;margin-bottom:10px;}
.alert-s{background:#d1fae5;color:#065f46;padding:10px;border-radius:8px;margin-bottom:10px;}
.login-link{text-align:center;margin-top:12px;font-size:0.85rem;}
</style>
</head>

<body>

<div class="wrap">
  <div class="logo">
    <span>🍳</span>
    <h2>Daftar Akun</h2>
    <p>Mulai sewa alat masak sekarang</p>
  </div>

  <?php if($error): ?><div class="alert-e"><?= $error ?></div><?php endif; ?>
  <?php if($success): ?><div class="alert-s"><?= $success ?> <a href="login.php">Login</a></div><?php endif; ?>

  <form method="POST">
    <div class="form-group">
      <label>Nama</label>
      <div class="iw">
        <i class="fas fa-user"></i>
        <input type="text" name="nama" required>
      </div>
    </div>

    <div class="form-group">
      <label>Email</label>
      <div class="iw">
        <i class="fas fa-envelope"></i>
        <input type="email" name="email" required>
      </div>
    </div>

    <div class="form-group">
      <label>Password</label>
      <div class="iw">
        <i class="fas fa-lock"></i>
        <input type="password" name="password" required>
      </div>
    </div>

    <div class="form-group">
      <label>Telepon</label>
      <div class="iw">
        <i class="fas fa-phone"></i>
        <input type="text" name="telepon">
      </div>
    </div>

    <div class="form-group">
      <label>Alamat</label>
      <div class="iw">
        <i class="fas fa-map-marker-alt"></i>
        <textarea name="alamat"></textarea>
      </div>
    </div>

    <button class="btn">Daftar</button>
  </form>

  <div class="login-link">
    Sudah punya akun? <a href="login.php">Login</a>
  </div>
</div>

</body>
</html>