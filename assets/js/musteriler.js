$(function () {

    const dt = $('#tblMusteriler').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/api/musteriler/list.php',
            type: 'POST',
            data: function (d) {
                d.firma_id = $('#filterFirma').val() || '';
                return d;
            },
            error: function () { Swal.fire('Hata', 'Veri yüklenemedi.', 'error'); }
        },
        columns: [
            { data: 'id',          title: '#',           width: '50px' },
            { data: 'musteri_adi', title: 'Müşteri Adı' },
            { data: 'telefon',     title: 'Telefon',     defaultContent: '-' },
            { data: 'adres',       title: 'Adres',       defaultContent: '-', orderable: false },
            { data: 'firma_adi',   title: 'Firma' },
            { data: 'created_at',  title: 'Kayıt Tarihi', width: '140px' },
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
                            data-musteri_adi="${esc(row.musteri_adi)}"
                            data-telefon="${esc(row.telefon || '')}"
                            data-adres="${esc(row.adres || '')}"
                            data-firma_id="${row.firma_id}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <a href="/sevkiyatlar.php?musteri_id=${row.id}"
                           class="btn btn-sm btn-info me-1" title="Sevkiyatlar">
                            <i class="bi bi-truck"></i>
                        </a>
                        <a href="/musteri_hareketler.php?musteri_id=${row.id}"
                           class="btn btn-sm btn-secondary me-1" title="Hareketler">
                            <i class="bi bi-arrow-left-right"></i>
                        </a>
                        <button class="btn btn-sm btn-danger btn-delete"
                            data-id="${row.id}"
                            data-name="${esc(row.musteri_adi)}">
                            <i class="bi bi-trash"></i>
                        </button>`;
                }
            }
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/tr.json' },
        pageLength: 25,
        order: [[0, 'desc']],
    });

    $('#filterFirma').on('change', function () { dt.ajax.reload(); });

    $('#btnAdd').on('click', function () {
        $('#modalTitle').text('Yeni Müşteri Ekle');
        $('#musteriForm')[0].reset();
        $('#musteri_id').val('');
        new bootstrap.Modal('#musteriModal').show();
    });

    $('#tblMusteriler').on('click', '.btn-edit', function () {
        const d = $(this).data();
        $('#modalTitle').text('Müşteri Düzenle');
        $('#musteri_id').val(d.id);
        $('#musteri_adi').val(d.musteri_adi);
        $('#firma_id').val(d.firma_id);
        $('#m_telefon').val(d.telefon);
        $('#m_adres').val(d.adres);
        new bootstrap.Modal('#musteriModal').show();
    });

    $('#btnSave').on('click', function () {
        const id  = $('#musteri_id').val();
        const url = id ? '/api/musteriler/update.php' : '/api/musteriler/create.php';
        $.post(url, $('#musteriForm').serialize(), function (res) {
            if (res.success) {
                bootstrap.Modal.getInstance('#musteriModal').hide();
                dt.ajax.reload(null, false);
                Swal.fire({ icon: 'success', title: res.message, timer: 1500, showConfirmButton: false });
            } else {
                Swal.fire('Hata', res.message, 'error');
            }
        }, 'json');
    });

    $('#tblMusteriler').on('click', '.btn-delete', function () {
        const id   = $(this).data('id');
        const name = $(this).data('name');
        Swal.fire({
            title: `"${name}" silinsin mi?`,
            text: 'Bu işlem geri alınamaz!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Evet, Sil',
            cancelButtonText: 'İptal',
            confirmButtonColor: '#dc3545',
        }).then(result => {
            if (result.isConfirmed) {
                $.post('/api/musteriler/delete.php', { id }, function (res) {
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
