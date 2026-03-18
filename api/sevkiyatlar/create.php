<?php
require_once '../../config.php';

$musteri_id = intInput('musteri_id');
$firma_id   = intInput('firma_id');
$tarih      = input('tarih');
$aciklama   = input('aciklama');
$durum      = input('durum');

if ($musteri_id <= 0) jsonResponse(false, null, 'Müşteri seçimi zorunludur.');
if ($firma_id <= 0)   jsonResponse(false, null, 'Firma seçimi zorunludur.');
if ($tarih === '')    jsonResponse(false, null, 'Tarih zorunludur.');

$validDurum = ['beklemede', 'tamamlandi', 'iptal'];
if (!in_array($durum, $validDurum)) $durum = 'beklemede';

$pdo  = getDB();
$stmt = $pdo->prepare(
    "INSERT INTO sevkiyatlar (musteri_id, firma_id, tarih, aciklama, durum) VALUES (?, ?, ?, ?, ?)"
);
$stmt->execute([$musteri_id, $firma_id, $tarih, $aciklama ?: null, $durum]);

jsonResponse(true, ['id' => $pdo->lastInsertId()], 'Sevkiyat başarıyla eklendi.');
