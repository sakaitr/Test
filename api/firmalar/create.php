<?php
require_once '../../config.php';

$firma_adi = input('firma_adi');
$telefon   = input('telefon');
$adres     = input('adres');

if ($firma_adi === '') {
    jsonResponse(false, null, 'Firma adı zorunludur.');
}

$pdo = getDB();
$stmt = $pdo->prepare("INSERT INTO firmalar (firma_adi, telefon, adres) VALUES (?, ?, ?)");
$stmt->execute([$firma_adi, $telefon ?: null, $adres ?: null]);

jsonResponse(true, ['id' => $pdo->lastInsertId()], 'Firma başarıyla eklendi.');
