let sevkiyatlar_veri = [];
let sevk_firmalar = [];
let sevk_musteriler = [];

async function yukle_sevkiyatlar() {
    const tbody = document.getElementById('sevkiyatlar-tbody');
    if (!tbody) return;
    tbody.innerHTML = `<tr><td colspan="8">${yukleniyorHTML()}</td></tr>`;

    const params = new URLSearchParams();
    const durum = document.getElementById('sevkiyat-durum-filtre')?.value;
    const firma = document.getElementById('sevkiyat-firma-filtre')?.value;
    const musteri = document.getElementById('sevkiyat-musteri-filtre')?.value;
    const arama = document.getElementById('sevkiyat-arama')?.value;
    if (durum) params.set('durum', durum);
    if (firma) params.set('firma_id', firma);
    if (musteri) params.set('musteri_id', musteri);
    if (arama) params.set('q', arama);

    sevkiyatlar_veri = await API.get(`api/sevkiyatlar.php?${params}`);
    render_sevkiyatlar();
}

function render_sevkiyatlar() {
    const tbody = document.getElementById('sevkiyatlar-tbody');
    if (!sevkiyatlar_veri.length) {
        tbody.innerHTML = `<tr><td colspan="8">${bosHTML('Sevkiyat bulunamadı')}</td></tr>`;
        return;
    }
    tbody.innerHTML = sevkiyatlar_veri.map(s => `
        <tr>
            <td><strong>${s.takip_no}</strong></td>
            <td>${s.ad_soyad}<br><small style="color:var(--muted)">${s.musteri_kodu}</small></td>
            <td><span class="badge badge-blue">${s.kargo_firma}</span></td>
            <td>${s.kalkis_yeri || '-'} → ${s.varis_yeri || '-'}</td>
            <td>${formatTarih(s.sevkiyat_tarihi)}</td>
            <td>${s.teslim_tarihi ? formatTarih(s.teslim_tarihi) : '-'}</td>
            <td>${durumBadge(s.durum)}</td>
            <td>
                <button class="btn btn-secondary btn-sm btn-icon" title="Düzenle" onclick="sevkiyat_duzenle(${s.id})">✏️</button>
                <button class="btn btn-danger btn-sm btn-icon" title="İptal Et" onclick="sevkiyat_iptal(${s.id}, '${s.takip_no}')">🗑️</button>
            </td>
        </tr>
    `).join('');
}

async function sevkiyat_yeni_form() {
    document.getElementById('sevkiyat-modal-title').textContent = 'Yeni Sevkiyat Oluştur';
    document.getElementById('sevkiyat-form').reset();
    document.getElementById('sevkiyat-id').value = '';
    document.getElementById('sevkiyat-tarih').value = new Date().toISOString().split('T')[0];
    await doldur_firma_secenekleri('sevk-firma-sel');
    await doldur_musteri_secenekleri('sevk-musteri-sel');
    modalAc('sevkiyat-modal');
}

async function sevkiyat_duzenle(id) {
    const s = sevkiyatlar_veri.find(x => x.id == id) || await API.get(`api/sevkiyatlar.php?id=${id}`);
    document.getElementById('sevkiyat-modal-title').textContent = 'Sevkiyatı Düzenle';
    await doldur_firma_secenekleri('sevk-firma-sel');
    await doldur_musteri_secenekleri('sevk-musteri-sel');
    document.getElementById('sevkiyat-id').value = s.id;
    document.getElementById('sevk-musteri-sel').value = s.musteri_id;
    document.getElementById('sevk-firma-sel').value = s.firma_id;
    document.getElementById('sevk-kalkis').value = s.kalkis_yeri || '';
    document.getElementById('sevk-varis').value = s.varis_yeri || '';
    document.getElementById('sevkiyat-tarih').value = s.sevkiyat_tarihi || '';
    document.getElementById('sevk-teslim-tarih').value = s.teslim_tarihi || '';
    document.getElementById('sevk-agirlik').value = s.agirlik || '';
    document.getElementById('sevk-hacim').value = s.hacim || '';
    document.getElementById('sevk-durum').value = s.durum;
    document.getElementById('sevk-aciklama').value = s.aciklama || '';
    modalAc('sevkiyat-modal');
}

async function sevkiyat_kaydet() {
    const id = document.getElementById('sevkiyat-id').value;
    const data = {
        musteri_id: document.getElementById('sevk-musteri-sel').value,
        firma_id: document.getElementById('sevk-firma-sel').value,
        kalkis_yeri: document.getElementById('sevk-kalkis').value,
        varis_yeri: document.getElementById('sevk-varis').value,
        sevkiyat_tarihi: document.getElementById('sevkiyat-tarih').value,
        teslim_tarihi: document.getElementById('sevk-teslim-tarih').value,
        agirlik: document.getElementById('sevk-agirlik').value,
        hacim: document.getElementById('sevk-hacim').value,
        durum: document.getElementById('sevk-durum').value,
        aciklama: document.getElementById('sevk-aciklama').value,
    };
    if (!data.musteri_id || !data.firma_id) {
        toast('Müşteri ve firma seçimi zorunludur', 'error'); return;
    }
    const sonuc = id
        ? await API.put(`api/sevkiyatlar.php?id=${id}`, data)
        : await API.post('api/sevkiyatlar.php', data);
    if (sonuc.hata) { toast(sonuc.hata, 'error'); return; }
    toast(sonuc.mesaj + (sonuc.takip_no ? ` (${sonuc.takip_no})` : ''));
    modalKapat('sevkiyat-modal');
    yukle_sevkiyatlar();
}

async function sevkiyat_iptal(id, takipNo) {
    if (!confirm(`"${takipNo}" sevkiyatını iptal etmek istediğinizden emin misiniz?`)) return;
    const sonuc = await API.delete(`api/sevkiyatlar.php?id=${id}`);
    toast(sonuc.mesaj, sonuc.hata ? 'error' : 'success');
    yukle_sevkiyatlar();
}

async function doldur_firma_secenekleri(selectId) {
    if (!sevk_firmalar.length) {
        sevk_firmalar = await API.get('api/firmalar.php');
    }
    const sel = document.getElementById(selectId);
    if (!sel) return;
    sel.innerHTML = '<option value="">-- Kargo Firması Seç --</option>' +
        sevk_firmalar.map(f => `<option value="${f.id}">${f.firma_adi}</option>`).join('');
}

async function doldur_musteri_secenekleri(selectId) {
    if (!sevk_musteriler.length) {
        sevk_musteriler = await API.get('api/musteriler.php');
    }
    const sel = document.getElementById(selectId);
    if (!sel) return;
    sel.innerHTML = '<option value="">-- Müşteri Seç --</option>' +
        sevk_musteriler.map(m => `<option value="${m.id}">${m.ad_soyad} (${m.musteri_kodu})</option>`).join('');
}

// Filtre selectlerini doldur
async function doldur_sevkiyat_filtreleri() {
    const firmaSel = document.getElementById('sevkiyat-firma-filtre');
    const musteriSel = document.getElementById('sevkiyat-musteri-filtre');
    if (!firmaSel) return;
    const [firmalar, musteriler] = await Promise.all([
        API.get('api/firmalar.php'),
        API.get('api/musteriler.php')
    ]);
    firmaSel.innerHTML = '<option value="">Tüm Firmalar</option>' +
        firmalar.map(f => `<option value="${f.id}">${f.firma_adi}</option>`).join('');
    musteriSel.innerHTML = '<option value="">Tüm Müşteriler</option>' +
        musteriler.map(m => `<option value="${m.id}">${m.ad_soyad}</option>`).join('');
    sevk_firmalar = firmalar;
    sevk_musteriler = musteriler;
}
