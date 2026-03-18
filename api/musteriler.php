<?php
require_once __DIR__ . '/../config.php';

$method = getMethod();
$db = getDB();
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

switch ($method) {
    case 'GET':
        if (isset($_GET['hareketler']) && $id) {
            // Müşteri hareketleri (girdi/çıktı)
            $stmt = $db->prepare("
                SELECT mh.*, s.takip_no
                FROM musteri_hareketleri mh
                LEFT JOIN sevkiyatlar s ON s.id = mh.sevkiyat_id
                WHERE mh.musteri_id = ?
                ORDER BY mh.hareket_tarihi DESC, mh.id DESC
            ");
            $stmt->execute([$id]);
            $hareketler = $stmt->fetchAll();
            // Özet
            $stmt2 = $db->prepare("
                SELECT
                    COALESCE(SUM(CASE WHEN hareket_turu='giris' THEN tutar ELSE 0 END),0) AS toplam_giris,
                    COALESCE(SUM(CASE WHEN hareket_turu='cikis' THEN tutar ELSE 0 END),0) AS toplam_cikis
                FROM musteri_hareketleri WHERE musteri_id=?
            ");
            $stmt2->execute([$id]);
            $ozet = $stmt2->fetch();
            jsonResponse(['hareketler' => $hareketler, 'ozet' => $ozet]);
        }
        if ($id) {
            $stmt = $db->prepare("SELECT * FROM musteriler WHERE id=?");
            $stmt->execute([$id]);
            $m = $stmt->fetch();
            if (!$m) jsonResponse(['hata' => 'Müşteri bulunamadı'], 404);
            jsonResponse($m);
        }
        $arama = isset($_GET['q']) ? '%' . $_GET['q'] . '%' : '%';
        $stmt = $db->prepare("
            SELECT m.*,
                COUNT(DISTINCT s.id) AS toplam_sevkiyat,
                COUNT(DISTINCT f.id) AS toplam_fatura,
                COALESCE(SUM(CASE WHEN mh.hareket_turu='giris' THEN mh.tutar ELSE 0 END),0) AS toplam_giris,
                COALESCE(SUM(CASE WHEN mh.hareket_turu='cikis' THEN mh.tutar ELSE 0 END),0) AS toplam_cikis
            FROM musteriler m
            LEFT JOIN sevkiyatlar s ON s.musteri_id = m.id
            LEFT JOIN faturalar f ON f.musteri_id = m.id
            LEFT JOIN musteri_hareketleri mh ON mh.musteri_id = m.id
            WHERE (m.ad_soyad LIKE ? OR m.firma_adi LIKE ? OR m.musteri_kodu LIKE ?) AND m.aktif=1
            GROUP BY m.id
            ORDER BY m.ad_soyad
        ");
        $stmt->execute([$arama, $arama, $arama]);
        jsonResponse($stmt->fetchAll());

    case 'POST':
        $d = getBody();
        if (empty($d['ad_soyad'])) jsonResponse(['hata' => 'Ad soyad zorunludur'], 400);

        // Müşteri kodu oluştur
        $stmt = $db->query("SELECT MAX(id)+1 as next_id FROM musteriler");
        $nextId = $stmt->fetch()['next_id'] ?? 1;
        $musteriKodu = 'MUS' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        $stmt = $db->prepare("
            INSERT INTO musteriler (musteri_kodu, ad_soyad, firma_adi, vergi_no, telefon, email, adres)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $musteriKodu,
            sanitize($d['ad_soyad']),
            sanitize($d['firma_adi'] ?? ''),
            sanitize($d['vergi_no'] ?? ''),
            sanitize($d['telefon'] ?? ''),
            sanitize($d['email'] ?? ''),
            sanitize($d['adres'] ?? ''),
        ]);
        jsonResponse(['id' => $db->lastInsertId(), 'musteri_kodu' => $musteriKodu, 'mesaj' => 'Müşteri eklendi'], 201);

    case 'PUT':
        if (!$id) jsonResponse(['hata' => 'ID gerekli'], 400);
        $d = getBody();

        if (isset($d['hareket'])) {
            // Müşteri hareketi ekle
            $h = $d['hareket'];
            $stmt = $db->prepare("
                INSERT INTO musteri_hareketleri (musteri_id, sevkiyat_id, hareket_turu, tutar, aciklama, belge_no, hareket_tarihi)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $id,
                !empty($h['sevkiyat_id']) ? (int)$h['sevkiyat_id'] : null,
                $h['hareket_turu'],
                (float)$h['tutar'],
                sanitize($h['aciklama'] ?? ''),
                sanitize($h['belge_no'] ?? ''),
                $h['hareket_tarihi'],
            ]);
            // Bakiye güncelle
            $fark = $h['hareket_turu'] === 'cikis' ? -(float)$h['tutar'] : (float)$h['tutar'];
            $db->prepare("UPDATE musteriler SET bakiye = bakiye + ? WHERE id=?")->execute([$fark, $id]);
            jsonResponse(['mesaj' => 'Hareket eklendi']);
        }

        $stmt = $db->prepare("
            UPDATE musteriler SET ad_soyad=?, firma_adi=?, vergi_no=?, telefon=?, email=?, adres=?
            WHERE id=?
        ");
        $stmt->execute([
            sanitize($d['ad_soyad'] ?? ''),
            sanitize($d['firma_adi'] ?? ''),
            sanitize($d['vergi_no'] ?? ''),
            sanitize($d['telefon'] ?? ''),
            sanitize($d['email'] ?? ''),
            sanitize($d['adres'] ?? ''),
            $id
        ]);
        jsonResponse(['mesaj' => 'Müşteri güncellendi']);

    case 'DELETE':
        if (!$id) jsonResponse(['hata' => 'ID gerekli'], 400);
        $db->prepare("UPDATE musteriler SET aktif=0 WHERE id=?")->execute([$id]);
        jsonResponse(['mesaj' => 'Müşteri silindi']);

    default:
        jsonResponse(['hata' => 'Geçersiz metod'], 405);
}
