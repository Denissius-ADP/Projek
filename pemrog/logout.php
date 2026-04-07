<?php
require_once __DIR__ . "/helpers.php";
$u = auth_user();
if($u) log_activity("LOGOUT","User logout: ".$u['username'], $u['id']);
session_destroy();
session_start();
flash_set("Logout berhasil.");
redirect("/login.php");
