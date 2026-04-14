<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_role('petugas');
$page_title = 'Setujui Peminjaman';

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $id=(int)$_POST['id'];
    $action=$_POST['action'];
    $uid=$_SESSION['user_id'];
    
    if ($action==='setujui') {
        $conn->query("UPDATE peminjaman SET status='disetujui',disetujui_oleh=$uid WHERE id=$id AND status='menunggu'");
        log_activity($conn,$uid,'Setujui Peminjaman',"ID:$id");
        $_SESSION['flash_success']='Peminjaman disetujui.';
    } elseif ($action==='tolak') {
        $conn->query("UPDATE peminjaman SET status='ditolak',disetujui_oleh=$uid WHERE id=$id AND status='menunggu'");
        log_activity($conn,$uid,'Tolak Peminjaman',"ID:$id");
        $_SESSION['flash_success']='Peminjaman ditolak.';
    } elseif ($action==='mulai') {
        // Mark as dipinjam - reduce stok
        $items=$conn->query("SELECT * FROM detail_peminjaman WHERE peminjaman_id=$id");
        while($item=$items->fetch_assoc()){
            $conn->query("UPDATE alat SET stok=stok-{$item['jumlah']} WHERE id={$item['alat_id']} AND stok>={$item['jumlah']}");
        }
        $conn->query("UPDATE peminjaman SET status='dipinjam' WHERE id=$id AND status='disetujui'");
        log_activity($conn,$uid,'Mulai Peminjaman',"ID:$id");
        $_SESSION['flash_success']='Status diubah ke Dipinjam.';
    }
    header('Location: peminjaman.php');
    exit;
}

$status_filter = $_GET['status'] ?? 'menunggu';
$peminjamans = $conn->query("
  SELECT p.*,u.nama user_nama FROM peminjaman p JOIN users u ON p.user_id=u.id
  WHERE p.status='$status_filter' ORDER BY p.created_at DESC
");

$detail=null;
if(isset($_GET['id'])){
    $did=(int)$_GET['id'];
    $detail=$conn->query("SELECT p.*,u.nama user_nama,u.telepon FROM peminjaman p JOIN users u ON p.user_id=u.id WHERE p.id=$did")->fetch_assoc();
    $detail_items=$conn->query("SELECT dp.*,a.nama alat_nama,a.kode,a.harga_sewa,a.stok FROM detail_peminjaman dp JOIN alat a ON dp.alat_id=a.id WHERE dp.peminjaman_id=$did");
}

include '../includes/header.php';
$status_labels=['menunggu'=>'Menunggu','disetujui'=>'Disetujui','ditolak'=>'Ditolak','dipinjam'=>'Dipinjam','selesai'=>'Selesai'];
$status_badges=['menunggu'=>'badge-warning','disetujui'=>'badge-info','ditolak'=>'badge-danger','dipinjam'=>'badge-orange','selesai'=>'badge-success'];
?>

<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px;">
  <?php foreach($status_labels as $v=>$l): ?>
  <a href="peminjaman.php?status=<?=$v?>" class="btn <?=$status_filter===$v?'btn-primary':'btn-secondary'?> btn-sm"><?=$l?></a>
  <?php endforeach; ?>
</div>

<?php if($detail): ?>
<div class="card">
  <div class="card-header">
    <h3>Detail: <?=$detail['kode_pinjam']?></h3>
    <a href="peminjaman.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
  </div>
  <div class="form-grid-2" style="margin-bottom:16px;">
    <div>
      <p style="margin-bottom:8px"><strong>Peminjam:</strong> <?=htmlspecialchars($detail['user_nama'])?></p>
      <p style="margin-bottom:8px"><strong>Telepon:</strong> <?=htmlspecialchars($detail['telepon']??'-')?></p>
    </div>
    <div>
      <p style="margin-bottom:8px"><strong>Tgl Pinjam:</strong> <?=date('d M Y',strtotime($detail['tanggal_pinjam']))?></p>
      <p style="margin-bottom:8px"><strong>Tgl Kembali:</strong> <?=date('d M Y',strtotime($detail['tanggal_kembali']))?></p>
      <p><strong>Status:</strong> <span class="badge <?=$status_badges[$detail['status']]?>"><?=$status_labels[$detail['status']]?></span></p>
    </div>
  </div>
  
  <table>
    <thead><tr><th>Alat</th><th>Kode</th><th>Stok Tersedia</th><th>Jumlah Diminta</th></tr></thead>
    <tbody>
      <?php while($item=$detail_items->fetch_assoc()): ?>
      <tr>
        <td><?=htmlspecialchars($item['alat_nama'])?></td>
        <td><code><?=$item['kode']?></code></td>
        <td><?=$item['stok']?></td>
        <td><?=$item['jumlah']?></td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
  
  <?php if($detail['status']==='menunggu'): ?>
  <div style="display:flex;gap:10px;margin-top:20px;">
    <form method="POST">
      <input type="hidden" name="id" value="<?=$detail['id']?>">
      <input type="hidden" name="action" value="setujui">
      <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Setujui</button>
    </form>
    <form method="POST">
      <input type="hidden" name="id" value="<?=$detail['id']?>">
      <input type="hidden" name="action" value="tolak">
      <button type="submit" class="btn btn-danger" onclick="return confirm('Tolak peminjaman ini?')"><i class="fas fa-times"></i> Tolak</button>
    </form>
  </div>
  <?php elseif($detail['status']==='disetujui'): ?>
  <form method="POST" style="margin-top:16px;">
    <input type="hidden" name="id" value="<?=$detail['id']?>">
    <input type="hidden" name="action" value="mulai">
    <button type="submit" class="btn btn-primary"><i class="fas fa-play"></i> Tandai Dipinjam</button>
  </form>
  <?php endif; ?>
</div>

<?php else: ?>
<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>Kode</th><th>Peminjam</th><th>Tgl Pinjam</th><th>Tgl Kembali</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php $cnt=0; while($p=$peminjamans->fetch_assoc()): $cnt++; ?>
        <tr>
          <td><strong><?=$p['kode_pinjam']?></strong></td>
          <td><?=htmlspecialchars($p['user_nama'])?></td>
          <td><?=date('d/m/Y',strtotime($p['tanggal_pinjam']))?></td>
          <td><?=date('d/m/Y',strtotime($p['tanggal_kembali']))?></td>
          <td><a href="?status=<?=$status_filter?>&id=<?=$p['id']?>" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> Detail</a></td>
        </tr>
        <?php endwhile; ?>
        <?php if($cnt==0): ?><tr><td colspan="5"><div class="empty-state"><i class="fas fa-inbox"></i><p>Tidak ada data</p></div></td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>