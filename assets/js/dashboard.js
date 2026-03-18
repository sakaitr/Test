async function yukle_dashboard() {
    const container = document.getElementById('dashboard-content');
    container.innerHTML = yukleniyorHTML();
    try {
        const veri = await API.get('api/dashboard.php');
        const ist = veri.istatistikler;
        container.innerHTML = `
            <!-- İstatistik Kartları -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue">🏢</div>
                    <div class="stat-info">
                        <div class="label">Kargo Firmaları</div>
                        <div class="value">${ist.toplam_firma}</div>
                        <div class="sub">Aktif firma</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green">👥</div>
                    <div class="stat-info">
                        <div class="label">Müşteriler</div>
                        <div class="value">${ist.toplam_musteri}</div>
                        <div class="sub">Kayıtlı müşteri</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon orange">📦</div>
                    <div class="stat-info">
                        <div class="label">Sevkiyatlar</div>
                        <div class="value">${ist.toplam_sevkiyat}</div>
                        <div class="sub">${ist.aktif_sevkiyat} aktif</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon purple">📤</div>
                    <div class="stat-info">
                        <div class="label">Giden Faturalar</div>
                        <div class="value">${formatPara(ist.giden_toplam)}</div>
                        <div class="sub money-red">Tahsil edilmemiş: ${formatPara(ist.tahsil_edilmemis)}</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon blue">📥</div>
                    <div class="stat-info">
                        <div class="label">Gelen Faturalar</div>
                        <div class="value">${formatPara(ist.gelen_toplam)}</div>
                        <div class="sub money-red">Ödenmemiş: ${formatPara(ist.odenmemis_gelen)}</div>
                    </div>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
                <!-- Firmalara Göre Sevkiyat -->
                <div class="card">
                    <div class="card-header"><h3>🚚 Firmalara Göre Sevkiyat</h3></div>
                    <div class="table-wrap">
                        <table>
                            <thead><tr><th>Firma</th><th>Toplam</th><th>Aktif</th><th>Teslim</th></tr></thead>
                            <tbody>
                                ${veri.firma_dagilim.length ? veri.firma_dagilim.map(f => `
                                    <tr>
                                        <td><strong>${f.firma_adi}</strong></td>
                                        <td>${f.sevkiyat_sayisi}</td>
                                        <td><span class="badge badge-blue">${f.aktif}</span></td>
                                        <td><span class="badge badge-green">${f.teslim_edildi}</span></td>
                                    </tr>
                                `).join('') : `<tr><td colspan="4">${bosHTML('Sevkiyat yok')}</td></tr>`}
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Vadesi Geçmiş Faturalar -->
                <div class="card">
                    <div class="card-header"><h3>⚠️ Vadesi Geçmiş Faturalar</h3></div>
                    <div class="table-wrap">
                        <table>
                            <thead><tr><th>Fatura No</th><th>Tür</th><th>Kalan</th><th>Vade</th></tr></thead>
                            <tbody>
                                ${veri.vadesi_gecmis.length ? veri.vadesi_gecmis.map(f => `
                                    <tr>
                                        <td><strong>${f.fatura_no}</strong><br><small>${f.ad_soyad || f.firma_adi || ''}</small></td>
                                        <td>${durumBadge(f.fatura_turu)}</td>
                                        <td class="money money-red">${formatPara(f.kalan)}</td>
                                        <td><span class="badge badge-red">${formatTarih(f.vade_tarihi)}</span></td>
                                    </tr>
                                `).join('') : `<tr><td colspan="4"><div style="padding:20px;text-align:center;color:#64748b">✅ Vadesi geçmiş fatura yok</div></td></tr>`}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Son Sevkiyatlar -->
            <div class="card">
                <div class="card-header">
                    <h3>📦 Son Sevkiyatlar</h3>
                    <button class="btn btn-primary btn-sm" onclick="sayfaGit('sevkiyatlar')">Tümünü Gör</button>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Takip No</th><th>Müşteri</th><th>Kargo Firması</th><th>Tarih</th><th>Durum</th></tr></thead>
                        <tbody>
                            ${veri.son_sevkiyatlar.length ? veri.son_sevkiyatlar.map(s => `
                                <tr>
                                    <td><strong>${s.takip_no}</strong></td>
                                    <td>${s.ad_soyad}</td>
                                    <td>${s.firma_adi}</td>
                                    <td>${formatTarih(s.sevkiyat_tarihi)}</td>
                                    <td>${durumBadge(s.durum)}</td>
                                </tr>
                            `).join('') : `<tr><td colspan="5">${bosHTML()}</td></tr>`}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    } catch (e) {
        container.innerHTML = `<div class="empty-state"><div class="icon">❌</div><p>Veri yüklenirken hata oluştu</p></div>`;
    }
}
