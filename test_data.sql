-- ============================================================
-- Test Verisi - agnowzbg_logi veritabanı seçili iken import edin
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE musteri_hareketler;
TRUNCATE TABLE faturalar;
TRUNCATE TABLE sevkiyatlar;
TRUNCATE TABLE musteriler;
TRUNCATE TABLE firmalar;
SET FOREIGN_KEY_CHECKS = 1;

-- Firmalar
INSERT INTO firmalar (id, firma_adi, telefon, adres) VALUES
(1, 'Agno Lojistik A.Ş.',      '0212 555 10 10', 'Bağcılar, İstanbul'),
(2, 'Marmara Taşımacılık Ltd.', '0216 444 20 20', 'Pendik, İstanbul'),
(3, 'Anadolu Kargo ve Lojistik','0312 333 30 30', 'Yenimahalle, Ankara'),
(4, 'Ege Nakliyat A.Ş.',        '0232 222 40 40', 'Bornova, İzmir');

-- Müşteriler
INSERT INTO musteriler (id, musteri_adi, telefon, adres, firma_id) VALUES
(1,  'Ahmet Yılmaz',      '0532 111 11 11', 'Kadıköy, İstanbul',   1),
(2,  'Fatma Kaya',        '0533 222 22 22', 'Üsküdar, İstanbul',   1),
(3,  'Mehmet Demir',      '0534 333 33 33', 'Maltepe, İstanbul',   2),
(4,  'Ayşe Çelik',        '0535 444 44 44', 'Kartal, İstanbul',    2),
(5,  'Mustafa Şahin',     '0536 555 55 55', 'Çankaya, Ankara',     3),
(6,  'Zeynep Arslan',     '0537 666 66 66', 'Keçiören, Ankara',    3),
(7,  'Hasan Koç',         '0538 777 77 77', 'Konak, İzmir',        4),
(8,  'Merve Aydın',       '0539 888 88 88', 'Karşıyaka, İzmir',    4);

-- Sevkiyatlar
INSERT INTO sevkiyatlar (id, musteri_id, firma_id, tarih, aciklama, durum) VALUES
(1,  1, 1, '2026-01-05', 'İstanbul içi ağır yük taşıması',         'tamamlandi'),
(2,  2, 1, '2026-01-12', 'Ofis taşıma - Kadıköy → Beşiktaş',       'tamamlandi'),
(3,  3, 2, '2026-01-18', 'Depo sevkiyatı, 3 palet',                 'tamamlandi'),
(4,  4, 2, '2026-02-02', 'Soğuk zincir taşıma',                     'tamamlandi'),
(5,  5, 3, '2026-02-10', 'Ankara → İstanbul parsiyel yük',          'tamamlandi'),
(6,  6, 3, '2026-02-20', 'Yurt içi kargo, 150 kg',                  'tamamlandi'),
(7,  7, 4, '2026-03-01', 'İzmir liman çıkış sevkiyatı',             'tamamlandi'),
(8,  8, 4, '2026-03-05', 'Fabrika → depo transferi',                'beklemede'),
(9,  1, 1, '2026-03-10', 'Haftalık rutin teslimat',                 'beklemede'),
(10, 3, 2, '2026-03-15', 'Acil kurye - ilaç malzemesi',             'beklemede');

-- Faturalar
INSERT INTO faturalar (sevkiyat_id, musteri_id, firma_id, fatura_no, tarih, tutar, tur, aciklama) VALUES
(1,  1, 1, 'FAT-2026-001', '2026-01-06',  4500.00, 'cikis', 'Taşıma hizmet bedeli'),
(2,  2, 1, 'FAT-2026-002', '2026-01-13',  3200.00, 'cikis', 'Ofis taşıma bedeli'),
(3,  3, 2, 'FAT-2026-003', '2026-01-19',  6800.00, 'cikis', 'Palet sevkiyat bedeli'),
(4,  4, 2, 'FAT-2026-004', '2026-02-03',  5100.00, 'cikis', 'Soğuk zincir taşıma'),
(5,  5, 3, 'FAT-2026-005', '2026-02-11',  9200.00, 'cikis', 'Parsiyel yük taşıma'),
(6,  6, 3, 'FAT-2026-006', '2026-02-21',  2750.00, 'cikis', 'Kargo hizmet bedeli'),
(7,  7, 4, 'FAT-2026-007', '2026-03-02', 11500.00, 'cikis', 'Liman çıkış lojistik'),
(NULL, 1, 1, 'FAT-2026-008', '2026-01-20', 2000.00, 'giris', 'Müşteri avans ödemesi'),
(NULL, 3, 2, 'FAT-2026-009', '2026-02-05', 3000.00, 'giris', 'Kısmi ödeme alındı'),
(NULL, 5, 3, 'FAT-2026-010', '2026-02-15', 5000.00, 'giris', 'Ön ödeme');

-- Müşteri Hareketleri
INSERT INTO musteri_hareketler (musteri_id, tarih, tur, tutar, aciklama) VALUES
(1, '2026-01-20', 'giris',  2000.00, 'Nakit tahsilat'),
(1, '2026-01-06', 'cikis',  4500.00, 'FAT-2026-001 borç kaydı'),
(2, '2026-01-13', 'cikis',  3200.00, 'FAT-2026-002 borç kaydı'),
(3, '2026-02-05', 'giris',  3000.00, 'Havale ile ödeme'),
(3, '2026-01-19', 'cikis',  6800.00, 'FAT-2026-003 borç kaydı'),
(4, '2026-02-03', 'cikis',  5100.00, 'FAT-2026-004 borç kaydı'),
(5, '2026-02-15', 'giris',  5000.00, 'Çek tahsilatı'),
(5, '2026-02-11', 'cikis',  9200.00, 'FAT-2026-005 borç kaydı'),
(6, '2026-02-21', 'cikis',  2750.00, 'FAT-2026-006 borç kaydı'),
(7, '2026-03-02', 'cikis', 11500.00, 'FAT-2026-007 borç kaydı'),
(8, '2026-03-05', 'cikis',  7800.00, 'Bekleyen sevkiyat tahmini');
