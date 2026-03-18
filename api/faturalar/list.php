<?php
require_once '../../config.php';

$pdo    = getDB();
$draw   = intInput('draw', 1);
$start  = intInput('start', 0);
$length = intInput('length', 25);
$search        = input('search[value]');
$musteriFilter = intInput('musteri_id', 0);
$firmaFilter   = intInput('firma_id', 0);
$sevkFilter    = intInput('sevkiyat_id', 0);
$turFilter     = input('tur');
$tarihBas      = input('tarih_baslangic');
$tarihBit      = input('tarih_bitis');
$orderColIdx   = intInput('order[0][column]', 0);
$orderDir      = strtoupper(input('order[0][dir]', 'DESC')) === 'DESC' ? 'DESC' : 'ASC';

$columns     = ['f.id', 'f.fatura_no', 'f.sevkiyat_id', 'm.musteri_adi', 'fi.firma_adi', 'f.tarih', 'f.tur', 'f.tutar', 'f.created_at'];
$orderColumn = $columns[$orderColIdx] ?? 'f.id';

$baseQuery = "FROM faturalar f
              JOIN musteriler m  ON m.id  = f.musteri_id
              JOIN firmalar   fi ON fi.id = f.firma_id
              LEFT JOIN sevkiyatlar s ON s.id = f.sevkiyat_id";

$total = (int) $pdo->query("SELECT COUNT(*) $baseQuery")->fetchColumn();

$conditions = [];
$params     = [];

if ($musteriFilter > 0) { $conditions[] = "f.musteri_id = ?";  $params[] = $musteriFilter; }
if ($firmaFilter > 0)   { $conditions[] = "f.firma_id = ?";    $params[] = $firmaFilter; }
if ($sevkFilter > 0)    { $conditions[] = "f.sevkiyat_id = ?"; $params[] = $sevkFilter; }
if ($turFilter !== '')  { $conditions[] = "f.tur = ?";          $params[] = $turFilter; }
if ($tarihBas !== '')   { $conditions[] = "f.tarih >= ?";       $params[] = $tarihBas; }
if ($tarihBit !== '')   { $conditions[] = "f.tarih <= ?";       $params[] = $tarihBit; }
if ($search !== '') {
    $conditions[] = "(f.fatura_no LIKE ? OR m.musteri_adi LIKE ? OR fi.firma_adi LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%";
}

$where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

$filteredStmt = $pdo->prepare("SELECT COUNT(*) $baseQuery $where");
$filteredStmt->execute($params);
$filtered = (int) $filteredStmt->fetchColumn();

$params[] = $length;
$params[] = $start;
$stmt = $pdo->prepare(
    "SELECT f.id, f.fatura_no, f.sevkiyat_id, f.musteri_id, m.musteri_adi,
            f.firma_id, fi.firma_adi, f.tarih, f.tur, f.tutar, f.aciklama, f.created_at
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
