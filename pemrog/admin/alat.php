<?php
require_once __DIR__ . "/../helpers.php";
require_once __DIR__ . "/../ui.php";
require_role(['admin']);

if($_SERVER['REQUEST_METHOD']==='POST'){
  csrf_check();

  // tambah alat
  if(isset($_POST['tambah'])){
    $kode = trim($_POST['kode'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $kategori = trim($_POST['kategori'] ?? '');
    $lokasi = trim($_POST['lokasi'] ?? '');
    $stok_total = (int)($_POST['stok_total'] ?? 0);
    $stok_tersedia = (int)($_POST['stok_tersedia'] ?? 0);
    $kondisi = $_POST['kondisi'] ?? 'baik';

    if($stok_total < 0 || $stok_tersedia < 0 || $stok_tersedia > $stok_total){
      flash_set("Stok tidak valid (tersedia tidak boleh > total).","err");
      redirect("/admin/alat.php");
    }

    $ok = db_exec(
      "INSERT INTO alat(kode,nama,kategori,lokasi,stok_total,stok_tersedia,kondisi) VALUES(?,?,?,?,?,?,?)",
      "ssssiis",
      [$kode,$nama,$kategori,$lokasi,$stok_total,$stok_tersedia,$kondisi]
    );

    if($ok){
      log_activity("ALAT_TAMBAH","Tambah alat $kode - $nama");
      flash_set("Alat berhasil ditambahkan.");
    } else {
      flash_set("Gagal tambah alat (kode mungkin sudah ada).","err");
    }
    redirect("/admin/alat.php");
  }

  // stok masuk/keluar manual (biar admin bisa input output stok)
  if(isset($_POST['stok_adjust'])){
    $id = (int)($_POST['alat_id'] ?? 0);
    $mode = $_POST['mode'] ?? 'masuk'; // masuk / keluar
    $qty = (int)($_POST['qty'] ?? 0);

    $a = db_one("SELECT * FROM alat WHERE id=?","i",[$id]);
    if(!$a){ flash_set("Alat tidak ditemukan.","err"); redirect("/admin/alat.php"); }
    if($qty <= 0){ flash_set("Qty harus > 0.","err"); redirect("/admin/alat.php"); }

    $stok_total = (int)$a['stok_total'];
    $stok_tersedia = (int)$a['stok_tersedia'];

    if($mode==='masuk'){
      $stok_total += $qty;
      $stok_tersedia += $qty;
      db_exec("UPDATE alat SET stok_total=?, stok_tersedia=? WHERE id=?","iii",[$stok_total,$stok_tersedia,$id]);
      log_activity("STOK_MASUK","Alat {$a['kode']} +$qty (total:$stok_total, tersedia:$stok_tersedia)");
      flash_set("Stok masuk berhasil.");
    } else {
      // keluar manual (misal rusak/hilang)
      if($qty > $stok_total){
        flash_set("Qty keluar melebihi stok total.","err"); redirect("/admin/alat.php");
      }
      // kurangi total dan juga tersedia (maksimal sampai 0)
      $stok_total -= $qty;
      $stok_tersedia = max(0, $stok_tersedia - $qty);
      db_exec("UPDATE alat SET stok_total=?, stok_tersedia=? WHERE id=?","iii",[$stok_total,$stok_tersedia,$id]);
      log_activity("STOK_KELUAR","Alat {$a['kode']} -$qty (total:$stok_total, tersedia:$stok_tersedia)");
      flash_set("Stok keluar berhasil.");
    }
    redirect("/admin/alat.php");
  }
}

if(isset($_GET['hapus'])){
  $id = (int)$_GET['hapus'];
  $cek = db_one("SELECT COUNT(*) c FROM peminjaman WHERE alat_id=? AND status IN ('pending','disetujui')","i",[$id]);
  if(((int)($cek['c']??0))>0){
    flash_set("Tidak bisa hapus: ada peminjaman aktif/pending.","err");
    redirect("/admin/alat.php");
  }
  $a = db_one("SELECT * FROM alat WHERE id=?","i",[$id]);
  db_exec("DELETE FROM alat WHERE id=?","i",[$id]);
  log_activity("ALAT_HAPUS","Hapus alat ".($a['kode']??$id));
  flash_set("Alat dihapus.");
  redirect("/admin/alat.php");
}

$q = trim($_GET['q'] ?? '');
if($q){
  $alat = db_all("SELECT * FROM alat WHERE kode LIKE ? OR nama LIKE ? ORDER BY id DESC","ss",["%$q%","%$q%"]);
} else {
  $alat = db_all("SELECT * FROM alat ORDER BY id DESC");
}

page_head("Admin - Kelola Alat");
?>
<div class="glass rounded-xxl shadow-soft p-4">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
      <h3 class="fw-bold mb-1"><i class="bi bi-tools me-1"></i>Kelola Alat</h3>
      <div class="muted-mini">Tambah alat baru, atur stok masuk/keluar, dan kontrol kondisi.</div>
    </div>
    <form class="d-flex gap-2" method="get">
      <input class="form-control" name="q" placeholder="Cari kode/nama..." value="<?= esc($q) ?>">
      <button class="btn btn-outline-light" style="border-color:rgba(255,255,255,.35);color:white"><i class="bi bi-search"></i></button>
    </form>
  </div>

  <div class="row g-3 mt-2">
    <div class="col-lg-5">
      <div class="glass rounded-xxl p-3">
        <div class="fw-semibold mb-2"><i class="bi bi-plus-circle me-1"></i>Tambah Alat</div>
        <form method="post" class="row g-2">
          <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">
          <div class="col-md-4"><input class="form-control" name="kode" placeholder="Kode" required></div>
          <div class="col-md-8"><input class="form-control" name="nama" placeholder="Nama alat" required></div>
          <div class="col-md-6"><input class="form-control" name="kategori" placeholder="Kategori"></div>
          <div class="col-md-6"><input class="form-control" name="lokasi" placeholder="Lokasi"></div>
          <div class="col-md-4"><input class="form-control" name="stok_total" type="number" min="0" placeholder="Stok total" required></div>
          <div class="col-md-4"><input class="form-control" name="stok_tersedia" type="number" min="0" placeholder="Stok tersedia" required></div>
          <div class="col-md-4">
            <select class="form-select" name="kondisi">
              <option value="baik">baik</option>
              <option value="rusak">rusak</option>
              <option value="maintenance">maintenance</option>
            </select>
          </div>
          <div class="col-12">
            <button class="btn btn-primary" name="tambah"><i class="bi bi-check2 me-1"></i>Simpan</button>
            <a class="btn btn-outline-secondary" href="/admin/dashboard.php">Kembali</a>
          </div>
        </form>
      </div>
    </div>

    <div class="col-lg-7">
      <div class="glass rounded-xxl p-3">
        <div class="fw-semibold mb-2"><i class="bi bi-arrow-down-up me-1"></i>Stok Masuk/Keluar Manual</div>
        <form method="post" class="row g-2 align-items-end">
          <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">
          <div class="col-md-6">
            <label class="form-label">Pilih Alat</label>
            <select class="form-select" name="alat_id" required>
              <option value="">-- pilih --</option>
              <?php foreach($alat as $a): ?>
                <option value="<?= (int)$a['id'] ?>"><?= esc($a['kode']." - ".$a['nama']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Mode</label>
            <select class="form-select" name="mode">
              <option value="masuk">stok masuk</option>
              <option value="keluar">stok keluar</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Qty</label>
            <input class="form-control" name="qty" type="number" min="1" required>
          </div>
          <div class="col-12">
            <button class="btn btn-outline-primary" name="stok_adjust">
              <i class="bi bi-upload me-1"></i>Proses
            </button>
            <div class="muted-mini mt-2">Catatan: “stok keluar” cocok untuk alat rusak/hilang, total & tersedia akan dikurangi.</div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="glass rounded-xxl p-3 mt-3 table-responsive">
    <table class="table table-sm align-middle mb-0">
      <thead>
        <tr>
          <th>Kode</th><th>Nama</th><th>Kategori</th><th>Lokasi</th>
          <th>Total</th><th>Tersedia</th><th>Kondisi</th><th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($alat as $a): ?>
          <tr>
            <td class="fw-semibold"><?= esc($a['kode']) ?></td>
            <td><?= esc($a['nama']) ?></td>
            <td><?= esc($a['kategori']) ?></td>
            <td><?= esc($a['lokasi']) ?></td>
            <td><?= (int)$a['stok_total'] ?></td>
            <td><?= (int)$a['stok_tersedia'] ?></td>
            <td>
              <?php
                $k = $a['kondisi'];
                $cls = $k==='baik'?'success':($k==='rusak'?'danger':'warning');
              ?>
              <span class="badge text-bg-<?= $cls ?>"><?= esc($k) ?></span>
            </td>
            <td>
              <a class="btn btn-sm btn-outline-danger" href="?hapus=<?= (int)$a['id'] ?>"
                 onclick="return confirm('Hapus alat ini?')">
                 <i class="bi bi-trash"></i>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if(!$alat): ?>
          <tr><td colspan="8" class="text-muted">Belum ada data.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php page_foot(); ?>
