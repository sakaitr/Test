<?php
require_once '../../config.php';

$pdo           = getDB();
$musteriFilter = intInput('musteri_id', 0);
$firmaFilter   = intInput('firma_id', 0);
$sevkFilter    = intInput('sevkiyat_id', 0);
$turFilter     = input('tur');
$tarihBas      = input('tarih_baslangic');
$tarihBit      = input('tarih_bitis');
$search        = input('search[value]');

$baseQuery = "FROM faturalar f
              JOIN musteriler m  ON m.id  = f.musteri_id
              JOIN firmalar   fi ON fi.id = f.firma_id";

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

$stmt = $pdo->prepare(
    "SELECT
        COALESCE(SUM(CASE WHEN f.tur='giris' THEN f.tutar ELSE 0 END), 0) AS toplam_giris,
        COALESCE(SUM(CASE WHEN f.tur='cikis' THEN f.tutar ELSE 0 END), 0) AS toplam_cikis
     $baseQuery $where"
);
$stmt->execute($params);
$row = $stmt->fetch();

$giris = (float) $row['toplam_giris'];
$cikis = (float) $row['toplam_cikis'];

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'toplam_giris' => $giris,
    'toplam_cikis' => $cikis,
    'net'          => $giris - $cikis,
], JSON_UNESCAPED_UNICODE);
