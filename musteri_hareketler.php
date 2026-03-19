<?php
require_once 'config.php';
$musteriler = getDB()->query("SELECT id, musteri_adi FROM musteriler ORDER BY musteri_adi")->fetchAll();
require_once 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-arrow-left-right text-primary"></i> Müşteri Hareketleri</h4>
    <button id="btnAdd" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Yeni Hareket Ekle
    </button>
</div>

<!-- Filtreler -->
<div class="card shadow-sm mb-3">
    <div class="card-body py-2">
        <div class="row g-2 align-items-center">
            <div class="col-md-3">
                <label class="form-label mb-0 small fw-semibold">Müşteri:</label>
                <select id="filterMusteri" class="form-select form-select-sm">
                    <option value="">Tümü</option>
                    <?php foreach ($musteriler as $m): ?>
                        <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['musteri_adi']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-0 small fw-semibold">Tür:</label>
                <select id="filterTur" class="form-select form-select-sm">
                    <option value="">Tümü</option>
                    <option value="giris">Giriş</option>
                    <option value="cikis">Çıkış</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-0 small fw-semibold">Başlangıç:</label>
                <input type="date" id="filterTarihBas" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
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

<!-- Özet Satırı -->
<div id="totalsBar" class="d-flex gap-4 flex-wrap mb-3">
    <span>
        <i class="bi bi-arrow-down-circle text-success"></i>
        Toplam Giriş: <strong id="lblGiris" class="text-success">₺0,00</strong>
    </span>
    <span>
        <i class="bi bi-arrow-up-circle text-danger"></i>
        Toplam Çıkış: <strong id="lblCikis" class="text-danger">₺0,00</strong>
    </span>
    <span>
        <i class="bi bi-calculator"></i>
        Net Bakiye: <strong id="lblNet">₺0,00</strong>
    </span>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="tblHareketler" class="table table-hover table-striped mb-0 w-100">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Müşteri</th>
                        <th>Tarih</th>
                        <th>Tür</th>
                        <th>Tutar</th>
                        <th>Kümülatif Bakiye</th>
                        <th>Açıklama</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Hareket Modal -->
<div class="modal fade" id="hareketModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalTitle">Yeni Hareket Ekle</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="hareketForm">
                    <input type="hidden" id="hareket_id" name="id">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Müşteri <span class="text-danger">*</span></label>
                        <select class="form-select" id="har_musteri_id" name="musteri_id" required>
                            <option value="">Müşteri Seçin</option>
                            <?php foreach ($musteriler as $m): ?>
                                <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['musteri_adi']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tarih <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="har_tarih" name="tarih" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tür <span class="text-danger">*</span></label>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tur" id="harGiris" value="giris" required>
                                <label class="form-check-label text-success fw-semibold" for="harGiris">
                                    <i class="bi bi-arrow-down-circle"></i> Giriş
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tur" id="harCikis" value="cikis">
                                <label class="form-check-label text-danger fw-semibold" for="harCikis">
                                    <i class="bi bi-arrow-up-circle"></i> Çıkış
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tutar <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">₺</span>
                            <input type="number" step="0.01" min="0.01" class="form-control"
                                   id="har_tutar" name="tutar" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Açıklama</label>
                        <textarea class="form-control" id="har_aciklama" name="aciklama" rows="2"></textarea>
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

<?php $page_script = '/assets/js/musteri_hareketler.js'; require_once 'includes/footer.php'; ?>
