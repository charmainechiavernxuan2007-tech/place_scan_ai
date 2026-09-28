<?php
require_once __DIR__ . '/../includes/helpers.php';
api_boot('register', 'POST');
$in = json_decode(file_get_contents('php://input'), true);
if (!is_array($in)) json_out(['status'=>'error','code'=>'BAD_INPUT','message'=>'Invalid request.'], 400);
$f = fn($k, $n) => mb_substr(trim(preg_replace('/\s+/u', ' ', (string)($in[$k] ?? ''))), 0, $n);

$plate = strtoupper(preg_replace('/[\s-]+/', '', $f('plate_number', 20)));
$owner = $f('owner_name', 100); $phone = $f('phone', 20);
$make = $f('make', 50); $model = $f('model', 50); $color = $f('color', 30);
$body = $f('body_type', 30); $cur = strtoupper($f('currency', 3));

$err = [];
if (!preg_match('/^[A-Z0-9]{2,10}$/', $plate)) $err['plate_number'] = 'Plate must be 2-10 letters/digits.';
if (!preg_match('/^[\p{L}\p{M}\' .,\-@\/]{2,100}$/u', $owner)) $err['owner_name'] = 'Enter a valid full name.';
if (!preg_match('/^\+?[0-9][0-9 \-]{6,18}$/', $phone)) $err['phone'] = 'Enter a valid phone number.';
foreach (['make'=>$make,'model'=>$model] as $k=>$v) if (!preg_match('/^[\p{L}\p{N} .\-]{1,50}$/u', $v)) $err[$k] = 'Required; letters/digits only.';
if (!preg_match('/^[\p{L} \-]{2,30}$/u', $color)) $err['color'] = 'Enter a valid color.';
if (!in_array($body, BODY_TYPES, true)) $err['body_type'] = 'Choose a body type.';
if (!in_array($cur, CURRENCIES, true)) $err['currency'] = 'Unsupported currency.';
if ($err) json_out(['status'=>'error','code'=>'VALIDATION','message'=>'Please correct the highlighted fields.','fields'=>$err], 422);

$r = get_rates('USD');
if (!$r || empty($r['rates'][$cur])) json_out(['status'=>'error','code'=>'RATES_UNAVAILABLE','message'=>'Exchange rates are unavailable. Try again shortly.'], 503);
$fee = round(BASE_FEE_USD * (float)$r['rates'][$cur], 2);   // computed server-side; client value is never trusted

try {
    db()->prepare('INSERT INTO vehicles (plate_number,owner_name,phone,make,model,color,body_type,base_fee,currency,fee_converted) VALUES (?,?,?,?,?,?,?,?,?,?)')
        ->execute([$plate,$owner,$phone,$make,$model,$color,$body,BASE_FEE_USD,$cur,$fee]);
} catch (PDOException $ex) {
    if ($ex->getCode() === '23000') json_out(['status'=>'error','code'=>'DUPLICATE_PLATE','message'=>'This plate is already registered.','fields'=>['plate_number'=>'Already registered.']], 409);
    throw $ex;
}
json_out(['status'=>'success','data'=>['plate_number'=>$plate,'fee_converted'=>$fee,'currency'=>$cur]], 201);
