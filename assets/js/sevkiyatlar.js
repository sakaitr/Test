$(function () {

    const durumBadge = {
        beklemede:  '<span class="badge bg-warning text-dark">Beklemede</span>',
        tamamlandi: '<span class="badge bg-success">Tamamlandı</span>',
        iptal:      '<span class="badge bg-danger">İptal</span>',
    };

    const dt = $('#tblSevkiyatlar').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/api/sevkiyatlar/list.php',
            type: 'POST',
            data: function (d) {
                d.durum           = $('#filterDurum').val()     || '';
                d.tarih_baslangic = $('#filterTarihBas').val()  || '';
                d.tarih_bitis     = $('#filterTarihBit').val()  || '';
                // URL parametrelerinden filtre
                const urlParams = new URLSearchParams(window.location.search);
                d.musteri_id = urlParams.get('musteri_id') || '';
                d.firma_id   = urlParams.get('firma_id')   || '';
                return d;
            },
            error: function () { Swal.fire('Hata', 'Veri yüklenemedi.', 'error'); }
        },
        columns: [
            { data: 'id',          title: '#',        width: '50px' },
            { data: 'musteri_adi', title: 'Müşteri' },
            { data: 'firma_adi',   title: 'Firma' },
            { data: 'tarih',       title: 'Tarih',    width: '100px' },
            { data: 'aciklama',    title: 'Açıklama', defaultContent: '-', orderable: false },
            {
                data: 'durum',
                title: 'Durum',
                width: '110px',
                render: function (val) { return durumBadge[val] || val; }
            },
            {
                data: null,
                title: 'İşlemler',
                orderable: false,
                searchable: false,
                width: '130px',
                render: function (data, type, row) {
                    return `
                        <button class="btn btn-sm btn-warning btn-edit me-1"
                            data-id="${row.id}"
                            data-musteri_id="${row.musteri_id}"
                            data-firma_id="${row.firma_id}"
                            data-tarih="${row.tarih}"
                            data-durum="${row.durum}"
                            data-aciklama="${esc(row.aciklama || '')}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <a href="/faturalar.php?sevkiyat_id=${row.id}"
                           class="btn btn-sm btn-info me-1" title="Faturalar">
                            <i class="bi bi-file-earmark-text"></i>
                        </a>
                        <button class="btn btn-sm btn-danger btn-delete"
                            data-id="${row.id}"
                            data-name="${row.id}. Sevkiyat">
                            <i class="bi bi-trash"></i>
                        </button>`;
                }
            }
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/tr.json' },
        pageLength: 25,
        order: [[0, 'desc']],
    });

    $('#btnFilter').on('click', function () { dt.ajax.reload(); });
    $('#btnReset').on('click', function () {
        $('#filterDurum').val('');
        $('#filterTarihBas').val('');
        $('#filterTarihBit').val('');
        dt.ajax.reload();
    });

    $('#btnAdd').on('click', function () {
        $('#modalTitle').text('Yeni Sevkiyat Ekle');
        $('#sevkiyatForm')[0].reset();
        $('#sevk_id').val('');
        $('#sevk_tarih').val(new Date().toISOString().split('T')[0]);
        new bootstrap.Modal('#sevkiyatModal').show();
    });

    $('#tblSevkiyatlar').on('click', '.btn-edit', function () {
        const d = $(this).data();
        $('#modalTitle').text('Sevkiyat Düzenle');
        $('#sevk_id').val(d.id);
        $('#sevk_musteri_id').val(d.musteri_id);
        $('#sevk_firma_id').val(d.firma_id);
        $('#sevk_tarih').val(d.tarih);
        $('#sevk_durum').val(d.durum);
        $('#sevk_aciklama').val(d.aciklama);
        new bootstrap.Modal('#sevkiyatModal').show();
    });

    $('#btnSave').on('click', function () {
        const id  = $('#sevk_id').val();
        const url = id ? '/api/sevkiyatlar/update.php' : '/api/sevkiyatlar/create.php';
        $.post(url, $('#sevkiyatForm').serialize(), function (res) {
            if (res.success) {
                bootstrap.Modal.getInstance('#sevkiyatModal').hide();
                dt.ajax.reload(null, false);
                Swal.fire({ icon: 'success', title: res.message, timer: 1500, showConfirmButton: false });
            } else {
                Swal.fire('Hata', res.message, 'error');
            }
        }, 'json');
    });

    $('#tblSevkiyatlar').on('click', '.btn-delete', function () {
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
                $.post('/api/sevkiyatlar/delete.php', { id }, function (res) {
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
