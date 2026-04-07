<?php
require_once __DIR__ . "/../helpers.php";
require_once __DIR__ . "/../ui.php";
require_role(['user']);

$uid = (int)auth_user()['id'];

$rows = db_all("
  SELECT p.*, a.kode, a.nama alat_nama
  FROM peminjaman p
  JOIN alat a ON a.id=p.alat_id
  WHERE p.user_id=?
  ORDER BY p.id DESC
","i",[$uid]);

page_head("User - Riwayat");
?>
<div class="glass rounded-xxl shadow-soft p-4">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
      <h3 class="fw-bold mb-1"><i class="bi bi-clock-history me-1"></i>Riwayat Peminjaman</h3>
      <div class="muted-mini">Pantau status: pending/disetujui/ditolak/dikembalikan.</div>
    </div>
    <a class="btn btn-primary" href="<?= esc(url('/user/pinjam.php')) ?>">
  <i class="bi bi-plus-lg me-1"></i> Ajukan
</a>
  </div>

  <div class="glass rounded-xxl p-3 mt-3 table-responsive">
    <table class="table table-sm align-middle mb-0">
      <thead>
        <tr>
          <th>ID</th><th>Alat</th><th>Qty</th>
          <th>Pinjam</th><th>Rencana</th><th>Kembali</th><th>Status</th><th>Catatan</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($rows as $r): ?>
          <tr>
            <td class="fw-semibold">#<?= (int)$r['id'] ?></td>
            <td><?= esc($r['kode']." · ".$r['alat_nama']) ?></td>
            <td><?= (int)$r['qty'] ?></td>
            <td><?= esc($r['tgl_pinjam']) ?></td>
            <td><?= esc($r['tgl_rencana_kembali']) ?></td>
            <td><?= esc($r['tgl_kembali'] ?? '-') ?></td>
            <td><?= badge_status($r['status']) ?></td>
            <td>
              <div class="muted-mini">User: <?= esc($r['catatan_user'] ?? '-') ?></div>
              <div class="muted-mini">Admin: <?= esc($r['catatan_admin'] ?? '-') ?></div>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if(!$rows): ?><tr><td colspan="8" class="text-muted">Belum ada transaksi.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php page_foot(); ?>
