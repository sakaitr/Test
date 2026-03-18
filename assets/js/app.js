// ===== YARDIMCI FONKSİYONLAR =====

const API = {
    base: '',
    async get(url) {
        const res = await fetch(this.base + url);
        return res.json();
    },
    async post(url, data) {
        const res = await fetch(this.base + url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        return res.json();
    },
    async put(url, data) {
        const res = await fetch(this.base + url, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        return res.json();
    },
    async delete(url) {
        const res = await fetch(this.base + url, { method: 'DELETE' });
        return res.json();
    }
};

// Para formatlama
function formatPara(tutar) {
    if (tutar === null || tutar === undefined || tutar === '') return '0,00 ₺';
    return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(tutar);
}

// Tarih formatlama
function formatTarih(tarih) {
    if (!tarih) return '-';
    const d = new Date(tarih);
    return d.toLocaleDateString('tr-TR', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

// Toast mesajı
function toast(mesaj, tur = 'success') {
    const container = document.getElementById('toast-container');
    const el = document.createElement('div');
    el.className = `toast ${tur}`;
    const ikonlar = { success: '✓', error: '✗', warning: '⚠' };
    el.innerHTML = `<span>${ikonlar[tur] || '•'}</span> ${mesaj}`;
    container.appendChild(el);
    setTimeout(() => el.remove(), 3500);
}

// Modal aç/kapat
function modalAc(id) {
    document.getElementById(id)?.classList.add('open');
}
function modalKapat(id) {
    document.getElementById(id)?.classList.remove('open');
}

// Yükleniyor
function yukleniyorHTML() {
    return `<div class="loading"><div class="spinner"></div> Yükleniyor...</div>`;
}

// Boş durum
function bosHTML(mesaj = 'Kayıt bulunamadı') {
    return `<div class="empty-state"><div class="icon">📭</div><p>${mesaj}</p></div>`;
}

// Durum badge
function durumBadge(durum) {
    const harita = {
        beklemede: ['badge-orange', '⏳ Beklemede'],
        yolda: ['badge-blue', '🚚 Yolda'],
        teslim_edildi: ['badge-green', '✅ Teslim Edildi'],
        iptal: ['badge-red', '❌ İptal'],
        odenmedi: ['badge-red', '⏳ Ödenmedi'],
        kismi_odendi: ['badge-orange', '🔶 Kısmi Ödendi'],
        odendi: ['badge-green', '✅ Ödendi'],
        gelen: ['badge-blue', '📥 Gelen'],
        giden: ['badge-purple', '📤 Giden'],
    };
    const [cls, label] = harita[durum] || ['badge-gray', durum];
    return `<span class="badge ${cls}">${label}</span>`;
}

// Sayfa navigasyonu
function sayfaGit(sayfa) {
    document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
    const hedef = document.getElementById('page-' + sayfa);
    if (hedef) hedef.classList.add('active');
    document.querySelectorAll(`[data-page="${sayfa}"]`).forEach(l => l.classList.add('active'));
    const baslik = document.getElementById('page-title');
    const basliklar = {
        dashboard: 'Dashboard',
        firmalar: 'Kargo Firmaları',
        musteriler: 'Müşteriler',
        sevkiyatlar: 'Sevkiyatlar',
        faturalar: 'Faturalar',
    };
    if (baslik) baslik.textContent = basliklar[sayfa] || sayfa;

    // Sayfayı yükle
    const yukle = {
        dashboard: yukle_dashboard,
        firmalar: yukle_firmalar,
        musteriler: yukle_musteriler,
        sevkiyatlar: yukle_sevkiyatlar,
        faturalar: yukle_faturalar,
    };
    if (yukle[sayfa]) yukle[sayfa]();
}

// Overlay tıklamasında kapat
document.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('open');
    }
});

// ESC ile kapat
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.open').forEach(m => m.classList.remove('open'));
    }
});
