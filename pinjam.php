<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_role('admin');
$page_title = 'Data Peminjaman';

$status_filter = $_GET['status'] ?? '';
$where = $status_filter ? "WHERE p.status='$status_filter'" : '';
$peminjamans = $conn->query("
  SELECT p.*, u.nama user_nama,
  (SELECT GROUP_CONCAT(a.nama SEPARATOR ', ') FROM detail_peminjaman dp JOIN alat a ON dp.alat_id=a.id WHERE dp.peminjaman_id=p.id) alat_list
  FROM peminjaman p JOIN users u ON p.user_id=u.id
  $where ORDER BY p.created_at DESC
");

// Detail view
$detail = null;
if(isset($_GET['id'])){
    $did=(int)$_GET['id'];
    $detail=$conn->query("SELECT p.*,u.nama user_nama,u.telepon,u.alamat,u2.nama approved_by FROM peminjaman p JOIN users u ON p.user_id=u.id LEFT JOIN users u2 ON p.disetujui_oleh=u2.id WHERE p.id=$did")->fetch_assoc();
    $detail_items=$conn->query("SELECT dp.*,a.nama alat_nama,a.kode,a.harga_sewa FROM detail_peminjaman dp JOIN alat a ON dp.alat_id=a.id WHERE dp.peminjaman_id=$did");
}

include '../includes/header.php';

$status_labels=['menunggu'=>'Menunggu','disetujui'=>'Disetujui','ditolak'=>'Ditolak','dipinjam'=>'Dipinjam','selesai'=>'Selesai'];
$status_badges=['menunggu'=>'badge-warning','disetujui'=>'badge-info','ditolak'=>'badge-danger','dipinjam'=>'badge-orange','selesai'=>'badge-success'];
?>

<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px;">
  <?php foreach([''=>'Semua']+$status_labels as $v=>$l): ?>
  <a href="peminjaman.php<?=$v?"?status=$v":''?>" class="btn <?=$status_filter===$v?'btn-primary':'btn-secondary'?> btn-sm"><?=$l?></a>
  <?php endforeach; ?>
</div>

<?php if($detail): ?>
<!-- Detail View -->
<div class="card">
  <div class="card-header">
    <h3><i class="fas fa-file-alt" style="color:var(--orange)"></i> Detail Peminjaman: <?=$detail['kode_pinjam']?></h3>
    <a href="peminjaman.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
  </div>
  <div class="form-grid-2">
    <div>
      <p style="margin-bottom:8px"><strong>Peminjam:</strong> <?=htmlspecialchars($detail['user_nama'])?></p>
      <p style="margin-bottom:8px"><strong>Telepon:</strong> <?=htmlspecialchars($detail['telepon']??'-')?></p>
      <p style="margin-bottom:8px"><strong>Alamat:</strong> <?=htmlspecialchars($detail['alamat']??'-')?></p>
    </div>
    <div>
      <p style="margin-bottom:8px"><strong>Tgl Pinjam:</strong> <?=date('d M Y',strtotime($detail['tanggal_pinjam']))?></p>
      <p style="margin-bottom:8px"><strong>Tgl Kembali:</strong> <?=date('d M Y',strtotime($detail['tanggal_kembali']))?></p>
      <p style="margin-bottom:8px"><strong>Status:</strong> <span class="badge <?=$status_badges[$detail['status']]?>"><?=$status_labels[$detail['status']]?></span></p>
      <?php if($detail['approved_by']): ?><p style="margin-bottom:8px"><strong>Disetujui oleh:</strong> <?=htmlspecialchars($detail['approved_by'])?></p><?php endif; ?>
    </div>
  </div>
  <?php if($detail['catatan']): ?><p style="margin:10px 0"><strong>Catatan:</strong> <?=htmlspecialchars($detail['catatan'])?></p><?php endif; ?>
  
  <h4 style="margin:16px 0 10px;font-family:'Playfair Display',serif;">Daftar Alat</h4>
  <table>
    <thead><tr><th>Alat</th><th>Kode</th><th>Jumlah</th><th>Harga/Hari</th></tr></thead>
    <tbody>
      <?php $total=0; while($item=$detail_items->fetch_assoc()):
        $sub=$item['harga_sewa']*$item['jumlah'];$total+=$sub;
      ?>
      <tr>
        <td><?=htmlspecialchars($item['alat_nama'])?></td>
        <td><code><?=$item['kode']?></code></td>
        <td><?=$item['jumlah']?></td>
        <td><?=rupiah($item['harga_sewa'])?></td>
      </tr>
      <?php endwhile; ?>
      <tr style="font-weight:700;background:var(--orange-light)">
        <td colspan="3" style="text-align:right">Total/hari:</td>
        <td><?=rupiah($total)?></td>
      </tr>
    </tbody>
  </table>
</div>

<?php else: ?>
<!-- List View -->
<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>Kode</th><th>Peminjam</th><th>Alat</th><th>Tgl Pinjam</th><th>Tgl Kembali</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php while($p=$peminjamans->fetch_assoc()): ?>
        <tr>
          <td><strong><?=$p['kode_pinjam']?></strong></td>
          <td><?=htmlspecialchars($p['user_nama'])?></td>
          <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?=htmlspecialchars($p['alat_list']??'')?>"><?=htmlspecialchars(substr($p['alat_list']??'-',0,40))?></td>
          <td><?=date('d/m/Y',strtotime($p['tanggal_pinjam']))?></td>
          <td><?=date('d/m/Y',strtotime($p['tanggal_kembali']))?></td>
          <td><span class="badge <?=$status_badges[$p['status']]?>"><?=$status_labels[$p['status']]?></span></td>
          <td><a href="?id=<?=$p['id']?>" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>