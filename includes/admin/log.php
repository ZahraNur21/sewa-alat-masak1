<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_role('admin');
$page_title = 'Log Aktifitas';

$page = max(1, (int)($_GET['page']??1));
$limit = 20; $offset = ($page-1)*$limit;
$total = $conn->query("SELECT COUNT(*) c FROM log_aktifitas")->fetch_assoc()['c'];
$pages = ceil($total/$limit);
$logs = $conn->query("SELECT l.*,u.nama user_nama FROM log_aktifitas l LEFT JOIN users u ON l.user_id=u.id ORDER BY l.created_at DESC LIMIT $limit OFFSET $offset");

include '../includes/header.php';
?>
<div class="card">
  <div class="card-header">
    <h3><i class="fas fa-history" style="color:var(--orange)"></i> Log Aktifitas Sistem</h3>
    <span style="font-size:0.85rem;color:var(--gray)"><?=$total?> total log</span>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Waktu</th><th>User</th><th>Aksi</th><th>Detail</th><th>IP</th></tr></thead>
      <tbody>
        <?php while($l=$logs->fetch_assoc()): ?>
        <tr>
          <td style="white-space:nowrap;font-size:0.82rem"><?=date('d/m/Y H:i',strtotime($l['created_at']))?></td>
          <td><?=htmlspecialchars($l['user_nama']??'System')?></td>
          <td><span class="badge badge-orange"><?=htmlspecialchars($l['aksi'])?></span></td>
          <td style="max-width:250px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?=htmlspecialchars($l['detail']??'')?></td>
          <td style="font-family:monospace;font-size:0.82rem"><?=$l['ip_address']?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
  <?php if($pages>1): ?>
  <div class="pagination">
    <?php for($i=1;$i<=$pages;$i++): ?>
    <a href="?page=<?=$i?>" class="page-btn <?=$i==$page?'active':''?>"><?=$i?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
</div>
<?php include '../includes/footer.php'; ?>