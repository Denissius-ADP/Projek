<?php
require_once __DIR__ . "/helpers.php";

function page_head($title){
  $u = auth_user();
  $f = flash_get();
  ?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= esc($title) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= esc(asset('app.css')) ?>">
  <style>
    :root{
      --bg1:#0ea5e9; --bg2:#6366f1; --bg3:#22c55e;
      --card: rgba(255,255,255,.86);
    }
    body{font-family:Inter,system-ui,Segoe UI,Arial; background:
      radial-gradient(1200px 600px at 10% 10%, rgba(34,197,94,.25), transparent 60%),
      radial-gradient(900px 500px at 90% 0%, rgba(99,102,241,.25), transparent 60%),
      radial-gradient(1000px 700px at 70% 90%, rgba(14,165,233,.25), transparent 60%),
      #0b1220;
      min-height:100vh;
    }
    .glass{background:var(--card); backdrop-filter: blur(10px); border:1px solid rgba(255,255,255,.35)}
    .rounded-xxl{border-radius: 1.25rem;}
    .brand-badge{
      background: linear-gradient(90deg, var(--bg1), var(--bg2));
      -webkit-background-clip:text; background-clip:text; color:transparent;
    }
    .shadow-soft{box-shadow: 0 12px 30px rgba(0,0,0,.18);}
    .table thead th{white-space:nowrap}
    .muted-mini{font-size:.86rem;color:#64748b}
  </style>
</head>
<body>
  <?php include __DIR__ . "/navbar.php"; ?>
  <div class="container py-4">
    <?php if($f): ?>
      <div class="alert <?= $f['type']==='err' ? 'alert-danger' : 'alert-success' ?> shadow-soft rounded-xxl">
        <?= esc($f['msg']) ?>
      </div>
    <?php endif; ?>
<?php
}

function page_foot(){
  ?>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
}
