<?php
require_once __DIR__ . '/../config.php';

$db = getDB();

// Özet istatistikler
$istatistikler = [];

// Firma sayısı
$istatistikler['toplam_firma'] = $db->query("SELECT COUNT(*) FROM firmalar WHERE aktif=1")->fetchColumn();
$istatistikler['toplam_musteri'] = $db->query("SELECT COUNT(*) FROM musteriler WHERE aktif=1")->fetchColumn();
$istatistikler['toplam_sevkiyat'] = $db->query("SELECT COUNT(*) FROM sevkiyatlar")->fetchColumn();
$istatistikler['aktif_sevkiyat'] = $db->query("SELECT COUNT(*) FROM sevkiyatlar WHERE durum='yolda'")->fetchColumn();

// Fatura özetleri
$faturaOzet = $db->query("
    SELECT
        SUM(CASE WHEN fatura_turu='giden' THEN genel_toplam ELSE 0 END) AS giden_toplam,
        SUM(CASE WHEN fatura_turu='gelen' THEN genel_toplam ELSE 0 END) AS gelen_toplam,
        SUM(CASE WHEN fatura_turu='giden' AND odeme_durumu!='odendi' THEN genel_toplam-odenen_tutar ELSE 0 END) AS tahsil_edilmemis,
        SUM(CASE WHEN fatura_turu='gelen' AND odeme_durumu!='odendi' THEN genel_toplam-odenen_tutar ELSE 0 END) AS odenmemis_gelen
    FROM faturalar
")->fetch();
$istatistikler = array_merge($istatistikler, $faturaOzet);

// Firmalara göre sevkiyat dağılımı
$firmaDagilim = $db->query("
    SELECT f.firma_adi,
           COUNT(s.id) AS sevkiyat_sayisi,
           COUNT(CASE WHEN s.durum='yolda' THEN 1 END) AS aktif,
           COUNT(CASE WHEN s.durum='teslim_edildi' THEN 1 END) AS teslim_edildi
    FROM firmalar f
    LEFT JOIN sevkiyatlar s ON s.firma_id = f.id
    WHERE f.aktif=1
    GROUP BY f.id, f.firma_adi
    ORDER BY sevkiyat_sayisi DESC
")->fetchAll();

// Son sevkiyatlar
$sonSevkiyatlar = $db->query("
    SELECT s.takip_no, s.durum, s.sevkiyat_tarihi,
           m.ad_soyad, f.firma_adi
    FROM sevkiyatlar s
    JOIN musteriler m ON m.id = s.musteri_id
    JOIN firmalar f ON f.id = s.firma_id
    ORDER BY s.id DESC LIMIT 10
")->fetchAll();

// Vadesi geçmiş faturalar
$vadesiGecmis = $db->query("
    SELECT fa.fatura_no, fa.fatura_turu, fa.vade_tarihi,
           fa.genel_toplam - fa.odenen_tutar AS kalan,
           m.ad_soyad, fi.firma_adi
    FROM faturalar fa
    LEFT JOIN musteriler m ON m.id = fa.musteri_id
    LEFT JOIN firmalar fi ON fi.id = fa.firma_id
    WHERE fa.odeme_durumu != 'odendi'
      AND fa.vade_tarihi < CURDATE()
    ORDER BY fa.vade_tarihi
    LIMIT 10
")->fetchAll();

// Aylık ciro (son 6 ay - giden faturalar)
$aylikCiro = $db->query("
    SELECT DATE_FORMAT(fatura_tarihi, '%Y-%m') AS ay,
           SUM(CASE WHEN fatura_turu='giden' THEN genel_toplam ELSE 0 END) AS ciro,
           SUM(CASE WHEN fatura_turu='gelen' THEN genel_toplam ELSE 0 END) AS maliyet
    FROM faturalar
    WHERE fatura_tarihi >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY ay
    ORDER BY ay
")->fetchAll();

jsonResponse([
    'istatistikler' => $istatistikler,
    'firma_dagilim' => $firmaDagilim,
    'son_sevkiyatlar' => $sonSevkiyatlar,
    'vadesi_gecmis' => $vadesiGecmis,
    'aylik_ciro' => $aylikCiro,
]);
