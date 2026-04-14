<?php
// petugas/pengembalian.php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_role('petugas');
$page_title = 'Pantau Pengembalian';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $pid=(int)$_POST['peminjaman_id'];
    $tgl=$_POST['tanggal_kembali'];
    $kondisi=$_POST['kondisi_alat'];
    $denda=(float)$_POST['denda'];
    $catatan=$_POST['catatan'];
    $uid=$_SESSION['user_id'];
    
    $s=$conn->prepare("INSERT INTO pengembalian(peminjaman_id,tanggal_kembali,kondisi_alat,denda,catatan,dicatat_oleh)VALUES(?,?,?,?,?,?)");
    $s->bind_param("issdsi",$pid,$tgl,$kondisi,$denda,$catatan,$uid);
    if($s->execute()){
        // Return stok
        $items=$conn->query("SELECT * FROM detail_peminjaman WHERE peminjaman_id=$pid");
        while($item=$items->fetch_assoc()){
            $conn->query("UPDATE alat SET stok=stok+{$item['jumlah']} WHERE id={$item['alat_id']}");
        }
        $conn->query("UPDATE peminjaman SET status='selesai' WHERE id=$pid");
        log_activity($conn,$uid,'Catat Pengembalian',"PeminjamanID:$pid");
        $_SESSION['flash_success']='Pengembalian dicatat, status selesai.';
    }
    header('Location: pengembalian.php');
    exit;
}

// Active loans
$actives=$conn->query("
  SELECT p.*,u.nama user_nama FROM peminjaman p JOIN users u ON p.user_id=u.id
  WHERE p.status='dipinjam' ORDER BY p.tanggal_kembali ASC
");

include '../includes/header.php';
?>
<div class="card">
  <div class="card-header"><h3>📦 Peminjaman Aktif (Belum Dikembalikan)</h3></div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Kode</th><th>Peminjam</th><th>Tgl Pinjam</th><th>Tgl Kembali</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php $cnt=0; while($p=$actives->fetch_assoc()): $cnt++;
          $overdue = strtotime($p['tanggal_kembali']) < time();
        ?>
        <tr>
          <td><strong><?=$p['kode_pinjam']?></strong></td>
          <td><?=htmlspecialchars($p['user_nama'])?></td>
          <td><?=date('d/m/Y',strtotime($p['tanggal_pinjam']))?></td>
          <td style="<?=$overdue?'color:var(--danger);font-weight:700':''?>"><?=date('d/m/Y',strtotime($p['tanggal_kembali']))?> <?=$overdue?'⚠️':''?></td>
          <td><span class="badge badge-orange">Dipinjam</span></td>
          <td>
            <button onclick="openModal('modal-kembalikan-<?=$p['id']?>')" class="btn btn-success btn-sm"><i class="fas fa-undo"></i> Catat Kembali</button>
            
            <div class="modal-overlay" id="modal-kembalikan-<?=$p['id']?>">
              <div class="modal">
                <div class="modal-header"><h3>Catat Pengembalian</h3><button class="modal-close" onclick="closeModal('modal-kembalikan-<?=$p['id']?>')">✕</button></div>
                <p style="margin-bottom:14px">Kode: <strong><?=$p['kode_pinjam']?></strong> | Peminjam: <strong><?=htmlspecialchars($p['user_nama'])?></strong></p>
                <form method="POST">
                  <input type="hidden" name="peminjaman_id" value="<?=$p['id']?>">
                  <div class="form-grid-2">
                    <div class="form-group"><label>Tgl Kembali *</label><input type="date" name="tanggal_kembali" value="<?=date('Y-m-d')?>" required></div>
                    <div class="form-group"><label>Kondisi Alat</label><select name="kondisi_alat"><option value="baik">Baik</option><option value="rusak_ringan">Rusak Ringan</option><option value="rusak_berat">Rusak Berat</option></select></div>
                  </div>
                  <div class="form-group"><label>Denda (Rp)</label><input type="number" name="denda" value="0" min="0"></div>
                  <div class="form-group"><label>Catatan</label><textarea name="catatan" rows="2"></textarea></div>
                  <div style="display:flex;gap:10px;justify-content:flex-end;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modal-kembalikan-<?=$p['id']?>')">Batal</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Simpan</button>
                  </div>
                </form>
              </div>
            </div>
          </td>
        </tr>
        <?php endwhile; ?>
        <?php if($cnt==0): ?><tr><td colspan="6"><div class="empty-state"><i class="fas fa-check-circle"></i><p>Semua alat sudah dikembalikan</p></div></td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include '../includes/footer.php'; ?>