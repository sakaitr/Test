let firmalar_veri = [];

async function yukle_firmalar() {
    const tbody = document.getElementById('firmalar-tbody');
    if (!tbody) return;
    tbody.innerHTML = `<tr><td colspan="7">${yukleniyorHTML()}</td></tr>`;
    const arama = document.getElementById('firma-arama')?.value || '';
    firmalar_veri = await API.get(`api/firmalar.php${arama ? '?q=' + encodeURIComponent(arama) : ''}`);
    render_firmalar();
}

function render_firmalar() {
    const tbody = document.getElementById('firmalar-tbody');
    if (!firmalar_veri.length) {
        tbody.innerHTML = `<tr><td colspan="7">${bosHTML('Kayıtlı firma bulunamadı')}</td></tr>`;
        return;
    }
    tbody.innerHTML = firmalar_veri.map(f => `
        <tr>
            <td><strong>${f.firma_adi}</strong>${f.yetkili_kisi ? `<br><small class="muted">${f.yetkili_kisi}</small>` : ''}</td>
            <td>${f.vergi_no || '-'}</td>
            <td>${f.telefon || '-'}</td>
            <td>${f.email || '-'}</td>
            <td><span class="badge badge-blue">${f.toplam_sevkiyat || 0} sevkiyat</span>${f.aktif_sevkiyat > 0 ? ` <span class="badge badge-orange">${f.aktif_sevkiyat} aktif</span>` : ''}</td>
            <td class="money">${formatPara(f.toplam_fatura_tutari)}</td>
            <td>
                <button class="btn btn-secondary btn-sm btn-icon" title="Düzenle" onclick="firma_duzenle(${f.id})">✏️</button>
                <button class="btn btn-secondary btn-sm btn-icon" title="Sevkiyatlar" onclick="firma_sevkiyatlar(${f.id})">📦</button>
                <button class="btn btn-danger btn-sm btn-icon" title="Sil" onclick="firma_sil(${f.id}, '${f.firma_adi}')">🗑️</button>
            </td>
        </tr>
    `).join('');
}

function firma_yeni_form() {
    document.getElementById('firma-modal-title').textContent = 'Yeni Firma Ekle';
    document.getElementById('firma-form').reset();
    document.getElementById('firma-id').value = '';
    modalAc('firma-modal');
}

async function firma_duzenle(id) {
    const firma = firmalar_veri.find(f => f.id == id) || await API.get(`api/firmalar.php?id=${id}`);
    document.getElementById('firma-modal-title').textContent = 'Firmayı Düzenle';
    document.getElementById('firma-id').value = firma.id;
    document.getElementById('firma-adi').value = firma.firma_adi;
    document.getElementById('firma-vergi').value = firma.vergi_no || '';
    document.getElementById('firma-telefon').value = firma.telefon || '';
    document.getElementById('firma-email').value = firma.email || '';
    document.getElementById('firma-yetkili').value = firma.yetkili_kisi || '';
    document.getElementById('firma-adres').value = firma.adres || '';
    modalAc('firma-modal');
}

async function firma_kaydet() {
    const id = document.getElementById('firma-id').value;
    const data = {
        firma_adi: document.getElementById('firma-adi').value,
        vergi_no: document.getElementById('firma-vergi').value,
        telefon: document.getElementById('firma-telefon').value,
        email: document.getElementById('firma-email').value,
        yetkili_kisi: document.getElementById('firma-yetkili').value,
        adres: document.getElementById('firma-adres').value,
    };
    if (!data.firma_adi) { toast('Firma adı zorunludur', 'error'); return; }
    const sonuc = id
        ? await API.put(`api/firmalar.php?id=${id}`, data)
        : await API.post('api/firmalar.php', data);
    if (sonuc.hata) { toast(sonuc.hata, 'error'); return; }
    toast(sonuc.mesaj);
    modalKapat('firma-modal');
    yukle_firmalar();
}

async function firma_sil(id, ad) {
    if (!confirm(`"${ad}" firmasını pasife almak istediğinizden emin misiniz?`)) return;
    const sonuc = await API.delete(`api/firmalar.php?id=${id}`);
    toast(sonuc.mesaj || sonuc.hata, sonuc.hata ? 'error' : 'success');
    yukle_firmalar();
}

function firma_sevkiyatlar(firmaId) {
    // Sevkiyatlar sayfasına git ve filtrele
    sayfaGit('sevkiyatlar');
    setTimeout(() => {
        const sel = document.getElementById('sevkiyat-firma-filtre');
        if (sel) { sel.value = firmaId; yukle_sevkiyatlar(); }
    }, 100);
}
