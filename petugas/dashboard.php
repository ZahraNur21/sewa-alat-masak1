<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_role('petugas');
$page_title = 'Dashboard Petugas';

$menunggu = $conn->query("SELECT COUNT(*) c FROM peminjaman WHERE status='menunggu'")->fetch_assoc()['c'];
$dipinjam = $conn->query("SELECT COUNT(*) c FROM peminjaman WHERE status='dipinjam'")->fetch_assoc()['c'];
$selesai  = $conn->query("SELECT COUNT(*) c FROM peminjaman WHERE status='selesai'")->fetch_assoc()['c'];

$pending = $conn->query("
  SELECT p.*,u.nama user_nama FROM peminjaman p JOIN users u ON p.user_id=u.id
  WHERE p.status='menunggu' ORDER BY p.created_at ASC LIMIT 5
");

include '../includes/header.php';
?>
<div class="stats-grid">
  <div class="stat-card red">
    <div class="stat-icon" style="color:var(--warning)"><i class="fas fa-clock"></i></div>
    <div class="stat-label">Menunggu Persetujuan</div>
    <div class="stat-value"><?=$menunggu?></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon"><i class="fas fa-hand-holding"></i></div>
    <div class="stat-label">Sedang Dipinjam</div>
    <div class="stat-value"><?=$dipinjam?></div>
  </div>
  <div class="stat-card green">
    <div class="stat-icon" style="color:var(--success)"><i class="fas fa-check"></i></div>
    <div class="stat-label">Selesai</div>
    <div class="stat-value"><?=$selesai?></div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <h3>⏳ Menunggu Persetujuan</h3>
    <a href="peminjaman.php" class="btn btn-primary btn-sm">Kelola Semua</a>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Kode</th><th>Peminjam</th><th>Tgl Pinjam</th><th>Tgl Kembali</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php while($p=$pending->fetch_assoc()): ?>
        <tr>
          <td><strong><?=$p['kode_pinjam']?></strong></td>
          <td><?=htmlspecialchars($p['user_nama'])?></td>
          <td><?=date('d/m/Y',strtotime($p['tanggal_pinjam']))?></td>
          <td><?=date('d/m/Y',strtotime($p['tanggal_kembali']))?></td>
          <td><a href="peminjaman.php?id=<?=$p['id']?>" class="btn btn-warning btn-sm"><i class="fas fa-gavel"></i> Proses</a></td>
        </tr>
        <?php endwhile; ?>
        <?php if($menunggu==0): ?>
        <tr><td colspan="5" class="empty-state">Tidak ada pengajuan menunggu persetujuan</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include '../includes/footer.php'; ?>