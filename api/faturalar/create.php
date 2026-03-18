<?php
require_once '../../config.php';

$fatura_no   = input('fatura_no');
$sevkiyat_id = intInput('sevkiyat_id', 0);
$musteri_id  = intInput('musteri_id');
$firma_id    = intInput('firma_id');
$tarih       = input('tarih');
$tutar       = (float) input('tutar', '0');
$tur         = input('tur');
$aciklama    = input('aciklama');

if ($fatura_no === '')  jsonResponse(false, null, 'Fatura numarası zorunludur.');
if ($musteri_id <= 0)   jsonResponse(false, null, 'Müşteri seçimi zorunludur.');
if ($firma_id <= 0)     jsonResponse(false, null, 'Firma seçimi zorunludur.');
if ($tarih === '')      jsonResponse(false, null, 'Tarih zorunludur.');
if (!in_array($tur, ['giris','cikis'])) jsonResponse(false, null, 'Tür (giriş/çıkış) zorunludur.');

$pdo = getDB();
try {
    $stmt = $pdo->prepare(
        "INSERT INTO faturalar (fatura_no, sevkiyat_id, musteri_id, firma_id, tarih, tutar, tur, aciklama)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([
        $fatura_no,
        $sevkiyat_id > 0 ? $sevkiyat_id : null,
        $musteri_id,
        $firma_id,
        $tarih,
        $tutar,
        $tur,
        $aciklama ?: null,
    ]);
    jsonResponse(true, ['id' => $pdo->lastInsertId()], 'Fatura başarıyla eklendi.');
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        jsonResponse(false, null, 'Bu fatura numarası zaten kayıtlı.');
    }
    throw $e;
}
