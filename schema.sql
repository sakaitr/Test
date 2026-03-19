-- ============================================================
-- Müşteri Fatura Takip Sistemi - Veritabanı Şeması
-- agnowzbg_logi veritabanı seçili iken import edin
-- ============================================================

-- Firmalar (Sevkiyat yapılan şirketler)
CREATE TABLE IF NOT EXISTS firmalar (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    firma_adi  VARCHAR(150) NOT NULL,
    telefon    VARCHAR(30)  NULL,
    adres      TEXT         NULL,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_firma_adi (firma_adi)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Müşteriler
CREATE TABLE IF NOT EXISTS musteriler (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    musteri_adi VARCHAR(150) NOT NULL,
    telefon     VARCHAR(30)  NULL,
    adres       TEXT         NULL,
    firma_id    INT UNSIGNED NOT NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_musteri_firma (firma_id),
    INDEX idx_musteri_adi   (musteri_adi),
    CONSTRAINT fk_musteri_firma
        FOREIGN KEY (firma_id) REFERENCES firmalar(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sevkiyatlar
CREATE TABLE IF NOT EXISTS sevkiyatlar (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    musteri_id INT UNSIGNED NOT NULL,
    firma_id   INT UNSIGNED NOT NULL,
    tarih      DATE         NOT NULL,
    aciklama   TEXT         NULL,
    durum      ENUM('beklemede','tamamlandi','iptal') NOT NULL DEFAULT 'beklemede',
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_sevk_musteri (musteri_id),
    INDEX idx_sevk_firma   (firma_id),
    INDEX idx_sevk_tarih   (tarih),
    CONSTRAINT fk_sevk_musteri
        FOREIGN KEY (musteri_id) REFERENCES musteriler(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_sevk_firma
        FOREIGN KEY (firma_id) REFERENCES firmalar(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Faturalar
CREATE TABLE IF NOT EXISTS faturalar (
    id          INT UNSIGNED      NOT NULL AUTO_INCREMENT PRIMARY KEY,
    sevkiyat_id INT UNSIGNED      NULL,
    musteri_id  INT UNSIGNED      NOT NULL,
    firma_id    INT UNSIGNED      NOT NULL,
    fatura_no   VARCHAR(60)       NOT NULL,
    tarih       DATE              NOT NULL,
    tutar       DECIMAL(15,2)     NOT NULL DEFAULT 0.00,
    tur         ENUM('giris','cikis') NOT NULL,
    aciklama    TEXT              NULL,
    created_at  DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_fatura_no (fatura_no),
    INDEX idx_fat_sevkiyat (sevkiyat_id),
    INDEX idx_fat_musteri  (musteri_id),
    INDEX idx_fat_firma    (firma_id),
    INDEX idx_fat_tarih    (tarih),
    CONSTRAINT fk_fat_sevkiyat
        FOREIGN KEY (sevkiyat_id) REFERENCES sevkiyatlar(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_fat_musteri
        FOREIGN KEY (musteri_id) REFERENCES musteriler(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_fat_firma
        FOREIGN KEY (firma_id) REFERENCES firmalar(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Müşteri Hareketleri (manuel giriş/çıkış)
CREATE TABLE IF NOT EXISTS musteri_hareketler (
    id         INT UNSIGNED      NOT NULL AUTO_INCREMENT PRIMARY KEY,
    musteri_id INT UNSIGNED      NOT NULL,
    tarih      DATE              NOT NULL,
    tur        ENUM('giris','cikis') NOT NULL,
    tutar      DECIMAL(15,2)     NOT NULL DEFAULT 0.00,
    aciklama   TEXT              NULL,
    created_at DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_har_musteri (musteri_id),
    INDEX idx_har_tarih   (tarih),
    CONSTRAINT fk_har_musteri
        FOREIGN KEY (musteri_id) REFERENCES musteriler(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
