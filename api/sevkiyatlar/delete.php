<?php
require_once '../../config.php';

$id = intInput('id');
if ($id <= 0) jsonResponse(false, null, 'Geçersiz ID.');

$pdo = getDB();
try {
    $stmt = $pdo->prepare("DELETE FROM sevkiyatlar WHERE id=?");
    $stmt->execute([$id]);
    jsonResponse(true, null, 'Sevkiyat silindi.');
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        jsonResponse(false, null, 'Bu sevkiyata bağlı fatura var; silinemez.');
    }
    throw $e;
}
