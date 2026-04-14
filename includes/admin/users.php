<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_role('admin');
$page_title = 'Kelola User';

// Handle actions
$msg = $err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'tambah') {
        $nama=$_POST['nama']; $email=$_POST['email']; $role=$_POST['role'];
        $pass=password_hash($_POST['password'], PASSWORD_DEFAULT);
        $tel=$_POST['telepon']; $alamat=$_POST['alamat'];
        $s=$conn->prepare("INSERT INTO users(nama,email,password,role,telepon,alamat)VALUES(?,?,?,?,?,?)");
        $s->bind_param("ssssss",$nama,$email,$pass,$role,$tel,$alamat);
        if($s->execute()){$msg='User berhasil ditambahkan.';log_activity($conn,$_SESSION['user_id'],'Tambah User',$email);}
        else{$err='Email sudah digunakan.';}
    } elseif ($action === 'edit') {
        $id=$_POST['id'];$nama=$_POST['nama'];$email=$_POST['email'];$role=$_POST['role'];
        $tel=$_POST['telepon'];$alamat=$_POST['alamat'];
        $s=$conn->prepare("UPDATE users SET nama=?,email=?,role=?,telepon=?,alamat=? WHERE id=?");
        $s->bind_param("sssssi",$nama,$email,$role,$tel,$alamat,$id);
        if($s->execute()){$msg='User berhasil diperbarui.';log_activity($conn,$_SESSION['user_id'],'Edit User',$email);}
        else{$err='Gagal memperbarui.';}
    } elseif ($action === 'hapus') {
        $id=$_POST['id'];
        if($id == $_SESSION['user_id']){$err='Tidak bisa hapus akun sendiri.';}
        else{
            $conn->query("DELETE FROM users WHERE id=$id");
            $msg='User dihapus.';log_activity($conn,$_SESSION['user_id'],'Hapus User',"ID:$id");
        }
    } elseif ($action === 'reset_pass') {
        $id=$_POST['id'];$pass=password_hash('password',PASSWORD_DEFAULT);
        $conn->query("UPDATE users SET password='$pass' WHERE id=$id");
        $msg='Password direset ke "password".';
    }
}

$search = $_GET['q'] ?? '';
$where = $search ? "WHERE nama LIKE '%$search%' OR email LIKE '%$search%'" : '';
$users = $conn->query("SELECT * FROM users $where ORDER BY created_at DESC");

// Get single user for edit
$edit_user = null;
if (isset($_GET['edit'])) {
    $eid = (int)$_GET['edit'];
    $edit_user = $conn->query("SELECT * FROM users WHERE id=$eid")->fetch_assoc();
}

include '../includes/header.php';
?>
<?php if($msg): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i><?=$msg?></div><?php endif; ?>
<?php if($err): ?><div class="alert alert-error"><i class="fas fa-times-circle"></i><?=$err?></div><?php endif; ?>

<div style="display:flex;gap:12px;align-items:center;margin-bottom:20px;flex-wrap:wrap;">
  <form method="GET" style="display:flex;gap:8px;flex:1;min-width:200px;">
    <input type="text" name="q" placeholder="Cari nama/email..." value="<?=htmlspecialchars($search)?>" style="max-width:280px;">
    <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-search"></i></button>
  </form>
  <button class="btn btn-primary" onclick="openModal('modal-tambah')"><i class="fas fa-plus"></i> Tambah User</button>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>#</th><th>Nama</th><th>Email</th><th>Role</th><th>Telepon</th><th>Terdaftar</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php $no=1; while($u=$users->fetch_assoc()): ?>
        <tr>
          <td><?=$no++?></td>
          <td><strong><?=htmlspecialchars($u['nama'])?></strong></td>
          <td><?=htmlspecialchars($u['email'])?></td>
          <td><span class="badge <?=$u['role']=='admin'?'badge-danger':($u['role']=='petugas'?'badge-info':'badge-success')?>"><?=$u['role']?></span></td>
          <td><?=htmlspecialchars($u['telepon']??'-')?></td>
          <td><?=date('d/m/Y',strtotime($u['created_at']))?></td>
          <td style="display:flex;gap:6px;">
            <a href="?edit=<?=$u['id']?>" class="btn btn-warning btn-sm" title="Edit"><i class="fas fa-edit"></i></a>
            <form method="POST" style="display:inline" onsubmit="return confirm('Reset password ke default?')">
              <input type="hidden" name="action" value="reset_pass">
              <input type="hidden" name="id" value="<?=$u['id']?>">
              <button type="submit" class="btn btn-info btn-sm" title="Reset Password"><i class="fas fa-key"></i></button>
            </form>
            <?php if($u['id']!=$_SESSION['user_id']): ?>
            <form method="POST" style="display:inline" onsubmit="return confirm('Hapus user ini?')">
              <input type="hidden" name="action" value="hapus">
              <input type="hidden" name="id" value="<?=$u['id']?>">
              <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
            </form>
            <?php endif; ?>
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
    <div class="modal-header">
      <h3>Tambah User Baru</h3>
      <button class="modal-close" onclick="closeModal('modal-tambah')">✕</button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="tambah">
      <div class="form-grid-2">
        <div class="form-group"><label>Nama *</label><input type="text" name="nama" required></div>
        <div class="form-group"><label>Role *</label><select name="role"><option value="peminjam">Peminjam</option><option value="petugas">Petugas</option><option value="admin">Admin</option></select></div>
      </div>
      <div class="form-group"><label>Email *</label><input type="email" name="email" required></div>
      <div class="form-group"><label>Password *</label><input type="password" name="password" required></div>
      <div class="form-grid-2">
        <div class="form-group"><label>Telepon</label><input type="text" name="telepon"></div>
        <div class="form-group"><label>Alamat</label><input type="text" name="alamat"></div>
      </div>
      <div style="display:flex;gap:10px;justify-content:flex-end;">
        <button type="button" class="btn btn-secondary" onclick="closeModal('modal-tambah')">Batal</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Edit -->
<?php if($edit_user): ?>
<div class="modal-overlay open" id="modal-edit">
  <div class="modal">
    <div class="modal-header">
      <h3>Edit User</h3>
      <a href="users.php" class="modal-close">✕</a>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="id" value="<?=$edit_user['id']?>">
      <div class="form-grid-2">
        <div class="form-group"><label>Nama *</label><input type="text" name="nama" value="<?=htmlspecialchars($edit_user['nama'])?>" required></div>
        <div class="form-group"><label>Role *</label><select name="role"><option value="peminjam" <?=$edit_user['role']=='peminjam'?'selected':''?>>Peminjam</option><option value="petugas" <?=$edit_user['role']=='petugas'?'selected':''?>>Petugas</option><option value="admin" <?=$edit_user['role']=='admin'?'selected':''?>>Admin</option></select></div>
      </div>
      <div class="form-group"><label>Email *</label><input type="email" name="email" value="<?=htmlspecialchars($edit_user['email'])?>" required></div>
      <div class="form-grid-2">
        <div class="form-group"><label>Telepon</label><input type="text" name="telepon" value="<?=htmlspecialchars($edit_user['telepon']??'')?>"></div>
        <div class="form-group"><label>Alamat</label><input type="text" name="alamat" value="<?=htmlspecialchars($edit_user['alamat']??'')?>"></div>
      </div>
      <div style="display:flex;gap:10px;justify-content:flex-end;">
        <a href="users.php" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>