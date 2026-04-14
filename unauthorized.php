<?php
session_start();
require_once 'includes/config.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Akses Ditolak – <?=APP_NAME?></title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;600&display=swap" rel="stylesheet">
<style>
body{font-family:'DM Sans',sans-serif;background:#fdf6ec;display:flex;align-items:center;justify-content:center;min-height:100vh;text-align:center;}
.box{max-width:400px;padding:40px;}
.icon{font-size:4rem;margin-bottom:16px;}
h1{font-family:'Playfair Display',serif;color:#3d1f0a;font-size:2rem;margin-bottom:10px;}
p{color:#6b6b6b;margin-bottom:24px;}
a{display:inline-block;padding:11px 24px;background:#e8620a;color:#fff;border-radius:10px;font-weight:600;text-decoration:none;}
a:hover{background:#c04f06;}
</style>
</head>
<body>
<div class="box">
  <div class="icon">🚫</div>
  <h1>Akses Ditolak</h1>
  <p>Anda tidak memiliki izin untuk mengakses halaman ini.</p>
  <a href="<?=APP_URL?>/<?=$_SESSION['role']??''?>/dashboard.php">Kembali ke Dashboard</a>
</div>
</body>
</html>