<?php
require_once '../../config.php';

$id         = intInput('id');
$musteri_id = intInput('musteri_id');
$firma_id   = intInput('firma_id');
$tarih      = input('tarih');
$aciklama   = input('aciklama');
$durum      = input('durum');

if ($id <= 0)         jsonResponse(false, null, 'Geçersiz ID.');
if ($musteri_id <= 0) jsonResponse(false, null, 'Müşteri seçimi zorunludur.');
if ($firma_id <= 0)   jsonResponse(false, null, 'Firma seçimi zorunludur.');
if ($tarih === '')    jsonResponse(false, null, 'Tarih zorunludur.');

$validDurum = ['beklemede', 'tamamlandi', 'iptal'];
if (!in_array($durum, $validDurum)) $durum = 'beklemede';

$pdo  = getDB();
$stmt = $pdo->prepare(
    "UPDATE sevkiyatlar SET musteri_id=?, firma_id=?, tarih=?, aciklama=?, durum=? WHERE id=?"
);
$stmt->execute([$musteri_id, $firma_id, $tarih, $aciklama ?: null, $durum, $id]);

jsonResponse(true, null, 'Sevkiyat güncellendi.');
