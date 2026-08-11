<?php
require_once __DIR__ . "/config/app.php"; if(session_status()!==PHP_SESSION_ACTIVE)session_start();
if(!isset($_SESSION["id_user"])) redirect("auth/login.php");
redirect($_SESSION["role"]==="admin"?"admin/dashboard.php":"user/dashboard.php");
