<?php
require_once 'config.php';
$firmalar = getDB()->query("SELECT id, firma_adi FROM firmalar ORDER BY firma_adi")->fetchAll();
require_once 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-people text-primary"></i> Müşteriler</h4>
    <button id="btnAdd" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Yeni Müşteri Ekle
    </button>
</div>

<!-- Filtre -->
<div class="card shadow-sm mb-3">
    <div class="card-body py-2">
        <div class="row g-2 align-items-center">
            <div class="col-auto">
                <label class="form-label mb-0 fw-semibold">Firmaya Göre Filtrele:</label>
            </div>
            <div class="col-auto">
                <select id="filterFirma" class="form-select form-select-sm">
                    <option value="">Tümü</option>
                    <?php foreach ($firmalar as $f): ?>
                        <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['firma_adi']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="tblMusteriler" class="table table-hover table-striped mb-0 w-100">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Müşteri Adı</th>
                        <th>Telefon</th>
                        <th>Adres</th>
                        <th>Firma</th>
                        <th>Kayıt Tarihi</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Müşteri Ekle/Düzenle Modal -->
<div class="modal fade" id="musteriModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalTitle">Yeni Müşteri Ekle</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="musteriForm">
                    <input type="hidden" id="musteri_id" name="id">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Müşteri Adı <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="musteri_adi" name="musteri_adi" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Firma <span class="text-danger">*</span></label>
                        <select class="form-select" id="firma_id" name="firma_id" required>
                            <option value="">Firma Seçin</option>
                            <?php foreach ($firmalar as $f): ?>
                                <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['firma_adi']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Telefon</label>
                        <input type="text" class="form-control" id="m_telefon" name="telefon">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Adres</label>
                        <textarea class="form-control" id="m_adres" name="adres" rows="3"></textarea>
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

<?php $page_script = '/assets/js/musteriler.js'; require_once 'includes/footer.php'; ?>
