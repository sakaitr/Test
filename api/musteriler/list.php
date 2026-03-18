<?php
require_once '../../config.php';

$pdo    = getDB();
$draw   = intInput('draw', 1);
$start  = intInput('start', 0);
$length = intInput('length', 25);
$search   = input('search[value]');
$firmaFilter = intInput('firma_id', 0);
$orderColIdx = intInput('order[0][column]', 0);
$orderDir    = strtoupper(input('order[0][dir]', 'DESC')) === 'DESC' ? 'DESC' : 'ASC';

$columns     = ['m.id', 'm.musteri_adi', 'm.telefon', 'm.adres', 'f.firma_adi', 'm.created_at'];
$orderColumn = $columns[$orderColIdx] ?? 'm.id';

$baseQuery = "FROM musteriler m JOIN firmalar f ON f.id = m.firma_id";

$total = (int) $pdo->query("SELECT COUNT(*) $baseQuery")->fetchColumn();

$conditions = [];
$params     = [];

if ($firmaFilter > 0) {
    $conditions[] = "m.firma_id = ?";
    $params[]     = $firmaFilter;
}
if ($search !== '') {
    $conditions[] = "(m.musteri_adi LIKE ? OR f.firma_adi LIKE ? OR m.telefon LIKE ?)";
    $params[]     = "%$search%";
    $params[]     = "%$search%";
    $params[]     = "%$search%";
}

$where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

$filteredStmt = $pdo->prepare("SELECT COUNT(*) $baseQuery $where");
$filteredStmt->execute($params);
$filtered = (int) $filteredStmt->fetchColumn();

$params[] = $length;
$params[] = $start;
$stmt = $pdo->prepare(
    "SELECT m.id, m.musteri_adi, m.telefon, m.adres, m.firma_id,
            f.firma_adi, m.created_at
     $baseQuery
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
