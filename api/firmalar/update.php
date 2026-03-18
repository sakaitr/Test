<?php
require_once '../../config.php';

$id        = intInput('id');
$firma_adi = input('firma_adi');
$telefon   = input('telefon');
$adres     = input('adres');

if ($id <= 0)        jsonResponse(false, null, 'Geçersiz ID.');
if ($firma_adi === '') jsonResponse(false, null, 'Firma adı zorunludur.');

$pdo = getDB();
$stmt = $pdo->prepare(
    "UPDATE firmalar SET firma_adi=?, telefon=?, adres=? WHERE id=?"
);
$stmt->execute([$firma_adi, $telefon ?: null, $adres ?: null, $id]);

jsonResponse(true, null, 'Firma güncellendi.');
