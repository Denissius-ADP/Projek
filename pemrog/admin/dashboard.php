<?php
require_once __DIR__ . "/../helpers.php";
require_once __DIR__ . "/../ui.php";
require_role(['admin']);

$totalAlat = (int)(db_one("SELECT COUNT(*) c FROM alat")['c'] ?? 0);
$stokMenipis = (int)(db_one("SELECT COUNT(*) c FROM alat WHERE stok_tersedia <= 2")['c'] ?? 0);
$pending = (int)(db_one("SELECT COUNT(*) c FROM peminjaman WHERE status='pending'")['c'] ?? 0);
$dipinjam = (int)(db_one("SELECT COUNT(*) c FROM peminjaman WHERE status='disetujui'")['c'] ?? 0);

page_head("Admin Dashboard");
?>
<div class="glass rounded-xxl shadow-soft p-4">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
      <h3 class="fw-bold mb-1"><i class="bi bi-speedometer2 me-1"></i>Dashboard Admin</h3>
      <div class="muted-mini">Kontrol alat, approval peminjaman, pengembalian, dan audit log.</div>
    </div>
    <div class="d-flex gap-2">
      <a class="btn btn-primary" href="/admin/alat.php"><i class="bi bi-tools me-1"></i>Kelola Alat</a>
      <a class="btn btn-outline-primary" href="/admin/transaksi.php"><i class="bi bi-arrow-left-right me-1"></i>Transaksi</a>
      <a class="btn btn-outline-secondary" href="/admin/riwayat.php"><i class="bi bi-activity me-1"></i>Aktivitas</a>
    </div>
  </div>

  <div class="row g-3 mt-1">
    <div class="col-md-3"><div class="glass rounded-xxl p-3">
      <div class="muted-mini">Total Alat</div><div class="fs-3 fw-bold"><?= $totalAlat ?></div>
    </div></div>
    <div class="col-md-3"><div class="glass rounded-xxl p-3">
      <div class="muted-mini">Stok Menipis</div><div class="fs-3 fw-bold"><?= $stokMenipis ?></div>
    </div></div>
    <div class="col-md-3"><div class="glass rounded-xxl p-3">
      <div class="muted-mini">Request Pending</div><div class="fs-3 fw-bold"><?= $pending ?></div>
    </div></div>
    <div class="col-md-3"><div class="glass rounded-xxl p-3">
      <div class="muted-mini">Sedang Dipinjam</div><div class="fs-3 fw-bold"><?= $dipinjam ?></div>
    </div></div>
  </div>
</div>
<?php page_foot(); ?>
