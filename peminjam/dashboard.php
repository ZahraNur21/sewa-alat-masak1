<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_role('peminjam');
$page_title = 'Beranda';

$uid = $_SESSION['user_id'];
$total_pinjam = $conn->query("SELECT COUNT(*) c FROM peminjaman WHERE user_id=$uid")->fetch_assoc()['c'];
$aktif        = $conn->query("SELECT COUNT(*) c FROM peminjaman WHERE user_id=$uid AND status IN('dipinjam','disetujui')")->fetch_assoc()['c'];
$menunggu     = $conn->query("SELECT COUNT(*) c FROM peminjaman WHERE user_id=$uid AND status='menunggu'")->fetch_assoc()['c'];
$selesai      = $conn->query("SELECT COUNT(*) c FROM peminjaman WHERE user_id=$uid AND status='selesai'")->fetch_assoc()['c'];

$recent = $conn->query("
    SELECT p.*, 
    (SELECT GROUP_CONCAT(a.nama SEPARATOR ', ') FROM detail_peminjaman dp JOIN alat a ON dp.alat_id=a.id WHERE dp.peminjaman_id=p.id) alat_list
    FROM peminjaman p WHERE p.user_id=$uid ORDER BY p.created_at DESC LIMIT 5
");

include '../includes/header.php';
?>

<div style="background:linear-gradient(135deg,var(--brown),var(--orange));border-radius:var(--radius);padding:28px;margin-bottom:24px;color:#fff;">
  <h2 style="font-family:'Playfair Display',serif;font-size:1.5rem;margin-bottom:4px">
    Selamat datang, <?=htmlspecialchars($_SESSION['nama'])?>! 👋
  </h2>
  <p style="opacity:.85;font-size:0.9rem">Sewa alat masak berkualitas dengan mudah dan cepat.</p>
  <div style="margin-top:16px;">
    <a href="pinjam.php" class="btn btn-primary" style="background:#fff;color:var(--orange-deep);font-weight:700">
      <i class="fas fa-plus-circle"></i> Ajukan Peminjaman Baru
    </a>
  </div>
</div>

<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon"><i class="fas fa-history"></i></div>
    <div class="stat-label">Total Pinjam</div>
    <div class="stat-value"><?=$total_pinjam?></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon"><i class="fas fa-hand-holding"></i></div>
    <div class="stat-label">Sedang Aktif</div>
    <div class="stat-value"><?=$aktif?></div>
  </div>
  <div class="stat-card red">
    <div class="stat-icon" style="color:var(--warning)"><i class="fas fa-clock"></i></div>
    <div class="stat-label">Menunggu</div>
    <div class="stat-value"><?=$menunggu?></div>
  </div>
  <div class="stat-card green">
    <div class="stat-icon" style="color:var(--success)"><i class="fas fa-check-circle"></i></div>
    <div class="stat-label">Selesai</div>
    <div class="stat-value"><?=$selesai?></div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <h3>📋 Riwayat Terbaru</h3>
    <a href="riwayat.php" class="btn btn-secondary btn-sm">Lihat Semua</a>
  </div>
  <?php
  $status_labels=['menunggu'=>'Menunggu','disetujui'=>'Disetujui','ditolak'=>'Ditolak','dipinjam'=>'Dipinjam','selesai'=>'Selesai'];
  $status_badges=['menunggu'=>'badge-warning','disetujui'=>'badge-info','ditolak'=>'badge-danger','dipinjam'=>'badge-orange','selesai'=>'badge-success'];
  ?>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Kode</th><th>Alat</th><th>Tgl Pinjam</th><th>Tgl Kembali</th><th>Status</th></tr></thead>
      <tbody>
        <?php $cnt=0; while($r=$recent->fetch_assoc()): $cnt++; ?>
        <tr>
          <td><strong><?=$r['kode_pinjam']?></strong></td>
          <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?=htmlspecialchars(substr($r['alat_list']??'-',0,45))?></td>
          <td><?=date('d/m/Y',strtotime($r['tanggal_pinjam']))?></td>
          <td><?=date('d/m/Y',strtotime($r['tanggal_kembali']))?></td>
          <td><span class="badge <?=$status_badges[$r['status']]?>"><?=$status_labels[$r['status']]?></span></td>
        </tr>
        <?php endwhile; ?>
        <?php if($cnt==0): ?>
        <tr><td colspan="5">
          <div class="empty-state"><i class="fas fa-utensils"></i><p>Belum ada riwayat peminjaman.<br><a href="pinjam.php">Ajukan peminjaman pertama Anda</a></p></div>
        </td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include '../includes/footer.php'; ?>