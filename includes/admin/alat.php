<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_role('admin');
$page_title = 'Kelola Alat Masak';

$msg=$err='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $action=$_POST['action']??'';
    if ($action==='tambah') {
        $kat=$_POST['kategori_id'];$nama=$_POST['nama'];$kode=$_POST['kode'];
        $desk=$_POST['deskripsi'];$stok=$_POST['stok'];$harga=$_POST['harga_sewa'];$kondisi=$_POST['kondisi'];
        $s=$conn->prepare("INSERT INTO alat(kategori_id,nama,kode,deskripsi,stok,harga_sewa,kondisi)VALUES(?,?,?,?,?,?,?)");
        $s->bind_param("isssdss",$kat,$nama,$kode,$desk,$stok,$harga,$kondisi);
        $s->execute()?($msg='Alat ditambahkan.'):($err='Kode sudah digunakan.');
    } elseif ($action==='edit') {
        $id=$_POST['id'];$kat=$_POST['kategori_id'];$nama=$_POST['nama'];$kode=$_POST['kode'];
        $desk=$_POST['deskripsi'];$stok=$_POST['stok'];$harga=$_POST['harga_sewa'];$kondisi=$_POST['kondisi'];
        $s=$conn->prepare("UPDATE alat SET kategori_id=?,nama=?,kode=?,deskripsi=?,stok=?,harga_sewa=?,kondisi=? WHERE id=?");
        $s->bind_param("isssdssі",$kat,$nama,$kode,$desk,$stok,$harga,$kondisi,$id);
        $conn->query("UPDATE alat SET kategori_id=$kat,nama='".addslashes($nama)."',kode='".addslashes($kode)."',deskripsi='".addslashes($desk)."',stok=$stok,harga_sewa=$harga,kondisi='$kondisi' WHERE id=$id");
        $msg='Alat diperbarui.';
    } elseif ($action==='hapus') {
        $id=(int)$_POST['id'];
        $conn->query("DELETE FROM alat WHERE id=$id");
        $msg='Alat dihapus.';
    }
}

$search=$_GET['q']??'';$kat_filter=$_GET['kat']??'';
$where="WHERE 1=1";
if($search) $where.=" AND (a.nama LIKE '%$search%' OR a.kode LIKE '%$search%')";
if($kat_filter) $where.=" AND a.kategori_id=$kat_filter";
$alats=$conn->query("SELECT a.*,k.nama kat_nama FROM alat a LEFT JOIN kategori k ON a.kategori_id=k.id $where ORDER BY a.nama");
$kats=$conn->query("SELECT * FROM kategori ORDER BY nama");
$kats_arr=[];$r=$conn->query("SELECT * FROM kategori ORDER BY nama");
while($row=$r->fetch_assoc()) $kats_arr[]=$row;

$edit_alat=null;
if(isset($_GET['edit'])){$eid=(int)$_GET['edit'];$edit_alat=$conn->query("SELECT * FROM alat WHERE id=$eid")->fetch_assoc();}

include '../includes/header.php';
?>
<?php if($msg): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i><?=$msg?></div><?php endif; ?>
<?php if($err): ?><div class="alert alert-error"><?=$err?></div><?php endif; ?>

<div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:20px;">
  <form method="GET" style="display:flex;gap:8px;flex:1;min-width:200px;flex-wrap:wrap;">
    <input type="text" name="q" placeholder="Cari nama/kode..." value="<?=htmlspecialchars($search)?>" style="max-width:220px;">
    <select name="kat" style="max-width:180px;">
      <option value="">Semua Kategori</option>
      <?php foreach($kats_arr as $k): ?>
      <option value="<?=$k['id']?>" <?=$kat_filter==$k['id']?'selected':''?>><?=htmlspecialchars($k['nama'])?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-search"></i> Filter</button>
  </form>
  <button class="btn btn-primary" onclick="openModal('modal-tambah')"><i class="fas fa-plus"></i> Tambah Alat</button>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>Kode</th><th>Nama Alat</th><th>Kategori</th><th>Stok</th><th>Harga/Hari</th><th>Kondisi</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php $no=1; while($a=$alats->fetch_assoc()): ?>
        <tr>
          <td><?=$no++?></td>
          <td><code><?=$a['kode']?></code></td>
          <td><strong><?=htmlspecialchars($a['nama'])?></strong><br><small style="color:var(--gray)"><?=htmlspecialchars(substr($a['deskripsi']??'',0,50))?></small></td>
          <td><?=htmlspecialchars($a['kat_nama']??'-')?></td>
          <td><?=$a['stok']?></td>
          <td><?=rupiah($a['harga_sewa'])?></td>
          <td><span class="badge <?=$a['kondisi']=='baik'?'badge-success':($a['kondisi']=='rusak_ringan'?'badge-warning':'badge-danger')?>"><?=str_replace('_',' ',$a['kondisi'])?></span></td>
          <td style="display:flex;gap:5px;">
            <a href="?edit=<?=$a['id']?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
            <form method="POST" style="display:inline" onsubmit="return confirm('Hapus alat ini?')">
              <input type="hidden" name="action" value="hapus"><input type="hidden" name="id" value="<?=$a['id']?>">
              <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
            </form>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Tambah -->
<div class="modal-overlay" id="modal-tambah">
  <div class="modal">
    <div class="modal-header"><h3>Tambah Alat Masak</h3><button class="modal-close" onclick="closeModal('modal-tambah')">✕</button></div>
    <form method="POST">
      <input type="hidden" name="action" value="tambah">
      <div class="form-grid-2">
        <div class="form-group"><label>Nama Alat *</label><input type="text" name="nama" required></div>
        <div class="form-group"><label>Kode *</label><input type="text" name="kode" required></div>
      </div>
      <div class="form-grid-2">
        <div class="form-group"><label>Kategori</label><select name="kategori_id"><option value="">-- Pilih --</option><?php foreach($kats_arr as $k): ?><option value="<?=$k['id']?>"><?=htmlspecialchars($k['nama'])?></option><?php endforeach; ?></select></div>
        <div class="form-group"><label>Kondisi</label><select name="kondisi"><option value="baik">Baik</option><option value="rusak_ringan">Rusak Ringan</option><option value="rusak_berat">Rusak Berat</option></select></div>
      </div>
      <div class="form-grid-2">
        <div class="form-group"><label>Stok</label><input type="number" name="stok" value="1" min="0"></div>
        <div class="form-group"><label>Harga Sewa/Hari (Rp)</label><input type="number" name="harga_sewa" value="0" min="0"></div>
      </div>
      <div class="form-group"><label>Deskripsi</label><textarea name="deskripsi" rows="2"></textarea></div>
      <div style="display:flex;gap:10px;justify-content:flex-end;">
        <button type="button" class="btn btn-secondary" onclick="closeModal('modal-tambah')">Batal</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Edit -->
<?php if($edit_alat): ?>
<div class="modal-overlay open" id="modal-edit">
  <div class="modal">
    <div class="modal-header"><h3>Edit Alat</h3><a href="alat.php" class="modal-close">✕</a></div>
    <form method="POST">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="id" value="<?=$edit_alat['id']?>">
      <div class="form-grid-2">
        <div class="form-group"><label>Nama Alat *</label><input type="text" name="nama" value="<?=htmlspecialchars($edit_alat['nama'])?>" required></div>
        <div class="form-group"><label>Kode *</label><input type="text" name="kode" value="<?=htmlspecialchars($edit_alat['kode'])?>" required></div>
      </div>
      <div class="form-grid-2">
        <div class="form-group"><label>Kategori</label><select name="kategori_id"><?php foreach($kats_arr as $k): ?><option value="<?=$k['id']?>" <?=$edit_alat['kategori_id']==$k['id']?'selected':''?>><?=htmlspecialchars($k['nama'])?></option><?php endforeach; ?></select></div>
        <div class="form-group"><label>Kondisi</label><select name="kondisi"><?php foreach(['baik','rusak_ringan','rusak_berat'] as $k): ?><option value="<?=$k?>" <?=$edit_alat['kondisi']==$k?'selected':''?>><?=str_replace('_',' ',$k)?></option><?php endforeach; ?></select></div>
      </div>
      <div class="form-grid-2">
        <div class="form-group"><label>Stok</label><input type="number" name="stok" value="<?=$edit_alat['stok']?>" min="0"></div>
        <div class="form-group"><label>Harga Sewa/Hari (Rp)</label><input type="number" name="harga_sewa" value="<?=$edit_alat['harga_sewa']?>" min="0"></div>
      </div>
      <div class="form-group"><label>Deskripsi</label><textarea name="deskripsi" rows="2"><?=htmlspecialchars($edit_alat['deskripsi']??'')?></textarea></div>
      <div style="display:flex;gap:10px;justify-content:flex-end;">
        <a href="alat.php" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>