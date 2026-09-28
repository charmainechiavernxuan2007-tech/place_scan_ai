<?php
require_once __DIR__ . '/../includes/helpers.php';
api_boot('vehicles', 'GET');
$q = mb_substr(trim((string)($_GET['q'] ?? '')), 0, 60);
$type = (string)($_GET['type'] ?? '');
$from = (string)($_GET['from'] ?? ''); $to = (string)($_GET['to'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1)); $per = 10;

$w = []; $p = [];
if ($q !== '') {
    $like = '%' . addcslashes($q, '%_\\') . '%';
    $w[] = '(plate_number LIKE ? OR owner_name LIKE ? OR make LIKE ? OR model LIKE ? OR CONCAT(make," ",model) LIKE ?)';
    array_push($p, $like, $like, $like, $like, $like);
}
if (in_array($type, BODY_TYPES, true)) { $w[] = 'body_type = ?'; $p[] = $type; }
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) { $w[] = 'created_at >= ?'; $p[] = $from . ' 00:00:00'; }
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $to))   { $w[] = 'created_at <= ?'; $p[] = $to . ' 23:59:59'; }
$where = $w ? 'WHERE ' . implode(' AND ', $w) : '';

$pdo = db();
$c = $pdo->prepare("SELECT COUNT(*) FROM vehicles $where"); $c->execute($p); $total = (int)$c->fetchColumn();
$s = $pdo->prepare("SELECT plate_number,owner_name,make,model,color,body_type,base_fee,currency,fee_converted,created_at FROM vehicles $where ORDER BY id DESC LIMIT $per OFFSET " . (($page - 1) * $per));
$s->execute($p);
json_out(['status'=>'success','data'=>['items'=>$s->fetchAll(),'page'=>$page,'per_page'=>$per,'total'=>$total,'pages'=>max(1,(int)ceil($total/$per))]]);
