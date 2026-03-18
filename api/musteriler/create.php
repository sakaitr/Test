<?php
require_once '../../config.php';

$musteri_adi = input('musteri_adi');
$telefon     = input('telefon');
$adres       = input('adres');
$firma_id    = intInput('firma_id');

if ($musteri_adi === '') jsonResponse(false, null, 'Müşteri adı zorunludur.');
if ($firma_id <= 0)      jsonResponse(false, null, 'Firma seçimi zorunludur.');

$pdo  = getDB();
$stmt = $pdo->prepare(
    "INSERT INTO musteriler (musteri_adi, telefon, adres, firma_id) VALUES (?, ?, ?, ?)"
);
$stmt->execute([$musteri_adi, $telefon ?: null, $adres ?: null, $firma_id]);

jsonResponse(true, ['id' => $pdo->lastInsertId()], 'Müşteri başarıyla eklendi.');
