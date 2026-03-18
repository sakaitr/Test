<?php
require_once '../../config.php';

$pdo = getDB();

$firma_count   = (int) $pdo->query("SELECT COUNT(*) FROM firmalar")->fetchColumn();
$musteri_count = (int) $pdo->query("SELECT COUNT(*) FROM musteriler")->fetchColumn();
$sevk_count    = (int) $pdo->query("SELECT COUNT(*) FROM sevkiyatlar")->fetchColumn();

$row = $pdo->query(
    "SELECT
        COALESCE(SUM(CASE WHEN tur='giris' THEN tutar ELSE 0 END), 0) AS toplam_giris,
        COALESCE(SUM(CASE WHEN tur='cikis' THEN tutar ELSE 0 END), 0) AS toplam_cikis
     FROM faturalar"
)->fetch();

$giris = (float) $row['toplam_giris'];
$cikis = (float) $row['toplam_cikis'];

$sonFaturalar = $pdo->query(
    "SELECT f.id, f.fatura_no, f.tarih, f.tur, f.tutar, m.musteri_adi, fi.firma_adi
     FROM faturalar f
     JOIN musteriler m  ON m.id  = f.musteri_id
     JOIN firmalar   fi ON fi.id = f.firma_id
     ORDER BY f.created_at DESC
     LIMIT 10"
)->fetchAll();

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'firma_count'   => $firma_count,
    'musteri_count' => $musteri_count,
    'sevk_count'    => $sevk_count,
    'toplam_giris'  => $giris,
    'toplam_cikis'  => $cikis,
    'net'           => $giris - $cikis,
    'son_faturalar' => $sonFaturalar,
], JSON_UNESCAPED_UNICODE);
