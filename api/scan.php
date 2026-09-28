<?php
require_once __DIR__ . '/../includes/helpers.php';
api_boot('scan', 'POST');

$fail = fn($code, $msg, $http = 422) => json_out(['status'=>'error','code'=>$code,'message'=>$msg], $http);
if (empty($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) $fail('UPLOAD_FAILED', 'Image upload failed. Please try again.', 400);
$file = $_FILES['image'];
if ($file['size'] > 4 * 1024 * 1024) $fail('FILE_TOO_LARGE', 'Image is too large (max 4 MB).', 413);
$mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
if (!in_array($mime, ['image/jpeg','image/png','image/webp'], true) || !($dim = @getimagesize($file['tmp_name']))) $fail('BAD_IMAGE', 'Please upload a JPG, PNG or WebP image.', 415);

/** Pick the most plate-like token from OCR words: [['text','l','t','w','h','conf'], ...] */
function best_plate(array $words): ?array {
    $best = null;
    foreach ($words as $w) {
        $t = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $w['text']));
        if (strlen($t) < 3 || strlen($t) > 10 || !preg_match('/[A-Z]/', $t) || !preg_match('/\d/', $t)) continue;
        $score = strlen($t) + (preg_match('/^[A-Z]{1,3}\d{1,4}[A-Z]?$/', $t) ? 10 : 0);
        if (!$best || $score > $best['score']) $best = $w + ['plate'=>$t, 'score'=>$score];
    }
    return $best;
}
$words = []; $engine = '';

// 1) OCR.space (works on shared hosting with cURL)
$ch = curl_init('https://api.ocr.space/parse/image');
curl_setopt_array($ch, [CURLOPT_POST=>true, CURLOPT_RETURNTRANSFER=>true, CURLOPT_TIMEOUT=>20, CURLOPT_CONNECTTIMEOUT=>5,
    CURLOPT_POSTFIELDS=>['apikey'=>OCR_SPACE_KEY,'isOverlayRequired'=>'true','OCREngine'=>'2','scale'=>'true','file'=>new CURLFile($file['tmp_name'], $mime, 'plate')]]);
$body = curl_exec($ch); curl_close($ch);
$d = $body ? json_decode($body, true) : null;
if ($d && empty($d['IsErroredOnProcessing']) && !empty($d['ParsedResults'][0]['TextOverlay']['Lines'])) {
    $engine = 'ocr.space';
    foreach ($d['ParsedResults'][0]['TextOverlay']['Lines'] as $ln) foreach ($ln['Words'] as $w)
        $words[] = ['text'=>$w['WordText'],'l'=>$w['Left'],'t'=>$w['Top'],'w'=>$w['Width'],'h'=>$w['Height'],'conf'=>null];
}

// 2) Optional local Tesseract fallback
if (!$words && TESSERACT_PATH !== '' && function_exists('shell_exec')) {
    $out = shell_exec(escapeshellcmd(TESSERACT_PATH) . ' ' . escapeshellarg($file['tmp_name']) . ' stdout --psm 7 -c tessedit_char_whitelist=ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789 tsv 2>/dev/null');
    foreach (explode("\n", (string)$out) as $row) {
        $c = explode("\t", $row);
        if (count($c) >= 12 && (int)$c[0] === 5 && trim($c[11]) !== '' && (float)$c[10] > 0)
            $words[] = ['text'=>$c[11],'l'=>(int)$c[6],'t'=>(int)$c[7],'w'=>(int)$c[8],'h'=>(int)$c[9],'conf'=>(float)$c[10]/100];
    }
    if ($words) $engine = 'tesseract';
}
if (!$engine) { error_log('OCR unavailable: ' . substr((string)$body, 0, 200)); $fail('SCAN_UNAVAILABLE', 'The scanner is temporarily unavailable. You can type the plate manually.', 503); }

$b = best_plate($words);
if (!$words) $fail('NO_PLATE_DETECTED', 'No plate detected. Adjust lighting or framing so the plate fills the guide box.');
if (!$b) $fail('PLATE_UNREADABLE', 'Unable to read license plate clearly. Please adjust lighting or reposition the camera.');

// OCR.space gives no per-word confidence; estimate from plate-pattern fit.
$conf = $b['conf'] ?? (preg_match('/^[A-Z]{1,3}\d{1,4}[A-Z]?$/', $b['plate']) ? 0.92 : 0.7);
json_out(['status'=>'success','data'=>[
    'plate_number'=>$b['plate'], 'confidence'=>round($conf, 2), 'engine'=>$engine,
    'bbox'=>['x'=>(int)$b['l'],'y'=>(int)$b['t'],'w'=>(int)$b['w'],'h'=>(int)$b['h']],
    'image'=>['w'=>$dim[0],'h'=>$dim[1]],
]]);
