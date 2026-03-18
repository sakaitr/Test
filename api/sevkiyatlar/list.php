<?php
require_once '../../config.php';

$pdo    = getDB();
$draw   = intInput('draw', 1);
$start  = intInput('start', 0);
$length = intInput('length', 25);
$search       = input('search[value]');
$musteriFilter = intInput('musteri_id', 0);
$firmaFilter   = intInput('firma_id', 0);
$durumFilter   = input('durum');
$tarihBas      = input('tarih_baslangic');
$tarihBit      = input('tarih_bitis');
$orderColIdx   = intInput('order[0][column]', 0);
$orderDir      = strtoupper(input('order[0][dir]', 'DESC')) === 'DESC' ? 'DESC' : 'ASC';

$columns     = ['s.id', 'm.musteri_adi', 'f.firma_adi', 's.tarih', 's.aciklama', 's.durum', 's.created_at'];
$orderColumn = $columns[$orderColIdx] ?? 's.id';

$baseQuery = "FROM sevkiyatlar s
              JOIN musteriler m ON m.id = s.musteri_id
              JOIN firmalar   f ON f.id = s.firma_id";

$total = (int) $pdo->query("SELECT COUNT(*) $baseQuery")->fetchColumn();

$conditions = [];
$params     = [];

if ($musteriFilter > 0) { $conditions[] = "s.musteri_id = ?"; $params[] = $musteriFilter; }
if ($firmaFilter > 0)   { $conditions[] = "s.firma_id = ?";   $params[] = $firmaFilter; }
if ($durumFilter !== '') { $conditions[] = "s.durum = ?";       $params[] = $durumFilter; }
if ($tarihBas !== '')   { $conditions[] = "s.tarih >= ?";      $params[] = $tarihBas; }
if ($tarihBit !== '')   { $conditions[] = "s.tarih <= ?";      $params[] = $tarihBit; }
if ($search !== '') {
    $conditions[] = "(m.musteri_adi LIKE ? OR f.firma_adi LIKE ? OR s.aciklama LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%";
}

$where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

$filteredStmt = $pdo->prepare("SELECT COUNT(*) $baseQuery $where");
$filteredStmt->execute($params);
$filtered = (int) $filteredStmt->fetchColumn();

$params[] = $length;
$params[] = $start;
$stmt = $pdo->prepare(
    "SELECT s.id, s.musteri_id, m.musteri_adi, s.firma_id, f.firma_adi,
            s.tarih, s.aciklama, s.durum, s.created_at
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
