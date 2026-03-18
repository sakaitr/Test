$(function () {

    const fmt = new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' });

    function getFilters() {
        const urlParams = new URLSearchParams(window.location.search);
        return {
            tur:              $('#filterTur').val()     || '',
            musteri_id:       $('#filterMusteri').val() || urlParams.get('musteri_id') || '',
            sevkiyat_id:      urlParams.get('sevkiyat_id') || '',
            tarih_baslangic:  $('#filterTarihBas').val() || '',
            tarih_bitis:      $('#filterTarihBit').val() || '',
        };
    }

    function refreshTotals(ajaxParams) {
        const p = Object.assign({}, ajaxParams, getFilters());
        $.post('/api/faturalar/totals.php', p, function (res) {
            $('#lblGiris').text(fmt.format(res.toplam_giris));
            $('#lblCikis').text(fmt.format(res.toplam_cikis));
            const net = res.net;
            $('#lblNet').text(fmt.format(net))
                        .toggleClass('text-success', net >= 0)
                        .toggleClass('text-danger', net < 0);
        }, 'json');
    }

    const dt = $('#tblFaturalar').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/api/faturalar/list.php',
            type: 'POST',
            data: function (d) {
                return Object.assign(d, getFilters());
            },
            error: function () { Swal.fire('Hata', 'Veri yüklenemedi.', 'error'); }
        },
        columns: [
            { data: 'id',          title: '#',          width: '50px' },
            { data: 'fatura_no',   title: 'Fatura No' },
            {
                data: 'sevkiyat_id',
                title: 'Sevkiyat',
                width: '80px',
                defaultContent: '-',
                render: function (val) {
                    return val ? `<a href="/sevkiyatlar.php?id=${val}">#${val}</a>` : '-';
                }
            },
            { data: 'musteri_adi', title: 'Müşteri' },
            { data: 'firma_adi',   title: 'Firma' },
            { data: 'tarih',       title: 'Tarih',      width: '100px' },
            {
                data: 'tur',
                title: 'Tür',
                width: '80px',
                render: function (val) {
                    return val === 'giris'
                        ? '<span class="badge bg-success">Giriş</span>'
                        : '<span class="badge bg-danger">Çıkış</span>';
                }
            },
            {
                data: 'tutar',
                title: 'Tutar',
                width: '110px',
                render: function (val, type, row) {
                    const cls = row.tur === 'giris' ? 'text-success' : 'text-danger';
                    return `<span class="${cls} fw-semibold">${fmt.format(val)}</span>`;
                }
            },
            { data: 'aciklama', title: 'Açıklama', defaultContent: '-', orderable: false },
            {
                data: null,
                title: 'İşlemler',
                orderable: false,
                searchable: false,
                width: '90px',
                render: function (data, type, row) {
                    return `
                        <button class="btn btn-sm btn-warning btn-edit me-1"
                            data-id="${row.id}"
                            data-fatura_no="${esc(row.fatura_no)}"
                            data-sevkiyat_id="${row.sevkiyat_id || ''}"
                            data-musteri_id="${row.musteri_id}"
                            data-firma_id="${row.firma_id}"
                            data-tarih="${row.tarih}"
                            data-tutar="${row.tutar}"
                            data-tur="${row.tur}"
                            data-aciklama="${esc(row.aciklama || '')}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-danger btn-delete"
                            data-id="${row.id}"
                            data-name="${esc(row.fatura_no)}">
                            <i class="bi bi-trash"></i>
                        </button>`;
                }
            }
        ],
        drawCallback: function () {
            refreshTotals(this.ajax.params());
        },
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/tr.json' },
        pageLength: 25,
        order: [[0, 'desc']],
    });

    // Filtreler
    $('#btnFilter').on('click', function () { dt.ajax.reload(); });
    $('#btnReset').on('click', function () {
        $('#filterTur').val('');
        $('#filterMusteri').val('');
        $('#filterTarihBas').val('');
        $('#filterTarihBit').val('');
        dt.ajax.reload();
    });

    // URL param'dan filtre ayarla
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('musteri_id')) $('#filterMusteri').val(urlParams.get('musteri_id'));
    if (urlParams.get('tur'))        $('#filterTur').val(urlParams.get('tur'));

    // Yeni Fatura
    $('#btnAdd').on('click', function () {
        $('#modalTitle').text('Yeni Fatura Ekle');
        $('#faturaForm')[0].reset();
        $('#fatura_id').val('');
        $('#fat_tarih').val(new Date().toISOString().split('T')[0]);
        new bootstrap.Modal('#faturaModal').show();
    });

    // Düzenle
    $('#tblFaturalar').on('click', '.btn-edit', function () {
        const d = $(this).data();
        $('#modalTitle').text('Fatura Düzenle');
        $('#fatura_id').val(d.id);
        $('#fatura_no').val(d.fatura_no);
        $('#fat_sevkiyat_id').val(d.sevkiyat_id || '');
        $('#fat_musteri_id').val(d.musteri_id);
        $('#fat_firma_id').val(d.firma_id);
        $('#fat_tarih').val(d.tarih);
        $('#fat_tutar').val(d.tutar);
        $(`input[name="tur"][value="${d.tur}"]`).prop('checked', true);
        $('#fat_aciklama').val(d.aciklama);
        new bootstrap.Modal('#faturaModal').show();
    });

    // Kaydet
    $('#btnSave').on('click', function () {
        const id  = $('#fatura_id').val();
        const url = id ? '/api/faturalar/update.php' : '/api/faturalar/create.php';
        $.post(url, $('#faturaForm').serialize(), function (res) {
            if (res.success) {
                bootstrap.Modal.getInstance('#faturaModal').hide();
                dt.ajax.reload(null, false);
                Swal.fire({ icon: 'success', title: res.message, timer: 1500, showConfirmButton: false });
            } else {
                Swal.fire('Hata', res.message, 'error');
            }
        }, 'json');
    });

    // Sil
    $('#tblFaturalar').on('click', '.btn-delete', function () {
        const id   = $(this).data('id');
        const name = $(this).data('name');
        Swal.fire({
            title: `"${name}" silinsin mi?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Evet, Sil',
            cancelButtonText: 'İptal',
            confirmButtonColor: '#dc3545',
        }).then(result => {
            if (result.isConfirmed) {
                $.post('/api/faturalar/delete.php', { id }, function (res) {
                    if (res.success) {
                        dt.ajax.reload(null, false);
                        Swal.fire({ icon: 'success', title: res.message, timer: 1500, showConfirmButton: false });
                    } else {
                        Swal.fire('Silinemedi', res.message, 'error');
                    }
                }, 'json');
            }
        });
    });

    function esc(str) {
        return String(str).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }
});
