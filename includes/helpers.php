<?php
require_once __DIR__ . '/db.php';

function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_check(): void {
    $t = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!$t || !hash_equals($_SESSION['csrf'] ?? '', $t)) json_out(['status'=>'error','code'=>'CSRF','message'=>'Session expired. Please reload the page.'], 403);
}
function json_out(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
function api_boot(string $endpoint, string $method): void {
    set_exception_handler(function ($ex) {
        error_log($ex->getMessage());
        json_out(['status'=>'error','code'=>'SERVER_ERROR','message'=>'Something went wrong. Please try again later.'], 500);
    });
    if ($_SERVER['REQUEST_METHOD'] !== $method) json_out(['status'=>'error','code'=>'METHOD','message'=>'Method not allowed.'], 405);
    rate_limit($endpoint);
    if ($method === 'POST') csrf_check();
}
function rate_limit(string $endpoint): void {
    $ip = substr($_SERVER['REMOTE_ADDR'] ?? '0', 0, 45); $now = time(); $pdo = db();
    $s = $pdo->prepare('SELECT request_count,last_request FROM rate_limits WHERE ip_address=? AND endpoint=?');
    $s->execute([$ip, $endpoint]); $r = $s->fetch();
    if (!$r) { $pdo->prepare('INSERT INTO rate_limits (ip_address,endpoint,request_count,last_request) VALUES (?,?,1,?)')->execute([$ip,$endpoint,$now]); return; }
    if ($now - $r['last_request'] > 60) { $pdo->prepare('UPDATE rate_limits SET request_count=1,last_request=? WHERE ip_address=? AND endpoint=?')->execute([$now,$ip,$endpoint]); return; }
    if ($r['request_count'] >= RATE_LIMIT_PER_MIN) { header('Retry-After: 60'); json_out(['status'=>'error','code'=>'RATE_LIMITED','message'=>'Too many requests. Please wait a minute.'], 429); }
    $pdo->prepare('UPDATE rate_limits SET request_count=request_count+1 WHERE ip_address=? AND endpoint=?')->execute([$ip,$endpoint]);
}
function http_get(string $url): ?array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_TIMEOUT=>6, CURLOPT_CONNECTTIMEOUT=>4, CURLOPT_SSL_VERIFYPEER=>true]);
    $body = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    return ($body !== false && $code === 200) ? (json_decode($body, true) ?: null) : null;
}
/** Returns ['rates'=>[...], 'fetched_at'=>int, 'source'=>'cache|open.er-api.com|frankfurter.dev|stale-cache'] or null */
function get_rates(string $base = 'USD'): ?array {
    $pdo = db(); $now = time();
    $s = $pdo->prepare('SELECT rates_json,fetched_at FROM rate_cache WHERE base_currency=? ORDER BY fetched_at DESC LIMIT 1');
    $s->execute([$base]); $c = $s->fetch();
    if ($c && $now - $c['fetched_at'] < RATE_TTL) return ['rates'=>json_decode($c['rates_json'],true),'fetched_at'=>(int)$c['fetched_at'],'source'=>'cache'];

    $rates = null; $src = '';
    $d = http_get('https://open.er-api.com/v6/latest/' . $base);
    if ($d && ($d['result'] ?? '') === 'success' && !empty($d['rates'])) { $rates = $d['rates']; $src = 'open.er-api.com'; }
    if (!$rates) {
        $d = http_get('https://api.frankfurter.dev/v1/latest?base=' . $base);
        if ($d && !empty($d['rates'])) { $rates = $d['rates'] + [$base => 1.0]; $src = 'frankfurter.dev'; }
    }
    if ($rates) {
        $rates = array_intersect_key($rates, array_flip(CURRENCIES));
        $pdo->prepare('INSERT INTO rate_cache (base_currency,rates_json,fetched_at) VALUES (?,?,?)')->execute([$base, json_encode($rates), $now]);
        $pdo->prepare('DELETE FROM rate_cache WHERE base_currency=? AND fetched_at < ?')->execute([$base, $now - 86400 * 7]);
        return ['rates'=>$rates,'fetched_at'=>$now,'source'=>$src];
    }
    return $c ? ['rates'=>json_decode($c['rates_json'],true),'fetched_at'=>(int)$c['fetched_at'],'source'=>'stale-cache'] : null;
}
