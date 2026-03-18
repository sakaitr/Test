<?php
require_once '../../config.php';

$pdo    = getDB();
$draw   = intInput('draw', 1);
$start  = intInput('start', 0);
$length = intInput('length', 25);
$search = input('search[value]');
$orderColIdx = intInput('order[0][column]', 0);
$orderDir    = strtoupper(input('order[0][dir]', 'DESC')) === 'DESC' ? 'DESC' : 'ASC';

$columns     = ['id', 'firma_adi', 'telefon', 'adres', 'created_at'];
$orderColumn = $columns[$orderColIdx] ?? 'id';

$total = (int) $pdo->query("SELECT COUNT(*) FROM firmalar")->fetchColumn();

$where  = '';
$params = [];
if ($search !== '') {
    $where    = "WHERE firma_adi LIKE ? OR telefon LIKE ?";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$filteredStmt = $pdo->prepare("SELECT COUNT(*) FROM firmalar $where");
$filteredStmt->execute($params);
$filtered = (int) $filteredStmt->fetchColumn();

$params[] = $length;
$params[] = $start;
$stmt = $pdo->prepare(
    "SELECT id, firma_adi, telefon, adres, created_at
       FROM firmalar
     $where
     ORDER BY $orderColumn $orderDir
     LIMIT ? OFFSET ?"
);
$stmt->execute($params);
$data = $stmt->fetchAll();

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'draw'            => $draw,
    'recordsTotal'    => $total,
    'recordsFiltered' => $filtered,
    'data'            => $data,
], JSON_UNESCAPED_UNICODE);
