<?php require_once 'includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-building text-primary"></i> Firmalar</h4>
    <button id="btnAdd" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Yeni Firma Ekle
    </button>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="tblFirmalar" class="table table-hover table-striped mb-0 w-100">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Firma Adı</th>
                        <th>Telefon</th>
                        <th>Adres</th>
                        <th>Kayıt Tarihi</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Firma Ekle/Düzenle Modal -->
<div class="modal fade" id="firmaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalTitle">Yeni Firma Ekle</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="firmaForm">
                    <input type="hidden" id="firma_id" name="id">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Firma Adı <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="firma_adi" name="firma_adi" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Telefon</label>
                        <input type="text" class="form-control" id="telefon" name="telefon">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Adres</label>
                        <textarea class="form-control" id="adres" name="adres" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button type="button" id="btnSave" class="btn btn-primary">
                    <i class="bi bi-save"></i> Kaydet
                </button>
            </div>
        </div>
    </div>
</div>

<?php $page_script = '/assets/js/firmalar.js'; require_once 'includes/footer.php'; ?>
