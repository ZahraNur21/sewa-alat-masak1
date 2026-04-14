<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_role('peminjam');
$page_title = 'Daftar Alat Tersedia';

$search     = $_GET['q'] ?? '';
$kat_filter = $_GET['kat'] ?? '';
$where = "WHERE a.kondisi != 'rusak_berat' AND a.stok > 0";
if ($search)     $where .= " AND a.nama LIKE '%".addslashes($search)."%'";
if ($kat_filter) $where .= " AND a.kategori_id=" . (int)$kat_filter;

$alats = $conn->query("
    SELECT a.*, k.nama kat_nama 
    FROM alat a LEFT JOIN kategori k ON a.kategori_id=k.id
    $where ORDER BY k.nama, a.nama
");
$kats_arr = [];
$r = $conn->query("SELECT * FROM kategori ORDER BY nama");
while ($row = $r->fetch_assoc()) $kats_arr[] = $row;

include '../includes/header.php';
?>

<div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:20px;">
  <form method="GET" style="display:flex;gap:8px;flex:1;flex-wrap:wrap;">
    <input type="text" name="q" placeholder="Cari nama alat..." value="<?=htmlspecialchars($search)?>" style="max-width:240px;">
    <select name="kat" style="max-width:200px;">
      <option value="">Semua Kategori</option>
      <?php foreach($kats_arr as $k): ?>
      <option value="<?=$k['id']?>" <?=$kat_filter==$k['id']?'selected':''?>><?=htmlspecialchars($k['nama'])?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Cari</button>
    <?php if($search||$kat_filter): ?><a href="daftar_alat.php" class="btn btn-secondary btn-sm">Reset</a><?php endif; ?>
  </form>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:20px;">
  <?php 
  $cnt=0;
  while ($a = $alats->fetch_assoc()):
    $cnt++;
    $emoji_map = ['Peralatan Memasak'=>'🍳','Peralatan Memanggang'=>'🔥','Peralatan Minum'=>'☕','Peralatan Makan'=>'🍽️'];
    $emoji = $emoji_map[$a['kat_nama']] ?? '🥘';
  ?>
  <div style="background:var(--warm-white);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden;transition:transform 0.2s,box-shadow 0.2s;" 
       onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 30px rgba(61,31,10,0.18)'"
       onmouseout="this.style.transform='';this.style.boxShadow=''">
    <div style="background:linear-gradient(135deg,var(--brown),var(--brown-mid));height:110px;display:flex;align-items:center;justify-content:center;font-size:3.5rem;">
      <?=$emoji?>
    </div>
    <div style="padding:18px;">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px;">
        <h4 style="font-family:'Playfair Display',serif;font-size:1rem;color:var(--brown);line-height:1.3"><?=htmlspecialchars($a['nama'])?></h4>
      </div>
      <p style="font-size:0.8rem;color:var(--gray);margin-bottom:10px;"><?=htmlspecialchars($a['kat_nama']??'Umum')?> &middot; <code style="font-size:0.78rem"><?=$a['kode']?></code></p>
      <?php if($a['deskripsi']): ?>
      <p style="font-size:0.83rem;color:var(--gray);margin-bottom:10px;line-height:1.4"><?=htmlspecialchars(substr($a['deskripsi'],0,70))?><?=strlen($a['deskripsi'])>70?'...':''?></p>
      <?php endif; ?>
      <div style="display:flex;justify-content:space-between;align-items:center;margin-top:12px;">
        <div>
          <div style="font-weight:700;color:var(--orange);font-size:1rem"><?=rupiah($a['harga_sewa'])?><span style="font-weight:400;font-size:0.78rem;color:var(--gray)">/hari</span></div>
          <div style="font-size:0.78rem;color:var(--gray)">Stok: <strong style="color:var(--brown)"><?=$a['stok']?></strong></div>
        </div>
        <div style="display:flex;flex-direction:column;gap:5px;align-items:flex-end;">
          <span class="badge <?=$a['kondisi']=='baik'?'badge-success':'badge-warning'?>"><?=str_replace('_',' ',$a['kondisi'])?></span>
          <a href="pinjam.php?alat=<?=$a['id']?>" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Pinjam</a>
        </div>
      </div>
    </div>
  </div>
  <?php endwhile; ?>
</div>

<?php if($cnt===0): ?>
<div class="empty-state"><i class="fas fa-search"></i><p>Tidak ada alat yang tersedia untuk kriteria pencarian ini.</p></div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>