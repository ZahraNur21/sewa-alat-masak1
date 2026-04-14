<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_role('peminjam');
$page_title = 'Kembalikan Alat';

$uid = $_SESSION['user_id'];

// Get active loans for this user
$actives = $conn->query("
    SELECT p.*,
    (SELECT GROUP_CONCAT(a.nama SEPARATOR ', ') FROM detail_peminjaman dp JOIN alat a ON dp.alat_id=a.id WHERE dp.peminjaman_id=p.id) alat_list
    FROM peminjaman p
    WHERE p.user_id=$uid AND p.status='dipinjam'
    ORDER BY p.tanggal_kembali ASC
");

$selected = null;
$detail_items_list = [];
if (isset($_GET['id'])) {
    $sid = (int)$_GET['id'];
    $selected = $conn->query("SELECT * FROM peminjaman WHERE id=$sid AND user_id=$uid AND status='dipinjam'")->fetch_assoc();
    if ($selected) {
        $di = $conn->query("SELECT dp.*,a.nama alat_nama,a.kode,a.harga_sewa FROM detail_peminjaman dp JOIN alat a ON dp.alat_id=a.id WHERE dp.peminjaman_id=$sid");
        while($row=$di->fetch_assoc()) $detail_items_list[] = $row;
    }
}

include '../includes/header.php';
?>

<?php if(empty($actives->num_rows) && !$selected): ?>
<div class="card">
  <div class="empty-state">
    <i class="fas fa-check-circle" style="color:var(--success)"></i>
    <p>Anda tidak memiliki peminjaman aktif saat ini.</p>
    <a href="pinjam.php" class="btn btn-primary" style="margin-top:14px"><i class="fas fa-plus"></i> Ajukan Peminjaman</a>
  </div>
</div>
<?php else: ?>

<?php if(!$selected): ?>
<!-- List aktif -->
<div class="card">
  <div class="card-header"><h3>📦 Pilih Peminjaman yang Ingin Dikembalikan</h3></div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Kode</th><th>Alat</th><th>Tgl Pinjam</th><th>Tgl Kembali</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php while($p=$actives->fetch_assoc()):
          $overdue = strtotime($p['tanggal_kembali']) < time();
        ?>
        <tr>
          <td><strong><?=$p['kode_pinjam']?></strong></td>
          <td><?=htmlspecialchars(substr($p['alat_list']??'-',0,50))?></td>
          <td><?=date('d/m/Y',strtotime($p['tanggal_pinjam']))?></td>
          <td style="<?=$overdue?'color:var(--danger);font-weight:700':''?>"><?=date('d/m/Y',strtotime($p['tanggal_kembali']))?><?=$overdue?' ⚠️ Terlambat':''?></td>
          <td><a href="?id=<?=$p['id']?>" class="btn btn-success btn-sm"><i class="fas fa-undo"></i> Kembalikan</a></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php else: ?>
<!-- Konfirmasi pengembalian -->
<?php
$overdue = strtotime($selected['tanggal_kembali']) < time();
$hari_terlambat = $overdue ? floor((time()-strtotime($selected['tanggal_kembali']))/86400) : 0;
$total_per_hari = 0;
foreach($detail_items_list as $item) $total_per_hari += $item['harga_sewa']*$item['jumlah'];
$durasi = max(1,round((strtotime($selected['tanggal_kembali'])-strtotime($selected['tanggal_pinjam']))/86400));
?>
<div class="card">
  <div class="card-header">
    <h3><i class="fas fa-undo" style="color:var(--orange)"></i> Konfirmasi Pengembalian</h3>
    <a href="pengembalian.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
  </div>
  
  <?php if($overdue): ?>
  <div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> <strong>Terlambat <?=$hari_terlambat?> hari!</strong> Akan dikenakan denda oleh petugas.</div>
  <?php endif; ?>

  <div class="form-grid-2" style="margin-bottom:16px;">
    <div>
      <p style="margin-bottom:8px"><strong>Kode:</strong> <?=$selected['kode_pinjam']?></p>
      <p style="margin-bottom:8px"><strong>Tgl Pinjam:</strong> <?=date('d M Y',strtotime($selected['tanggal_pinjam']))?></p>
      <p><strong>Tgl Kembali (rencana):</strong> <?=date('d M Y',strtotime($selected['tanggal_kembali']))?></p>
    </div>
    <div>
      <p style="margin-bottom:8px"><strong>Durasi:</strong> <?=$durasi?> hari</p>
      <p style="margin-bottom:8px"><strong>Biaya/hari:</strong> <?=rupiah($total_per_hari)?></p>
      <p><strong>Estimasi Total:</strong> <span style="color:var(--orange);font-weight:700"><?=rupiah($total_per_hari*$durasi)?></span></p>
    </div>
  </div>

  <h4 style="font-family:'Playfair Display',serif;margin-bottom:10px">Alat yang Dipinjam:</h4>
  <table style="margin-bottom:20px">
    <thead><tr><th>Alat</th><th>Kode</th><th>Jumlah</th></tr></thead>
    <tbody>
      <?php foreach($detail_items_list as $item): ?>
      <tr>
        <td><?=htmlspecialchars($item['alat_nama'])?></td>
        <td><code><?=$item['kode']?></code></td>
        <td><?=$item['jumlah']?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <div style="background:var(--orange-light);border-radius:var(--radius-sm);padding:16px;margin-bottom:20px;">
    <p style="color:var(--brown-mid);font-size:0.88rem"><i class="fas fa-info-circle"></i> Dengan mengklik tombol di bawah, Anda mengkonfirmasi bahwa alat sudah siap untuk dikembalikan. Petugas akan mencatat kondisi dan denda (jika ada).</p>
  </div>

  <div style="display:flex;gap:12px;">
    <a href="pengembalian.php" class="btn btn-secondary"><i class="fas fa-times"></i> Batal</a>
    <form method="POST" action="../petugas/pengembalian.php" onsubmit="return confirm('Konfirmasi pengembalian alat ini?')">
      <input type="hidden" name="peminjaman_id" value="<?=$selected['id']?>">
      <input type="hidden" name="tanggal_kembali" value="<?=date('Y-m-d')?>">
      <input type="hidden" name="kondisi_alat" value="baik">
      <input type="hidden" name="denda" value="0">
      <input type="hidden" name="catatan" value="Dikembalikan oleh peminjam via portal">
      <button type="submit" class="btn btn-success"><i class="fas fa-undo"></i> Konfirmasi Kembalikan</button>
    </form>
  </div>
</div>
<?php endif; ?>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>