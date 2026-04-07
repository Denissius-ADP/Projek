<?php
require_once __DIR__ . "/helpers.php";
$u = auth_user();
$uri = $_SERVER['REQUEST_URI'] ?? '';
function is_active($needle,$uri){ return str_contains($uri,$needle) ? "active" : ""; }
?>
<nav class="navbar navbar-expand-lg navbar-dark navbar-glass">
  <div class="container">
    <a class="navbar-brand fw-bold" href="<?= esc(url('/index.php')) ?>">
      <i class="bi bi-box-seam me-1"></i> LabTools <span class="brand-badge">Keperawatan</span>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav me-auto">
        <?php if($u && $u['role']==='admin'): ?>
          <li class="nav-item"><a class="nav-link <?= is_active('/admin/dashboard',$uri) ?>" href="<?= esc(url('/admin/dashboard.php')) ?>"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
          <li class="nav-item"><a class="nav-link <?= is_active('/admin/alat',$uri) ?>" href="<?= esc(url('/admin/alat.php')) ?>"><i class="bi bi-tools me-1"></i>Alat</a></li>
          <li class="nav-item"><a class="nav-link <?= is_active('/admin/transaksi',$uri) ?>" href="<?= esc(url('/admin/transaksi.php')) ?>"><i class="bi bi-arrow-left-right me-1"></i>Transaksi</a></li>
          <li class="nav-item"><a class="nav-link <?= is_active('/admin/riwayat',$uri) ?>" href="<?= esc(url('/admin/riwayat.php')) ?>"><i class="bi bi-activity me-1"></i>Aktivitas</a></li>
        <?php elseif($u && $u['role']==='user'): ?>
          <li class="nav-item"><a class="nav-link <?= is_active('/user/dashboard',$uri) ?>" href="<?= esc(url('/user/dashboard.php')) ?>"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a></li>
          <li class="nav-item"><a class="nav-link <?= is_active('/user/alat',$uri) ?>" href="<?= esc(url('/user/alat.php')) ?>"><i class="bi bi-search me-1"></i>Alat Tersedia</a></li>
          <li class="nav-item"><a class="nav-link <?= is_active('/user/pinjam',$uri) ?>" href="<?= esc(url('/user/pinjam.php')) ?>"><i class="bi bi-box-arrow-up-right me-1"></i>Pinjam</a></li>
          <li class="nav-item"><a class="nav-link <?= is_active('/user/riwayat',$uri) ?>" href="<?= esc(url('/user/riwayat.php')) ?>"><i class="bi bi-clock-history me-1"></i>Riwayat</a></li>
        <?php endif; ?>
      </ul>

      <div class="d-flex align-items-center gap-2">
        <?php if($u): ?>
          <span class="text-white-50 small">
            <?= esc($u['nama']) ?> · <span class="badge text-bg-light"><?= esc($u['role']) ?></span>
          </span>
          <a class="btn btn-outline-light btn-sm" href="<?= esc(url('/logout.php')) ?>"><i class="bi bi-box-arrow-right"></i> Logout</a>
        <?php else: ?>
          <a class="btn btn-light btn-sm" href="<?= esc(url('/login.php')) ?>">Login</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>
