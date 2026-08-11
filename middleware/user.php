<?php
require_once __DIR__ . "/auth.php";
if ($_SESSION["role"] !== "user") redirect("admin/dashboard.php");
