$(function () {

    const dt = $('#tblFirmalar').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/api/firmalar/list.php',
            type: 'POST',
            error: function () {
                Swal.fire('Hata', 'Veri yüklenemedi.', 'error');
            }
        },
        columns: [
            { data: 'id',         title: '#',           width: '50px' },
            { data: 'firma_adi',  title: 'Firma Adı' },
            { data: 'telefon',    title: 'Telefon',      defaultContent: '-' },
            { data: 'adres',      title: 'Adres',        defaultContent: '-', orderable: false },
            { data: 'created_at', title: 'Kayıt Tarihi', width: '140px' },
            {
                data: null,
                title: 'İşlemler',
                orderable: false,
                searchable: false,
                width: '100px',
                render: function (data, type, row) {
                    return `
                        <button class="btn btn-sm btn-warning btn-edit me-1"
                            data-id="${row.id}"
                            data-firma_adi="${escHtml(row.firma_adi)}"
                            data-telefon="${escHtml(row.telefon || '')}"
                            data-adres="${escHtml(row.adres || '')}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-danger btn-delete"
                            data-id="${row.id}"
                            data-name="${escHtml(row.firma_adi)}">
                            <i class="bi bi-trash"></i>
                        </button>`;
                }
            }
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/tr.json' },
        pageLength: 25,
        order: [[0, 'desc']],
        responsive: true,
    });

    // --- Yeni Firma ---
    $('#btnAdd').on('click', function () {
        $('#modalTitle').text('Yeni Firma Ekle');
        $('#firmaForm')[0].reset();
        $('#firma_id').val('');
        new bootstrap.Modal('#firmaModal').show();
    });

    // --- Düzenle ---
    $('#tblFirmalar').on('click', '.btn-edit', function () {
        const d = $(this).data();
        $('#modalTitle').text('Firma Düzenle');
        $('#firma_id').val(d.id);
        $('#firma_adi').val(d.firma_adi);
        $('#telefon').val(d.telefon);
        $('#adres').val(d.adres);
        new bootstrap.Modal('#firmaModal').show();
    });

    // --- Kaydet ---
    $('#btnSave').on('click', function () {
        const id  = $('#firma_id').val();
        const url = id ? '/api/firmalar/update.php' : '/api/firmalar/create.php';
        $.post(url, $('#firmaForm').serialize(), function (res) {
            if (res.success) {
                bootstrap.Modal.getInstance('#firmaModal').hide();
                dt.ajax.reload(null, false);
                Swal.fire({ icon: 'success', title: res.message, timer: 1500, showConfirmButton: false });
            } else {
                Swal.fire('Hata', res.message, 'error');
            }
        }, 'json');
    });

    // --- Sil ---
    $('#tblFirmalar').on('click', '.btn-delete', function () {
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
                $.post('/api/firmalar/delete.php', { id }, function (res) {
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

    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }
});
