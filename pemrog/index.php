<?php
// update fitur login
require_once __DIR__ . "/helpers.php";   // WAJIB ada ini

$u = auth_user();
if(!$u) redirect("/login.php");

if(($u['role'] ?? '') === 'admin'){
  redirect("/admin/dashboard.php");
}
redirect("/user/dashboard.php");
