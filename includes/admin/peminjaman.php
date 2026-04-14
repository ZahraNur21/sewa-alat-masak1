<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_role('peminjam');
$page_title = 'Ajukan Peminjaman';

$uid = $_SESSION['user_id'];
$msg = $err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tgl_pinjam  = $_POST['tanggal_pinjam'];
    $tgl_kembali = $_POST['tanggal_kembali'];
    $catatan     = trim($_POST['catatan'] ?? '');
    $alat_ids    = $_POST['alat_id'] ?? [];
    $jumlah_arr  = $_POST['jumlah'] ?? [];

    if (empty($alat_ids)) {
        $err = 'Pilih minimal satu alat.';
    } elseif ($tgl_pinjam >= $tgl_kembali) {
        $err = 'Tanggal kembali harus setelah tanggal pinjam.';
    } elseif ($tgl_pinjam < date('Y-m-d')) {
        $err = 'Tanggal pinjam tidak boleh di masa lalu.';
    } else {
        // Check stok
        $stok_ok = true;
        foreach ($alat_ids as $i => $aid) {
            $jml = (int)($jumlah_arr[$i] ?? 1);
            $stok = $conn->query("SELECT stok FROM alat WHERE id=" . (int)$aid)->fetch_assoc()['stok'];
            if ($jml > $stok) { $stok_ok = false; $err = "Stok tidak mencukupi untuk beberapa alat."; break; }
        }

        if ($stok_ok) {
            $kode = kode_pinjam();
            $s = $conn->prepare("INSERT INTO peminjaman(kode_pinjam,user_id,tanggal_pinjam,tanggal_kembali,catatan) VALUES(?,?,?,?,?)");
            $s->bind_param("sisss", $kode, $uid, $tgl_pinjam, $tgl_kembali, $catatan);
            if ($s->execute()) {
                $pid = $conn->insert_id;
                foreach ($alat_ids as $i => $aid) {
                    $jml = max(1, (int)($jumlah_arr[$i] ?? 1));
                    $ds = $conn->prepare("INSERT INTO detail_peminjaman(peminjaman_id,alat_id,jumlah) VALUES(?,?,?)");
                    $ds->bind_param("iii", $pid, $aid, $jml);
                    $ds->execute();
                }
                log_activity($conn, $uid, 'Ajukan Peminjaman', "Kode: $kode");
                $_SESSION['flash_success'] = "Pengajuan berhasil! Kode: <strong>$kode</strong>. Menunggu persetujuan petugas.";
                header('Location: riwayat.php');
                exit;
            } else {
                $err = 'Gagal menyimpan pengajuan.';
            }
        }
    }
}

// Load available alat
$alats = $conn->query("
    SELECT a.*, k.nama kat_nama FROM alat a 
    LEFT JOIN kategori k ON a.kategori_id=k.id
    WHERE a.stok > 0 AND a.kondisi != 'rusak_berat'
    ORDER BY k.nama, a.nama
");
$alat_list = [];
while ($row = $alats->fetch_assoc()) $alat_list[] = $row;

// Pre-select alat from URL
$preselect = (int)($_GET['alat'] ?? 0);

include '../includes/header.php';
?>

<?php if($err): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?=$err?></div><?php endif; ?>

<div class="card">
  <div class="card-header"><h3><i class="fas fa-plus-circle" style="color:var(--orange)"></i> Form Pengajuan Peminjaman</h3></div>
  <form method="POST" id="form-pinjam">
    <div class="form-grid-2">
      <div class="form-group">
        <label>Tanggal Pinjam *</label>
        <input type="date" name="tanggal_pinjam" id="tgl_pinjam" value="<?=date('Y-m-d')?>" min="<?=date('Y-m-d')?>" required>
      </div>
      <div class="form-group">
        <label>Tanggal Kembali *</label>
        <input type="date" name="tanggal_kembali" id="tgl_kembali" value="<?=date('Y-m-d',strtotime('+1 day'))?>" min="<?=date('Y-m-d',strtotime('+1 day'))?>" required>
      </div>
    </div>
    <div class="form-group">
      <label>Catatan (opsional)</label>
      <textarea name="catatan" rows="2" placeholder="Keperluan, event, dll..."></textarea>
    </div>

    <div style="margin-bottom:14px;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
        <label style="margin:0;font-size:1rem">Pilih Alat *</label>
        <button type="button" class="btn btn-secondary btn-sm" onclick="tambahBaris()"><i class="fas fa-plus"></i> Tambah Baris</button>
      </div>
      <div id="alat-container">
        <div class="alat-row" style="display:grid;grid-template-columns:1fr 80px auto;gap:10px;margin-bottom:10px;align-items:center;">
          <select name="alat_id[]" required>
            <option value="">-- Pilih Alat --</option>
            <?php foreach($alat_list as $a): ?>
            <option value="<?=$a['id']?>" <?=$preselect==$a['id']?'selected':''?> data-stok="<?=$a['stok']?>" data-harga="<?=$a['harga_sewa']?>">
              <?=htmlspecialchars($a['nama'])?> (Stok: <?=$a['stok']?>, <?=rupiah($a['harga_sewa'])?>/hari)
            </option>
            <?php endforeach; ?>
          </select>
          <input type="number" name="jumlah[]" value="1" min="1" placeholder="Jml">
          <button type="button" class="btn btn-danger btn-sm" onclick="hapusBaris(this)"><i class="fas fa-times"></i></button>
        </div>
      </div>
    </div>

    <!-- Estimasi Biaya -->
    <div id="estimasi" style="background:var(--orange-light);border-radius:var(--radius-sm);padding:14px;margin-bottom:18px;display:none;">
      <strong style="color:var(--orange-deep)">💰 Estimasi Biaya:</strong>
      <div id="estimasi-detail" style="margin-top:6px;font-size:0.88rem;color:var(--brown)"></div>
    </div>

    <div style="display:flex;gap:12px;">
      <a href="daftar_alat.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
      <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Kirim Pengajuan</button>
    </div>
  </form>
</div>

<script>
const alatTemplate = `<div class="alat-row" style="display:grid;grid-template-columns:1fr 80px auto;gap:10px;margin-bottom:10px;align-items:center;">
  <select name="alat_id[]" required onchange="hitungEstimasi()">
    <option value="">-- Pilih Alat --</option>
    <?php foreach($alat_list as $a): ?>
    <option value="<?=$a['id']?>" data-stok="<?=$a['stok']?>" data-harga="<?=$a['harga_sewa']?>"><?=addslashes(htmlspecialchars($a['nama']))?> (Stok: <?=$a['stok']?>, <?=rupiah($a['harga_sewa'])?>/hari)</option>
    <?php endforeach; ?>
  </select>
  <input type="number" name="jumlah[]" value="1" min="1" placeholder="Jml" onchange="hitungEstimasi()">
  <button type="button" class="btn btn-danger btn-sm" onclick="hapusBaris(this)"><i class="fas fa-times"></i></button>
</div>`;

function tambahBaris() {
  document.getElementById('alat-container').insertAdjacentHTML('beforeend', alatTemplate);
}

function hapusBaris(btn) {
  const rows = document.querySelectorAll('.alat-row');
  if (rows.length > 1) { btn.closest('.alat-row').remove(); hitungEstimasi(); }
}

function hitungEstimasi() {
  const tglP = document.getElementById('tgl_pinjam').value;
  const tglK = document.getElementById('tgl_kembali').value;
  if (!tglP || !tglK) return;
  const hari = Math.max(1, Math.round((new Date(tglK) - new Date(tglP)) / 86400000));
  
  let detail = `<strong>Durasi: ${hari} hari</strong><br>`;
  let totalPerHari = 0;
  let valid = false;

  document.querySelectorAll('.alat-row').forEach(row => {
    const sel = row.querySelector('select');
    const jml = parseInt(row.querySelector('input').value) || 1;
    const opt = sel.options[sel.selectedIndex];
    if (opt && opt.value) {
      valid = true;
      const harga = parseFloat(opt.dataset.harga) || 0;
      const sub = harga * jml;
      totalPerHari += sub;
      detail += `• ${opt.text.split('(')[0].trim()} x${jml} = Rp ${(sub*hari).toLocaleString('id-ID')}<br>`;
    }
  });

  const el = document.getElementById('estimasi');
  if (valid) {
    document.getElementById('estimasi-detail').innerHTML = detail + `<strong style="color:var(--orange)">Total Estimasi: Rp ${(totalPerHari*hari).toLocaleString('id-ID')}</strong>`;
    el.style.display = 'block';
  } else { el.style.display = 'none'; }
}

document.getElementById('tgl_pinjam').addEventListener('change', hitungEstimasi);
document.getElementById('tgl_kembali').addEventListener('change', hitungEstimasi);
document.querySelector('select[name="alat_id[]"]').addEventListener('change', hitungEstimasi);
document.querySelector('input[name="jumlah[]"]').addEventListener('change', hitungEstimasi);
</script>

<?php include '../includes/footer.php'; ?>