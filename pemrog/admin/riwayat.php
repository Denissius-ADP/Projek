<?php
require_once __DIR__ . "/../helpers.php";
require_once __DIR__ . "/../ui.php";
require_role(['admin']);

$rows = db_all("
  SELECT a.*, u.nama, u.username
  FROM aktivitas a
  LEFT JOIN users u ON u.id=a.actor_user_id
  ORDER BY a.id DESC
  LIMIT 200
");

page_head("Admin - Aktivitas");
?>
<div class="glass rounded-xxl shadow-soft p-4">
  <h3 class="fw-bold mb-1"><i class="bi bi-activity me-1"></i>Riwayat Aktivitas</h3>
  <div class="muted-mini mb-3">Audit log terakhir (max 200 data).</div>

  <div class="glass rounded-xxl p-3 table-responsive">
    <table class="table table-sm align-middle mb-0">
      <thead>
        <tr><th>Waktu</th><th>Aktor</th><th>Aksi</th><th>Detail</th></tr>
      </thead>
      <tbody>
        <?php foreach($rows as $r): ?>
          <tr>
            <td><?= esc($r['created_at']) ?></td>
            <td><?= esc($r['nama'] ?? '-') ?><div class="muted-mini"><?= esc($r['username'] ?? '') ?></div></td>
            <td><span class="badge text-bg-dark"><?= esc($r['aksi']) ?></span></td>
            <td><?= esc($r['detail']) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if(!$rows): ?><tr><td colspan="4" class="text-muted">Belum ada log.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php page_foot(); ?>
