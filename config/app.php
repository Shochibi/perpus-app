<?php
define("BASE_URL", "/perpustakaan"); // sesuaikan dengan nama folder project

define("COVER_DIR", __DIR__ . "/../uploads/cover/");
define("PDF_DIR", __DIR__ . "/../uploads/pdf/");
function e($value): string { return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8"); }
function redirect(string $path): never { header("Location: " . BASE_URL . "/" . ltrim($path, "/")); exit; }
