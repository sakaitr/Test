let faturalar_veri = [];
let fatura_kalem_sayac = 0;

async function yukle_faturalar() {
    const tbody = document.getElementById('faturalar-tbody');
    if (!tbody) return;
    tbody.innerHTML = `<tr><td colspan="8">${yukleniyorHTML()}</td></tr>`;

    const params = new URLSearchParams();
    const tur = document.getElementById('fatura-tur-filtre')?.value;
    const durum = document.getElementById('fatura-durum-filtre')?.value;
    const musteriId = document.getElementById('fatura-musteri-filtre')?.value;
    const arama = document.getElementById('fatura-arama')?.value;
    if (tur) params.set('tur', tur);
    if (durum) params.set('durum', durum);
    if (musteriId) params.set('musteri_id', musteriId);
    if (arama) params.set('q', arama);

    faturalar_veri = await API.get(`api/faturalar.php?${params}`);
    render_faturalar();
}

function render_faturalar() {
    const tbody = document.getElementById('faturalar-tbody');
    if (!faturalar_veri.length) {
        tbody.innerHTML = `<tr><td colspan="8">${bosHTML('Fatura bulunamadı')}</td></tr>`;
        return;
    }
    tbody.innerHTML = faturalar_veri.map(f => `
        <tr>
            <td><strong>${f.fatura_no}</strong>${f.takip_no ? `<br><small style="color:var(--muted)">${f.takip_no}</small>` : ''}</td>
            <td>${durumBadge(f.fatura_turu)}</td>
            <td>${f.ad_soyad || f.musteri_firma || f.kargo_firma || '-'}</td>
            <td>${formatTarih(f.fatura_tarihi)}</td>
            <td>${f.vade_tarihi ? formatTarih(f.vade_tarihi) : '-'}</td>
            <td class="money">${formatPara(f.genel_toplam)}</td>
            <td class="money money-green">${formatPara(f.odenen_tutar)}</td>
            <td>${durumBadge(f.odeme_durumu)}</td>
            <td>
                <button class="btn btn-primary btn-sm btn-icon" title="Detay / Ödeme" onclick="fatura_detay(${f.id})">👁️</button>
                <button class="btn btn-danger btn-sm btn-icon" title="Sil" onclick="fatura_sil(${f.id}, '${f.fatura_no}')">🗑️</button>
            </td>
        </tr>
    `).join('');
}

async function fatura_yeni_form() {
    document.getElementById('fatura-modal-title').textContent = 'Yeni Fatura Oluştur';
    document.getElementById('fatura-form').reset();
    document.getElementById('fatura-id').value = '';
    document.getElementById('fatura-tarih').value = new Date().toISOString().split('T')[0];
    document.getElementById('fatura-kalemler').innerHTML = '';
    fatura_kalem_sayac = 0;
    fatura_kalem_ekle();
    await doldur_fatura_secenekleri();
    fatura_tur_degisti();
    modalAc('fatura-modal');
}

async function doldur_fatura_secenekleri() {
    const [firmalar, musteriler] = await Promise.all([
        API.get('api/firmalar.php'),
        API.get('api/musteriler.php')
    ]);
    const firmaSel = document.getElementById('fatura-firma-sel');
    const musteriSel = document.getElementById('fatura-musteri-sel');
    firmaSel.innerHTML = '<option value="">-- Firma Seç --</option>' +
        firmalar.map(f => `<option value="${f.id}">${f.firma_adi}</option>`).join('');
    musteriSel.innerHTML = '<option value="">-- Müşteri Seç --</option>' +
        musteriler.map(m => `<option value="${m.id}">${m.ad_soyad} (${m.musteri_kodu})</option>`).join('');
}

function fatura_tur_degisti() {
    const tur = document.getElementById('fatura-tur-sel').value;
    document.getElementById('fatura-musteri-grup').style.display = tur === 'giden' ? 'block' : 'none';
    document.getElementById('fatura-firma-grup').style.display = tur === 'gelen' ? 'block' : 'none';
}

function fatura_kalem_ekle() {
    const i = ++fatura_kalem_sayac;
    const div = document.createElement('div');
    div.className = 'kalem-satir';
    div.id = `kalem-${i}`;
    div.innerHTML = `
        <input class="form-control" placeholder="Açıklama" id="k-aciklama-${i}" required>
        <input class="form-control" type="number" placeholder="Miktar" id="k-miktar-${i}" value="1" min="0.01" step="0.01" onchange="fatura_toplam_hesapla()">
        <input class="form-control" placeholder="Birim" id="k-birim-${i}" value="adet">
        <input class="form-control" type="number" placeholder="Birim Fiyat" id="k-fiyat-${i}" step="0.01" onchange="fatura_toplam_hesapla()">
        <span id="k-toplam-${i}" class="money" style="font-size:13px">0,00 ₺</span>
        <button type="button" class="btn btn-danger btn-sm btn-icon" onclick="kalem_sil('kalem-${i}')">×</button>
    `;
    document.getElementById('fatura-kalemler').appendChild(div);
}

function kalem_sil(id) {
    document.getElementById(id)?.remove();
    fatura_toplam_hesapla();
}

function fatura_toplam_hesapla() {
    let ara = 0;
    document.querySelectorAll('[id^="k-miktar-"]').forEach(el => {
        const i = el.id.replace('k-miktar-', '');
        const m = parseFloat(el.value) || 0;
        const p = parseFloat(document.getElementById(`k-fiyat-${i}`)?.value) || 0;
        const t = m * p;
        ara += t;
        const topEl = document.getElementById(`k-toplam-${i}`);
        if (topEl) topEl.textContent = formatPara(t);
    });
    const kdv = parseFloat(document.getElementById('fatura-kdv').value) || 20;
    const kdvT = ara * (kdv / 100);
    const genel = ara + kdvT;
    document.getElementById('fatura-ara-toplam').textContent = formatPara(ara);
    document.getElementById('fatura-kdv-tutar').textContent = formatPara(kdvT);
    document.getElementById('fatura-genel-toplam').textContent = formatPara(genel);
}

async function fatura_kaydet() {
    const kalemler = [];
    document.querySelectorAll('[id^="k-miktar-"]').forEach(el => {
        const i = el.id.replace('k-miktar-', '');
        const aciklama = document.getElementById(`k-aciklama-${i}`)?.value;
        const miktar = parseFloat(el.value) || 0;
        const birim_fiyat = parseFloat(document.getElementById(`k-fiyat-${i}`)?.value) || 0;
        if (aciklama && miktar > 0 && birim_fiyat > 0) {
            kalemler.push({ aciklama, miktar, birim: document.getElementById(`k-birim-${i}`)?.value || 'adet', birim_fiyat });
        }
    });
    if (!kalemler.length) { toast('En az bir kalem giriniz', 'error'); return; }

    const tur = document.getElementById('fatura-tur-sel').value;
    const data = {
        fatura_no: document.getElementById('fatura-no').value,
        fatura_turu: tur,
        musteri_id: tur === 'giden' ? document.getElementById('fatura-musteri-sel').value : null,
        firma_id: tur === 'gelen' ? document.getElementById('fatura-firma-sel').value : null,
        fatura_tarihi: document.getElementById('fatura-tarih').value,
        vade_tarihi: document.getElementById('fatura-vade').value,
        kdv_orani: document.getElementById('fatura-kdv').value,
        aciklama: document.getElementById('fatura-aciklama').value,
        kalemler,
    };
    if (!data.fatura_no || !data.fatura_turu) { toast('Fatura no ve tür zorunludur', 'error'); return; }

    const sonuc = await API.post('api/faturalar.php', data);
    if (sonuc.hata) { toast(sonuc.hata, 'error'); return; }
    toast('Fatura oluşturuldu');
    modalKapat('fatura-modal');
    yukle_faturalar();
}

// ===== FATURA DETAY & ÖDEME =====
async function fatura_detay(id) {
    const fatura = await API.get(`api/faturalar.php?id=${id}`);
    const hareketler = await API.get(`api/faturalar.php?id=${id}&hareketler=1`);

    document.getElementById('detay-fatura-no').textContent = fatura.fatura_no;
    document.getElementById('detay-fatura-tur').innerHTML = durumBadge(fatura.fatura_turu);
    document.getElementById('detay-fatura-tarih').textContent = formatTarih(fatura.fatura_tarihi);
    document.getElementById('detay-vade').textContent = formatTarih(fatura.vade_tarihi);
    document.getElementById('detay-ara').textContent = formatPara(fatura.ara_toplam);
    document.getElementById('detay-kdv').textContent = `%${fatura.kdv_orani} = ${formatPara(fatura.kdv_tutari)}`;
    document.getElementById('detay-genel').textContent = formatPara(fatura.genel_toplam);
    document.getElementById('detay-odenen').textContent = formatPara(fatura.odenen_tutar);
    document.getElementById('detay-kalan').textContent = formatPara(fatura.genel_toplam - fatura.odenen_tutar);
    document.getElementById('detay-durum').innerHTML = durumBadge(fatura.odeme_durumu);
    document.getElementById('detay-ilgili').textContent = fatura.ad_soyad || fatura.kargo_firma || '-';

    // Kalemler
    document.getElementById('detay-kalemler').innerHTML = (fatura.kalemler || []).map(k => `
        <tr>
            <td style="padding:6px 10px;border-bottom:1px solid #f1f5f9">${k.aciklama}</td>
            <td style="padding:6px 10px;border-bottom:1px solid #f1f5f9">${k.miktar} ${k.birim}</td>
            <td style="padding:6px 10px;border-bottom:1px solid #f1f5f9" class="money">${formatPara(k.birim_fiyat)}</td>
            <td style="padding:6px 10px;border-bottom:1px solid #f1f5f9" class="money"><strong>${formatPara(k.toplam)}</strong></td>
        </tr>
    `).join('');

    // Ödeme hareketleri
    document.getElementById('detay-hareketler').innerHTML = hareketler.length ? hareketler.map(h => `
        <tr>
            <td style="padding:6px 10px;border-bottom:1px solid #f1f5f9">${formatTarih(h.odeme_tarihi)}</td>
            <td style="padding:6px 10px;border-bottom:1px solid #f1f5f9" class="money money-green">${formatPara(h.odeme_tutari)}</td>
            <td style="padding:6px 10px;border-bottom:1px solid #f1f5f9">${h.odeme_yontemi}</td>
            <td style="padding:6px 10px;border-bottom:1px solid #f1f5f9">${h.aciklama || '-'}</td>
        </tr>
    `).join('') : `<tr><td colspan="4" style="padding:12px;text-align:center;color:#64748b">Ödeme kaydı yok</td></tr>`;

    document.getElementById('odeme-fatura-id').value = id;
    document.getElementById('odeme-tarih').value = new Date().toISOString().split('T')[0];
    document.getElementById('odeme-tutar').value = '';
    modalAc('fatura-detay-modal');
}

async function odeme_kaydet() {
    const id = document.getElementById('odeme-fatura-id').value;
    const data = {
        odeme: {
            odeme_tarihi: document.getElementById('odeme-tarih').value,
            odeme_tutari: document.getElementById('odeme-tutar').value,
            odeme_yontemi: document.getElementById('odeme-yontem').value,
            aciklama: document.getElementById('odeme-aciklama').value,
        }
    };
    if (!data.odeme.odeme_tutari || !data.odeme.odeme_tarihi) {
        toast('Tutar ve tarih zorunludur', 'error'); return;
    }
    const sonuc = await API.put(`api/faturalar.php?id=${id}`, data);
    if (sonuc.hata) { toast(sonuc.hata, 'error'); return; }
    toast('Ödeme kaydedildi');
    await fatura_detay(id);
    yukle_faturalar();
}

async function fatura_sil(id, no) {
    if (!confirm(`"${no}" faturasını silmek istediğinizden emin misiniz?`)) return;
    const sonuc = await API.delete(`api/faturalar.php?id=${id}`);
    toast(sonuc.mesaj, sonuc.hata ? 'error' : 'success');
    yukle_faturalar();
}

// Fatura filtre müşteri seçenekleri doldur
async function doldur_fatura_musteri_filtre() {
    const sel = document.getElementById('fatura-musteri-filtre');
    if (!sel || sel.options.length > 1) return;
    const musteriler = await API.get('api/musteriler.php');
    sel.innerHTML = '<option value="">Tüm Müşteriler</option>' +
        musteriler.map(m => `<option value="${m.id}">${m.ad_soyad}</option>`).join('');
}
