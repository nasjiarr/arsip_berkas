<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
check_login();

// ponytail: aggregate counts for dashboard metrics summary for ti_admin only; saves db queries for other roles.
$stats = [
    'users' => 0,
    'disposisi' => 0,
    'disp_masuk' => 0,
    'disp_keluar' => 0,
    'sk' => 0,
    'sop' => 0
];

if ($role === 'ti_admin') {
    try {
        $stats['users'] = (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $stats['disp_masuk'] = (int) $pdo->query("SELECT COUNT(*) FROM disposisi_surat")->fetchColumn();
        $stats['disp_keluar'] = (int) $pdo->query("SELECT COUNT(*) FROM disposisi_keluar")->fetchColumn();
        $stats['disposisi'] = $stats['disp_masuk'] + $stats['disp_keluar'];
        $stats['sk'] = (int) $pdo->query("SELECT COUNT(*) FROM sk_table")->fetchColumn();
        $stats['sop'] = (int) $pdo->query("SELECT COUNT(*) FROM sop_table")->fetchColumn();
    } catch (Exception $e) {
        // Graceful fallback to default 0
    }
}

// ponytail: query latest 6 dispositions for activity log in tab 3 (ti_admin only); upgrade with paginated logs if needed.
$recent_activities = [];
if ($role === 'ti_admin') {
    try {
        $stmt = $pdo->query("
            (SELECT 'masuk' as tipe, id, kode, nomer_surat as nomor, dari as pihak, perihal, tanggal_masuk as tanggal FROM disposisi_surat)
            UNION ALL
            (SELECT 'keluar' as tipe, id, kode, nomor_surat as nomor, ke as pihak, perihal, tanggal FROM disposisi_keluar)
            ORDER BY tanggal DESC, id DESC LIMIT 6
        ");
        $recent_activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // Graceful fallback
    }
}

// ponytail: dynamic role capability flags for adaptive dashboard UI
$can_upload_kredit = ($role === 'ti_admin' || $role === 'adminkredit');
$can_cek_kredit    = ($role !== 'teller' && $role !== 'admin_dok');
$has_kredit        = ($can_upload_kredit || $can_cek_kredit);

$can_ttd           = ($role === 'teller' || $role === 'ti_admin');
$has_tab_kredit    = ($has_kredit || $can_ttd);

// Dynamic Tab 1 Label & Icon based on role
if ($role === 'teller') {
    $tab1_title = 'Spesimen Tanda Tangan';
    $tab1_icon  = 'bi-pen';
} elseif ($role === 'adminkredit') {
    $tab1_title = 'Berkas Kredit';
    $tab1_icon  = 'bi-folder-check';
} elseif (!$can_upload_kredit && $can_cek_kredit) {
    $tab1_title = 'Cek Berkas Kredit';
    $tab1_icon  = 'bi-search';
} else {
    $tab1_title = 'Berkas Kredit & Spesimen TTD';
    $tab1_icon  = 'bi-folder-check';
}

$default_tab_id = ($has_tab_kredit ? 'tab-kredit-tab' : 'tab-regulasi-tab');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <?php require_once '../includes/sidebar.php'; ?>

    <div class="container-fluid p-0">
        <div class="row g-0">
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 content">
                <div class="user-welcome d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <h1>Selamat Datang, <?= htmlspecialchars($_SESSION['user']['username']); ?></h1>
                        <p class="mb-0 text-white-50 small mt-1">Sistem Manajemen Berkas & Disposisi Surat PT BPR Bank Kulon Progo</p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge border py-2 px-3" style="background: rgba(255,255,255,0.12); color: #e0f2fe; font-size: 0.8rem;">
                            <i class="bi bi-shield-check me-1"></i>Role: <?= strtoupper(htmlspecialchars($role)) ?>
                        </span>
                    </div>
                </div>

                <!-- Metrics Summary (Stat Cards) - Khusus Admin TI -->
                <?php if ($role === 'ti_admin'): ?>
                <div class="row g-3 mb-4">
                    <!-- Total Pengguna -->
                    <div class="col-sm-6 col-xl-3">
                        <div class="card stat-card h-100 p-3 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem;">Total Pengguna</span>
                                <div class="stat-icon" style="background: rgba(2, 132, 199, 0.12); color: var(--brand-primary, #0284c7);">
                                    <i class="bi bi-people fs-5"></i>
                                </div>
                            </div>
                            <div class="d-flex align-items-baseline gap-2 mb-2">
                                <h3 class="mb-0 fw-bold"><?= number_format($stats['users']) ?></h3>
                                <span class="text-muted small">Akun</span>
                            </div>
                            <div class="mt-auto pt-2 border-top small text-muted d-flex align-items-center justify-content-between">
                                <?php if ($role === 'ti_admin'): ?>
                                    <a href="add_user.php" class="text-decoration-none small text-primary fw-medium d-flex align-items-center gap-1">
                                        <span>Kelola User</span> <i class="bi bi-arrow-right"></i>
                                    </a>
                                <?php else: ?>
                                    <span>Akun Terdaftar</span>
                                <?php endif; ?>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.65rem;">Sistem</span>
                            </div>
                        </div>
                    </div>

                    <!-- Disposisi Surat -->
                    <div class="col-sm-6 col-xl-3">
                        <div class="card stat-card h-100 p-3 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem;">Disposisi Surat</span>
                                <div class="stat-icon" style="background: rgba(14, 165, 233, 0.12); color: #0284c7;">
                                    <i class="bi bi-envelope-paper fs-5"></i>
                                </div>
                            </div>
                            <div class="d-flex align-items-baseline gap-2 mb-2">
                                <h3 class="mb-0 fw-bold"><?= number_format($stats['disposisi']) ?></h3>
                                <span class="text-muted small">Surat</span>
                            </div>
                            <div class="mt-auto pt-2 border-top small text-muted d-flex align-items-center justify-content-between">
                                <span>Masuk: <strong class="text-body"><?= $stats['disp_masuk'] ?></strong> &bull; Keluar: <strong class="text-body"><?= $stats['disp_keluar'] ?></strong></span>
                                <a href="disposisi/disposisi.php" class="text-decoration-none text-muted" title="Buka Disposisi">
                                    <i class="bi bi-arrow-up-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Total SK -->
                    <div class="col-sm-6 col-xl-3">
                        <div class="card stat-card h-100 p-3 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem;">Total Berkas SK</span>
                                <div class="stat-icon" style="background: rgba(16, 185, 129, 0.12); color: #10b981;">
                                    <i class="bi bi-file-earmark-text fs-5"></i>
                                </div>
                            </div>
                            <div class="d-flex align-items-baseline gap-2 mb-2">
                                <h3 class="mb-0 fw-bold"><?= number_format($stats['sk']) ?></h3>
                                <span class="text-muted small">Dokumen</span>
                            </div>
                            <div class="mt-auto pt-2 border-top small text-muted d-flex align-items-center justify-content-between">
                                <a href="cek_sk.php" class="text-decoration-none small text-success fw-medium d-flex align-items-center gap-1">
                                    <span>Cek SK</span> <i class="bi bi-arrow-right"></i>
                                </a>
                                <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.65rem;">Regulasi</span>
                            </div>
                        </div>
                    </div>

                    <!-- Total SOP -->
                    <div class="col-sm-6 col-xl-3">
                        <div class="card stat-card h-100 p-3 mb-0">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem;">Total Berkas SOP</span>
                                <div class="stat-icon" style="background: rgba(245, 158, 11, 0.12); color: #f59e0b;">
                                    <i class="bi bi-file-earmark-ruled fs-5"></i>
                                </div>
                            </div>
                            <div class="d-flex align-items-baseline gap-2 mb-2">
                                <h3 class="mb-0 fw-bold"><?= number_format($stats['sop']) ?></h3>
                                <span class="text-muted small">Dokumen</span>
                            </div>
                            <div class="mt-auto pt-2 border-top small text-muted d-flex align-items-center justify-content-between">
                                <a href="cek_sop.php" class="text-decoration-none small text-warning fw-medium d-flex align-items-center gap-1">
                                    <span>Cek SOP</span> <i class="bi bi-arrow-right"></i>
                                </a>
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle" style="font-size: 0.65rem;">Prosedur</span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Navigation Tabs (Tengah: Tabbed Utility) -->
                <ul class="nav nav-tabs nav-tabs-custom mb-4" id="dashboardTab" role="tablist">
                    <?php if ($has_tab_kredit): ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?= ($default_tab_id === 'tab-kredit-tab' ? 'active' : '') ?>" id="tab-kredit-tab" data-bs-toggle="tab" data-bs-target="#tab-kredit" type="button" role="tab" aria-controls="tab-kredit" aria-selected="<?= ($default_tab_id === 'tab-kredit-tab' ? 'true' : 'false') ?>">
                                <i class="bi <?= $tab1_icon ?>"></i> <?= htmlspecialchars($tab1_title) ?>
                            </button>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?= ($default_tab_id === 'tab-regulasi-tab' ? 'active' : '') ?>" id="tab-regulasi-tab" data-bs-toggle="tab" data-bs-target="#tab-regulasi" type="button" role="tab" aria-controls="tab-regulasi" aria-selected="<?= ($default_tab_id === 'tab-regulasi-tab' ? 'true' : 'false') ?>">
                            <i class="bi bi-file-earmark-ruled"></i> Regulasi (SK & SOP)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-log-tab" data-bs-toggle="tab" data-bs-target="#tab-log" type="button" role="tab" aria-controls="tab-log" aria-selected="false">
                            <i class="bi <?= ($role === 'ti_admin' ? 'bi-speedometer2' : 'bi-lightning-charge') ?>"></i> <?= ($role === 'ti_admin' ? 'Log Sistem / Quick Access' : 'Pintasan Akses Cepat') ?>
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="dashboardTabContent">
                    <!-- Tab 1: Berkas Kredit / Spesimen TTD (Sesuai Hak Akses Role) -->
                    <?php if ($has_tab_kredit): ?>
                        <div class="tab-pane fade <?= ($default_tab_id === 'tab-kredit-tab' ? 'show active' : '') ?>" id="tab-kredit" role="tabpanel" aria-labelledby="tab-kredit-tab">
                            <?php if ($has_kredit): ?>
                                <?php if ($can_upload_kredit && $can_cek_kredit): ?>
                                    <!-- Two-column: Upload & Cek Berkas Kredit -->
                                    <div class="row g-3">
                                        <div class="col-lg-6">
                                            <div class="card shadow-sm h-100 mb-0">
                                                <div class="card-header bg-primary text-white">
                                                    <h5 class="card-title mb-0"><i class="bi bi-upload me-2"></i>Upload Berkas Kredit</h5>
                                                </div>
                                                <div class="card-body">
                                                    <p class="text-muted small mb-3">Silahkan upload berkas kredit dalam format PDF.</p>
                                                    <form action="" method="post" enctype="multipart/form-data" class="needs-validation" novalidate id="uploadFormBerkas">
                                                        <div class="mb-3">
                                                            <div class="upload-drop-zone" id="dropZoneBerkas">
                                                                <i class="bi bi-cloud-upload fs-2"></i>
                                                                <p class="mb-2">Drag & drop file PDF di sini atau klik untuk memilih</p>
                                                                <input type="file" name="files[]" class="form-control" accept=".pdf" multiple required id="fileInputBerkas" style="display: none;">
                                                                <button type="button" class="btn btn-outline-primary" id="browseButtonBerkas">Pilih File</button>
                                                            </div>
                                                            <div class="selected-files-list" id="filesListBerkas"></div>
                                                        </div>
                                                        <button type="submit" name="upload" class="btn btn-primary" id="uploadButtonBerkas" disabled>Unggah</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="card shadow-sm h-100 mb-0">
                                                <div class="card-header bg-primary text-white">
                                                    <h5 class="card-title mb-0"><i class="bi bi-search me-2"></i>Cek Berkas Kredit</h5>
                                                </div>
                                                <div class="card-body">
                                                    <p class="text-muted small mb-3">Pencarian berkas kredit berdasarkan nomor rekening / berkas pinjaman.</p>
                                                    <form method="POST" class="needs-validation" action="#previewCardPDF" novalidate>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-medium">Nomor Berkas:</label>
                                                            <input type="text" name="norek" class="form-control" placeholder="Contoh: KRD-00123" required>
                                                            <div class="invalid-feedback">
                                                                Nomor rekening harus diisi
                                                            </div>
                                                        </div>
                                                        <button type="submit" name="cek" class="btn btn-primary">
                                                            <i class="bi bi-search me-1"></i> Cek Berkas
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php elseif ($can_cek_kredit): ?>
                                    <!-- Centered layout for Marketing & Other Search-only Roles -->
                                    <div class="row justify-content-center mb-3">
                                        <div class="col-lg-8 col-xl-7">
                                            <div class="card shadow-sm">
                                                <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between">
                                                    <h5 class="card-title mb-0"><i class="bi bi-search me-2"></i>Pencarian Berkas Kredit</h5>
                                                    <span class="badge bg-white text-primary">Marketing & Petugas</span>
                                                </div>
                                                <div class="card-body p-4">
                                                    <p class="text-muted small mb-3">Masukkan nomor berkas / rekening pinjaman debitur untuk memverifikasi dan melihat dokumen kredit.</p>
                                                    <form method="POST" class="needs-validation" action="#previewCardPDF" novalidate>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Nomor Berkas / Rekening:</label>
                                                            <div class="input-group">
                                                                <span class="input-group-text bg-body-tertiary"><i class="bi bi-file-earmark-person"></i></span>
                                                                <input type="text" name="norek" class="form-control" placeholder="Masukkan nomor rekening debitur..." required autofocus>
                                                            </div>
                                                            <div class="invalid-feedback">
                                                                Nomor rekening harus diisi
                                                            </div>
                                                        </div>
                                                        <button type="submit" name="cek" class="btn btn-primary px-4">
                                                            <i class="bi bi-search me-1"></i> Cek Berkas
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if (isset($pdf_url)): ?>
                                    <div class="card shadow-sm mt-4 mb-4" id="previewCardPDF">
                                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0"><i class="bi bi-file-earmark-pdf me-2"></i>Preview Berkas Kredit</h5>
                                            <div class="btn-group">
                                                <a href="<?= htmlspecialchars($pdf_url) ?>" target="_blank" class="btn btn-light btn-sm me-2">
                                                    <i class="bi bi-box-arrow-up-right me-1"></i> Buka di Tab Baru
                                                </a>
                                                <?php if ($role === 'adminkredit' || $role === 'ti_admin'): ?>
                                                    <a href="<?= htmlspecialchars($pdf_url) ?>&download=1" class="btn btn-light btn-sm">
                                                        <i class="bi bi-download me-1"></i> Unduh PDF
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <object
                                                data="<?= htmlspecialchars($pdf_url) ?>"
                                                type="application/pdf"
                                                width="100%"
                                                height="600px">
                                                <p class="text-center py-4">
                                                    Browser Anda tidak mendukung preview PDF langsung.
                                                    <br>
                                                    <a href="<?= htmlspecialchars($pdf_url) ?>" target="_blank" class="btn btn-primary btn-sm mt-3">
                                                        <i class="bi bi-box-arrow-up-right me-1"></i> Buka di Tab Baru
                                                    </a>
                                                    <?php if ($role === 'adminkredit' || $role === 'ti_admin'): ?>
                                                        <a href="<?= htmlspecialchars($pdf_url) ?>&download=1" class="btn btn-primary btn-sm mt-3 ms-2">
                                                            <i class="bi bi-download me-1"></i> Unduh PDF
                                                        </a>
                                                    <?php endif; ?>
                                                </p>
                                            </object>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php if ($can_ttd): ?>
                                <div class="<?= ($has_kredit ? 'mt-4 pt-4 border-top' : '') ?>">
                                    <?php if ($has_kredit): ?>
                                        <h6 class="fw-bold mb-3"><i class="bi bi-pen me-2 text-primary"></i>Spesimen Tanda Tangan Nasabah</h6>
                                    <?php endif; ?>
                                    <div class="row g-3">
                                        <!-- Upload Card Spesimen Tanda Tangan -->
                                        <div class="col-lg-6">
                                            <div class="card shadow-sm h-100 mb-0">
                                                <div class="card-header bg-primary text-white">
                                                    <h5 class="card-title mb-0"><i class="bi bi-upload me-2"></i>Upload Spesimen Tanda Tangan</h5>
                                                </div>
                                                <div class="card-body">
                                                    <p class="text-muted small mb-3">Unggah file spesimen tanda tangan (format JPG / PNG).</p>
                                                    <form action="" method="post" enctype="multipart/form-data" class="needs-validation" novalidate id="uploadFormSpesimen">
                                                        <div class="mb-3">
                                                            <div class="upload-drop-zone" id="dropZoneSpesimen">
                                                                <i class="bi bi-cloud-upload fs-2"></i>
                                                                <p class="mb-2">Drag & drop file gambar di sini atau klik untuk memilih</p>
                                                                <input type="file" name="files[]" class="form-control" accept=".jpg,.jpeg,.png" multiple required id="fileInputSpesimen" style="display: none;">
                                                                <button type="button" class="btn btn-outline-primary" id="browseButtonSpesimen">Pilih File</button>
                                                            </div>
                                                            <div class="selected-files-list" id="filesListSpesimen"></div>
                                                        </div>
                                                        <button type="submit" name="upload_ttd" class="btn btn-primary" id="uploadButtonSpesimen" disabled>Unggah</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Cek Spesimen -->
                                        <div class="col-lg-6">
                                            <div class="card shadow-sm h-100 mb-0">
                                                <div class="card-header bg-primary text-white">
                                                    <h5 class="card-title mb-0"><i class="bi bi-search me-2"></i>Cek Spesimen Tanda Tangan</h5>
                                                </div>
                                                <div class="card-body">
                                                    <p class="text-muted small mb-3">Verifikasi tanda tangan penarikan dengan memasukkan nomor rekening.</p>
                                                    <form method="POST" class="needs-validation" action="#previewCardTTD" novalidate>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-medium">Nomor Rekening:</label>
                                                            <div class="input-group">
                                                                <span class="input-group-text bg-body-tertiary"><i class="bi bi-credit-card-2-front"></i></span>
                                                                <input type="text" name="norek" class="form-control" placeholder="Contoh: 102.34.5678" required <?= ($role === 'teller' ? 'autofocus' : '') ?>>
                                                            </div>
                                                            <div class="invalid-feedback">
                                                                Nomor rekening harus diisi
                                                            </div>
                                                        </div>
                                                        <button type="submit" name="cek_ttd" class="btn btn-primary">
                                                            <i class="bi bi-search me-1"></i> Cek Tanda Tangan
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <?php if (isset($jpg_url_ttd)): ?>
                                        <div class="card shadow-sm mt-4 mb-3" id="previewCardTTD">
                                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                                <h5 class="mb-0"><i class="bi bi-file-earmark-image me-2"></i>Preview Spesimen Tanda Tangan</h5>
                                                <a href="<?= htmlspecialchars($jpg_url_ttd) ?>" target="_blank" class="btn btn-light text-dark btn-sm me-2">
                                                    <i class="bi bi-box-arrow-up-right me-1"></i> Buka di Tab Baru
                                                </a>
                                            </div>
                                            <div class="card-body text-center p-4">
                                                <img src="<?= htmlspecialchars($jpg_url_ttd) ?>" alt="Preview Spesimen Tanda Tangan" class="img-fluid rounded border p-1 bg-white shadow-sm" style="max-height: 500px;">
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Tab 2: Regulasi (SK & SOP) -->
                    <div class="tab-pane fade <?= ($default_tab_id === 'tab-regulasi-tab' ? 'show active' : '') ?>" id="tab-regulasi" role="tabpanel" aria-labelledby="tab-regulasi-tab">
                        <?php if ($role === 'admin_dok' || $role === 'ti_admin'): ?>
                            <div class="row g-3">
                                <!-- Upload Card SK -->
                                <div class="col-lg-6">
                                    <div class="card shadow-sm h-100 mb-0">
                                        <div class="card-header bg-primary text-white">
                                            <h5 class="card-title mb-0"><i class="bi bi-upload me-2"></i>Upload Berkas SK</h5>
                                        </div>
                                        <div class="card-body">
                                            <form action="" method="post" enctype="multipart/form-data" class="needs-validation" novalidate id="uploadFormSK">
                                                <div class="mb-3">
                                                    <label for="nomor_sk" class="form-label fw-medium">Nomor SK</label>
                                                    <input type="text" class="form-control" id="nomor_sk" name="nomor_sk" placeholder="Contoh: SK/DIR/2026/012" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="judul_sk" class="form-label fw-medium">Judul SK</label>
                                                    <input type="text" class="form-control" id="judul_sk" name="judul_sk" placeholder="Judul penetapan keputusan direksi..." required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="tahun_disahkan_sk" class="form-label fw-medium">Tahun Disahkan</label>
                                                    <input type="number" class="form-control" id="tahun_disahkan_sk" name="tahun_disahkan_sk" value="<?= date('Y') ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <div class="upload-drop-zone" id="dropZoneSK">
                                                        <i class="bi bi-cloud-upload fs-2"></i>
                                                        <p class="mb-2">Drag & drop file SK PDF di sini atau klik untuk memilih</p>
                                                        <input type="file" name="files[]" class="form-control" accept=".pdf" multiple required id="fileInputSK" style="display: none;">
                                                        <button type="button" class="btn btn-outline-primary" id="browseButtonSK">Pilih File</button>
                                                    </div>
                                                    <div class="selected-files-list" id="filesListSK"></div>
                                                </div>
                                                <button type="submit" name="upload_sk" class="btn btn-primary" id="uploadButtonSK" disabled>Unggah</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Upload Card SOP -->
                                <div class="col-lg-6">
                                    <div class="card shadow-sm h-100 mb-0">
                                        <div class="card-header bg-primary text-white">
                                            <h5 class="card-title mb-0"><i class="bi bi-upload me-2"></i>Upload Berkas SOP</h5>
                                        </div>
                                        <div class="card-body">
                                            <form action="" method="post" enctype="multipart/form-data" class="needs-validation" novalidate id="uploadFormSOP">
                                                <div class="mb-3">
                                                    <label for="nomor_sop" class="form-label fw-medium">Nomor SOP</label>
                                                    <input type="text" class="form-control" id="nomor_sop" name="nomor_sop" placeholder="Contoh: SOP/OPS/2026/005" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="judul_sop" class="form-label fw-medium">Judul SOP</label>
                                                    <input type="text" class="form-control" id="judul_sop" name="judul_sop" placeholder="Judul standar operasional prosedur..." required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="tahun_disahkan_sop" class="form-label fw-medium">Tahun Disahkan</label>
                                                    <input type="number" class="form-control" id="tahun_disahkan_sop" name="tahun_disahkan_sop" value="<?= date('Y') ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <div class="upload-drop-zone" id="dropZoneSOP">
                                                        <i class="bi bi-cloud-upload fs-2"></i>
                                                        <p class="mb-2">Drag & drop file SOP PDF di sini atau klik untuk memilih</p>
                                                        <input type="file" name="files[]" class="form-control" accept=".pdf" multiple required id="fileInputSOP" style="display: none;">
                                                        <button type="button" class="btn btn-outline-primary" id="browseButtonSOP">Pilih File</button>
                                                    </div>
                                                    <div class="selected-files-list" id="filesListSOP"></div>
                                                </div>
                                                <button type="submit" name="upload_sop" class="btn btn-primary" id="uploadButtonSOP" disabled>Unggah</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Non-admin Readers (Marketing, Teller, Kredit, Direksi) -->
                            <div class="card border-0 bg-body-tertiary p-4 mb-4 text-center rounded-3">
                                <div class="d-inline-flex align-items-center justify-content-center mx-auto mb-2 text-primary" style="width: 48px; height: 48px; background: rgba(2, 132, 199, 0.1); border-radius: 50%;">
                                    <i class="bi bi-book fs-4"></i>
                                </div>
                                <h5 class="fw-bold mb-1">Pusat Regulasi & Kebijakan Operasional</h5>
                                <p class="text-muted small mb-0 mx-auto" style="max-width: 600px;">
                                    Akses dan cari seluruh Surat Keputusan Direksi (SK) serta Standar Operasional Prosedur (SOP) resmi untuk pedoman operasional perbankan.
                                </p>
                            </div>
                        <?php endif; ?>

                        <!-- SK Preview Section -->
                        <?php if (isset($pdf_url_sk)): ?>
                            <div class="card shadow-sm mt-4 mb-4" id="previewCardPDF">
                                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0"><i class="bi bi-file-earmark-pdf me-2"></i>Preview Berkas SK</h5>
                                    <div class="btn-group">
                                        <a href="<?= htmlspecialchars($pdf_url_sk) ?>" target="_blank" class="btn btn-light btn-sm me-2">
                                            <i class="bi bi-box-arrow-up-right me-1"></i> Buka di Tab Baru
                                        </a>
                                        <a href="<?= htmlspecialchars($pdf_url_sk) ?>&download=1" class="btn btn-light btn-sm">
                                            <i class="bi bi-download me-1"></i> Unduh PDF
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <object
                                        data="<?= htmlspecialchars($pdf_url_sk) ?>"
                                        type="application/pdf"
                                        width="100%"
                                        height="600px">
                                        <p class="text-center py-4">
                                            Browser Anda tidak mendukung preview PDF langsung.
                                            <br>
                                            <a href="<?= htmlspecialchars($pdf_url_sk) ?>" target="_blank" class="btn btn-primary btn-sm mt-3">
                                                <i class="bi bi-box-arrow-up-right me-1"></i> Buka di Tab Baru
                                            </a>
                                            <a href="<?= htmlspecialchars($pdf_url_sk) ?>&download=1" class="btn btn-primary btn-sm mt-3 ms-2">
                                                <i class="bi bi-download me-1"></i> Unduh PDF
                                            </a>
                                        </p>
                                    </object>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- SOP PDF Preview Section -->
                        <?php if (isset($pdf_url_sop)): ?>
                            <div class="card shadow-sm mt-4 mb-4" id="previewCardPDF">
                                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0"><i class="bi bi-file-earmark-pdf me-2"></i>Preview Berkas SOP</h5>
                                    <div class="btn-group">
                                        <a href="<?= htmlspecialchars($pdf_url_sop) ?>" target="_blank" class="btn btn-light btn-sm me-2">
                                            <i class="bi bi-box-arrow-up-right me-1"></i> Buka di Tab Baru
                                        </a>
                                        <a href="<?= htmlspecialchars($pdf_url_sop) ?>&download=1" class="btn btn-light btn-sm">
                                            <i class="bi bi-download me-1"></i> Unduh PDF
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <object
                                        data="<?= htmlspecialchars($pdf_url_sop) ?>"
                                        type="application/pdf"
                                        width="100%"
                                        height="600px">
                                        <p class="text-center py-4">
                                            Browser Anda tidak mendukung preview PDF langsung.
                                            <br>
                                            <a href="<?= htmlspecialchars($pdf_url_sop) ?>" target="_blank" class="btn btn-primary btn-sm mt-3">
                                                <i class="bi bi-box-arrow-up-right me-1"></i> Buka di Tab Baru
                                            </a>
                                            <a href="<?= htmlspecialchars($pdf_url_sop) ?>&download=1" class="btn btn-primary btn-sm mt-3 ms-2">
                                                <i class="bi bi-download me-1"></i> Unduh PDF
                                            </a>
                                        </p>
                                    </object>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Quick Navigation to Regulation Databases -->
                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <div class="card p-3 h-100 shadow-sm">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <h6 class="fw-bold mb-0"><i class="bi bi-file-earmark-text text-primary me-2"></i>Pangkalan Data SK</h6>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><?= number_format($stats['sk']) ?> Dokumen</span>
                                    </div>
                                    <p class="text-muted small mb-3">Cari, verifikasi nomor keputusan, dan akses berkas regulasi Surat Keputusan.</p>
                                    <a href="cek_sk.php" class="btn btn-outline-primary btn-sm mt-auto">
                                        <i class="bi bi-search me-1"></i> Buka Pencarian SK
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card p-3 h-100 shadow-sm">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <h6 class="fw-bold mb-0"><i class="bi bi-file-earmark-ruled text-warning me-2"></i>Pangkalan Data SOP</h6>
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle"><?= number_format($stats['sop']) ?> Prosedur</span>
                                    </div>
                                    <p class="text-muted small mb-3">Pedoman Standar Operasional Prosedur kerja seluruh divisi dan cabang.</p>
                                    <a href="cek_sop.php" class="btn btn-outline-warning btn-sm mt-auto">
                                        <i class="bi bi-search me-1"></i> Buka Pencarian SOP
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 3: Log Sistem / Quick Access -->
                    <div class="tab-pane fade" id="tab-log" role="tabpanel" aria-labelledby="tab-log-tab">
                        <!-- Quick Access Section -->
                        <h6 class="fw-bold mb-3"><i class="bi bi-lightning-charge text-warning me-2"></i>Pintasan Akses Cepat (Quick Access)</h6>
                        <div class="row g-3 mb-4">
                            <?php if ($role === 'ti_admin'): ?>
                                <div class="col-sm-6 col-md-4 col-xl-2">
                                    <a href="add_user.php" class="quick-access-card text-center h-100">
                                        <div class="mb-2"><i class="bi bi-person-gear fs-2 text-primary"></i></div>
                                        <div class="fw-semibold small">Kelola User</div>
                                        <small class="text-muted d-block mt-1">Admin TI</small>
                                    </a>
                                </div>
                            <?php endif; ?>
                            <div class="col-sm-6 col-md-4 col-xl-2">
                                <a href="disposisi/disposisi.php" class="quick-access-card text-center h-100">
                                    <div class="mb-2"><i class="bi bi-envelope-arrow-down fs-2 text-info"></i></div>
                                    <div class="fw-semibold small">Surat Masuk</div>
                                    <small class="text-muted d-block mt-1"><?= $stats['disp_masuk'] ?> Arsip</small>
                                </a>
                            </div>
                            <div class="col-sm-6 col-md-4 col-xl-2">
                                <a href="disposisi/disposisi_keluar.php" class="quick-access-card text-center h-100">
                                    <div class="mb-2"><i class="bi bi-envelope-arrow-up fs-2 text-primary"></i></div>
                                    <div class="fw-semibold small">Surat Keluar</div>
                                    <small class="text-muted d-block mt-1"><?= $stats['disp_keluar'] ?> Arsip</small>
                                </a>
                            </div>
                            <div class="col-sm-6 col-md-4 col-xl-2">
                                <a href="cek_sk.php" class="quick-access-card text-center h-100">
                                    <div class="mb-2"><i class="bi bi-file-earmark-text fs-2 text-success"></i></div>
                                    <div class="fw-semibold small">Arsip SK</div>
                                    <small class="text-muted d-block mt-1"><?= $stats['sk'] ?> Berkas</small>
                                </a>
                            </div>
                            <div class="col-sm-6 col-md-4 col-xl-2">
                                <a href="cek_sop.php" class="quick-access-card text-center h-100">
                                    <div class="mb-2"><i class="bi bi-file-earmark-ruled fs-2 text-warning"></i></div>
                                    <div class="fw-semibold small">Arsip SOP</div>
                                    <small class="text-muted d-block mt-1"><?= $stats['sop'] ?> Berkas</small>
                                </a>
                            </div>
                            <div class="col-sm-6 col-md-4 col-xl-2">
                                <a href="disposisi/export_excel.php" class="quick-access-card text-center h-100">
                                    <div class="mb-2"><i class="bi bi-file-earmark-excel fs-2 text-success"></i></div>
                                    <div class="fw-semibold small">Ekspor Excel</div>
                                    <small class="text-muted d-block mt-1">Laporan</small>
                                </a>
                            </div>
                        </div>

                        <?php if ($role === 'ti_admin'): ?>
                        <div class="row g-4">
                            <!-- Aktivitas Terkini (Disposisi Terbaru) -->
                            <div class="col-lg-7">
                                <div class="card shadow-sm h-100 mb-0">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <span class="fw-bold"><i class="bi bi-clock-history me-2 text-primary"></i>Aktivitas Disposisi Surat Terkini</span>
                                        <a href="disposisi/disposisi.php" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 0.75rem;">Lihat Semua</a>
                                    </div>
                                    <div class="card-body p-0">
                                        <?php if (empty($recent_activities)): ?>
                                            <div class="p-4 text-center text-muted">
                                                <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                                                <span class="small">Belum ada riwayat aktivitas surat.</span>
                                            </div>
                                        <?php else: ?>
                                            <div class="table-responsive">
                                                <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th class="ps-3">Tipe</th>
                                                            <th>Nomor / Pihak</th>
                                                            <th>Perihal</th>
                                                            <th class="pe-3">Tanggal</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($recent_activities as $act): ?>
                                                            <tr>
                                                                <td class="ps-3">
                                                                    <?php if ($act['tipe'] === 'masuk'): ?>
                                                                        <span class="badge bg-info-subtle text-info border border-info-subtle">Masuk</span>
                                                                    <?php else: ?>
                                                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Keluar</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <div class="fw-semibold text-truncate" style="max-width: 160px;"><?= htmlspecialchars($act['nomor'] ?: '-') ?></div>
                                                                    <small class="text-muted text-truncate d-block" style="max-width: 160px;"><?= htmlspecialchars($act['pihak'] ?: '-') ?></small>
                                                                </td>
                                                                <td>
                                                                    <div class="text-truncate text-muted" style="max-width: 220px;" title="<?= htmlspecialchars($act['perihal']) ?>">
                                                                        <?= htmlspecialchars($act['perihal'] ?: '-') ?>
                                                                    </div>
                                                                </td>
                                                                <td class="pe-3 text-nowrap text-muted small">
                                                                    <?= $act['tanggal'] ? date('d/m/Y', strtotime($act['tanggal'])) : '-' ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- System & Environment Info -->
                            <div class="col-lg-5">
                                <div class="card shadow-sm h-100 mb-0">
                                    <div class="card-header">
                                        <span class="fw-bold"><i class="bi bi-hdd-network me-2 text-info"></i>Status Sistem & Lingkungan</span>
                                    </div>
                                    <div class="card-body p-3">
                                        <ul class="list-group list-group-flush small">
                                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                                <span class="text-muted"><i class="bi bi-person me-2"></i>Pengguna Aktif</span>
                                                <span class="fw-semibold"><?= htmlspecialchars($_SESSION['user']['username'] ?? '-') ?></span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                                <span class="text-muted"><i class="bi bi-shield-lock me-2"></i>Hak Akses</span>
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle text-uppercase"><?= htmlspecialchars($role) ?></span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                                <span class="text-muted"><i class="bi bi-database me-2"></i>Koneksi Database</span>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-check-circle me-1"></i>MySQL PDO OK</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                                <span class="text-muted"><i class="bi bi-code-square me-2"></i>Versi PHP</span>
                                                <span class="font-monospace text-body"><?= PHP_VERSION ?></span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                                <span class="text-muted"><i class="bi bi-clock me-2"></i>Waktu Server</span>
                                                <span class="text-body"><?= date('d M Y, H:i') ?> WIB</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
</div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            'use strict';

            // Form validation
            var forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });

            // Handle upload functionality
            function setupUploadHandlers(formId, dropZoneId, fileInputId, filesListId, uploadButtonId, validTypes) {
                const dropZone = document.getElementById(dropZoneId);
                const fileInput = document.getElementById(fileInputId);
                const filesList = document.getElementById(filesListId);
                const uploadButton = document.getElementById(uploadButtonId);

                console.log('Setup handlers for:', {
                    dropZone,
                    fileInput,
                    filesList,
                    uploadButton
                });

                // Pastikan semua elemen ada
                if (!dropZone || !fileInput || !filesList || !uploadButton) {
                    console.error('Some elements are missing for', formId);
                    return;
                }

                // Drag and drop
                dropZone.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    dropZone.classList.add('dragover');
                });

                dropZone.addEventListener('dragleave', () => {
                    dropZone.classList.remove('dragover');
                });

                dropZone.addEventListener('drop', (e) => {
                    e.preventDefault();
                    dropZone.classList.remove('dragover');
                    const files = e.dataTransfer.files;
                    handleFiles(files);
                });

                // Browse button
                const browseButton = dropZone.querySelector('.btn-outline-primary');
                browseButton.addEventListener('click', () => {
                    fileInput.click();
                });

                // File input change
                fileInput.addEventListener('change', (e) => {
                    handleFiles(e.target.files);
                });

                function formatSize(bytes) {
                    if (bytes === 0) return '0 B';
                    const k = 1024;
                    const sizes = ['B', 'KB', 'MB', 'GB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
                }

                // Handle files and validation
                function handleFiles(files) {
                    filesList.innerHTML = '';
                    let validFiles = true;

                    Array.from(files).forEach(file => {
                        const div = document.createElement('div');
                        div.className = 'selected-file-item';

                        const isPdf = file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');
                        const iconClass = isPdf ? 'bi bi-file-earmark-pdf text-danger' : 'bi bi-file-earmark-image text-primary';
                        const isTypeValid = validTypes.includes(file.type) || (isPdf && validTypes.includes('application/pdf'));
                        const isSizeValid = file.size <= 25 * 1024 * 1024; // 25MB max

                        if (!isTypeValid) {
                            div.innerHTML = `
                                <div class="file-meta">
                                    <i class="bi bi-exclamation-triangle text-danger fs-5"></i>
                                    <span class="file-name text-danger" title="${file.name}">${file.name}</span>
                                    <span class="badge bg-danger-subtle text-danger file-size">Format Salah</span>
                                </div>
                                <i class="bi bi-x-circle-fill remove-file" title="Hapus berkas"></i>
                            `;
                            validFiles = false;
                        } else if (!isSizeValid) {
                            div.innerHTML = `
                                <div class="file-meta">
                                    <i class="bi bi-exclamation-triangle text-warning fs-5"></i>
                                    <span class="file-name text-warning" title="${file.name}">${file.name}</span>
                                    <span class="badge bg-warning-subtle text-warning file-size">&gt;25MB</span>
                                </div>
                                <i class="bi bi-x-circle-fill remove-file" title="Hapus berkas"></i>
                            `;
                            validFiles = false;
                        } else {
                            div.innerHTML = `
                                <div class="file-meta">
                                    <i class="${iconClass} fs-5"></i>
                                    <span class="file-name" title="${file.name}">${file.name}</span>
                                    <span class="badge bg-secondary-subtle text-secondary file-size font-monospace">${formatSize(file.size)}</span>
                                </div>
                                <i class="bi bi-x-circle-fill remove-file" title="Hapus berkas"></i>
                            `;
                        }

                        filesList.appendChild(div);
                    });

                    // Enable upload button if all files are valid
                    uploadButton.disabled = !validFiles || files.length === 0;
                }

                // Remove file
                filesList.addEventListener('click', (e) => {
                    if (e.target.classList.contains('remove-file')) {
                        const dt = new DataTransfer();
                        const files = fileInput.files;
                        const parent = e.target.closest('.selected-file-item');
                        const index = Array.from(filesList.children).indexOf(parent);

                        for (let i = 0; i < files.length; i++) {
                            if (i !== index) {
                                dt.items.add(files[i]);
                            }
                        }

                        fileInput.files = dt.files;
                        parent.remove();
                        uploadButton.disabled = fileInput.files.length === 0;
                    }
                });

                // Loading feedback on upload
                const uploadForm = document.getElementById(formId);
                if (uploadForm) {
                    uploadForm.addEventListener('submit', function() {
                        uploadButton.disabled = true;
                        uploadButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Mengunggah...';
                    });
                }
            }

            // Setup handlers for both forms
            setupUploadHandlers(
                'uploadFormSpesimen',
                'dropZoneSpesimen',
                'fileInputSpesimen',
                'filesListSpesimen',
                'uploadButtonSpesimen',
                ['image/jpeg', 'image/png']
            );

            setupUploadHandlers(
                'uploadFormBerkas',
                'dropZoneBerkas',
                'fileInputBerkas',
                'filesListBerkas',
                'uploadButtonBerkas',
                ['application/pdf']
            );

            setupUploadHandlers(
                'uploadFormSK',
                'dropZoneSK',
                'fileInputSK',
                'filesListSK',
                'uploadButtonSK',
                ['application/pdf']
            );

            setupUploadHandlers(
                'uploadFormSOP',
                'dropZoneSOP',
                'fileInputSOP',
                'filesListSOP',
                'uploadButtonSOP',
                ['application/pdf']
            );
        });

        document.addEventListener('DOMContentLoaded', function() {
            const hash = window.location.hash;
            if (hash === '#previewCardTTD' || hash === '#previewCardPDF') {
                const target = document.querySelector(hash);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            }
        });
    
        // Tab Persistence & Context Auto-Switching
        document.addEventListener('DOMContentLoaded', function() {
            const triggerTabList = document.querySelectorAll('#dashboardTab button[data-bs-toggle="tab"]');
            triggerTabList.forEach(function(triggerEl) {
                triggerEl.addEventListener('shown.bs.tab', function(event) {
                    sessionStorage.setItem('dashboard_active_tab', event.target.id);
                });
            });

            // Auto-activate tab based on context or session
            const defaultTabId = '<?= $default_tab_id ?>';
            <?php if (isset($pdf_url_sk) || isset($pdf_url_sop) || isset($_POST['upload_sk']) || isset($_POST['upload_sop'])): ?>
                let targetTabId = 'tab-regulasi-tab';
            <?php elseif (isset($_GET['tab'])): ?>
                let targetTabId = 'tab-<?= htmlspecialchars($_GET['tab']) ?>-tab';
            <?php else: ?>
                let targetTabId = sessionStorage.getItem('dashboard_active_tab') || defaultTabId;
            <?php endif; ?>

            let targetTabEl = document.getElementById(targetTabId);
            if (!targetTabEl) {
                targetTabEl = document.getElementById(defaultTabId);
            }
            if (targetTabEl) {
                const tabInstance = bootstrap.Tab.getOrCreateInstance(targetTabEl);
                tabInstance.show();
            }
        });
</script>
</body>

</html>