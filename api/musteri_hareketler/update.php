<?php
require_once '../../config.php';

$id         = intInput('id');
$musteri_id = intInput('musteri_id');
$tarih      = input('tarih');
$tur        = input('tur');
$tutar      = (float) input('tutar', '0');
$aciklama   = input('aciklama');

if ($id <= 0)         jsonResponse(false, null, 'Geçersiz ID.');
if ($musteri_id <= 0) jsonResponse(false, null, 'Müşteri seçimi zorunludur.');
if ($tarih === '')    jsonResponse(false, null, 'Tarih zorunludur.');
if (!in_array($tur, ['giris','cikis'])) jsonResponse(false, null, 'Tür zorunludur.');

$pdo  = getDB();
$stmt = $pdo->prepare(
    "UPDATE musteri_hareketler SET musteri_id=?, tarih=?, tur=?, tutar=?, aciklama=? WHERE id=?"
);
$stmt->execute([$musteri_id, $tarih, $tur, $tutar, $aciklama ?: null, $id]);

jsonResponse(true, null, 'Hareket güncellendi.');
