<?php
// ---- Configuration: edit for your host ----
define('DB_HOST', 'localhost');
define('DB_NAME', 'plate_scan');
define('DB_USER', 'root');
define('DB_PASS', '');
define('BASE_FEE_USD', 50.00);
define('RATE_TTL', 3600);
define('RATE_LIMIT_PER_MIN', 30);
define('OCR_SPACE_KEY', 'helloworld');      // free key from ocr.space (replace for production)
define('TESSERACT_PATH', '');               // optional local fallback, e.g. /usr/bin/tesseract
define('CURRENCIES', ['USD','MYR','SGD','EUR','GBP','CNY','JPY','AUD','THB','IDR','INR']);
define('BODY_TYPES', ['Sedan','SUV','Hatchback','Pickup','Van','Motorcycle','Lorry']);
define('FORCE_HTTPS', true);

ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/error.log');
error_reporting(E_ALL);
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS'])]);
if (session_status() === PHP_SESSION_NONE) session_start();
