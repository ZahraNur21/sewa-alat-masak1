<?php
// includes/header.php
$user = current_user();
$role = get_role();
$base = APP_URL;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $page_title ?? APP_NAME ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
:root {
  --cream: #fdf6ec;
  --warm-white: #fffaf4;
  --orange: #e8620a;
  --orange-deep: #c04f06;
  --orange-light: #fde8d8;
  --brown: #3d1f0a;
  --brown-mid: #7a3b10;
  --gray: #6b6b6b;
  --gray-light: #f0ebe3;
  --success: #2d7a3a;
  --danger: #c0392b;
  --warning: #d4700a;
  --info: #1a6fa3;
  --shadow: 0 2px 16px rgba(61,31,10,0.10);
  --shadow-md: 0 4px 24px rgba(61,31,10,0.13);
  --radius: 14px;
  --radius-sm: 8px;
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body {
  font-family: 'DM Sans', sans-serif;
  background: var(--cream);
  color: var(--brown);
  min-height: 100vh;
}
a { color: var(--orange); text-decoration: none; }
a:hover { color: var(--orange-deep); }

/* SIDEBAR */
.sidebar {
  position: fixed; top: 0; left: 0; height: 100vh; width: 260px;
  background: var(--brown);
  display: flex; flex-direction: column;
  z-index: 100;
  transition: transform 0.3s;
}
.sidebar-brand {
  padding: 28px 24px 20px;
  border-bottom: 1px solid rgba(255,255,255,0.1);
}
.sidebar-brand .brand-icon {
  font-size: 2rem; margin-bottom: 6px;
}
.sidebar-brand h2 {
  font-family: 'Playfair Display', serif;
  color: #fff;
  font-size: 1.25rem;
  line-height: 1.2;
}
.sidebar-brand span {
  color: var(--orange);
  font-size: 0.78rem;
  font-weight: 500;
  letter-spacing: .04em;
  text-transform: uppercase;
}
.sidebar-nav { flex: 1; padding: 16px 0; overflow-y: auto; }
.nav-section {
  padding: 8px 24px 4px;
  font-size: 0.68rem;
  font-weight: 600;
  letter-spacing: .1em;
  text-transform: uppercase;
  color: rgba(255,255,255,0.35);
}
.nav-item {
  display: flex; align-items: center; gap: 12px;
  padding: 11px 24px;
  color: rgba(255,255,255,0.75);
  font-size: 0.92rem;
  font-weight: 400;
  transition: all 0.2s;
  border-left: 3px solid transparent;
}
.nav-item:hover, .nav-item.active {
  background: rgba(232,98,10,0.15);
  color: #fff;
  border-left-color: var(--orange);
}
.nav-item i { width: 18px; text-align: center; font-size: 0.95rem; }
.sidebar-footer {
  padding: 20px 24px;
  border-top: 1px solid rgba(255,255,255,0.1);
}
.user-card {
  display: flex; align-items: center; gap: 12px;
  margin-bottom: 12px;
}
.user-avatar {
  width: 38px; height: 38px; border-radius: 50%;
  background: var(--orange);
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-weight: 700; font-size: 1rem;
  flex-shrink: 0;
}
.user-info .name { color: #fff; font-size: 0.88rem; font-weight: 600; }
.user-info .role-badge {
  font-size: 0.72rem; color: var(--orange);
  text-transform: capitalize; font-weight: 500;
}
.btn-logout {
  display: flex; align-items: center; gap: 8px;
  width: 100%; padding: 9px 14px;
  background: rgba(192,56,6,0.2);
  color: #ffb99a; border: none; border-radius: var(--radius-sm);
  cursor: pointer; font-size: 0.88rem; font-family: inherit;
  transition: background 0.2s;
}
.btn-logout:hover { background: rgba(192,56,6,0.4); color: #fff; }

/* MAIN LAYOUT */
.main-wrapper {
  margin-left: 260px;
  min-height: 100vh;
  display: flex; flex-direction: column;
}
.topbar {
  background: var(--warm-white);
  padding: 16px 32px;
  border-bottom: 1px solid rgba(61,31,10,0.08);
  display: flex; align-items: center; justify-content: space-between;
  position: sticky; top: 0; z-index: 50;
}
.topbar h1 {
  font-family: 'Playfair Display', serif;
  font-size: 1.4rem;
  color: var(--brown);
}
.topbar-right { display: flex; align-items: center; gap: 12px; }
.topbar-date { font-size: 0.83rem; color: var(--gray); }
.main-content { padding: 32px; flex: 1; }

/* CARDS */
.card {
  background: var(--warm-white);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  padding: 24px;
  margin-bottom: 24px;
}
.card-header {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 20px;
  padding-bottom: 14px;
  border-bottom: 1px solid var(--gray-light);
}
.card-header h3 {
  font-family: 'Playfair Display', serif;
  font-size: 1.1rem; color: var(--brown);
}

/* STATS */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 28px;
}
.stat-card {
  background: var(--warm-white);
  border-radius: var(--radius);
  padding: 22px;
  box-shadow: var(--shadow);
  display: flex; flex-direction: column; gap: 8px;
  border-top: 4px solid var(--orange);
  transition: transform 0.2s, box-shadow 0.2s;
}
.stat-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }
.stat-card.blue { border-top-color: var(--info); }
.stat-card.green { border-top-color: var(--success); }
.stat-card.red { border-top-color: var(--danger); }
.stat-label { font-size: 0.78rem; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; color: var(--gray); }
.stat-value { font-family: 'Playfair Display', serif; font-size: 2rem; color: var(--brown); }
.stat-icon { font-size: 1.5rem; color: var(--orange); }

/* BUTTONS */
.btn {
  display: inline-flex; align-items: center; gap: 7px;
  padding: 9px 18px;
  border-radius: var(--radius-sm);
  font-size: 0.88rem; font-family: inherit; font-weight: 500;
  cursor: pointer; border: none; transition: all 0.2s;
  text-decoration: none;
}
.btn-primary { background: var(--orange); color: #fff; }
.btn-primary:hover { background: var(--orange-deep); color: #fff; }
.btn-secondary { background: var(--gray-light); color: var(--brown); }
.btn-secondary:hover { background: #e0d8cc; }
.btn-success { background: var(--success); color: #fff; }
.btn-success:hover { background: #245e2e; color: #fff; }
.btn-danger { background: var(--danger); color: #fff; }
.btn-danger:hover { background: #9b2c2c; color: #fff; }
.btn-warning { background: var(--warning); color: #fff; }
.btn-warning:hover { background: #b85e08; color: #fff; }
.btn-info { background: var(--info); color: #fff; }
.btn-info:hover { background: #155f8c; color: #fff; }
.btn-sm { padding: 6px 12px; font-size: 0.8rem; }

/* TABLE */
.table-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; font-size: 0.88rem; }
th {
  background: var(--gray-light);
  padding: 11px 14px;
  text-align: left;
  font-weight: 600;
  font-size: 0.78rem;
  text-transform: uppercase;
  letter-spacing: .05em;
  color: var(--brown-mid);
}
td { padding: 11px 14px; border-bottom: 1px solid var(--gray-light); vertical-align: middle; }
tr:last-child td { border-bottom: none; }
tr:hover td { background: rgba(232,98,10,0.04); }

/* BADGE */
.badge {
  padding: 3px 10px; border-radius: 99px; font-size: 0.74rem; font-weight: 600;
}
.badge-success { background: #d1fae5; color: #065f46; }
.badge-warning { background: #fef3c7; color: #92400e; }
.badge-danger { background: #fee2e2; color: #7f1d1d; }
.badge-info { background: #dbeafe; color: #1e40af; }
.badge-secondary { background: var(--gray-light); color: var(--gray); }
.badge-orange { background: var(--orange-light); color: var(--orange-deep); }

/* FORMS */
.form-group { margin-bottom: 18px; }
label { display: block; margin-bottom: 6px; font-size: 0.85rem; font-weight: 600; color: var(--brown); }
input[type=text], input[type=email], input[type=password], input[type=number],
input[type=date], select, textarea {
  width: 100%; padding: 10px 14px;
  border: 1.5px solid #ddd5c9;
  border-radius: var(--radius-sm);
  font-family: inherit; font-size: 0.9rem;
  background: #fff; color: var(--brown);
  transition: border-color 0.2s;
}
input:focus, select:focus, textarea:focus {
  outline: none; border-color: var(--orange);
  box-shadow: 0 0 0 3px rgba(232,98,10,0.12);
}
.form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.form-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }

/* ALERT */
.alert {
  padding: 12px 16px; border-radius: var(--radius-sm);
  margin-bottom: 20px; font-size: 0.88rem; display: flex; gap: 10px; align-items: flex-start;
}
.alert-success { background: #d1fae5; color: #065f46; border-left: 4px solid #10b981; }
.alert-error { background: #fee2e2; color: #7f1d1d; border-left: 4px solid #ef4444; }
.alert-warning { background: #fef3c7; color: #92400e; border-left: 4px solid #f59e0b; }
.alert-info { background: #dbeafe; color: #1e40af; border-left: 4px solid #3b82f6; }

/* PAGINATION */
.pagination { display: flex; gap: 6px; justify-content: center; margin-top: 20px; }
.page-btn {
  padding: 7px 13px; border-radius: var(--radius-sm);
  background: var(--warm-white); border: 1.5px solid #ddd5c9;
  color: var(--brown); font-size: 0.85rem; cursor: pointer; font-family: inherit;
  transition: all 0.2s;
}
.page-btn:hover, .page-btn.active { background: var(--orange); color: #fff; border-color: var(--orange); }

/* MODAL */
.modal-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,0.5);
  z-index: 999; display: none; align-items: center; justify-content: center;
}
.modal-overlay.open { display: flex; }
.modal {
  background: var(--warm-white); border-radius: var(--radius);
  padding: 28px; width: 90%; max-width: 540px;
  box-shadow: var(--shadow-md);
}
.modal-header {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 20px;
}
.modal-header h3 { font-family: 'Playfair Display', serif; font-size: 1.2rem; }
.modal-close { background: none; border: none; font-size: 1.3rem; cursor: pointer; color: var(--gray); }

/* EMPTY STATE */
.empty-state { text-align: center; padding: 50px 20px; color: var(--gray); }
.empty-state i { font-size: 3rem; margin-bottom: 12px; opacity: .4; }

@media (max-width: 900px) {
  .sidebar { transform: translateX(-260px); }
  .main-wrapper { margin-left: 0; }
  .form-grid-2, .form-grid-3 { grid-template-columns: 1fr; }
}
</style>
</head>
<body>

<div class="sidebar">
  <div class="sidebar-brand">
    <div class="brand-icon">🍳</div>
    <h2><?= APP_NAME ?></h2>
    <span>Sistem Sewa Alat Masak</span>
  </div>
  <nav class="sidebar-nav">
    <?php if ($role === 'admin'): ?>
    <div class="nav-section">Menu Admin</div>
    <a href="<?=$base?>/admin/dashboard.php" class="nav-item <?= strpos($_SERVER['PHP_SELF'],'dashboard')!==false?'active':'' ?>">
      <i class="fas fa-tachometer-alt"></i> Dashboard
    </a>
    <div class="nav-section">Master Data</div>
    <a href="<?=$base?>/admin/users.php" class="nav-item <?= strpos($_SERVER['PHP_SELF'],'users')!==false?'active':'' ?>">
      <i class="fas fa-users"></i> Kelola User
    </a>
    <a href="<?=$base?>/admin/alat.php" class="nav-item <?= strpos($_SERVER['PHP_SELF'],'/alat')!==false?'active':'' ?>">
      <i class="fas fa-utensils"></i> Kelola Alat
    </a>
    <a href="<?=$base?>/admin/kategori.php" class="nav-item <?= strpos($_SERVER['PHP_SELF'],'kategori')!==false?'active':'' ?>">
      <i class="fas fa-tags"></i> Kategori
    </a>
    <div class="nav-section">Transaksi</div>
    <a href="<?=$base?>/admin/peminjaman.php" class="nav-item <?= strpos($_SERVER['PHP_SELF'],'peminjaman')!==false?'active':'' ?>">
      <i class="fas fa-clipboard-list"></i> Data Peminjaman
    </a>
    <a href="<?=$base?>/admin/pengembalian.php" class="nav-item <?= strpos($_SERVER['PHP_SELF'],'pengembalian')!==false?'active':'' ?>">
      <i class="fas fa-undo-alt"></i> Data Pengembalian
    </a>
    <a href="<?=$base?>/admin/log.php" class="nav-item <?= strpos($_SERVER['PHP_SELF'],'log')!==false?'active':'' ?>">
      <i class="fas fa-history"></i> Log Aktifitas
    </a>
    <?php elseif ($role === 'petugas'): ?>
    <div class="nav-section">Menu Petugas</div>
    <a href="<?=$base?>/petugas/dashboard.php" class="nav-item <?= strpos($_SERVER['PHP_SELF'],'dashboard')!==false?'active':'' ?>">
      <i class="fas fa-tachometer-alt"></i> Dashboard
    </a>
    <a href="<?=$base?>/petugas/peminjaman.php" class="nav-item <?= strpos($_SERVER['PHP_SELF'],'peminjaman')!==false?'active':'' ?>">
      <i class="fas fa-clipboard-check"></i> Setujui Peminjaman
    </a>
    <a href="<?=$base?>/petugas/pengembalian.php" class="nav-item <?= strpos($_SERVER['PHP_SELF'],'pengembalian')!==false?'active':'' ?>">
      <i class="fas fa-undo-alt"></i> Pantau Pengembalian
    </a>
    <a href="<?=$base?>/petugas/laporan.php" class="nav-item <?= strpos($_SERVER['PHP_SELF'],'laporan')!==false?'active':'' ?>">
      <i class="fas fa-print"></i> Cetak Laporan
    </a>
    <?php elseif ($role === 'peminjam'): ?>
    <div class="nav-section">Menu Saya</div>
    <a href="<?=$base?>/peminjam/dashboard.php" class="nav-item <?= strpos($_SERVER['PHP_SELF'],'dashboard')!==false?'active':'' ?>">
      <i class="fas fa-home"></i> Beranda
    </a>
    <a href="<?=$base?>/peminjam/daftar_alat.php" class="nav-item <?= strpos($_SERVER['PHP_SELF'],'daftar_alat')!==false?'active':'' ?>">
      <i class="fas fa-utensils"></i> Daftar Alat
    </a>
    <a href="<?=$base?>/peminjam/pinjam.php" class="nav-item <?= strpos($_SERVER['PHP_SELF'],'pinjam')!==false?'active':'' ?>">
      <i class="fas fa-plus-circle"></i> Ajukan Peminjaman
    </a>
    <a href="<?=$base?>/peminjam/riwayat.php" class="nav-item <?= strpos($_SERVER['PHP_SELF'],'riwayat')!==false?'active':'' ?>">
      <i class="fas fa-history"></i> Riwayat Peminjaman
    </a>
    <a href="<?=$base?>/peminjam/pengembalian.php" class="nav-item <?= strpos($_SERVER['PHP_SELF'],'pengembalian')!==false?'active':'' ?>">
      <i class="fas fa-undo"></i> Kembalikan Alat
    </a>
    <?php endif; ?>
  </nav>
  <div class="sidebar-footer">
    <div class="user-card">
      <div class="user-avatar"><?= strtoupper(substr($user['nama'],0,1)) ?></div>
      <div class="user-info">
        <div class="name"><?= htmlspecialchars($user['nama']) ?></div>
        <div class="role-badge"><?= $user['role'] ?></div>
      </div>
    </div>
    <<form method="POST" action="<?=$base?>/auth/logout.php">
      <button type="submit" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Keluar</button>
    </form>
  </div>
</div>

<div class="main-wrapper">
<div class="topbar">
  <h1><?= $page_title ?? APP_NAME ?></h1>
  <div class="topbar-right">
    <span class="topbar-date"><i class="far fa-calendar"></i> <?= date('d M Y') ?></span>
  </div>
</div>
<div class="main-content">
<?php
if (isset($_SESSION['flash_success'])): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $_SESSION['flash_success'] ?></div>
<?php unset($_SESSION['flash_success']); endif; ?>
<?php if (isset($_SESSION['flash_error'])): ?>
<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $_SESSION['flash_error'] ?></div>
<?php unset($_SESSION['flash_error']); endif; ?>