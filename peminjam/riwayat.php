<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_role('peminjam');
$page_title = 'Riwayat Peminjaman';

$uid = $_SESSION['user_id'];
$status_filter = $_GET['status'] ?? '';
$where = "WHERE p.user_id=$uid";
if ($status_filter) $where .= " AND p.status='$status_filter'";

$peminjamans = $conn->query("
    SELECT p.*,
    (SELECT GROUP_CONCAT(CONCAT(a.nama,' x',dp.jumlah) SEPARATOR ', ') FROM detail_peminjaman dp JOIN alat a ON dp.alat_id=a.id WHERE dp.peminjaman_id=p.id) alat_list
    FROM peminjaman p $where ORDER BY p.created_at DESC
");

$status_labels = ['menunggu'=>'Menunggu','disetujui'=>'Disetujui','ditolak'=>'Ditolak','dipinjam'=>'Dipinjam','selesai'=>'Selesai'];
$status_badges = ['menunggu'=>'badge-warning','disetujui'=>'badge-info','ditolak'=>'badge-danger','dipinjam'=>'badge-orange','selesai'=>'badge-success'];

include '../includes/header.php';
?>

<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px;">
  <a href="riwayat.php" class="btn <?=!$status_filter?'btn-primary':'btn-secondary'?> btn-sm">Semua</a>
  <?php foreach($status_labels as $v=>$l): ?>
  <a href="?status=<?=$v?>" class="btn <?=$status_filter===$v?'btn-primary':'btn-secondary'?> btn-sm"><?=$l?></a>
  <?php endforeach; ?>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>Kode</th><th>Alat Dipinjam</th><th>Tgl Pinjam</th><th>Tgl Kembali</th><th>Status</th><th>Detail</th></tr>
      </thead>
      <tbody>
        <?php $cnt=0; while($p=$peminjamans->fetch_assoc()): $cnt++;
          $overdue = in_array($p['status'],['dipinjam','disetujui']) && strtotime($p['tanggal_kembali']) < time();
        ?>
        <tr>
          <td><strong><?=$p['kode_pinjam']?></strong></td>
          <td style="max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?=htmlspecialchars($p['alat_list']??'')?>">
            <?=htmlspecialchars(substr($p['alat_list']??'-',0,50))?>
          </td>
          <td><?=date('d/m/Y',strtotime($p['tanggal_pinjam']))?></td>
          <td style="<?=$overdue?'color:var(--danger);font-weight:700':''?>">
            <?=date('d/m/Y',strtotime($p['tanggal_kembali']))?>
            <?=$overdue?' ⚠️':''?>
          </td>
          <td><span class="badge <?=$status_badges[$p['status']]?>"><?=$status_labels[$p['status']]?></span></td>
          <td>
            <button onclick="openModal('modal-detail-<?=$p['id']?>')" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></button>

            <!-- Modal Detail -->
            <?php
            $detail_items = $conn->query("SELECT dp.*,a.nama alat_nama,a.kode,a.harga_sewa FROM detail_peminjaman dp JOIN alat a ON dp.alat_id=a.id WHERE dp.peminjaman_id={$p['id']}");
            $items = [];
            while($di=$detail_items->fetch_assoc()) $items[]=$di;
            ?>
            <div class="modal-overlay" id="modal-detail-<?=$p['id']?>">
              <div class="modal">
                <div class="modal-header">
                  <h3>📋 <?=$p['kode_pinjam']?></h3>
                  <button class="modal-close" onclick="closeModal('modal-detail-<?=$p['id']?>')">✕</button>
                </div>
                <div style="margin-bottom:14px;font-size:0.88rem;">
                  <p><strong>Status:</strong> <span class="badge <?=$status_badges[$p['status']]?>"><?=$status_labels[$p['status']]?></span></p>
                  <p style="margin-top:6px"><strong>Tgl Pinjam:</strong> <?=date('d M Y',strtotime($p['tanggal_pinjam']))?></p>
                  <p style="margin-top:4px"><strong>Tgl Kembali:</strong> <?=date('d M Y',strtotime($p['tanggal_kembali']))?></p>
                  <?php if($p['catatan']): ?><p style="margin-top:4px"><strong>Catatan:</strong> <?=htmlspecialchars($p['catatan'])?></p><?php endif; ?>
                </div>
                <table>
                  <thead><tr><th>Alat</th><th>Kode</th><th>Jml</th><th>Harga/hari</th></tr></thead>
                  <tbody>
                    <?php $total_per_hari=0; foreach($items as $item): $total_per_hari+=$item['harga_sewa']*$item['jumlah']; ?>
                    <tr>
                      <td><?=htmlspecialchars($item['alat_nama'])?></td>
                      <td><code><?=$item['kode']?></code></td>
                      <td><?=$item['jumlah']?></td>
                      <td><?=rupiah($item['harga_sewa'])?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php
                    $hari = max(1, round((strtotime($p['tanggal_kembali'])-strtotime($p['tanggal_pinjam']))/86400));
                    ?>
                    <tr style="background:var(--orange-light);font-weight:700">
                      <td colspan="3" style="text-align:right">Estimasi (<?=$hari?> hari):</td>
                      <td><?=rupiah($total_per_hari*$hari)?></td>
                    </tr>
                  </tbody>
                </table>
                <?php if($p['status']==='dipinjam'): ?>
                <div style="margin-top:14px;">
                  <a href="pengembalian.php?id=<?=$p['id']?>" class="btn btn-success btn-sm"><i class="fas fa-undo"></i> Ajukan Pengembalian</a>
                </div>
                <?php endif; ?>
              </div>
            </div>
          </td>
        </tr>
        <?php endwhile; ?>
        <?php if($cnt===0): ?>
        <tr><td colspan="6"><div class="empty-state"><i class="fas fa-clipboard"></i><p>Tidak ada data peminjaman. <a href="pinjam.php">Buat pengajuan baru</a></p></div></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include '../includes/footer.php'; ?>