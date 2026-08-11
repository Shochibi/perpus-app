<?php
require_once __DIR__ . "/../config/app.php";
if(session_status()!==PHP_SESSION_ACTIVE) session_start();
$_SESSION=[]; session_destroy(); redirect("auth/login.php");
