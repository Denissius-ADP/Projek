<?php
require_once __DIR__ . "/helpers.php";
require_once __DIR__ . "/ui.php";

if(auth_user()) redirect("/index.php");

if(isset($_POST['login'])){
  csrf_check();
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';

  $user = db_one("SELECT * FROM users WHERE username=? LIMIT 1","s",[$username]);

  if($user && password_verify($password,$user['password_hash'])){
    $_SESSION['user']=[
      'id'=>(int)$user['id'],
      'nama'=>$user['nama'],
      'username'=>$user['username'],
      'role'=>$user['role']
    ];
    log_activity("LOGIN","User login: {$user['username']}",(int)$user['id']);
    flash_set("Login berhasil. Selamat datang, {$user['nama']}!");
    redirect("/index.php");
  } else {
    flash_set("Username atau password salah.","err");
  }
}

page_head("Login - LabTools");
?>
<div class="row justify-content-center">
  <div class="col-lg-10">
    <div class="glass rounded-xxl shadow-soft overflow-hidden">
      <div class="row g-0">
        <div class="col-md-6 p-4" style="background:linear-gradient(135deg, rgba(14,165,233,.22), rgba(99,102,241,.22));">
          <h2 class="fw-bold mb-2"><span class="brand-badge">LabTools</span> Keperawatan</h2>
          <p class="text-dark-emphasis">Sistem peminjaman alat lab: stok, approval, pengembalian, audit log.</p>

          <div class="mt-4 d-flex gap-3">
            <div class="glass rounded-xxl p-3 flex-fill card-hover">
              <div class="fw-semibold"><i class="bi bi-shield-check me-1"></i>RBAC</div>
              <div class="muted-mini">Role admin & user aman</div>
            </div>
            <div class="glass rounded-xxl p-3 flex-fill card-hover">
              <div class="fw-semibold"><i class="bi bi-activity me-1"></i>Audit Log</div>
              <div class="muted-mini">Semua aktivitas tercatat</div>
            </div>
          </div>

          <div class="mt-3 muted-mini">
            Demo: <b>admin/admin123</b> · <b>siti/user123</b>
          </div>
        </div>

        <div class="col-md-6 p-4 bg-white">
          <h4 class="fw-bold mb-1">Masuk</h4>
          <div class="text-muted mb-4">Gunakan akun sesuai role.</div>

          <form method="post" class="d-grid gap-3">
            <input type="hidden" name="_csrf" value="<?= esc(csrf_token()) ?>">

            <div>
              <label class="form-label">Username</label>
              <input name="username" class="form-control form-control-lg input-soft" required>
            </div>

            <div>
              <label class="form-label">Password</label>
              <input name="password" type="password" class="form-control form-control-lg input-soft" required>
            </div>

            <button name="login" class="btn btn-primary btn-lg">
              <i class="bi bi-box-arrow-in-right me-1"></i> Login
            </button>

            <div class="muted-mini">© <?= date('Y') ?> Lab Keperawatan</div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<?php page_foot(); ?>
