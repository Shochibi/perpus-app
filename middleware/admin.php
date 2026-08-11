<?php
require_once __DIR__ . "/auth.php";
if ($_SESSION["role"] !== "admin") redirect("user/dashboard.php");
