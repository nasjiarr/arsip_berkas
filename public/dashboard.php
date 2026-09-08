<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
check_login();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" type="text/css" href="assets/style.css">
</head>

<body>
    <!-- Toggle Button for Mobile -->
    <button class="navbar-toggle" id="sidebarToggle">
        <i class="bi bi-list"></i>
    </button>

    <!-- Backdrop for mobile -->
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <div class="container-fluid p-0">
        <div class="row g-0">
            <!-- Sidebar -->
            <div class="sidebar bg-white p-3" id="sidebar">
                <div class="d-flex align-items-center mb-4">
                    <i class="bi bi-bank fs-2 text-primary me-2"></i>
                    <h4 class="mb-0">Sistem Informasi Bank Kulon Progo</h4>
                </div>

                <hr>

                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active text-white bg-primary' : 'text-dark'; ?>" href="dashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-dark" href="#" id="dropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-list-check"></i>
                            Cek Berkas
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                            <li><a class="dropdown-item" href="cek_sk.php">Cek SK</a></li>
                            <li><a class="dropdown-item" href="cek_sop.php">Cek SOP</a></li>
                            <?php if ($role !== 'sekre'): ?>
                                <li><a class="dropdown-item <?php echo basename($_SERVER['PHP_SELF']) == 'disposisi/disposisi.php' ? 'active text-white bg-primary' : 'text-dark'; ?>" href="disposisi/disposisi.php">Disposisi Surat Masuk</a></li>
                                <li><a class="dropdown-item <?php echo basename($_SERVER['PHP_SELF']) == 'disposisi/disposisi_keluar.php' ? 'active text-white bg-primary' : 'text-dark'; ?>" href="disposisi/disposisi_keluar.php">Disposisi Surat Keluar</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <?php if ($role === 'ti_admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'add_user.php' ? 'active text-white bg-primary' : 'text-dark'; ?>" href="add_user.php">
                                <i class="bi bi-person-plus"></i> Kelola User
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link text-danger fw-bold" href="logout.php">
                            <i class="bi bi-box-arrow-right text-danger"></i> Keluar
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 content">
                <div class="user-welcome">
                    <h1>Selamat Datang, <?= htmlspecialchars($_SESSION['user']['username']); ?></h1>
                </div>

                <div class="row">
                    <!-- Cek Rekening Card -->
                    <?php if ($role === 'ti_admin' || $role === 'adminkredit'): ?>
                        <!-- Upload Card Berkas Kredit-->
                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="card-title mb-0"><i class="bi bi-upload"></i> Upload Berkas Kredit</h5>
                                </div>
                                <div class="card-body">
                                    <h6>Silahkan upload berkas kredit dalam format PDF</h6>
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


                    <?php endif; ?>
                    <?php if ($role !== 'teller' && $role !== 'admin_dok'): ?>

                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="card-title mb-0"><i class="bi bi-search"></i> Cek Berkas Kredit</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" class="needs-validation" action="#previewCardPDF" novalidate>
                                        <div class="mb-3">
                                            <label class="form-label">Nomor Berkas:</label>
                                            <input type="text" name="norek" class="form-control" required>
                                            <div class="invalid-feedback">
                                                Nomor rekening harus diisi
                                            </div>
                                        </div>
                                        <button type="submit" name="cek" class="btn btn-primary">
                                            Cek
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>


                </div>

                <!-- Cek & Upload Spesimen TTD -->
                <?php if ($role === 'teller' || $role === 'ti_admin'): ?>
                    <div class="row">

                        <!-- Upload Card Spesimen Tanda Tangan -->
                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-header bg-success text-white">
                                    <h5 class="card-title mb-0"><i class="bi bi-upload"></i> Upload Berkas Spesimen Tanda Tangan</h5>
                                </div>
                                <div class="card-body">
                                    <form action="" method="post" enctype="multipart/form-data" class="needs-validation" novalidate id="uploadFormSpesimen">
                                        <div class="mb-3">
                                            <div class="upload-drop-zone" id="dropZoneSpesimen">
                                                <i class="bi bi-cloud-upload fs-2"></i>
                                                <p class="mb-2">Drag & drop file JPG di sini atau klik untuk memilih</p>
                                                <input type="file" name="files[]" class="form-control" accept=".jpg,.jpeg,.png" multiple required id="fileInputSpesimen" style="display: none;">
                                                <button type="button" class="btn btn-outline-primary" id="browseButtonSpesimen">Pilih File</button>
                                            </div>
                                            <div class="selected-files-list" id="filesListSpesimen"></div>
                                        </div>
                                        <button type="submit" name="upload_ttd" class="btn btn-success" id="uploadButtonSpesimen" disabled>Unggah</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Cek Spesimen -->
                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-header bg-success text-white">
                                    <h5 class="card-title mb-0"><i class="bi bi-search"></i> Cek Spesimen Tanda Tangan</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" class="needs-validation" action="#previewCardTTD" novalidate>
                                        <div class="mb-3">
                                            <label class="form-label">Nomor Rekening:</label>
                                            <input type="text" name="norek" class="form-control" required>
                                            <div class="invalid-feedback">
                                                Nomor rekening harus diisi
                                            </div>
                                        </div>
                                        <button type="submit" name="cek_ttd" class="btn btn-success">
                                            Cek
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>



                    </div>

                    <!-- Spesimen Tanda Tangan Preview Section -->
                    <?php if (isset($jpg_url_ttd)): ?>
                        <div class="card mt-4" id="previewCardTTD">
                            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="bi bi-file-earmark-image"></i> Preview Spesimen Tanda Tangan</h5>
                                <a href="<?= htmlspecialchars($jpg_url_ttd) ?>" target="_blank" class="btn btn-light text-dark btn-sm me-2">
                                    <i class="bi bi-box-arrow-up-right"></i> Buka di Tab Baru
                                </a>
                            </div>
                            <div class="card-body text-center">
                                <img src="<?= htmlspecialchars($jpg_url_ttd) ?>" alt="Preview Spesimen Tanda Tangan" class="img-fluid rounded" style="max-height: 600px;">

                            </div>
                        </div>
                    <?php endif; ?>


                <?php endif; ?>


                <!-- PDF Preview Section -->
                <?php if (isset($pdf_url)): ?>
                    <div class="card mt-4" id="previewCardPDF">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-file-earmark-pdf"></i> Preview Berkas Kredit</h5>
                            <div class="btn-group">
                                <a href="<?= htmlspecialchars($pdf_url) ?>" target="_blank" class="btn btn-light btn-sm me-2">
                                    <i class="bi bi-box-arrow-up-right"></i> Buka di Tab Baru
                                </a>
                                <?php if ($role === 'adminkredit' || $role === 'ti_admin'): ?>
                                    <a href="<?= htmlspecialchars($pdf_url) ?>&download=1" class="btn btn-light btn-sm">
                                        <i class="bi bi-download"></i> Unduh PDF
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
                                <p class="text-center">
                                    Browser Anda tidak mendukung preview PDF.
                                    <br>
                                    <a href="<?= htmlspecialchars($pdf_url) ?>" target="_blank" class="btn btn-primary btn-sm mt-2">
                                        <i class="bi bi-box-arrow-up-right"></i> Buka di Tab Baru
                                    </a>
                                    <?php if ($role === 'adminkredit' || $role === 'ti_admin'): ?>
                                        <a href="<?= htmlspecialchars($pdf_url) ?>&download=1" class="btn btn-primary btn-sm mt-2 ms-2">
                                            <i class="bi bi-download"></i> Unduh PDF
                                        </a>
                                    <?php endif; ?>
                                </p>
                            </object>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($role === 'admin_dok' || $role === 'ti_admin'): ?>
                    <div class="row">

                        <!-- Upload Card SK -->
                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h4 class="card-title mb-0"><i class="bi bi-upload"></i> Upload Berkas SK</h4>
                                </div>
                                <div class="card-body">
                                    <form action="" method="post" enctype="multipart/form-data" class="needs-validation" novalidate id="uploadFormSK">
                                        <div class="mb-3">
                                            <label for="nomor_sk" class="form-label">Nomor SK</label>
                                            <input type="text" class="form-control" id="nomor_sk" name="nomor_sk" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="judul_sk" class="form-label">Judul SK</label>
                                            <input type="text" class="form-control" id="judul_sk" name="judul_sk" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="tahun_disahkan_sk" class="form-label">Tahun Disahkan</label>
                                            <input type="number" class="form-control" id="tahun_disahkan_sk" name="tahun_disahkan_sk" required>
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
                                        <button type="submit" name="upload_sk" class="btn btn-success" id="uploadButtonSK" disabled>Unggah</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Upload Card SOP -->
                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h4 class="card-title mb-0"><i class="bi bi-upload"></i> Upload Berkas SOP</h4>
                                </div>
                                <div class="card-body">
                                    <form action="" method="post" enctype="multipart/form-data" class="needs-validation" novalidate id="uploadFormSOP">
                                        <div class="mb-3">
                                            <label for="nomor_sop" class="form-label">Nomor SOP</label>
                                            <input type="text" class="form-control" id="nomor_sop" name="nomor_sop" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="judul_sop" class="form-label">Judul SOP</label>
                                            <input type="text" class="form-control" id="judul_sop" name="judul_sop" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="tahun_disahkan_sop" class="form-label">Tahun Disahkan</label>
                                            <input type="number" class="form-control" id="tahun_disahkan_sop" name="tahun_disahkan_sop" required>
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
                                        <button type="submit" name="upload_sop" class="btn btn-success" id="uploadButtonSOP" disabled>Unggah</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>

                <?php endif; ?>

                <!-- Alert Modal -->
                <div class="modal fade" id="alertModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow">
                            <div class="modal-header border-0 py-3">
                                <h5 class="modal-title fw-bold"></h5>
                                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body px-4 py-4">
                                <div class="text-center mb-4">
                                    <div class="alert-icon mb-3">
                                        <i class="bi" style="font-size: 3rem;"></i>
                                    </div>
                                    <div class="alert-message fs-5"></div>
                                </div>
                            </div>
                            <div class="modal-footer border-0 pt-0 pb-4">
                                <button type="button" class="btn btn-lg px-4 rounded-3" data-bs-dismiss="modal">Tutup</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PHP Alert Handler -->
                <?php foreach (['sk', 'sop', 'ttd', 'kredit'] as $type): ?>
                    <?php if (isset(${"message_$type"})): ?>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const modal = new bootstrap.Modal(document.getElementById('alertModal'));
                                const alertModal = document.getElementById('alertModal');

                                alertModal.querySelector('.modal-header').className = 'modal-header border-0 py-3 bg-success-subtle';
                                alertModal.querySelector('.modal-title').textContent = 'Berhasil!';
                                alertModal.querySelector('.alert-icon i').className = 'bi bi-check-circle-fill text-success';
                                alertModal.querySelector('.alert-message').innerHTML = '<?= htmlspecialchars(${"message_$type"}) ?>';
                                alertModal.querySelector('.modal-footer .btn').className = 'btn btn-success btn-lg px-4 rounded-3';

                                modal.show();
                            });
                        </script>
                    <?php endif; ?>

                    <?php if (isset(${"error_$type"})): ?>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const modal = new bootstrap.Modal(document.getElementById('alertModal'));
                                const alertModal = document.getElementById('alertModal');

                                alertModal.querySelector('.modal-header').className = 'modal-header border-0 py-3 bg-danger-subtle';
                                alertModal.querySelector('.modal-title').textContent = 'Gagal!';
                                alertModal.querySelector('.alert-icon i').className = 'bi bi-exclamation-circle-fill text-danger';
                                alertModal.querySelector('.alert-message').innerHTML = '<?= htmlspecialchars(${"error_$type"}) ?>';
                                alertModal.querySelector('.modal-footer .btn').className = 'btn btn-danger btn-lg px-4 rounded-3';

                                modal.show();
                            });
                        </script>
                    <?php endif; ?>
                <?php endforeach; ?>

                <!-- SK Preview Section -->
                <?php if (isset($pdf_url_sk)): ?>
                    <div class="card mt-4" id="previewCardPDF">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-file-earmark-pdf"></i> Preview Berkas SK</h5>
                            <div class="btn-group">
                                <a href="<?= htmlspecialchars($pdf_url_sk) ?>" target="_blank" class="btn btn-light btn-sm me-2">
                                    <i class="bi bi-box-arrow-up-right"></i> Buka di Tab Baru
                                </a>
                                <?php if ($role === 'admin_dok' || $role === 'ti_admin'): ?>
                                    <a href="<?= htmlspecialchars($pdf_url_sk) ?>&download=1" class="btn btn-light btn-sm">
                                        <i class="bi bi-download"></i> Unduh PDF
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <object
                                data="<?= htmlspecialchars($pdf_url_sk) ?>"
                                type="application/pdf"
                                width="100%"
                                height="600px">
                                <p class="text-center">
                                    Browser Anda tidak mendukung preview PDF.
                                    <br>
                                    <a href="<?= htmlspecialchars($pdf_url_sk) ?>" target="_blank" class="btn btn-primary btn-sm mt-2">
                                        <i class="bi bi-box-arrow-up-right"></i> Buka di Tab Baru
                                    </a>
                                    <?php if ($role === 'admin_dok' || $role === 'ti_admin'): ?>
                                        <a href="<?= htmlspecialchars($pdf_url_sk) ?>&download=1" class="btn btn-primary btn-sm mt-2 ms-2">
                                            <i class="bi bi-download"></i> Unduh PDF
                                        </a>
                                    <?php endif; ?>
                                </p>
                            </object>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- SOP PDF Preview Section -->
                <?php if (isset($pdf_url_sop)): ?>
                    <div class="card mt-4" id="previewCardPDF">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-file-earmark-pdf"></i> Preview Berkas SOP</h5>
                            <div class="btn-group">
                                <a href="<?= htmlspecialchars($pdf_url_sop) ?>" target="_blank" class="btn btn-light btn-sm me-2">
                                    <i class="bi bi-box-arrow-up-right"></i> Buka di Tab Baru
                                </a>
                                <?php if ($role === 'admin_dok' || $role === 'ti_admin'): ?>
                                    <a href="<?= htmlspecialchars($pdf_url_sop) ?>&download=1" class="btn btn-light btn-sm">
                                        <i class="bi bi-download"></i> Unduh PDF
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <object
                                data="<?= htmlspecialchars($pdf_url_sop) ?>"
                                type="application/pdf"
                                width="100%"
                                height="600px">
                                <p class="text-center">
                                    Browser Anda tidak mendukung preview PDF.
                                    <br>
                                    <a href="<?= htmlspecialchars($pdf_url_sop) ?>" target="_blank" class="btn btn-primary btn-sm mt-2">
                                        <i class="bi bi-box-arrow-up-right"></i> Buka di Tab Baru
                                    </a>
                                    <?php if ($role === 'admin_dok' || $role === 'ti_admin'): ?>
                                        <a href="<?= htmlspecialchars($pdf_url_sop) ?>&download=1" class="btn btn-primary btn-sm mt-2 ms-2">
                                            <i class="bi bi-download"></i> Unduh PDF
                                        </a>
                                    <?php endif; ?>
                                </p>
                            </object>
                        </div>
                    </div>
                <?php endif; ?>

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

                // Handle files and validation
                function handleFiles(files) {
                    filesList.innerHTML = '';
                    let validFiles = true;

                    Array.from(files).forEach(file => {
                        const div = document.createElement('div');
                        div.className = 'selected-file-item';

                        // Validate file type
                        if (!validTypes.includes(file.type)) {
                            div.innerHTML = `
                        <span class="text-danger">${file.name} (Format tidak sesuai!)</span>
                        <i class="bi bi-x-circle remove-file"></i>
                    `;
                            validFiles = false;
                        } else {
                            div.innerHTML = `
                        <span>${file.name}</span>
                        <i class="bi bi-x-circle remove-file"></i>
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
                        const parent = e.target.parentElement;
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

            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebarBackdrop = document.getElementById('sidebarBackdrop');

            function toggleSidebar() {
                sidebar.classList.toggle('show');
                sidebarBackdrop.classList.toggle('show');

                // Sembunyikan tombol hanya jika layar ≤ 768px
                if (window.innerWidth <= 768) {
                    sidebarToggle.style.display = sidebar.classList.contains('show') ? 'none' : 'block';
                }
            }

            sidebarToggle.addEventListener('click', toggleSidebar);
            sidebarBackdrop.addEventListener('click', toggleSidebar);

            // Menampilkan kembali tombol saat sidebar ditutup atau layar diperbesar
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    sidebar.classList.remove('show');
                    sidebarBackdrop.classList.remove('show');
                    sidebarToggle.style.display = "block"; // Pastikan tombol selalu muncul di desktop
                } else if (!sidebar.classList.contains('show')) {
                    sidebarToggle.style.display = "block"; // Jika sidebar tertutup di mobile, tampilkan kembali tombol
                }
            });
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
    </script>
</body>

</html>