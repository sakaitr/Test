<?php
require_once __DIR__ . '/../config.php';

$method = getMethod();
$db = getDB();
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

switch ($method) {
    case 'GET':
        if (isset($_GET['kalemler']) && $id) {
            $stmt = $db->prepare("SELECT * FROM fatura_kalemleri WHERE fatura_id=?");
            $stmt->execute([$id]);
            jsonResponse($stmt->fetchAll());
        }
        if (isset($_GET['hareketler']) && $id) {
            $stmt = $db->prepare("SELECT * FROM fatura_hareketleri WHERE fatura_id=? ORDER BY odeme_tarihi DESC");
            $stmt->execute([$id]);
            jsonResponse($stmt->fetchAll());
        }
        if ($id) {
            $stmt = $db->prepare("
                SELECT fa.*,
                       m.ad_soyad, m.firma_adi AS musteri_firma,
                       fi.firma_adi AS kargo_firma,
                       s.takip_no
                FROM faturalar fa
                LEFT JOIN musteriler m ON m.id = fa.musteri_id
                LEFT JOIN firmalar fi ON fi.id = fa.firma_id
                LEFT JOIN sevkiyatlar s ON s.id = fa.sevkiyat_id
                WHERE fa.id=?
            ");
            $stmt->execute([$id]);
            $f = $stmt->fetch();
            if (!$f) jsonResponse(['hata' => 'Fatura bulunamadı'], 404);
            // Kalemleri de getir
            $stmt2 = $db->prepare("SELECT * FROM fatura_kalemleri WHERE fatura_id=?");
            $stmt2->execute([$id]);
            $f['kalemler'] = $stmt2->fetchAll();
            jsonResponse($f);
        }

        $tur = $_GET['tur'] ?? '';
        $durum = $_GET['durum'] ?? '';
        $arama = isset($_GET['q']) ? '%' . $_GET['q'] . '%' : '%';

        $where = ['1=1'];
        $params = [];
        if ($tur) { $where[] = 'fa.fatura_turu = ?'; $params[] = $tur; }
        if ($durum) { $where[] = 'fa.odeme_durumu = ?'; $params[] = $durum; }
        if (isset($_GET['q'])) { $where[] = 'fa.fatura_no LIKE ?'; $params[] = $arama; }
        if (isset($_GET['musteri_id'])) { $where[] = 'fa.musteri_id = ?'; $params[] = (int)$_GET['musteri_id']; }
        if (isset($_GET['firma_id'])) { $where[] = 'fa.firma_id = ?'; $params[] = (int)$_GET['firma_id']; }

        $sql = "
            SELECT fa.*,
                   m.ad_soyad, m.firma_adi AS musteri_firma,
                   fi.firma_adi AS kargo_firma,
                   s.takip_no,
                   (fa.genel_toplam - fa.odenen_tutar) AS kalan_tutar
            FROM faturalar fa
            LEFT JOIN musteriler m ON m.id = fa.musteri_id
            LEFT JOIN firmalar fi ON fi.id = fa.firma_id
            LEFT JOIN sevkiyatlar s ON s.id = fa.sevkiyat_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY fa.fatura_tarihi DESC, fa.id DESC
            LIMIT 200
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        jsonResponse($stmt->fetchAll());

    case 'POST':
        $d = getBody();
        if (empty($d['fatura_turu']) || empty($d['fatura_no'])) {
            jsonResponse(['hata' => 'Fatura türü ve numarası zorunludur'], 400);
        }

        $db->beginTransaction();
        try {
            $araToplam = 0;
            $kalemler = $d['kalemler'] ?? [];
            foreach ($kalemler as $k) {
                $araToplam += (float)$k['miktar'] * (float)$k['birim_fiyat'];
            }
            $kdvOrani = (float)($d['kdv_orani'] ?? 20);
            $kdvTutari = $araToplam * ($kdvOrani / 100);
            $genelToplam = $araToplam + $kdvTutari;

            $stmt = $db->prepare("
                INSERT INTO faturalar
                    (fatura_no, fatura_turu, musteri_id, firma_id, sevkiyat_id,
                     fatura_tarihi, vade_tarihi, ara_toplam, kdv_orani, kdv_tutari,
                     genel_toplam, odeme_durumu, aciklama)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'odenmedi', ?)
            ");
            $stmt->execute([
                sanitize($d['fatura_no']),
                $d['fatura_turu'],
                !empty($d['musteri_id']) ? (int)$d['musteri_id'] : null,
                !empty($d['firma_id']) ? (int)$d['firma_id'] : null,
                !empty($d['sevkiyat_id']) ? (int)$d['sevkiyat_id'] : null,
                $d['fatura_tarihi'] ?? date('Y-m-d'),
                !empty($d['vade_tarihi']) ? $d['vade_tarihi'] : null,
                $araToplam, $kdvOrani, $kdvTutari, $genelToplam,
                sanitize($d['aciklama'] ?? ''),
            ]);
            $faturaId = $db->lastInsertId();

            foreach ($kalemler as $k) {
                $top = (float)$k['miktar'] * (float)$k['birim_fiyat'];
                $db->prepare("
                    INSERT INTO fatura_kalemleri (fatura_id, aciklama, miktar, birim, birim_fiyat, toplam)
                    VALUES (?, ?, ?, ?, ?, ?)
                ")->execute([
                    $faturaId, sanitize($k['aciklama']),
                    (float)$k['miktar'], sanitize($k['birim'] ?? 'adet'),
                    (float)$k['birim_fiyat'], $top
                ]);
            }
            $db->commit();
            jsonResponse(['id' => $faturaId, 'mesaj' => 'Fatura oluşturuldu'], 201);
        } catch (Exception $e) {
            $db->rollBack();
            jsonResponse(['hata' => $e->getMessage()], 500);
        }

    case 'PUT':
        if (!$id) jsonResponse(['hata' => 'ID gerekli'], 400);
        $d = getBody();

        if (isset($d['odeme'])) {
            // Ödeme kaydı
            $o = $d['odeme'];
            $tutar = (float)$o['odeme_tutari'];
            $db->prepare("
                INSERT INTO fatura_hareketleri (fatura_id, odeme_tarihi, odeme_tutari, odeme_yontemi, aciklama)
                VALUES (?, ?, ?, ?, ?)
            ")->execute([
                $id, $o['odeme_tarihi'], $tutar,
                $o['odeme_yontemi'] ?? 'nakit',
                sanitize($o['aciklama'] ?? ''),
            ]);
            // Fatura güncelle
            $stmt = $db->prepare("
                UPDATE faturalar SET
                    odenen_tutar = odenen_tutar + ?,
                    odeme_durumu = CASE
                        WHEN odenen_tutar + ? >= genel_toplam THEN 'odendi'
                        WHEN odenen_tutar + ? > 0 THEN 'kismi_odendi'
                        ELSE 'odenmedi'
                    END
                WHERE id=?
            ");
            $stmt->execute([$tutar, $tutar, $tutar, $id]);
            jsonResponse(['mesaj' => 'Ödeme kaydedildi']);
        }

        // Fatura güncelle
        $stmt = $db->prepare("
            UPDATE faturalar SET fatura_no=?, fatura_tarihi=?, vade_tarihi=?, aciklama=?
            WHERE id=?
        ");
        $stmt->execute([
            sanitize($d['fatura_no'] ?? ''),
            $d['fatura_tarihi'] ?? date('Y-m-d'),
            !empty($d['vade_tarihi']) ? $d['vade_tarihi'] : null,
            sanitize($d['aciklama'] ?? ''),
            $id
        ]);
        jsonResponse(['mesaj' => 'Fatura güncellendi']);

    case 'DELETE':
        if (!$id) jsonResponse(['hata' => 'ID gerekli'], 400);
        $db->prepare("DELETE FROM fatura_kalemleri WHERE fatura_id=?")->execute([$id]);
        $db->prepare("DELETE FROM faturalar WHERE id=?")->execute([$id]);
        jsonResponse(['mesaj' => 'Fatura silindi']);

    default:
        jsonResponse(['hata' => 'Geçersiz metod'], 405);
}
