<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_role('admin');
$page_title = 'Kelola Kategori';

$msg=$err='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $action=$_POST['action']??'';
    if ($action==='tambah') {
        $nama=$_POST['nama'];$desk=$_POST['deskripsi'];
        $s=$conn->prepare("INSERT INTO kategori(nama,deskripsi)VALUES(?,?)");
        $s->bind_param("ss",$nama,$desk);
        $s->execute()?($msg='Kategori ditambahkan.'):($err='Gagal.');
    } elseif ($action==='edit') {
        $id=(int)$_POST['id'];$nama=$_POST['nama'];$desk=$_POST['deskripsi'];
        $conn->query("UPDATE kategori SET nama='".addslashes($nama)."',deskripsi='".addslashes($desk)."' WHERE id=$id");
        $msg='Kategori diperbarui.';
    } elseif ($action==='hapus') {
        $id=(int)$_POST['id'];
        $cnt=$conn->query("SELECT COUNT(*) c FROM alat WHERE kategori_id=$id")->fetch_assoc()['c'];
        if($cnt>0){$err='Kategori masih memiliki alat, tidak bisa dihapus.';}
        else{$conn->query("DELETE FROM kategori WHERE id=$id");$msg='Kategori dihapus.';}
    }
}

$kats=$conn->query("SELECT k.*,COUNT(a.id) jml_alat FROM kategori k LEFT JOIN alat a ON k.id=a.kategori_id GROUP BY k.id ORDER BY k.nama");
$edit_kat=null;
if(isset($_GET['edit'])){$eid=(int)$_GET['edit'];$edit_kat=$conn->query("SELECT * FROM kategori WHERE id=$eid")->fetch_assoc();}

include '../includes/header.php';
?>
<?php if($msg): ?><div class="alert alert-success"><?=$msg?></div><?php endif; ?>
<?php if($err): ?><div class="alert alert-error"><?=$err?></div><?php endif; ?>

<div style="margin-bottom:20px;">
  <button class="btn btn-primary" onclick="openModal('modal-tambah')"><i class="fas fa-plus"></i> Tambah Kategori</button>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>Nama Kategori</th><th>Deskripsi</th><th>Jumlah Alat</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php $no=1; while($k=$kats->fetch_assoc()): ?>
        <tr>
          <td><?=$no++?></td>
          <td><strong><?=htmlspecialchars($k['nama'])?></strong></td>
          <td><?=htmlspecialchars($k['deskripsi']??'-')?></td>
          <td><span class="badge badge-orange"><?=$k['jml_alat']?> alat</span></td>
          <td style="display:flex;gap:6px;">
            <a href="?edit=<?=$k['id']?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
            <form method="POST" style="display:inline" onsubmit="return confirm('Hapus kategori ini?')">
              <input type="hidden" name="action" value="hapus"><input type="hidden" name="id" value="<?=$k['id']?>">
              <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
            </form>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal-overlay" id="modal-tambah">
  <div class="modal">
    <div class="modal-header"><h3>Tambah Kategori</h3><button class="modal-close" onclick="closeModal('modal-tambah')">✕</button></div>
    <form method="POST">
      <input type="hidden" name="action" value="tambah">
      <div class="form-group"><label>Nama Kategori *</label><input type="text" name="nama" required></div>
      <div class="form-group"><label>Deskripsi</label><textarea name="deskripsi" rows="2"></textarea></div>
      <div style="display:flex;gap:10px;justify-content:flex-end;">
        <button type="button" class="btn btn-secondary" onclick="closeModal('modal-tambah')">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<?php if($edit_kat): ?>
<div class="modal-overlay open">
  <div class="modal">
    <div class="modal-header"><h3>Edit Kategori</h3><a href="kategori.php" class="modal-close">✕</a></div>
    <form method="POST">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="id" value="<?=$edit_kat['id']?>">
      <div class="form-group"><label>Nama Kategori *</label><input type="text" name="nama" value="<?=htmlspecialchars($edit_kat['nama'])?>" required></div>
      <div class="form-group"><label>Deskripsi</label><textarea name="deskripsi" rows="2"><?=htmlspecialchars($edit_kat['deskripsi']??'')?></textarea></div>
      <div style="display:flex;gap:10px;justify-content:flex-end;">
        <a href="kategori.php" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>