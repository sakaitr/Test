-- Müşteri Fatura Takip Sistemi - Veritabanı Şeması
-- Customer Invoice Tracking System - Database Schema

CREATE DATABASE IF NOT EXISTS fatura_takip CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci;
USE fatura_takip;

-- Nakliye/Kargo Firmaları
CREATE TABLE IF NOT EXISTS firmalar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    firma_adi VARCHAR(150) NOT NULL,
    vergi_no VARCHAR(20),
    telefon VARCHAR(20),
    email VARCHAR(100),
    adres TEXT,
    yetkili_kisi VARCHAR(100),
    aktif TINYINT(1) DEFAULT 1,
    olusturma_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
    guncelleme_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Müşteriler
CREATE TABLE IF NOT EXISTS musteriler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    musteri_kodu VARCHAR(20) UNIQUE,
    ad_soyad VARCHAR(150) NOT NULL,
    firma_adi VARCHAR(150),
    vergi_no VARCHAR(20),
    telefon VARCHAR(20),
    email VARCHAR(100),
    adres TEXT,
    bakiye DECIMAL(12,2) DEFAULT 0.00 COMMENT 'Cari bakiye (+borçlu, -alacaklı)',
    aktif TINYINT(1) DEFAULT 1,
    olusturma_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
    guncelleme_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sevkiyatlar
CREATE TABLE IF NOT EXISTS sevkiyatlar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    takip_no VARCHAR(50) UNIQUE NOT NULL,
    musteri_id INT NOT NULL,
    firma_id INT NOT NULL,
    kalkis_yeri VARCHAR(150),
    varis_yeri VARCHAR(150),
    sevkiyat_tarihi DATE,
    teslim_tarihi DATE,
    agirlik DECIMAL(10,2) COMMENT 'kg',
    hacim DECIMAL(10,2) COMMENT 'm³',
    durum ENUM('beklemede','yolda','teslim_edildi','iptal') DEFAULT 'beklemede',
    aciklama TEXT,
    olusturma_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
    guncelleme_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (musteri_id) REFERENCES musteriler(id),
    FOREIGN KEY (firma_id) REFERENCES firmalar(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Müşteri Hareketleri (Girdi/Çıktı)
CREATE TABLE IF NOT EXISTS musteri_hareketleri (
    id INT AUTO_INCREMENT PRIMARY KEY,
    musteri_id INT NOT NULL,
    sevkiyat_id INT,
    hareket_turu ENUM('giris','cikis') NOT NULL COMMENT 'giris=ödeme aldık, cikis=iade/alacak',
    tutar DECIMAL(12,2) NOT NULL,
    aciklama VARCHAR(255),
    belge_no VARCHAR(50),
    hareket_tarihi DATE NOT NULL,
    olusturma_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (musteri_id) REFERENCES musteriler(id),
    FOREIGN KEY (sevkiyat_id) REFERENCES sevkiyatlar(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Faturalar
CREATE TABLE IF NOT EXISTS faturalar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fatura_no VARCHAR(50) UNIQUE NOT NULL,
    fatura_turu ENUM('gelen','giden') NOT NULL COMMENT 'gelen=firmadan bize, giden=bizden müşteriye',
    musteri_id INT COMMENT 'Giden fatura için',
    firma_id INT COMMENT 'Gelen fatura için',
    sevkiyat_id INT,
    fatura_tarihi DATE NOT NULL,
    vade_tarihi DATE,
    ara_toplam DECIMAL(12,2) DEFAULT 0.00,
    kdv_orani DECIMAL(5,2) DEFAULT 20.00,
    kdv_tutari DECIMAL(12,2) DEFAULT 0.00,
    genel_toplam DECIMAL(12,2) DEFAULT 0.00,
    odeme_durumu ENUM('odenmedi','kismi_odendi','odendi') DEFAULT 'odenmedi',
    odenen_tutar DECIMAL(12,2) DEFAULT 0.00,
    aciklama TEXT,
    olusturma_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
    guncelleme_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (musteri_id) REFERENCES musteriler(id),
    FOREIGN KEY (firma_id) REFERENCES firmalar(id),
    FOREIGN KEY (sevkiyat_id) REFERENCES sevkiyatlar(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Fatura Kalemleri
CREATE TABLE IF NOT EXISTS fatura_kalemleri (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fatura_id INT NOT NULL,
    aciklama VARCHAR(255) NOT NULL,
    miktar DECIMAL(10,2) DEFAULT 1,
    birim VARCHAR(20) DEFAULT 'adet',
    birim_fiyat DECIMAL(12,2) NOT NULL,
    toplam DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (fatura_id) REFERENCES faturalar(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Fatura Hareketleri (Ödeme Takibi)
CREATE TABLE IF NOT EXISTS fatura_hareketleri (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fatura_id INT NOT NULL,
    odeme_tarihi DATE NOT NULL,
    odeme_tutari DECIMAL(12,2) NOT NULL,
    odeme_yontemi ENUM('nakit','havale','kredi_karti','cek','diger') DEFAULT 'nakit',
    aciklama VARCHAR(255),
    olusturma_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (fatura_id) REFERENCES faturalar(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Örnek Veriler
INSERT INTO firmalar (firma_adi, vergi_no, telefon, email, yetkili_kisi) VALUES
('Aras Kargo', '1234567890', '0850 555 0000', 'info@araskargo.com.tr', 'Ahmet Yılmaz'),
('MNG Kargo', '9876543210', '0850 444 0000', 'info@mngkargo.com.tr', 'Mehmet Demir'),
('Yurtiçi Kargo', '5555555555', '0850 333 0000', 'info@yurticicargo.com.tr', 'Ayşe Kaya'),
('PTT Kargo', '1111111111', '0850 222 0000', 'info@ptt.gov.tr', 'Fatma Çelik');

INSERT INTO musteriler (musteri_kodu, ad_soyad, firma_adi, telefon, email) VALUES
('MUS001', 'Ali Öztürk', 'Öztürk Ticaret', '0532 111 2233', 'ali@ozturkticaret.com'),
('MUS002', 'Zeynep Şahin', 'Şahin Gıda A.Ş.', '0533 222 3344', 'zeynep@sahingida.com'),
('MUS003', 'Hasan Koç', 'Koç Tekstil Ltd.', '0534 333 4455', 'hasan@koctekstil.com');
