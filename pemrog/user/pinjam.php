<?php
require_once __DIR__ . "/../helpers.php";
require_once __DIR__ . "/../ui.php";
require_role(['user']);

$uid = (int)auth_user()['id'];
$alat = db_all("SELECT * FROM alat WHERE kondisi='baik' AND stok_tersedia>0 ORDER BY nama");

if(isset($_POST['pinjam'])){
  csrf_check();
  $alat_id = (int)($_POST['alat_id'] ?? 0);
  $qty = (int)($_POST['qty'] ?? 1);
  $tgl_pinjam = $_POST['tgl_pinjam'] ?? date('Y-m-d');
  $tgl_rencana = $_POST['tgl_rencana_kembali'] ?? date('Y-m-d', strtotime('+7 day'));
  $cat = trim($_POST['catatan_user'] ?? '');

  $a = db_one("SELECT * FROM alat WHERE id=?","i",[$alat_id]);
  if(!$a){ flash_set("Alat tidak ditemukan.","err"); redirect("/user/pinjam.php"); }
  if($a['kondisi']!=='baik' || (int)$a['stok_tersedia']<=0){ flash_set("Alat tidak tersedia.","err"); redirect("/user/pinjam.php"); }
  if($qty <= 0){ flash_set("Qty harus > 0.","err"); redirect("/user/pinjam.php"); }
  if($qty > (int)$a['stok_tersedia']){ flash_set("Qty melebihi stok tersedia.","err"); redirect("/user/pinjam.php"); }
  if($tgl_rencana < $tgl_pinjam){ flash_set("Tanggal kembali harus >= tanggal pinjam.","err"); redirect("/user/pinjam.php"); }

  db_exec("INSERT INTO peminjaman(user_id,alat_id,qty,tgl_pinjam,tgl_rencana_kembali,status,catatan_user)
           VALUES(?,?,?,?,?,'pending',?)",
          "iiisss", [$uid,$alat_id,$qty,$tgl_pinjam,$tgl_rencana,$cat]);

  log_activity("PINJAM_REQUEST","Request pinjam {$a['kode']} qty $qty (pending)");
  flash_set("Request peminjaman berhasil dikirim. Tunggu persetujuan admin.");
  redirect("/user/riwayat.php");
}

page_head("User - Ajukan Peminjaman");
?>
<div class="glass rounded-xxl shadow-soft p-4">
  <h3 class="fw-bold mb-1"><i class="bi bi-box-arrow-up-right me-1"></i>Ajukan Peminjaman</h3>
  <div class="muted-mini mb-3">Status awal <b>pending</b>, admin akan setujui/tolak.</div>

  <div class="glass rounded-xxl p-3">
    <form method="post" class="row g-3">
      <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">

      <div class="col-md-6">
        <label class="form-label">Alat</label>
        <select class="form-select" name="alat_id" required>
          <option value="">-- pilih alat --</option>
          <?php foreach($alat as $a): ?>
            <option value="<?= (int)$a['id'] ?>">
              <?= esc($a['kode']." · ".$a['nama']." (stok ".$a['stok_tersedia'].")") ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-2">
        <label class="form-label">Qty</label>
        <input class="form-control" type="number" min="1" name="qty" value="1" required>
      </div>

      <div class="col-md-2">
        <label class="form-label">Tanggal Pinjam</label>
        <input class="form-control" type="date" name="tgl_pinjam" value="<?= esc(date('Y-m-d')) ?>" required>
      </div>

      <div class="col-md-2">
        <label class="form-label">Rencana Kembali</label>
        <input class="form-control" type="date" name="tgl_rencana_kembali" value="<?= esc(date('Y-m-d', strtotime('+7 day'))) ?>" required>
      </div>

      <div class="col-12">
        <label class="form-label">Catatan (opsional)</label>
        <input class="form-control" name="catatan_user" placeholder="Contoh: untuk praktikum OSCE...">
      </div>

      <div class="col-12 d-flex gap-2">
        <button class="btn btn-primary" name="pinjam"><i class="bi bi-send me-1"></i>Kirim Request</button>
        <a class="btn btn-outline-secondary" href="/user/dashboard.php">Kembali</a>
      </div>
    </form>
  </div>
</div>
<?php page_foot(); ?>
