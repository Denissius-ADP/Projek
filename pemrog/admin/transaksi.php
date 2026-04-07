<?php
require_once __DIR__ . "/../helpers.php";
require_once __DIR__ . "/../ui.php";
require_role(['admin']);

if($_SERVER['REQUEST_METHOD']==='POST'){
  csrf_check();

  $id = (int)($_POST['id'] ?? 0);
  $aksi = $_POST['aksi'] ?? '';
  $catatan = trim($_POST['catatan_admin'] ?? '');

  $p = db_one("
    SELECT p.*, u.nama AS user_nama, u.username, a.kode, a.nama AS alat_nama, a.stok_tersedia
    FROM peminjaman p
    JOIN users u ON u.id=p.user_id
    JOIN alat a ON a.id=p.alat_id
    WHERE p.id=?
    LIMIT 1
  ","i",[$id]);

  if(!$p){
    flash_set("Transaksi tidak ditemukan.","err");
    redirect("/admin/transaksi.php");
  }

  // Biar update stok + status aman (anti race)
  mysqli_begin_transaction($koneksi);

  try {
    if($aksi==='setujui'){
      if($p['status']!=='pending') throw new Exception("Hanya status pending yang bisa disetujui.");
      if((int)$p['qty'] > (int)$p['stok_tersedia']) throw new Exception("Stok tersedia tidak cukup.");

      db_exec("UPDATE alat SET stok_tersedia = stok_tersedia - ? WHERE id=?","ii",[(int)$p['qty'], (int)$p['alat_id']]);
      db_exec("UPDATE peminjaman SET status='disetujui', catatan_admin=? WHERE id=?","si",[$catatan,$id]);

      log_activity("PINJAM_SETUJUI","Setujui #$id ({$p['kode']} qty {$p['qty']}) utk {$p['username']}");
      flash_set("Peminjaman disetujui.");
    }

    elseif($aksi==='tolak'){
      if($p['status']!=='pending') throw new Exception("Hanya status pending yang bisa ditolak.");

      db_exec("UPDATE peminjaman SET status='ditolak', catatan_admin=? WHERE id=?","si",[$catatan,$id]);
      log_activity("PINJAM_TOLAK","Tolak #$id ({$p['kode']} qty {$p['qty']}) utk {$p['username']}");
      flash_set("Peminjaman ditolak.");
    }

    elseif($aksi==='kembali'){
      if($p['status']!=='disetujui') throw new Exception("Hanya status disetujui yang bisa dikembalikan.");

      db_exec("UPDATE alat SET stok_tersedia = stok_tersedia + ? WHERE id=?","ii",[(int)$p['qty'], (int)$p['alat_id']]);
      db_exec("UPDATE peminjaman SET status='dikembalikan', tgl_kembali=CURDATE(), catatan_admin=? WHERE id=?","si",[$catatan,$id]);

      log_activity("PINJAM_KEMBALI","Pengembalian #$id ({$p['kode']} qty {$p['qty']}) dari {$p['username']}");
      flash_set("Pengembalian berhasil diproses.");
    }

    else {
      throw new Exception("Aksi tidak valid.");
    }

    mysqli_commit($koneksi);
    redirect("/admin/transaksi.php");

  } catch (Exception $e) {
    mysqli_rollback($koneksi);
    flash_set($e->getMessage(),"err");
    redirect("/admin/transaksi.php");
  }
}

// Filter yang aman
$filter = $_GET['filter'] ?? 'all';
if(!in_array($filter, ['all','pending','aktif','selesai'], true)) $filter = 'all';

$where = "1=1";
if($filter==='pending') $where="p.status='pending'";
if($filter==='aktif') $where="p.status='disetujui'";
if($filter==='selesai') $where="p.status IN ('ditolak','dikembalikan')";

$rows = db_all("
  SELECT p.*, u.nama AS user_nama, u.username, a.kode, a.nama AS alat_nama
  FROM peminjaman p
  JOIN users u ON u.id=p.user_id
  JOIN alat a ON a.id=p.alat_id
  WHERE $where
  ORDER BY p.id DESC
");

page_head("Admin - Transaksi");
?>
<div class="glass rounded-xxl shadow-soft p-4">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
      <h3 class="fw-bold mb-1"><i class="bi bi-arrow-left-right me-1"></i>Transaksi Peminjaman</h3>
      <div class="muted-mini">Approve/tolak request dan proses pengembalian (stok otomatis).</div>
    </div>
    <div class="btn-group">
      <a class="btn btn-outline-light <?= $filter==='all'?'active':'' ?>" href="?filter=all">Semua</a>
      <a class="btn btn-outline-light <?= $filter==='pending'?'active':'' ?>" href="?filter=pending">Pending</a>
      <a class="btn btn-outline-light <?= $filter==='aktif'?'active':'' ?>" href="?filter=aktif">Aktif</a>
      <a class="btn btn-outline-light <?= $filter==='selesai'?'active':'' ?>" href="?filter=selesai">Selesai</a>
    </div>
  </div>

  <div class="glass rounded-xxl p-3 mt-3 table-responsive">
    <table class="table table-sm align-middle mb-0">
      <thead>
        <tr>
          <th>ID</th><th>User</th><th>Alat</th><th>Qty</th>
          <th>Pinjam</th><th>Rencana Kembali</th><th>Status</th><th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($rows as $r): ?>
          <tr>
            <td class="fw-semibold">#<?= (int)$r['id'] ?></td>
            <td><?= esc($r['user_nama']) ?><div class="muted-mini">@<?= esc($r['username']) ?></div></td>
            <td><?= esc($r['kode']." · ".$r['alat_nama']) ?></td>
            <td><?= (int)$r['qty'] ?></td>
            <td><?= esc($r['tgl_pinjam']) ?></td>
            <td><?= esc($r['tgl_rencana_kembali']) ?></td>
            <td><?= badge_status($r['status']) ?></td>
            <td style="min-width:260px">
              <form method="post" class="d-flex gap-2 flex-wrap">
                <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">
                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                <input class="form-control form-control-sm" name="catatan_admin" placeholder="Catatan admin..." value="<?= esc($r['catatan_admin']) ?>">
                <?php if($r['status']==='pending'): ?>
                  <button class="btn btn-sm btn-primary" name="aksi" value="setujui" title="Setujui"><i class="bi bi-check2"></i></button>
                  <button class="btn btn-sm btn-danger" name="aksi" value="tolak" title="Tolak"><i class="bi bi-x"></i></button>
                <?php elseif($r['status']==='disetujui'): ?>
                  <button class="btn btn-sm btn-success" name="aksi" value="kembali" title="Proses pengembalian">
                    <i class="bi bi-box-arrow-in-down"></i> Kembali
                  </button>
                <?php else: ?>
                  <span class="text-muted small">-</span>
                <?php endif; ?>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if(!$rows): ?>
          <tr><td colspan="8" class="text-muted">Tidak ada data.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php page_foot(); ?>
