$(function () {

    const fmt = new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' });

    function getFilters() {
        const urlParams = new URLSearchParams(window.location.search);
        return {
            musteri_id:      $('#filterMusteri').val() || urlParams.get('musteri_id') || '',
            tur:             $('#filterTur').val()     || '',
            tarih_baslangic: $('#filterTarihBas').val() || '',
            tarih_bitis:     $('#filterTarihBit').val() || '',
        };
    }

    const dt = $('#tblHareketler').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/api/musteri_hareketler/list.php',
            type: 'POST',
            data: function (d) { return Object.assign(d, getFilters()); },
            error: function () { Swal.fire('Hata', 'Veri yüklenemedi.', 'error'); }
        },
        columns: [
            { data: 'id',          title: '#',        width: '50px' },
            { data: 'musteri_adi', title: 'Müşteri' },
            { data: 'tarih',       title: 'Tarih',    width: '100px' },
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
                    const prefix = row.tur === 'giris' ? '+' : '-';
                    return `<span class="${cls} fw-semibold">${prefix}${fmt.format(Math.abs(val))}</span>`;
                }
            },
            {
                data: null,
                title: 'Kümülatif Bakiye',
                width: '130px',
                orderable: false,
                searchable: false,
                render: function () { return '<span class="bakiye-cell">-</span>'; }
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
                            data-musteri_id="${row.musteri_id}"
                            data-tarih="${row.tarih}"
                            data-tur="${row.tur}"
                            data-tutar="${row.tutar}"
                            data-aciklama="${esc(row.aciklama || '')}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-danger btn-delete"
                            data-id="${row.id}"
                            data-name="${row.id}. Hareket">
                            <i class="bi bi-trash"></i>
                        </button>`;
                }
            }
        ],
        drawCallback: function () {
            // Kümülatif bakiye hesapla (client-side, mevcut sayfa)
            let balance = 0;
            this.api().rows({ page: 'current' }).every(function () {
                const d = this.data();
                const amount = parseFloat(d.tutar) || 0;
                balance += d.tur === 'giris' ? amount : -amount;
                const cls = balance >= 0 ? 'text-balance-positive' : 'text-balance-negative';
                $(this.node()).find('.bakiye-cell')
                    .text(fmt.format(balance))
                    .attr('class', `bakiye-cell ${cls}`);
            });

            // Özet güncelle
            let giris = 0, cikis = 0;
            this.api().rows().every(function () {
                const d = this.data();
                const amount = parseFloat(d.tutar) || 0;
                if (d.tur === 'giris') giris += amount;
                else cikis += amount;
            });
            $('#lblGiris').text(fmt.format(giris));
            $('#lblCikis').text(fmt.format(cikis));
            const net = giris - cikis;
            $('#lblNet').text(fmt.format(net))
                        .toggleClass('text-success', net >= 0)
                        .toggleClass('text-danger', net < 0);
        },
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/tr.json' },
        pageLength: 25,
        order: [[2, 'asc']],
    });

    // URL param'dan filtre
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('musteri_id')) {
        $('#filterMusteri').val(urlParams.get('musteri_id'));
    }

    $('#btnFilter').on('click', function () { dt.ajax.reload(); });
    $('#btnReset').on('click', function () {
        $('#filterMusteri').val('');
        $('#filterTur').val('');
        $('#filterTarihBas').val('');
        $('#filterTarihBit').val('');
        dt.ajax.reload();
    });

    $('#btnAdd').on('click', function () {
        $('#modalTitle').text('Yeni Hareket Ekle');
        $('#hareketForm')[0].reset();
        $('#hareket_id').val('');
        $('#har_tarih').val(new Date().toISOString().split('T')[0]);
        new bootstrap.Modal('#hareketModal').show();
    });

    $('#tblHareketler').on('click', '.btn-edit', function () {
        const d = $(this).data();
        $('#modalTitle').text('Hareket Düzenle');
        $('#hareket_id').val(d.id);
        $('#har_musteri_id').val(d.musteri_id);
        $('#har_tarih').val(d.tarih);
        $(`input[name="tur"][value="${d.tur}"]`).prop('checked', true);
        $('#har_tutar').val(d.tutar);
        $('#har_aciklama').val(d.aciklama);
        new bootstrap.Modal('#hareketModal').show();
    });

    $('#btnSave').on('click', function () {
        const id  = $('#hareket_id').val();
        const url = id ? '/api/musteri_hareketler/update.php' : '/api/musteri_hareketler/create.php';
        $.post(url, $('#hareketForm').serialize(), function (res) {
            if (res.success) {
                bootstrap.Modal.getInstance('#hareketModal').hide();
                dt.ajax.reload(null, false);
                Swal.fire({ icon: 'success', title: res.message, timer: 1500, showConfirmButton: false });
            } else {
                Swal.fire('Hata', res.message, 'error');
            }
        }, 'json');
    });

    $('#tblHareketler').on('click', '.btn-delete', function () {
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
                $.post('/api/musteri_hareketler/delete.php', { id }, function (res) {
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
