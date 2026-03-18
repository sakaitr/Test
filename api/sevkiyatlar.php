<?php
require_once __DIR__ . '/../config.php';

$method = getMethod();
$db = getDB();
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

switch ($method) {
    case 'GET':
        if ($id) {
            $stmt = $db->prepare("
                SELECT s.*, m.ad_soyad, m.firma_adi AS musteri_firma, m.musteri_kodu,
                       f.firma_adi AS kargo_firma
                FROM sevkiyatlar s
                JOIN musteriler m ON m.id = s.musteri_id
                JOIN firmalar f ON f.id = s.firma_id
                WHERE s.id = ?
            ");
            $stmt->execute([$id]);
            $s = $stmt->fetch();
            if (!$s) jsonResponse(['hata' => 'Sevkiyat bulunamadı'], 404);
            jsonResponse($s);
        }
        $durum = $_GET['durum'] ?? '';
        $firmaId = isset($_GET['firma_id']) ? (int)$_GET['firma_id'] : 0;
        $musteriId = isset($_GET['musteri_id']) ? (int)$_GET['musteri_id'] : 0;
        $arama = isset($_GET['q']) ? '%' . $_GET['q'] . '%' : '%';

        $where = ['1=1'];
        $params = [];
        if ($durum) { $where[] = 's.durum = ?'; $params[] = $durum; }
        if ($firmaId) { $where[] = 's.firma_id = ?'; $params[] = $firmaId; }
        if ($musteriId) { $where[] = 's.musteri_id = ?'; $params[] = $musteriId; }
        if (isset($_GET['q'])) { $where[] = 's.takip_no LIKE ?'; $params[] = $arama; }

        $sql = "
            SELECT s.*, m.ad_soyad, m.firma_adi AS musteri_firma, m.musteri_kodu,
                   f.firma_adi AS kargo_firma
            FROM sevkiyatlar s
            JOIN musteriler m ON m.id = s.musteri_id
            JOIN firmalar f ON f.id = s.firma_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY s.sevkiyat_tarihi DESC, s.id DESC
            LIMIT 200
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        jsonResponse($stmt->fetchAll());

    case 'POST':
        $d = getBody();
        if (empty($d['musteri_id']) || empty($d['firma_id'])) {
            jsonResponse(['hata' => 'Müşteri ve firma zorunludur'], 400);
        }
        // Takip no oluştur
        $takipNo = 'SEV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
        $stmt = $db->prepare("
            INSERT INTO sevkiyatlar
                (takip_no, musteri_id, firma_id, kalkis_yeri, varis_yeri,
                 sevkiyat_tarihi, teslim_tarihi, agirlik, hacim, durum, aciklama)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $takipNo,
            (int)$d['musteri_id'],
            (int)$d['firma_id'],
            sanitize($d['kalkis_yeri'] ?? ''),
            sanitize($d['varis_yeri'] ?? ''),
            $d['sevkiyat_tarihi'] ?? date('Y-m-d'),
            !empty($d['teslim_tarihi']) ? $d['teslim_tarihi'] : null,
            !empty($d['agirlik']) ? (float)$d['agirlik'] : null,
            !empty($d['hacim']) ? (float)$d['hacim'] : null,
            $d['durum'] ?? 'beklemede',
            sanitize($d['aciklama'] ?? ''),
        ]);
        jsonResponse(['id' => $db->lastInsertId(), 'takip_no' => $takipNo, 'mesaj' => 'Sevkiyat oluşturuldu'], 201);

    case 'PUT':
        if (!$id) jsonResponse(['hata' => 'ID gerekli'], 400);
        $d = getBody();
        $stmt = $db->prepare("
            UPDATE sevkiyatlar SET
                musteri_id=?, firma_id=?, kalkis_yeri=?, varis_yeri=?,
                sevkiyat_tarihi=?, teslim_tarihi=?, agirlik=?, hacim=?, durum=?, aciklama=?
            WHERE id=?
        ");
        $stmt->execute([
            (int)$d['musteri_id'],
            (int)$d['firma_id'],
            sanitize($d['kalkis_yeri'] ?? ''),
            sanitize($d['varis_yeri'] ?? ''),
            $d['sevkiyat_tarihi'] ?? date('Y-m-d'),
            !empty($d['teslim_tarihi']) ? $d['teslim_tarihi'] : null,
            !empty($d['agirlik']) ? (float)$d['agirlik'] : null,
            !empty($d['hacim']) ? (float)$d['hacim'] : null,
            $d['durum'] ?? 'beklemede',
            sanitize($d['aciklama'] ?? ''),
            $id
        ]);
        jsonResponse(['mesaj' => 'Sevkiyat güncellendi']);

    case 'DELETE':
        if (!$id) jsonResponse(['hata' => 'ID gerekli'], 400);
        $db->prepare("UPDATE sevkiyatlar SET durum='iptal' WHERE id=?")->execute([$id]);
        jsonResponse(['mesaj' => 'Sevkiyat iptal edildi']);

    default:
        jsonResponse(['hata' => 'Geçersiz metod'], 405);
}
