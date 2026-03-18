<?php
require_once 'config.php';
$pdo      = getDB();
$musteriler = $pdo->query("SELECT id, musteri_adi FROM musteriler ORDER BY musteri_adi")->fetchAll();
$firmalar   = $pdo->query("SELECT id, firma_adi FROM firmalar ORDER BY firma_adi")->fetchAll();
require_once 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-truck text-primary"></i> Sevkiyatlar</h4>
    <button id="btnAdd" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Yeni Sevkiyat Ekle
    </button>
</div>

<!-- Filtreler -->
<div class="card shadow-sm mb-3">
    <div class="card-body py-2">
        <div class="row g-2 align-items-center">
            <div class="col-md-3">
                <label class="form-label mb-0 small fw-semibold">Durum:</label>
                <select id="filterDurum" class="form-select form-select-sm">
                    <option value="">Tümü</option>
                    <option value="beklemede">Beklemede</option>
                    <option value="tamamlandi">Tamamlandı</option>
                    <option value="iptal">İptal</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label mb-0 small fw-semibold">Başlangıç:</label>
                <input type="date" id="filterTarihBas" class="form-control form-control-sm">
            </div>
            <div class="col-md-3">
                <label class="form-label mb-0 small fw-semibold">Bitiş:</label>
                <input type="date" id="filterTarihBit" class="form-control form-control-sm">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button id="btnFilter" class="btn btn-sm btn-outline-primary me-2">
                    <i class="bi bi-filter"></i> Filtrele
                </button>
                <button id="btnReset" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Sıfırla
                </button>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="tblSevkiyatlar" class="table table-hover table-striped mb-0 w-100">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Müşteri</th>
                        <th>Firma</th>
                        <th>Tarih</th>
                        <th>Açıklama</th>
                        <th>Durum</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Sevkiyat Modal -->
<div class="modal fade" id="sevkiyatModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalTitle">Yeni Sevkiyat Ekle</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="sevkiyatForm">
                    <input type="hidden" id="sevk_id" name="id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Müşteri <span class="text-danger">*</span></label>
                            <select class="form-select" id="sevk_musteri_id" name="musteri_id" required>
                                <option value="">Müşteri Seçin</option>
                                <?php foreach ($musteriler as $m): ?>
                                    <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['musteri_adi']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Firma <span class="text-danger">*</span></label>
                            <select class="form-select" id="sevk_firma_id" name="firma_id" required>
                                <option value="">Firma Seçin</option>
                                <?php foreach ($firmalar as $f): ?>
                                    <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['firma_adi']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tarih <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="sevk_tarih" name="tarih" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Durum</label>
                            <select class="form-select" id="sevk_durum" name="durum">
                                <option value="beklemede">Beklemede</option>
                                <option value="tamamlandi">Tamamlandı</option>
                                <option value="iptal">İptal</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Açıklama</label>
                            <textarea class="form-control" id="sevk_aciklama" name="aciklama" rows="3"></textarea>
                        </div>
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

<script src="/assets/js/sevkiyatlar.js"></script>
<?php require_once 'includes/footer.php'; ?>
