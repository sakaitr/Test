<?php require_once 'includes/header.php'; ?>

<h4 class="mb-4"><i class="bi bi-speedometer2 text-primary"></i> Dashboard</h4>

<!-- Özet Kartlar -->
<div class="row g-3 mb-4" id="summaryCards">
    <div class="col-6 col-md-4 col-lg-2-4">
        <div class="card card-summary bg-primary text-white">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon-wrap"><i class="bi bi-building"></i></div>
                <div>
                    <div class="value" id="cardFirma">-</div>
                    <div class="label">Firmalar</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2-4">
        <div class="card card-summary bg-info text-white">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon-wrap"><i class="bi bi-people"></i></div>
                <div>
                    <div class="value" id="cardMusteri">-</div>
                    <div class="label">Müşteriler</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2-4">
        <div class="card card-summary bg-secondary text-white">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon-wrap"><i class="bi bi-truck"></i></div>
                <div>
                    <div class="value" id="cardSevk">-</div>
                    <div class="label">Sevkiyatlar</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-6 col-lg-2-4">
        <div class="card card-summary bg-success text-white">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon-wrap"><i class="bi bi-arrow-down-circle"></i></div>
                <div>
                    <div class="value fs-6" id="cardGiris">-</div>
                    <div class="label">Toplam Giriş</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-6 col-lg-2-4">
        <div class="card card-summary bg-danger text-white">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon-wrap"><i class="bi bi-arrow-up-circle"></i></div>
                <div>
                    <div class="value fs-6" id="cardCikis">-</div>
                    <div class="label">Toplam Çıkış</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-12 col-lg-2-4">
        <div class="card card-summary bg-dark text-white">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="icon-wrap"><i class="bi bi-calculator"></i></div>
                <div>
                    <div class="value fs-6" id="cardNet">-</div>
                    <div class="label">Net Bakiye</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Son Faturalar -->
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-clock-history"></i> Son 10 Fatura</span>
        <a href="/faturalar.php" class="btn btn-sm btn-outline-primary">Tümünü Gör</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="tblSonFaturalar" class="table table-sm table-hover table-striped mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Fatura No</th>
                        <th>Müşteri</th>
                        <th>Firma</th>
                        <th>Tarih</th>
                        <th>Tür</th>
                        <th>Tutar</th>
                    </tr>
                </thead>
                <tbody id="sonFaturaBody">
                    <tr><td colspan="7" class="text-center py-3">Yükleniyor...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(function () {
    const fmt = new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' });

    $.getJSON('/api/dashboard/summary.php', function (data) {
        $('#cardFirma').text(data.firma_count);
        $('#cardMusteri').text(data.musteri_count);
        $('#cardSevk').text(data.sevk_count);
        $('#cardGiris').text(fmt.format(data.toplam_giris));
        $('#cardCikis').text(fmt.format(data.toplam_cikis));
        const net = data.net;
        $('#cardNet').text(fmt.format(net));

        let rows = '';
        if (data.son_faturalar.length === 0) {
            rows = '<tr><td colspan="7" class="text-center text-muted">Henüz fatura yok</td></tr>';
        } else {
            data.son_faturalar.forEach(function (f) {
                const turBadge = f.tur === 'giris'
                    ? '<span class="badge bg-success">Giriş</span>'
                    : '<span class="badge bg-danger">Çıkış</span>';
                const tutarCls = f.tur === 'giris' ? 'text-success' : 'text-danger';
                rows += `<tr>
                    <td>${f.id}</td>
                    <td>${$('<span>').text(f.fatura_no).html()}</td>
                    <td>${$('<span>').text(f.musteri_adi).html()}</td>
                    <td>${$('<span>').text(f.firma_adi).html()}</td>
                    <td>${f.tarih}</td>
                    <td>${turBadge}</td>
                    <td class="${tutarCls} fw-semibold">${fmt.format(f.tutar)}</td>
                </tr>`;
            });
        }
        $('#sonFaturaBody').html(rows);
    }).fail(function () {
        $('#sonFaturaBody').html('<tr><td colspan="7" class="text-center text-danger">Veri yüklenemedi.</td></tr>');
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
