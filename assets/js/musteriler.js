let musteriler_veri = [];
let aktif_musteri_id = null;

async function yukle_musteriler() {
    const tbody = document.getElementById('musteriler-tbody');
    if (!tbody) return;
    tbody.innerHTML = `<tr><td colspan="7">${yukleniyorHTML()}</td></tr>`;
    const arama = document.getElementById('musteri-arama')?.value || '';
    musteriler_veri = await API.get(`api/musteriler.php${arama ? '?q=' + encodeURIComponent(arama) : ''}`);
    render_musteriler();
}

function render_musteriler() {
    const tbody = document.getElementById('musteriler-tbody');
    if (!musteriler_veri.length) {
        tbody.innerHTML = `<tr><td colspan="7">${bosHTML('Kayıtlı müşteri bulunamadı')}</td></tr>`;
        return;
    }
    tbody.innerHTML = musteriler_veri.map(m => {
        const bakiye = parseFloat(m.bakiye || 0);
        const bakiyeClass = bakiye >= 0 ? 'money-green' : 'money-red';
        return `
        <tr>
            <td><span class="badge badge-gray">${m.musteri_kodu}</span></td>
            <td>
                <strong>${m.ad_soyad}</strong>
                ${m.firma_adi ? `<br><small style="color:var(--muted)">${m.firma_adi}</small>` : ''}
            </td>
            <td>${m.telefon || '-'}</td>
            <td>${m.email || '-'}</td>
            <td><span class="badge badge-blue">${m.toplam_sevkiyat || 0}</span></td>
            <td class="money ${bakiyeClass}">${formatPara(bakiye)}</td>
            <td>
                <button class="btn btn-primary btn-sm btn-icon" title="Hareketler" onclick="musteri_hareketler_goster(${m.id}, '${m.ad_soyad}')">💳</button>
                <button class="btn btn-secondary btn-sm btn-icon" title="Düzenle" onclick="musteri_duzenle(${m.id})">✏️</button>
                <button class="btn btn-secondary btn-sm btn-icon" title="Faturalar" onclick="musteri_faturalar(${m.id})">🧾</button>
                <button class="btn btn-danger btn-sm btn-icon" title="Sil" onclick="musteri_sil(${m.id}, '${m.ad_soyad}')">🗑️</button>
            </td>
        </tr>
        `;
    }).join('');
}

function musteri_yeni_form() {
    document.getElementById('musteri-modal-title').textContent = 'Yeni Müşteri Ekle';
    document.getElementById('musteri-form').reset();
    document.getElementById('musteri-id').value = '';
    modalAc('musteri-modal');
}

async function musteri_duzenle(id) {
    const m = musteriler_veri.find(x => x.id == id) || await API.get(`api/musteriler.php?id=${id}`);
    document.getElementById('musteri-modal-title').textContent = 'Müşteriyi Düzenle';
    document.getElementById('musteri-id').value = m.id;
    document.getElementById('musteri-ad').value = m.ad_soyad;
    document.getElementById('musteri-firma').value = m.firma_adi || '';
    document.getElementById('musteri-vergi').value = m.vergi_no || '';
    document.getElementById('musteri-telefon').value = m.telefon || '';
    document.getElementById('musteri-email').value = m.email || '';
    document.getElementById('musteri-adres').value = m.adres || '';
    modalAc('musteri-modal');
}

async function musteri_kaydet() {
    const id = document.getElementById('musteri-id').value;
    const data = {
        ad_soyad: document.getElementById('musteri-ad').value,
        firma_adi: document.getElementById('musteri-firma').value,
        vergi_no: document.getElementById('musteri-vergi').value,
        telefon: document.getElementById('musteri-telefon').value,
        email: document.getElementById('musteri-email').value,
        adres: document.getElementById('musteri-adres').value,
    };
    if (!data.ad_soyad) { toast('Ad soyad zorunludur', 'error'); return; }
    const sonuc = id
        ? await API.put(`api/musteriler.php?id=${id}`, data)
        : await API.post('api/musteriler.php', data);
    if (sonuc.hata) { toast(sonuc.hata, 'error'); return; }
    toast(sonuc.mesaj);
    modalKapat('musteri-modal');
    yukle_musteriler();
}

async function musteri_sil(id, ad) {
    if (!confirm(`"${ad}" müşterisini silmek istediğinizden emin misiniz?`)) return;
    const sonuc = await API.delete(`api/musteriler.php?id=${id}`);
    toast(sonuc.mesaj || sonuc.hata, sonuc.hata ? 'error' : 'success');
    yukle_musteriler();
}

// ===== MÜŞTERİ HAREKETLERİ =====
async function musteri_hareketler_goster(id, ad) {
    aktif_musteri_id = id;
    document.getElementById('hareket-musteri-adi').textContent = ad;
    document.getElementById('hareket-listesi').innerHTML = yukleniyorHTML();
    document.getElementById('hareket-musteri-id').value = id;
    modalAc('hareket-modal');
    await yukle_musteri_hareketleri(id);
}

async function yukle_musteri_hareketleri(id) {
    const veri = await API.get(`api/musteriler.php?id=${id}&hareketler=1`);
    const ozet = veri.ozet || {};
    const hareketler = veri.hareketler || [];

    document.getElementById('hareket-ozet').innerHTML = `
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:16px;">
            <div class="stat-card" style="padding:12px">
                <div class="stat-icon green">📥</div>
                <div class="stat-info">
                    <div class="label">Toplam Giriş</div>
                    <div class="value" style="font-size:16px;color:var(--success)">${formatPara(ozet.toplam_giris)}</div>
                </div>
            </div>
            <div class="stat-card" style="padding:12px">
                <div class="stat-icon red">📤</div>
                <div class="stat-info">
                    <div class="label">Toplam Çıkış</div>
                    <div class="value" style="font-size:16px;color:var(--danger)">${formatPara(ozet.toplam_cikis)}</div>
                </div>
            </div>
            <div class="stat-card" style="padding:12px">
                <div class="stat-icon ${parseFloat(ozet.toplam_giris)-parseFloat(ozet.toplam_cikis)>=0?'green':'red'}">⚖️</div>
                <div class="stat-info">
                    <div class="label">Net Bakiye</div>
                    <div class="value" style="font-size:16px;color:${parseFloat(ozet.toplam_giris)-parseFloat(ozet.toplam_cikis)>=0?'var(--success)':'var(--danger)'}">
                        ${formatPara(parseFloat(ozet.toplam_giris||0)-parseFloat(ozet.toplam_cikis||0))}
                    </div>
                </div>
            </div>
        </div>
    `;

    if (!hareketler.length) {
        document.getElementById('hareket-listesi').innerHTML = bosHTML('Henüz hareket yok');
        return;
    }
    document.getElementById('hareket-listesi').innerHTML = `
        <table style="width:100%;border-collapse:collapse">
            <thead><tr>
                <th style="text-align:left;padding:8px;background:#f8fafc;font-size:11px;color:#64748b">Tarih</th>
                <th style="text-align:left;padding:8px;background:#f8fafc;font-size:11px;color:#64748b">Tür</th>
                <th style="text-align:left;padding:8px;background:#f8fafc;font-size:11px;color:#64748b">Tutar</th>
                <th style="text-align:left;padding:8px;background:#f8fafc;font-size:11px;color:#64748b">Belge No</th>
                <th style="text-align:left;padding:8px;background:#f8fafc;font-size:11px;color:#64748b">Açıklama</th>
                <th style="text-align:left;padding:8px;background:#f8fafc;font-size:11px;color:#64748b">Sevkiyat</th>
            </tr></thead>
            <tbody>
                ${hareketler.map(h => `
                    <tr>
                        <td style="padding:8px;border-bottom:1px solid #f1f5f9">${formatTarih(h.hareket_tarihi)}</td>
                        <td style="padding:8px;border-bottom:1px solid #f1f5f9">
                            ${h.hareket_turu === 'giris'
                                ? '<span class="badge badge-green">📥 Giriş</span>'
                                : '<span class="badge badge-red">📤 Çıkış</span>'}
                        </td>
                        <td style="padding:8px;border-bottom:1px solid #f1f5f9" class="money ${h.hareket_turu==='giris'?'money-green':'money-red'}">
                            ${h.hareket_turu==='giris'?'+':'-'}${formatPara(h.tutar)}
                        </td>
                        <td style="padding:8px;border-bottom:1px solid #f1f5f9">${h.belge_no || '-'}</td>
                        <td style="padding:8px;border-bottom:1px solid #f1f5f9">${h.aciklama || '-'}</td>
                        <td style="padding:8px;border-bottom:1px solid #f1f5f9">${h.takip_no ? `<span class="badge badge-blue">${h.takip_no}</span>` : '-'}</td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
    `;
}

async function hareket_kaydet() {
    const id = document.getElementById('hareket-musteri-id').value;
    const data = {
        hareket: {
            hareket_turu: document.getElementById('hareket-tur').value,
            tutar: document.getElementById('hareket-tutar').value,
            belge_no: document.getElementById('hareket-belge').value,
            aciklama: document.getElementById('hareket-aciklama').value,
            hareket_tarihi: document.getElementById('hareket-tarih').value,
            sevkiyat_id: document.getElementById('hareket-sevkiyat').value || null,
        }
    };
    if (!data.hareket.tutar || !data.hareket.hareket_tarihi) {
        toast('Tutar ve tarih zorunludur', 'error'); return;
    }
    const sonuc = await API.put(`api/musteriler.php?id=${id}`, data);
    if (sonuc.hata) { toast(sonuc.hata, 'error'); return; }
    toast('Hareket kaydedildi');
    document.getElementById('yeni-hareket-form').reset();
    document.getElementById('hareket-tarih').value = new Date().toISOString().split('T')[0];
    yukle_musteri_hareketleri(id);
    yukle_musteriler();
}

function musteri_faturalar(musteriId) {
    sayfaGit('faturalar');
    setTimeout(() => {
        const sel = document.getElementById('fatura-musteri-filtre');
        if (sel) { sel.value = musteriId; yukle_faturalar(); }
    }, 100);
}

// Sevkiyat seçeneklerini doldur
async function doldur_sevkiyat_secenekleri(selectId, musteriId) {
    const sel = document.getElementById(selectId);
    if (!sel) return;
    const veriler = await API.get(`api/sevkiyatlar.php${musteriId ? '?musteri_id=' + musteriId : ''}`);
    sel.innerHTML = '<option value="">-- Sevkiyat Seç (opsiyonel) --</option>' +
        veriler.map(s => `<option value="${s.id}">${s.takip_no} - ${s.kalkis_yeri || ''} → ${s.varis_yeri || ''}</option>`).join('');
}
