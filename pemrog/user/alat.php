<?php
require_once __DIR__ . "/../helpers.php";
require_once __DIR__ . "/../ui.php";
require_role(['user']);

$q = trim($_GET['q'] ?? '');
if($q){
  $rows = db_all("SELECT * FROM alat
                 WHERE kondisi='baik' AND stok_tersedia>0 AND (kode LIKE ? OR nama LIKE ?)
                 ORDER BY nama","ss",["%$q%","%$q%"]);
} else {
  $rows = db_all("SELECT * FROM alat WHERE kondisi='baik' AND stok_tersedia>0 ORDER BY nama");
}

page_head("User - Alat Tersedia");
?>
<div class="glass rounded-xxl shadow-soft p-4">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
      <h3 class="fw-bold mb-1"><i class="bi bi-search me-1"></i>Alat Tersedia</h3>
      <div class="muted-mini">Hanya alat kondisi baik & stok tersedia.</div>
    </div>
    <form class="d-flex gap-2" method="get">
      <input class="form-control" name="q" placeholder="Cari alat..." value="<?= esc($q) ?>">
      <button class="btn btn-outline-light" style="border-color:rgba(255,255,255,.35);color:white"><i class="bi bi-search"></i></button>
    </form>
  </div>

  <div class="glass rounded-xxl p-3 mt-3 table-responsive">
    <table class="table table-sm align-middle mb-0">
      <thead>
        <tr><th>Kode</th><th>Nama</th><th>Kategori</th><th>Lokasi</th><th>Stok Tersedia</th></tr>
      </thead>
      <tbody>
        <?php foreach($rows as $r): ?>
          <tr>
            <td class="fw-semibold"><?= esc($r['kode']) ?></td>
            <td><?= esc($r['nama']) ?></td>
            <td><?= esc($r['kategori']) ?></td>
            <td><?= esc($r['lokasi']) ?></td>
            <td><span class="badge text-bg-success"><?= (int)$r['stok_tersedia'] ?></span></td>
          </tr>
        <?php endforeach; ?>
        <?php if(!$rows): ?><tr><td colspan="5" class="text-muted">Tidak ada alat tersedia.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="mt-3">
   <a class="btn btn-primary" href="<?= esc(url('/user/pinjam.php')) ?>">
  <i class="bi bi-box-arrow-up-right me-1"></i> Ajukan Pinjam
</a>

  </div>
</div>
<?php page_foot(); ?>
