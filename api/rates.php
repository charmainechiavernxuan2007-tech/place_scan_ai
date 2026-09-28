<?php
require_once __DIR__ . '/../includes/helpers.php';
api_boot('rates', 'GET');
$r = get_rates('USD');
if (!$r) json_out(['status'=>'error','code'=>'RATES_UNAVAILABLE','message'=>'Exchange rates are temporarily unavailable. Please try again later.'], 503);
json_out(['status'=>'success','data'=>['base'=>'USD','base_fee'=>BASE_FEE_USD,'rates'=>$r['rates'],'fetched_at'=>$r['fetched_at'],'source'=>$r['source'],'stale'=>$r['source']==='stale-cache']]);
