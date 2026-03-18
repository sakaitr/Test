<?php
require_once '../../config.php';

$id          = intInput('id');
$fatura_no   = input('fatura_no');
$sevkiyat_id = intInput('sevkiyat_id', 0);
$musteri_id  = intInput('musteri_id');
$firma_id    = intInput('firma_id');
$tarih       = input('tarih');
$tutar       = (float) input('tutar', '0');
$tur         = input('tur');
$aciklama    = input('aciklama');

if ($id <= 0)          jsonResponse(false, null, 'Geçersiz ID.');
if ($fatura_no === '') jsonResponse(false, null, 'Fatura numarası zorunludur.');
if ($musteri_id <= 0)  jsonResponse(false, null, 'Müşteri seçimi zorunludur.');
if ($firma_id <= 0)    jsonResponse(false, null, 'Firma seçimi zorunludur.');
if ($tarih === '')     jsonResponse(false, null, 'Tarih zorunludur.');
if (!in_array($tur, ['giris','cikis'])) jsonResponse(false, null, 'Tür zorunludur.');

$pdo = getDB();
try {
    $stmt = $pdo->prepare(
        "UPDATE faturalar SET fatura_no=?, sevkiyat_id=?, musteri_id=?, firma_id=?,
         tarih=?, tutar=?, tur=?, aciklama=? WHERE id=?"
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
        $id,
    ]);
    jsonResponse(true, null, 'Fatura güncellendi.');
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        jsonResponse(false, null, 'Bu fatura numarası zaten başka kayıtta mevcut.');
    }
    throw $e;
}
