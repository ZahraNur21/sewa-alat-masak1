<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_role('admin');

$page_title = 'Dashboard Admin';

// Stats
$total_user   = $conn->query("SELECT COUNT(*) c FROM users WHERE role='peminjam'")->fetch_assoc()['c'];
$total_alat   = $conn->query("SELECT COUNT(*) c FROM alat")->fetch_assoc()['c'];
$total_pinjam = $conn->query("SELECT COUNT(*) c FROM peminjaman")->fetch_assoc()['c'];
$menunggu     = $conn->query("SELECT COUNT(*) c FROM peminjaman WHERE status='menunggu'")->fetch_assoc()['c'];
$dipinjam     = $conn->query("SELECT COUNT(*) c FROM peminjaman WHERE status='dipinjam'")->fetch_assoc()['c'];

$recent = $conn->query("
  SELECT p.*, u.nama user_nama 
  FROM peminjaman p JOIN users u ON p.user_id=u.id 
  ORDER BY p.created_at DESC LIMIT 8
");

include '../includes/header.php';
?>

<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon"><i class="fas fa-users"></i></div>
    <div class="stat-label">Total Peminjam</div>
    <div class="stat-value"><?= $total_user ?></div>
  </div>
  <div class="stat-card blue">
    <div class="stat-icon" style="color:var(--info)"><i class="fas fa-utensils"></i></div>
    <div class="stat-label">Total Alat</div>
    <div class="stat-value"><?= $total_alat ?></div>
  </div>
  <div class="stat-card green">
    <div class="stat-icon" style="color:var(--success)"><i class="fas fa-clipboard-list"></i></div>
    <div class="stat-label">Total Transaksi</div>
    <div class="stat-value"><?= $total_pinjam ?></div>
  </div>
  <div class="stat-card red">
    <div class="stat-icon" style="color:var(--warning)"><i class="fas fa-clock"></i></div>
    <div class="stat-label">Menunggu Approval</div>
    <div class="stat-value"><?= $menunggu ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon"><i class="fas fa-hand-holding"></i></div>
    <div class="stat-label">Sedang Dipinjam</div>
    <div class="stat-value"><?= $dipinjam ?></div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <h3><i class="fas fa-clock" style="color:var(--orange)"></i> Transaksi Terbaru</h3>
    <a href="peminjaman.php" class="btn btn-secondary btn-sm">Lihat Semua</a>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>Kode</th><th>Peminjam</th><th>Tgl Pinjam</th><th>Tgl Kembali</th><th>Status</th><th>Aksi</th></tr>
      </thead>
      <tbody>
        <?php while($r=$recent->fetch_assoc()): ?>
        <tr>
          <td><strong><?= $r['kode_pinjam'] ?></strong></td>
          <td><?= htmlspecialchars($r['user_nama']) ?></td>
          <td><?= date('d/m/Y', strtotime($r['tanggal_pinjam'])) ?></td>
          <td><?= date('d/m/Y', strtotime($r['tanggal_kembali'])) ?></td>
          <td><?php
            $badges = ['menunggu'=>'badge-warning','disetujui'=>'badge-info','ditolak'=>'badge-danger','dipinjam'=>'badge-orange','selesai'=>'badge-success'];
            $labels = ['menunggu'=>'Menunggu','disetujui'=>'Disetujui','ditolak'=>'Ditolak','dipinjam'=>'Dipinjam','selesai'=>'Selesai'];
          ?><span class="badge <?= $badges[$r['status']] ?>"><?= $labels[$r['status']] ?></span></td>
          <td><a href="peminjaman.php?id=<?=$r['id']?>" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include '../includes/footer.php'; ?>