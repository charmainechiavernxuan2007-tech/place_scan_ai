# Plate Scan AI

PHP 8 + MySQL + vanilla JS license-plate scanner and vehicle registry (no frameworks).

## Features
- Camera (WebRTC) capture or drag-and-drop upload → OCR → plate auto-fill with bounding box and confidence
- Manual vehicle form, server-validated; fee converted with live rates (open.er-api.com → frankfurter.dev → stale cache), cached 1 h in MySQL
- Searchable, paginated registry (plate / owner / make / model, type and date filters)
- English / 中文 (i18n.js, saved in localStorage)
- Security: PDO prepared statements, output escaping, CSRF token on POST, HTTPS redirect, 30 req/min/IP limit, private error logging (`includes/error.log`)

## Install
1. Copy the project to your web root (XAMPP: `htdocs/plate`).
2. Import the schema: `mysql -u root -p < includes/schema.sql` (or phpMyAdmin → Import). On shared hosting, create the DB in the control panel first and remove the `CREATE DATABASE`/`USE` lines.
3. Edit `includes/config.php`: `DB_*`, `OCR_SPACE_KEY`, optional `TESSERACT_PATH`, `BASE_FEE_USD`, `CURRENCIES`.
4. Open `https://localhost/plate/` (camera needs HTTPS or `localhost`). For local HTTP set `FORCE_HTTPS` to false.

## OCR
`api/scan.php` uses the OCR.space API (cURL). The bundled `helloworld` key is for testing only and is heavily limited: get a free key at ocr.space and set `OCR_SPACE_KEY`. If a local `tesseract` binary exists and `shell_exec` is enabled, set `TESSERACT_PATH` as fallback. OCR.space returns no confidence value, so confidence is estimated from how well the text matches a plate pattern.

## iFastNet deployment
1. Upload files via FTP into `htdocs/`. 2. Create a MySQL DB/user in the panel, import `schema.sql`. 3. Set credentials (host is the panel's MySQL host, not `localhost`). 4. Enable free SSL in the panel; `.htaccess` forces HTTPS. 5. Ensure `includes/error.log` is writable.

## Troubleshooting
- **Camera blocked**: requires HTTPS/localhost and browser permission; use upload instead.
- **cURL errors / no rates**: check `curl` and `allow_url_fopen` in PHP settings; free hosts may block outbound requests. Stale cached rates are used when possible.
- **SSL errors from cURL**: update the host's CA bundle (`curl.cainfo`); don't disable verification.
- **SCAN_UNAVAILABLE**: OCR key/limit or blocked outbound requests; see `includes/error.log`.
- **HTTP 500**: check `includes/error.log`.
