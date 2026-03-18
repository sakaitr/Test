<?php
require_once '../../config.php';

$id = intInput('id');
if ($id <= 0) jsonResponse(false, null, 'Geçersiz ID.');

$pdo  = getDB();
$stmt = $pdo->prepare("DELETE FROM musteri_hareketler WHERE id=?");
$stmt->execute([$id]);

jsonResponse(true, null, 'Hareket silindi.');
