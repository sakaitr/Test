<?php
require_once '../../config.php';

$musteri_id = intInput('musteri_id');
$tarih      = input('tarih');
$tur        = input('tur');
$tutar      = (float) input('tutar', '0');
$aciklama   = input('aciklama');

if ($musteri_id <= 0) jsonResponse(false, null, 'Müşteri seçimi zorunludur.');
if ($tarih === '')    jsonResponse(false, null, 'Tarih zorunludur.');
if (!in_array($tur, ['giris','cikis'])) jsonResponse(false, null, 'Tür zorunludur.');
if ($tutar <= 0)      jsonResponse(false, null, 'Tutar 0\'dan büyük olmalıdır.');

$pdo  = getDB();
$stmt = $pdo->prepare(
    "INSERT INTO musteri_hareketler (musteri_id, tarih, tur, tutar, aciklama) VALUES (?, ?, ?, ?, ?)"
);
$stmt->execute([$musteri_id, $tarih, $tur, $tutar, $aciklama ?: null]);

jsonResponse(true, ['id' => $pdo->lastInsertId()], 'Hareket başarıyla eklendi.');
