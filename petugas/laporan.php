<?php
// petugas/laporan.php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_role('petugas');
$page_title = 'Cetak Laporan';

$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');
$jenis = $_GET['jenis'] ?? 'peminjaman';

$data = [];
if ($jenis === 'peminjaman') {
    $result = $conn->query("
        SELECT p.*, u.nama user_nama, u.telepon,
        (SELECT GROUP_CONCAT(a.nama SEPARATOR ', ') FROM detail_peminjaman dp JOIN alat a ON dp.alat_id=a.id WHERE dp.peminjaman_id=p.id) alat_list
        FROM peminjaman p JOIN users u ON p.user_id=u.id
        WHERE MONTH(p.created_at)=$bulan AND YEAR(p.created_at)=$tahun
        ORDER BY p.created_at DESC
    ");
} else {
    $result = $conn->query("
        SELECT pg.*, p.kode_pinjam, u.nama user_nama
        FROM pengembalian pg
        JOIN peminjaman p ON pg.peminjaman_id=p.id
        JOIN users u ON p.user_id=u.id
        WHERE MONTH(pg.created_at)=$bulan AND YEAR(pg.created_at)=$tahun
        ORDER BY pg.created_at DESC
    ");
}
while ($row = $result->fetch_assoc()) $data[] = $row;

include '../includes/header.php';
?>

<div class="card">
  <div class="card-header">
    <h3><i class="fas fa-filter" style="color:var(--orange)"></i> Filter Laporan</h3>
  </div>
  <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
    <div class="form-group" style="margin:0">
      <label>Jenis Laporan</label>
      <select name="jenis">
        <option value="peminjaman" <?=$jenis=='peminjaman'?'selected':''?>>Peminjaman</option>
        <option value="pengembalian" <?=$jenis=='pengembalian'?'selected':''?>>Pengembalian</option>
      </select>
    </div>
    <div class="form-group" style="margin:0">
      <label>Bulan</label>
      <select name="bulan">
        <?php for($i=1;$i<=12;$i++): ?>
        <option value="<?=str_pad($i,2,'0',STR_PAD_LEFT)?>" <?=$bulan==str_pad($i,2,'0',STR_PAD_LEFT)?'selected':''?>>
          <?=date('F',mktime(0,0,0,$i,1))?>
        </option>
        <?php endfor; ?>
      </select>
    </div>
    <div class="form-group" style="margin:0">
      <label>Tahun</label>
      <select name="tahun">
        <?php for($y=date('Y');$y>=date('Y')-3;$y--): ?>
        <option value="<?=$y?>" <?=$tahun==$y?'selected':''?>><?=$y?></option>
        <?php endfor; ?>
      </select>
    </div>
    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Tampilkan</button>
    <button type="button" onclick="window.print()" class="btn btn-success"><i class="fas fa-print"></i> Cetak</button>
  </form>
</div>

<div class="card" id="print-area">
  <div style="text-align:center;margin-bottom:20px;padding-bottom:14px;border-bottom:2px solid var(--orange)" class="print-header">
    <div style="font-size:2rem">🍳</div>
    <h2 style="font-family:'Playfair Display',serif;color:var(--brown);font-size:1.5rem">Laporan <?=ucfirst($jenis)?></h2>
    <p style="color:var(--gray)">Bulan <?=date('F Y',mktime(0,0,0,$bulan,1,$tahun))?> | Dicetak: <?=date('d M Y H:i')?></p>
  </div>

  <?php if($jenis==='peminjaman'): ?>
  <table>
    <thead>
      <tr><th>No</th><th>Kode</th><th>Peminjam</th><th>Alat</th><th>Tgl Pinjam</th><th>Tgl Kembali</th><th>Status</th></tr>
    </thead>
    <tbody>
      <?php $no=1; $status_labels=['menunggu'=>'Menunggu','disetujui'=>'Disetujui','ditolak'=>'Ditolak','dipinjam'=>'Dipinjam','selesai'=>'Selesai'];
      $status_badges=['menunggu'=>'badge-warning','disetujui'=>'badge-info','ditolak'=>'badge-danger','dipinjam'=>'badge-orange','selesai'=>'badge-success'];
      foreach($data as $d): ?>
      <tr>
        <td><?=$no++?></td>
        <td><strong><?=$d['kode_pinjam']?></strong></td>
        <td><?=htmlspecialchars($d['user_nama'])?></td>
        <td style="font-size:0.82rem"><?=htmlspecialchars(substr($d['alat_list']??'-',0,50))?></td>
        <td><?=date('d/m/Y',strtotime($d['tanggal_pinjam']))?></td>
        <td><?=date('d/m/Y',strtotime($d['tanggal_kembali']))?></td>
        <td><span class="badge <?=$status_badges[$d['status']]?>"><?=$status_labels[$d['status']]?></span></td>
      </tr>
      <?php endforeach; ?>
      <?php if(empty($data)): ?><tr><td colspan="7" style="text-align:center;padding:30px;color:var(--gray)">Tidak ada data</td></tr><?php endif; ?>
    </tbody>
  </table>
  <?php else: ?>
  <table>
    <thead>
      <tr><th>No</th><th>Kode Pinjam</th><th>Peminjam</th><th>Tgl Kembali</th><th>Kondisi</th><th>Denda</th></tr>
    </thead>
    <tbody>
      <?php $no=1; $total_denda=0; foreach($data as $d): $total_denda+=$d['denda']; ?>
      <tr>
        <td><?=$no++?></td>
        <td><strong><?=$d['kode_pinjam']?></strong></td>
        <td><?=htmlspecialchars($d['user_nama'])?></td>
        <td><?=date('d/m/Y',strtotime($d['tanggal_kembali']))?></td>
        <td><span class="badge <?=$d['kondisi_alat']=='baik'?'badge-success':($d['kondisi_alat']=='rusak_ringan'?'badge-warning':'badge-danger')?>"><?=str_replace('_',' ',$d['kondisi_alat'])?></span></td>
        <td><?=$d['denda']>0?rupiah($d['denda']):'–'?></td>
      </tr>
      <?php endforeach; ?>
      <?php if(!empty($data)): ?>
      <tr style="font-weight:700;background:var(--orange-light)">
        <td colspan="5" style="text-align:right">Total Denda:</td>
        <td><?=rupiah($total_denda)?></td>
      </tr>
      <?php endif; ?>
      <?php if(empty($data)): ?><tr><td colspan="6" style="text-align:center;padding:30px;color:var(--gray)">Tidak ada data</td></tr><?php endif; ?>
    </tbody>
  </table>
  <?php endif; ?>

  <div style="margin-top:20px;font-size:0.85rem;color:var(--gray)">
    Total: <strong><?=count($data)?></strong> data ditemukan
  </div>
</div>

<style>
@media print {
  .sidebar, .topbar, form, .card:first-child { display: none !important; }
  .main-wrapper { margin-left: 0 !important; }
  .main-content { padding: 0 !important; }
  #print-area { box-shadow: none !important; border: none; }
  .print-header { display: block !important; }
}
</style>

<?php include '../includes/footer.php'; ?>