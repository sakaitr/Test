<?php
require_once __DIR__ . '/../config.php';

$method = getMethod();
$db = getDB();
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

switch ($method) {
    case 'GET':
        if ($id) {
            $stmt = $db->prepare("SELECT * FROM firmalar WHERE id = ?");
            $stmt->execute([$id]);
            $firma = $stmt->fetch();
            if (!$firma) jsonResponse(['hata' => 'Firma bulunamadı'], 404);
            jsonResponse($firma);
        }
        // Sevkiyat sayısı ile birlikte listele
        $arama = isset($_GET['q']) ? '%' . $_GET['q'] . '%' : '%';
        $stmt = $db->prepare("
            SELECT f.*,
                COUNT(DISTINCT s.id) AS toplam_sevkiyat,
                COUNT(DISTINCT CASE WHEN s.durum='yolda' THEN s.id END) AS aktif_sevkiyat,
                COALESCE(SUM(fa.genel_toplam),0) AS toplam_fatura_tutari
            FROM firmalar f
            LEFT JOIN sevkiyatlar s ON s.firma_id = f.id
            LEFT JOIN faturalar fa ON fa.firma_id = f.id AND fa.fatura_turu = 'gelen'
            WHERE f.firma_adi LIKE ?
            GROUP BY f.id
            ORDER BY f.firma_adi
        ");
        $stmt->execute([$arama]);
        jsonResponse($stmt->fetchAll());

    case 'POST':
        $d = getBody();
        if (empty($d['firma_adi'])) jsonResponse(['hata' => 'Firma adı zorunludur'], 400);
        $stmt = $db->prepare("
            INSERT INTO firmalar (firma_adi, vergi_no, telefon, email, adres, yetkili_kisi, aktif)
            VALUES (?, ?, ?, ?, ?, ?, 1)
        ");
        $stmt->execute([
            sanitize($d['firma_adi']),
            sanitize($d['vergi_no'] ?? ''),
            sanitize($d['telefon'] ?? ''),
            sanitize($d['email'] ?? ''),
            sanitize($d['adres'] ?? ''),
            sanitize($d['yetkili_kisi'] ?? ''),
        ]);
        jsonResponse(['id' => $db->lastInsertId(), 'mesaj' => 'Firma eklendi'], 201);

    case 'PUT':
        if (!$id) jsonResponse(['hata' => 'ID gerekli'], 400);
        $d = getBody();
        $stmt = $db->prepare("
            UPDATE firmalar SET firma_adi=?, vergi_no=?, telefon=?, email=?, adres=?, yetkili_kisi=?, aktif=?
            WHERE id=?
        ");
        $stmt->execute([
            sanitize($d['firma_adi'] ?? ''),
            sanitize($d['vergi_no'] ?? ''),
            sanitize($d['telefon'] ?? ''),
            sanitize($d['email'] ?? ''),
            sanitize($d['adres'] ?? ''),
            sanitize($d['yetkili_kisi'] ?? ''),
            isset($d['aktif']) ? (int)$d['aktif'] : 1,
            $id
        ]);
        jsonResponse(['mesaj' => 'Firma güncellendi']);

    case 'DELETE':
        if (!$id) jsonResponse(['hata' => 'ID gerekli'], 400);
        $db->prepare("UPDATE firmalar SET aktif=0 WHERE id=?")->execute([$id]);
        jsonResponse(['mesaj' => 'Firma pasife alındı']);

    default:
        jsonResponse(['hata' => 'Geçersiz metod'], 405);
}
