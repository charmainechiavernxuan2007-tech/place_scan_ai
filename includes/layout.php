<?php
require_once __DIR__ . '/helpers.php';
if (FORCE_HTTPS && empty($_SERVER['HTTPS']) && ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') !== 'https' && PHP_SAPI !== 'cli-server' && !in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost','127.0.0.1'], true)) { header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], true, 301); exit; }
header('X-Frame-Options: SAMEORIGIN'); header('X-Content-Type-Options: nosniff'); header('Referrer-Policy: same-origin');
header('Permissions-Policy: camera=(self)');
function page_head(string $title, string $active): void { ?>
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf" content="<?= e(csrf_token()) ?>">
<title><?= e($title) ?> · Plate Scan AI</title>
<link rel="stylesheet" href="assets/css/style.css">
</head><body>
<header class="top"><div class="wrap bar">
  <a class="logo" href="index.php">▣ Plate Scan AI</a>
  <nav>
    <a href="index.php" class="<?= $active==='scan'?'on':'' ?>" data-i18n="nav_scan">Scan</a>
    <a href="vehicles.php" class="<?= $active==='vehicles'?'on':'' ?>" data-i18n="nav_vehicles">Vehicles</a>
    <a href="manual.php" class="<?= $active==='manual'?'on':'' ?>" data-i18n="nav_manual">Manual</a>
    <select id="lang" aria-label="Language"><option value="en">English</option><option value="zh">中文</option></select>
  </nav>
</div></header>
<main class="wrap">
<?php }
function page_foot(): void { ?>
</main>
<footer class="wrap foot">© <?= date('Y') ?> Plate Scan AI</footer>
<script src="assets/js/i18n.js"></script>
<script src="assets/js/app.js"></script>
</body></html>
<?php }
