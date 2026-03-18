<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sevkiyat & Fatura Takip Sistemi</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="layout">

<!-- ===== SIDEBAR ===== -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <span class="icon">🚛</span>
        <div>
            <h1>Sevkiyat Takip</h1>
            <small>Fatura & Müşteri Yönetimi</small>
        </div>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-group-title">Genel</div>
        <button class="nav-link active" data-page="dashboard" onclick="sayfaGit('dashboard')">
            <span class="icon">📊</span> Dashboard
        </button>

        <div class="nav-group-title">Yönetim</div>
        <button class="nav-link" data-page="firmalar" onclick="sayfaGit('firmalar')">
            <span class="icon">🏢</span> Kargo Firmaları
        </button>
        <button class="nav-link" data-page="musteriler" onclick="sayfaGit('musteriler')">
            <span class="icon">👥</span> Müşteriler
        </button>
        <button class="nav-link" data-page="sevkiyatlar" onclick="sayfaGit('sevkiyatlar')">
            <span class="icon">📦</span> Sevkiyatlar
        </button>

        <div class="nav-group-title">Finans</div>
        <button class="nav-link" data-page="faturalar" onclick="sayfaGit('faturalar')">
            <span class="icon">🧾</span> Faturalar
        </button>
    </nav>
</aside>

<!-- ===== ANA İÇERİK ===== -->
<div class="main-content">

    <header class="topbar">
        <h2 id="page-title">Dashboard</h2>
        <div class="topbar-actions">
            <span id="bugun" style="font-size:12px;color:var(--muted)"></span>
        </div>
    </header>

    <main class="page-content">

        <!-- DASHBOARD -->
        <div id="page-dashboard" class="page active">
            <div id="dashboard-content"></div>
        </div>

        <!-- FİRMALAR -->
        <div id="page-firmalar" class="page" style="display:none">
            <div class="card">
                <div class="card-header">
                    <h3>🏢 Kargo Firmaları</h3>
                    <div style="display:flex;gap:8px;align-items:center">
                        <div class="search-input">
                            <span class="search-icon">🔍</span>
                            <input type="text" id="firma-arama" class="form-control" placeholder="Firma ara..." oninput="yukle_firmalar()">
                        </div>
                        <button class="btn btn-primary" onclick="firma_yeni_form()">+ Yeni Firma</button>
                    </div>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Firma Adı / Yetkili</th>
                                <th>Vergi No</th>
                                <th>Telefon</th>
                                <th>E-posta</th>
                                <th>Sevkiyatlar</th>
                                <th>Toplam Fatura</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody id="firmalar-tbody">
                            <tr><td colspan="7"><div class="loading"><div class="spinner"></div></div></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- MÜŞTERİLER -->
        <div id="page-musteriler" class="page" style="display:none">
            <div class="card">
                <div class="card-header">
                    <h3>👥 Müşteriler</h3>
                    <div style="display:flex;gap:8px;align-items:center">
                        <div class="search-input">
                            <span class="search-icon">🔍</span>
                            <input type="text" id="musteri-arama" class="form-control" placeholder="Müşteri ara..." oninput="yukle_musteriler()">
                        </div>
                        <button class="btn btn-primary" onclick="musteri_yeni_form()">+ Yeni Müşteri</button>
                    </div>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Kod</th>
                                <th>Ad / Firma</th>
                                <th>Telefon</th>
                                <th>E-posta</th>
                                <th>Sevkiyat</th>
                                <th>Net Bakiye</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody id="musteriler-tbody">
                            <tr><td colspan="7"><div class="loading"><div class="spinner"></div></div></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- SEVKİYATLAR -->
        <div id="page-sevkiyatlar" class="page" style="display:none">
            <div class="card">
                <div class="card-header">
                    <h3>📦 Sevkiyatlar</h3>
                    <button class="btn btn-primary" onclick="sevkiyat_yeni_form()">+ Yeni Sevkiyat</button>
                </div>
                <div style="padding:12px 18px;border-bottom:1px solid var(--border);display:flex;gap:8px;flex-wrap:wrap;align-items:center">
                    <div class="search-input">
                        <span class="search-icon">🔍</span>
                        <input type="text" id="sevkiyat-arama" class="form-control" placeholder="Takip no ara..." oninput="yukle_sevkiyatlar()">
                    </div>
                    <select id="sevkiyat-durum-filtre" class="form-control" style="width:auto" onchange="yukle_sevkiyatlar()">
                        <option value="">Tüm Durumlar</option>
                        <option value="beklemede">Beklemede</option>
                        <option value="yolda">Yolda</option>
                        <option value="teslim_edildi">Teslim Edildi</option>
                        <option value="iptal">İptal</option>
                    </select>
                    <select id="sevkiyat-firma-filtre" class="form-control" style="width:auto" onchange="yukle_sevkiyatlar()">
                        <option value="">Tüm Firmalar</option>
                    </select>
                    <select id="sevkiyat-musteri-filtre" class="form-control" style="width:auto" onchange="yukle_sevkiyatlar()">
                        <option value="">Tüm Müşteriler</option>
                    </select>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Takip No</th>
                                <th>Müşteri</th>
                                <th>Kargo Firması</th>
                                <th>Güzergah</th>
                                <th>Sevk Tarihi</th>
                                <th>Teslim Tarihi</th>
                                <th>Durum</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody id="sevkiyatlar-tbody">
                            <tr><td colspan="8"><div class="loading"><div class="spinner"></div></div></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- FATURALAR -->
        <div id="page-faturalar" class="page" style="display:none">
            <div class="card">
                <div class="card-header">
                    <h3>🧾 Faturalar</h3>
                    <button class="btn btn-primary" onclick="fatura_yeni_form()">+ Yeni Fatura</button>
                </div>
                <div style="padding:12px 18px;border-bottom:1px solid var(--border);display:flex;gap:8px;flex-wrap:wrap;align-items:center">
                    <div class="search-input">
                        <span class="search-icon">🔍</span>
                        <input type="text" id="fatura-arama" class="form-control" placeholder="Fatura no ara..." oninput="yukle_faturalar()">
                    </div>
                    <select id="fatura-tur-filtre" class="form-control" style="width:auto" onchange="yukle_faturalar()">
                        <option value="">Tüm Türler</option>
                        <option value="giden">📤 Giden (Müşteriye)</option>
                        <option value="gelen">📥 Gelen (Firmadan)</option>
                    </select>
                    <select id="fatura-durum-filtre" class="form-control" style="width:auto" onchange="yukle_faturalar()">
                        <option value="">Tüm Durumlar</option>
                        <option value="odenmedi">Ödenmedi</option>
                        <option value="kismi_odendi">Kısmi Ödendi</option>
                        <option value="odendi">Ödendi</option>
                    </select>
                    <select id="fatura-musteri-filtre" class="form-control" style="width:auto" onchange="yukle_faturalar()" onclick="doldur_fatura_musteri_filtre()">
                        <option value="">Tüm Müşteriler</option>
                    </select>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Fatura No / Sevkiyat</th>
                                <th>Tür</th>
                                <th>İlgili</th>
                                <th>Fatura Tarihi</th>
                                <th>Vade</th>
                                <th>Toplam</th>
                                <th>Ödenen</th>
                                <th>Durum</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody id="faturalar-tbody">
                            <tr><td colspan="9"><div class="loading"><div class="spinner"></div></div></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </main>
</div>
</div>

<!-- ============================== -->
<!-- MODALLER -->
<!-- ============================== -->

<!-- FİRMA MODAL -->
<div id="firma-modal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <h3 id="firma-modal-title">Firma</h3>
            <button class="btn-close" onclick="modalKapat('firma-modal')">×</button>
        </div>
        <div class="modal-body">
            <form id="firma-form" onsubmit="return false">
                <input type="hidden" id="firma-id">
                <div class="form-grid form-grid-2">
                    <div class="form-group" style="grid-column:1/-1">
                        <label>Firma Adı *</label>
                        <input type="text" id="firma-adi" class="form-control" placeholder="Firma adını girin" required>
                    </div>
                    <div class="form-group">
                        <label>Vergi No</label>
                        <input type="text" id="firma-vergi" class="form-control" placeholder="Vergi numarası">
                    </div>
                    <div class="form-group">
                        <label>Yetkili Kişi</label>
                        <input type="text" id="firma-yetkili" class="form-control" placeholder="Yetkili adı">
                    </div>
                    <div class="form-group">
                        <label>Telefon</label>
                        <input type="tel" id="firma-telefon" class="form-control" placeholder="0xxx xxx xx xx">
                    </div>
                    <div class="form-group">
                        <label>E-posta</label>
                        <input type="email" id="firma-email" class="form-control" placeholder="email@firma.com">
                    </div>
                    <div class="form-group" style="grid-column:1/-1">
                        <label>Adres</label>
                        <textarea id="firma-adres" class="form-control" placeholder="Firma adresi"></textarea>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="modalKapat('firma-modal')">İptal</button>
            <button class="btn btn-primary" onclick="firma_kaydet()">💾 Kaydet</button>
        </div>
    </div>
</div>

<!-- MÜŞTERİ MODAL -->
<div id="musteri-modal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <h3 id="musteri-modal-title">Müşteri</h3>
            <button class="btn-close" onclick="modalKapat('musteri-modal')">×</button>
        </div>
        <div class="modal-body">
            <form id="musteri-form" onsubmit="return false">
                <input type="hidden" id="musteri-id">
                <div class="form-grid form-grid-2">
                    <div class="form-group">
                        <label>Ad Soyad *</label>
                        <input type="text" id="musteri-ad" class="form-control" placeholder="Ad Soyad" required>
                    </div>
                    <div class="form-group">
                        <label>Firma Adı</label>
                        <input type="text" id="musteri-firma" class="form-control" placeholder="Bağlı olduğu firma">
                    </div>
                    <div class="form-group">
                        <label>Vergi No</label>
                        <input type="text" id="musteri-vergi" class="form-control" placeholder="Vergi/TC no">
                    </div>
                    <div class="form-group">
                        <label>Telefon</label>
                        <input type="tel" id="musteri-telefon" class="form-control" placeholder="0xxx xxx xx xx">
                    </div>
                    <div class="form-group" style="grid-column:1/-1">
                        <label>E-posta</label>
                        <input type="email" id="musteri-email" class="form-control" placeholder="email@adres.com">
                    </div>
                    <div class="form-group" style="grid-column:1/-1">
                        <label>Adres</label>
                        <textarea id="musteri-adres" class="form-control" placeholder="Müşteri adresi"></textarea>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="modalKapat('musteri-modal')">İptal</button>
            <button class="btn btn-primary" onclick="musteri_kaydet()">💾 Kaydet</button>
        </div>
    </div>
</div>

<!-- MÜŞTERİ HAREKETLERİ MODAL -->
<div id="hareket-modal" class="modal-overlay">
    <div class="modal modal-xl">
        <div class="modal-header">
            <h3>💳 <span id="hareket-musteri-adi"></span> — Müşteri Hareketleri</h3>
            <button class="btn-close" onclick="modalKapat('hareket-modal')">×</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="hareket-musteri-id">

            <!-- Özet -->
            <div id="hareket-ozet"></div>

            <!-- Yeni Hareket Formu -->
            <div class="card" style="margin-bottom:16px">
                <div class="card-header"><h3>➕ Yeni Hareket Ekle</h3></div>
                <div class="card-body">
                    <form id="yeni-hareket-form" onsubmit="return false">
                        <div class="form-grid form-grid-3" style="margin-bottom:12px">
                            <div class="form-group">
                                <label>Hareket Türü *</label>
                                <select id="hareket-tur" class="form-control">
                                    <option value="giris">📥 Giriş (Ödeme Aldık)</option>
                                    <option value="cikis">📤 Çıkış (İade / Alacak)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Tutar (₺) *</label>
                                <input type="number" id="hareket-tutar" class="form-control" placeholder="0.00" step="0.01" min="0.01">
                            </div>
                            <div class="form-group">
                                <label>Tarih *</label>
                                <input type="date" id="hareket-tarih" class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Belge No</label>
                                <input type="text" id="hareket-belge" class="form-control" placeholder="Makbuz/dekont no">
                            </div>
                            <div class="form-group">
                                <label>Açıklama</label>
                                <input type="text" id="hareket-aciklama" class="form-control" placeholder="Açıklama">
                            </div>
                            <div class="form-group">
                                <label>Sevkiyat (opsiyonel)</label>
                                <select id="hareket-sevkiyat" class="form-control">
                                    <option value="">-- Sevkiyat Seçin --</option>
                                </select>
                            </div>
                        </div>
                        <button class="btn btn-primary" onclick="hareket_kaydet()">💾 Hareketi Kaydet</button>
                    </form>
                </div>
            </div>

            <!-- Hareket Listesi -->
            <div class="card">
                <div class="card-header"><h3>📋 Hareket Geçmişi</h3></div>
                <div id="hareket-listesi" style="overflow-x:auto"></div>
            </div>
        </div>
    </div>
</div>

<!-- SEVKİYAT MODAL -->
<div id="sevkiyat-modal" class="modal-overlay">
    <div class="modal modal-lg">
        <div class="modal-header">
            <h3 id="sevkiyat-modal-title">Sevkiyat</h3>
            <button class="btn-close" onclick="modalKapat('sevkiyat-modal')">×</button>
        </div>
        <div class="modal-body">
            <form id="sevkiyat-form" onsubmit="return false">
                <input type="hidden" id="sevkiyat-id">
                <div class="form-grid form-grid-2">
                    <div class="form-group">
                        <label>Müşteri *</label>
                        <select id="sevk-musteri-sel" class="form-control" required>
                            <option value="">-- Müşteri Seç --</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Kargo Firması *</label>
                        <select id="sevk-firma-sel" class="form-control" required>
                            <option value="">-- Firma Seç --</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Kalkış Yeri</label>
                        <input type="text" id="sevk-kalkis" class="form-control" placeholder="İstanbul">
                    </div>
                    <div class="form-group">
                        <label>Varış Yeri</label>
                        <input type="text" id="sevk-varis" class="form-control" placeholder="Ankara">
                    </div>
                    <div class="form-group">
                        <label>Sevkiyat Tarihi</label>
                        <input type="date" id="sevkiyat-tarih" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Teslim Tarihi</label>
                        <input type="date" id="sevk-teslim-tarih" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Ağırlık (kg)</label>
                        <input type="number" id="sevk-agirlik" class="form-control" placeholder="0.00" step="0.01">
                    </div>
                    <div class="form-group">
                        <label>Hacim (m³)</label>
                        <input type="number" id="sevk-hacim" class="form-control" placeholder="0.00" step="0.001">
                    </div>
                    <div class="form-group">
                        <label>Durum</label>
                        <select id="sevk-durum" class="form-control">
                            <option value="beklemede">Beklemede</option>
                            <option value="yolda">Yolda</option>
                            <option value="teslim_edildi">Teslim Edildi</option>
                            <option value="iptal">İptal</option>
                        </select>
                    </div>
                    <div class="form-group" style="grid-column:1/-1">
                        <label>Açıklama</label>
                        <textarea id="sevk-aciklama" class="form-control" placeholder="Notlar..."></textarea>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="modalKapat('sevkiyat-modal')">İptal</button>
            <button class="btn btn-primary" onclick="sevkiyat_kaydet()">💾 Kaydet</button>
        </div>
    </div>
</div>

<!-- FATURA OLUŞTUR MODAL -->
<div id="fatura-modal" class="modal-overlay">
    <div class="modal modal-xl">
        <div class="modal-header">
            <h3 id="fatura-modal-title">Fatura</h3>
            <button class="btn-close" onclick="modalKapat('fatura-modal')">×</button>
        </div>
        <div class="modal-body">
            <form id="fatura-form" onsubmit="return false">
                <input type="hidden" id="fatura-id">
                <div class="form-grid form-grid-3" style="margin-bottom:16px">
                    <div class="form-group">
                        <label>Fatura No *</label>
                        <input type="text" id="fatura-no" class="form-control" placeholder="FAT-2024-001" required>
                    </div>
                    <div class="form-group">
                        <label>Fatura Türü *</label>
                        <select id="fatura-tur-sel" class="form-control" onchange="fatura_tur_degisti()">
                            <option value="giden">📤 Giden (Müşteriye)</option>
                            <option value="gelen">📥 Gelen (Firmadan)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>KDV Oranı (%)</label>
                        <select id="fatura-kdv" class="form-control" onchange="fatura_toplam_hesapla()">
                            <option value="0">%0 - KDV Yok</option>
                            <option value="1">%1</option>
                            <option value="8">%8</option>
                            <option value="10">%10</option>
                            <option value="20" selected>%20</option>
                        </select>
                    </div>
                    <div class="form-group" id="fatura-musteri-grup">
                        <label>Müşteri</label>
                        <select id="fatura-musteri-sel" class="form-control">
                            <option value="">-- Müşteri Seç --</option>
                        </select>
                    </div>
                    <div class="form-group" id="fatura-firma-grup" style="display:none">
                        <label>Kargo Firması</label>
                        <select id="fatura-firma-sel" class="form-control">
                            <option value="">-- Firma Seç --</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Fatura Tarihi</label>
                        <input type="date" id="fatura-tarih" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Vade Tarihi</label>
                        <input type="date" id="fatura-vade" class="form-control">
                    </div>
                </div>

                <!-- Fatura Kalemleri -->
                <div style="margin-bottom:16px">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                        <strong>📋 Fatura Kalemleri</strong>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="fatura_kalem_ekle()">+ Kalem Ekle</button>
                    </div>
                    <div style="background:#f8fafc;border-radius:8px;padding:12px;margin-bottom:8px">
                        <div class="kalem-satir" style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;margin-bottom:6px">
                            <span>Açıklama</span><span>Miktar</span><span>Birim</span><span>Birim Fiyat</span><span>Toplam</span><span></span>
                        </div>
                        <div id="fatura-kalemler"></div>
                    </div>
                    <div style="text-align:right;padding:8px 12px;background:#f8fafc;border-radius:8px;font-size:13px">
                        <div style="display:flex;justify-content:flex-end;gap:24px">
                            <span>Ara Toplam: <strong id="fatura-ara-toplam" class="money">0,00 ₺</strong></span>
                            <span>KDV: <strong id="fatura-kdv-tutar" class="money">0,00 ₺</strong></span>
                            <span style="font-size:15px">Genel Toplam: <strong id="fatura-genel-toplam" class="money" style="color:var(--primary)">0,00 ₺</strong></span>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Açıklama / Not</label>
                    <textarea id="fatura-aciklama" class="form-control" placeholder="Fatura notu..."></textarea>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="modalKapat('fatura-modal')">İptal</button>
            <button class="btn btn-primary" onclick="fatura_kaydet()">💾 Faturayı Oluştur</button>
        </div>
    </div>
</div>

<!-- FATURA DETAY MODAL -->
<div id="fatura-detay-modal" class="modal-overlay">
    <div class="modal modal-xl">
        <div class="modal-header">
            <div>
                <h3>🧾 Fatura Detayı: <span id="detay-fatura-no"></span></h3>
                <span id="detay-fatura-tur"></span>
            </div>
            <button class="btn-close" onclick="modalKapat('fatura-detay-modal')">×</button>
        </div>
        <div class="modal-body">
            <!-- Fatura Bilgileri -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">
                <div class="card">
                    <div class="card-body">
                        <table style="width:100%;font-size:13px">
                            <tr><td style="color:#64748b;padding:4px 0">Fatura Tarihi:</td><td><strong id="detay-fatura-tarih"></strong></td></tr>
                            <tr><td style="color:#64748b;padding:4px 0">Vade Tarihi:</td><td><strong id="detay-vade"></strong></td></tr>
                            <tr><td style="color:#64748b;padding:4px 0">İlgili:</td><td><strong id="detay-ilgili"></strong></td></tr>
                            <tr><td style="color:#64748b;padding:4px 0">Durum:</td><td><span id="detay-durum"></span></td></tr>
                        </table>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <table style="width:100%;font-size:13px">
                            <tr><td style="color:#64748b;padding:4px 0">Ara Toplam:</td><td class="money"><strong id="detay-ara"></strong></td></tr>
                            <tr><td style="color:#64748b;padding:4px 0">KDV:</td><td class="money"><strong id="detay-kdv"></strong></td></tr>
                            <tr><td style="color:#64748b;padding:4px 0">Genel Toplam:</td><td class="money" style="font-size:16px;color:var(--primary)"><strong id="detay-genel"></strong></td></tr>
                            <tr><td style="color:#64748b;padding:4px 0">Ödenen:</td><td class="money money-green"><strong id="detay-odenen"></strong></td></tr>
                            <tr><td style="color:#64748b;padding:4px 0">Kalan:</td><td class="money money-red"><strong id="detay-kalan"></strong></td></tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Kalemler -->
            <div class="card" style="margin-bottom:16px">
                <div class="card-header"><h3>📋 Fatura Kalemleri</h3></div>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Açıklama</th><th>Miktar</th><th>Birim Fiyat</th><th>Toplam</th></tr></thead>
                        <tbody id="detay-kalemler"></tbody>
                    </table>
                </div>
            </div>

            <!-- Ödeme Ekle -->
            <div class="card" style="margin-bottom:16px">
                <div class="card-header"><h3>💳 Ödeme Ekle</h3></div>
                <div class="card-body">
                    <div class="form-grid form-grid-3">
                        <div class="form-group">
                            <label>Ödeme Tarihi *</label>
                            <input type="date" id="odeme-tarih" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Ödeme Tutarı (₺) *</label>
                            <input type="number" id="odeme-tutar" class="form-control" placeholder="0.00" step="0.01">
                        </div>
                        <div class="form-group">
                            <label>Ödeme Yöntemi</label>
                            <select id="odeme-yontem" class="form-control">
                                <option value="nakit">Nakit</option>
                                <option value="havale">Havale/EFT</option>
                                <option value="kredi_karti">Kredi Kartı</option>
                                <option value="cek">Çek</option>
                                <option value="diger">Diğer</option>
                            </select>
                        </div>
                        <div class="form-group" style="grid-column:1/-1">
                            <label>Açıklama</label>
                            <input type="text" id="odeme-aciklama" class="form-control" placeholder="Ödeme açıklaması">
                        </div>
                    </div>
                    <input type="hidden" id="odeme-fatura-id">
                    <button class="btn btn-success" onclick="odeme_kaydet()" style="margin-top:8px">💰 Ödemeyi Kaydet</button>
                </div>
            </div>

            <!-- Ödeme Geçmişi -->
            <div class="card">
                <div class="card-header"><h3>📜 Ödeme Geçmişi</h3></div>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Tarih</th><th>Tutar</th><th>Yöntem</th><th>Açıklama</th></tr></thead>
                        <tbody id="detay-hareketler"></tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="modalKapat('fatura-detay-modal')">Kapat</button>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div id="toast-container"></div>

<!-- ============================== -->
<!-- JAVASCRIPT -->
<!-- ============================== -->
<script src="assets/js/app.js"></script>
<script src="assets/js/dashboard.js"></script>
<script src="assets/js/firmalar.js"></script>
<script src="assets/js/musteriler.js"></script>
<script src="assets/js/sevkiyatlar.js"></script>
<script src="assets/js/faturalar.js"></script>

<script>
// Sayfa yönetimi - display toggle
const sayfaGitOrijinal = sayfaGit;
sayfaGit = function(sayfa) {
    document.querySelectorAll('.page').forEach(p => p.style.display = 'none');
    const hedef = document.getElementById('page-' + sayfa);
    if (hedef) hedef.style.display = 'block';

    document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
    document.querySelectorAll(`[data-page="${sayfa}"]`).forEach(l => l.classList.add('active'));

    const basliklar = {
        dashboard: '📊 Dashboard',
        firmalar: '🏢 Kargo Firmaları',
        musteriler: '👥 Müşteriler',
        sevkiyatlar: '📦 Sevkiyatlar',
        faturalar: '🧾 Faturalar',
    };
    document.getElementById('page-title').textContent = basliklar[sayfa] || sayfa;

    const yukle = {
        dashboard: yukle_dashboard,
        firmalar: yukle_firmalar,
        musteriler: yukle_musteriler,
        sevkiyatlar: async () => { await doldur_sevkiyat_filtreleri(); await yukle_sevkiyatlar(); },
        faturalar: yukle_faturalar,
    };
    if (yukle[sayfa]) yukle[sayfa]();
};

// Başlangıç
document.addEventListener('DOMContentLoaded', () => {
    // Bugünün tarihi
    document.getElementById('bugun').textContent = new Date().toLocaleDateString('tr-TR', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
    });

    // Hareket tarihini bugüne ayarla
    const ht = document.getElementById('hareket-tarih');
    if (ht) ht.value = new Date().toISOString().split('T')[0];

    // Dashboard'u yükle
    sayfaGit('dashboard');
});
</script>
</body>
</html>
