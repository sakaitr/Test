<?php
require_once '../../config.php';

$id          = intInput('id');
$musteri_adi = input('musteri_adi');
$telefon     = input('telefon');
$adres       = input('adres');
$firma_id    = intInput('firma_id');

if ($id <= 0)            jsonResponse(false, null, 'Geçersiz ID.');
if ($musteri_adi === '') jsonResponse(false, null, 'Müşteri adı zorunludur.');
if ($firma_id <= 0)      jsonResponse(false, null, 'Firma seçimi zorunludur.');

$pdo  = getDB();
$stmt = $pdo->prepare(
    "UPDATE musteriler SET musteri_adi=?, telefon=?, adres=?, firma_id=? WHERE id=?"
);
$stmt->execute([$musteri_adi, $telefon ?: null, $adres ?: null, $firma_id, $id]);

jsonResponse(true, null, 'Müşteri güncellendi.');
