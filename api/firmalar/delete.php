<?php
require_once '../../config.php';

$id = intInput('id');
if ($id <= 0) jsonResponse(false, null, 'Geçersiz ID.');

$pdo = getDB();
try {
    $stmt = $pdo->prepare("DELETE FROM firmalar WHERE id=?");
    $stmt->execute([$id]);
    jsonResponse(true, null, 'Firma silindi.');
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        jsonResponse(false, null, 'Bu firmaya bağlı müşteri veya sevkiyat var; silinemez.');
    }
    throw $e;
}
