<?php
require_once __DIR__ . "/../helpers.php";
require_once __DIR__ . "/../ui.php";
require_role(['user']);

$uid = (int)auth_user()['id'];
$ready = (int)(db_one("SELECT COUNT(*) c FROM alat WHERE stok_tersedia > 0 AND kondisi='baik'")['c'] ?? 0);
$pending = (int)(db_one("SELECT COUNT(*) c FROM peminjaman WHERE user_id=? AND status='pending'","i",[$uid])['c'] ?? 0);
$aktif = (int)(db_one("SELECT COUNT(*) c FROM peminjaman WHERE user_id=? AND status='disetujui'","i",[$uid])['c'] ?? 0);

page_head("User Dashboard");
?>
<div class="glass rounded-xxl shadow-soft p-4">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
      <h3 class="fw-bold mb-1"><i class="bi bi-speedometer2 me-1"></i>Dashboard User</h3>
      <div class="muted-mini">Cek alat, ajukan peminjaman, dan pantau status.</div>
    </div>
    <div class="d-flex gap-2">
      <a class="btn btn-primary" href="/user/pinjam.php"><i class="bi bi-box-arrow-up-right me-1"></i>Ajukan Pinjam</a>
      <a class="btn btn-outline-secondary" href="/user/riwayat.php"><i class="bi bi-clock-history me-1"></i>Riwayat</a>
    </div>
  </div>

  <div class="row g-3 mt-2">
    <div class="col-md-4"><div class="glass rounded-xxl p-3">
      <div class="muted-mini">Alat Ready</div><div class="fs-3 fw-bold"><?= $ready ?></div>
    </div></div>
    <div class="col-md-4"><div class="glass rounded-xxl p-3">
      <div class="muted-mini">Request Pending</div><div class="fs-3 fw-bold"><?= $pending ?></div>
    </div></div>
    <div class="col-md-4"><div class="glass rounded-xxl p-3">
      <div class="muted-mini">Sedang Dipinjam</div><div class="fs-3 fw-bold"><?= $aktif ?></div>
    </div></div>
  </div>
</div>
<?php page_foot(); ?>
