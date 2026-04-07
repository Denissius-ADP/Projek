<?php
require_once __DIR__ . "/config.php"; // wajib: koneksi $koneksi + session + BASE_URL kalau ada

function esc($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

/** URL helper (aman untuk hosting subfolder) */
function base_url(){
  global $BASE_URL;
  return rtrim($BASE_URL ?? '', '/');
}
function url($path){
  $path = '/' . ltrim($path, '/');
  return base_url() . $path;
}
function asset($path){
  return url('/assets/' . ltrim($path,'/'));
}
function redirect($to){
  header("Location: " . url($to));
  exit;
}

/** Flash message */
function flash_set($msg,$type='ok'){ $_SESSION['_flash']=['msg'=>$msg,'type'=>$type]; }
function flash_get(){
  if(!empty($_SESSION['_flash'])){ $f=$_SESSION['_flash']; unset($_SESSION['_flash']); return $f; }
  return null;
}

/** Auth + RBAC */
function auth_user(){ return $_SESSION['user'] ?? null; }
function require_login(){ if(!auth_user()) redirect("/login.php"); }
function require_role($roles=[]){
  require_login();
  $role = auth_user()['role'] ?? '';
  if(!in_array($role,$roles,true)){
    flash_set("Akses ditolak (RBAC).","err");
    redirect("/index.php");
  }
}

/** CSRF */
function csrf_token(){
  if(empty($_SESSION['_csrf'])) $_SESSION['_csrf']=bin2hex(random_bytes(16));
  return $_SESSION['_csrf'];
}
function csrf_check(){
  $t = $_POST['_csrf'] ?? '';
  if(!$t || empty($_SESSION['_csrf']) || !hash_equals($_SESSION['_csrf'],$t)){
    flash_set("CSRF token tidak valid.","err");
    redirect($_SERVER['HTTP_REFERER'] ?? "/index.php");
  }
}

/** ===== DB helper (FIX by-reference) ===== */
function _stmt_bind_params(mysqli_stmt $st, string $types, array $params): void {
  $refs = [];
  foreach ($params as $k => $v) $refs[$k] = &$params[$k];
  array_unshift($refs, $types);
  call_user_func_array([$st, 'bind_param'], $refs);
}

function db_one($sql, $types = "", $params = []) {
  global $koneksi;
  $st = mysqli_prepare($koneksi, $sql);
  if (!$st) return null;

  if ($types !== "") _stmt_bind_params($st, $types, $params);

  mysqli_stmt_execute($st);
  $res = mysqli_stmt_get_result($st);
  return $res ? mysqli_fetch_assoc($res) : null;
}

function db_all($sql, $types = "", $params = []) {
  global $koneksi;
  $st = mysqli_prepare($koneksi, $sql);
  if (!$st) return [];

  if ($types !== "") _stmt_bind_params($st, $types, $params);

  mysqli_stmt_execute($st);
  $res = mysqli_stmt_get_result($st);

  $rows = [];
  if ($res) while ($r = mysqli_fetch_assoc($res)) $rows[] = $r;
  return $rows;
}

function db_exec($sql, $types = "", $params = []) {
  global $koneksi;
  $st = mysqli_prepare($koneksi, $sql);
  if (!$st) return false;

  if ($types !== "") _stmt_bind_params($st, $types, $params);

  return mysqli_stmt_execute($st);
}

/** Aktivitas log */
function log_activity($aksi,$detail="",$actor_user_id=null){
  if($actor_user_id===null) $actor_user_id = auth_user()['id'] ?? null;
  db_exec("INSERT INTO aktivitas(actor_user_id, aksi, detail) VALUES(?,?,?)","iss",
    [(int)$actor_user_id,$aksi,$detail]
  );
}

/** Badge status helper (buat tabel transaksi) */
function badge_status($st){
  $map = [
    'pending' => 'warning',
    'disetujui' => 'primary',
    'ditolak' => 'danger',
    'dikembalikan' => 'success',
  ];
  $c = $map[$st] ?? 'secondary';
  return "<span class='badge text-bg-$c'>".esc($st)."</span>";
}
