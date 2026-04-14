<?php
// admin/pengembalian.php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_role('admin');
$page_title = 'Data Pengembalian';

$pengembaliians = $conn->query("
  SELECT pg.*, u.nama peminjam_nama, p.kode_pinjam, u2.nama petugas_nama
  FROM pengembalian pg
  JOIN peminjaman p ON pg.peminjaman_id=p.id
  JOIN users u ON p.user_id=u.id
  LEFT JOIN users u2 ON pg.dicatat_oleh=u2.id
  ORDER BY pg.created_at DESC
");
include '../includes/header.php';
?>
<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>Kode Pinjam</th><th>Peminjam</th><th>Tgl Kembali</th><th>Kondisi</th><th>Denda</th><th>Petugas</th><th>Catatan</th></tr></thead>
      <tbody>
        <?php while($pg=$pengembaliians->fetch_assoc()): ?>
        <tr>
          <td><strong><?=$pg['kode_pinjam']?></strong></td>
          <td><?=htmlspecialchars($pg['peminjam_nama'])?></td>
          <td><?=date('d/m/Y',strtotime($pg['tanggal_kembali']))?></td>
          <td><span class="badge <?=$pg['kondisi_alat']=='baik'?'badge-success':($pg['kondisi_alat']=='rusak_ringan'?'badge-warning':'badge-danger')?>"><?=str_replace('_',' ',$pg['kondisi_alat'])?></span></td>
          <td><?=$pg['denda']>0?'<span class="badge badge-danger">'.rupiah($pg['denda']).'</span>':'<span class="badge badge-success">Tidak ada</span>'?></td>
          <td><?=htmlspecialchars($pg['petugas_nama']??'-')?></td>
          <td><?=htmlspecialchars($pg['catatan']??'-')?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include '../includes/footer.php'; ?>