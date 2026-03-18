<?php
require_once '../../config.php';

$pdo    = getDB();
$draw   = intInput('draw', 1);
$start  = intInput('start', 0);
$length = intInput('length', 25);
$search        = input('search[value]');
$musteriFilter = intInput('musteri_id', 0);
$turFilter     = input('tur');
$tarihBas      = input('tarih_baslangic');
$tarihBit      = input('tarih_bitis');
$orderColIdx   = intInput('order[0][column]', 0);
$orderDir      = strtoupper(input('order[0][dir]', 'ASC')) === 'ASC' ? 'ASC' : 'DESC';

$columns     = ['mh.id', 'm.musteri_adi', 'mh.tarih', 'mh.tur', 'mh.tutar', 'mh.aciklama', 'mh.created_at'];
$orderColumn = $columns[$orderColIdx] ?? 'mh.tarih';

$baseQuery = "FROM musteri_hareketler mh
              JOIN musteriler m ON m.id = mh.musteri_id";

$total = (int) $pdo->query("SELECT COUNT(*) $baseQuery")->fetchColumn();

$conditions = [];
$params     = [];

if ($musteriFilter > 0) { $conditions[] = "mh.musteri_id = ?"; $params[] = $musteriFilter; }
if ($turFilter !== '')  { $conditions[] = "mh.tur = ?";         $params[] = $turFilter; }
if ($tarihBas !== '')   { $conditions[] = "mh.tarih >= ?";      $params[] = $tarihBas; }
if ($tarihBit !== '')   { $conditions[] = "mh.tarih <= ?";      $params[] = $tarihBit; }
if ($search !== '') {
    $conditions[] = "(m.musteri_adi LIKE ? OR mh.aciklama LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%";
}

$where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

$filteredStmt = $pdo->prepare("SELECT COUNT(*) $baseQuery $where");
$filteredStmt->execute($params);
$filtered = (int) $filteredStmt->fetchColumn();

$params[] = $length;
$params[] = $start;
$stmt = $pdo->prepare(
    "SELECT mh.id, mh.musteri_id, m.musteri_adi, mh.tarih, mh.tur, mh.tutar, mh.aciklama, mh.created_at
     $baseQuery
     $where
     ORDER BY $orderColumn $orderDir, mh.id ASC
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
