<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
check_login();
if (!in_array($_SESSION['user']['role'] ?? '', ['sekre', 'ti_admin'])) {
    http_response_code(403);
    exit('Akses ditolak.');
}

$current_user_role = $_SESSION['user']['role'];

// Define network path for PDF uploads
define('NETWORK_PDF_PATH', PATH_DISPOSISI);
define('LOCAL_IMAGE_PATH', PATH_DISPOSISI);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('Token keamanan tidak valid.');
    }
    try {
        $pdo->beginTransaction();

        // Get last sequence number
        $stmt = $pdo->query("SELECT MAX(no) as max_no FROM disposisi_surat");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $next_no = ($row['max_no'] ?? 0) + 1;

        // Validate input
        $kategori_input = $_POST['kategori_id'] ?? $_POST['kode'] ?? '';
        $tanggal_surat = $_POST['tanggal_surat'];
        $tanggal_masuk = $_POST['tanggal_masuk'];
        $nomer_surat = $_POST['nomer_surat'];
        $dari = $_POST['dari'];
        $perihal = $_POST['perihal'];
        $instruksi = $_POST['instruksi'];
        $diteruskan = $_POST['diteruskan'];
        $db_path = null;


        // Handle file upload
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['file'];

            // ponytail: validate real MIME type using finfo; extend map if other formats needed.
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime_type = $finfo->file($file['tmp_name']);

            $allowed_mime_to_ext = [
                'application/pdf' => 'pdf',
                'image/jpeg'      => 'jpg',
                'image/png'       => 'png',
                'image/gif'       => 'gif',
            ];

            if (!array_key_exists($mime_type, $allowed_mime_to_ext)) {
                throw new Exception('Tipe file tidak diizinkan. Hanya PDF dan Gambar (JPG, PNG, GIF) yang diperbolehkan.');
            }

            // Generate unique filename using safe extension from verified MIME
            $ext = $allowed_mime_to_ext[$mime_type];
            $filename = uniqid('disp_', true) . '.' . $ext;

            // Ensure directory exists
            if (!is_dir(PATH_DISPOSISI)) {
                if (!@mkdir(PATH_DISPOSISI, 0755, true)) {
                    throw new Exception('Gagal membuat direktori upload');
                }
            }

            $upload_path = PATH_DISPOSISI . $filename;
            $db_path = PATH_DISPOSISI . $filename;

            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
                throw new Exception('Gagal mengupload file. Pastikan folder network dapat diakses.');
            }
        }

        // Lookup category by kategori_id or fallback to kode
        $stmt_kat = $pdo->prepare("SELECT id_kategori, kode_kategori FROM kategori_surat WHERE id_kategori = :id_kat OR kode_kategori = :kode LIMIT 1");
        $stmt_kat->execute([':id_kat' => (int)$kategori_input, ':kode' => (string)$kategori_input]);
        $kategori_row = $stmt_kat->fetch(PDO::FETCH_ASSOC);

        if ($kategori_row) {
            $kategori_id = (int)$kategori_row['id_kategori'];
            $kode = $kategori_row['kode_kategori'];
        } else {
            $kategori_id = 23;
            $kode = '023';
        }

        // Insert into database
        $query = "INSERT INTO disposisi_surat (no, kode, kategori_id, tanggal_surat, tanggal_masuk, nomer_surat, dari, perihal, instruksi, diteruskan, file_path) 
                 VALUES (:no, :kode, :kategori_id, :tanggal_surat, :tanggal_masuk, :nomer_surat, :dari, :perihal, :instruksi, :diteruskan, :file_path)";

        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':no' => $next_no,
            ':kode' => $kode,
            ':kategori_id' => $kategori_id,
            ':tanggal_surat' => $tanggal_surat,
            ':tanggal_masuk' => $tanggal_masuk,
            ':nomer_surat' => $nomer_surat,
            ':dari' => $dari,
            ':perihal' => $perihal,
            ':instruksi' => $instruksi,
            ':diteruskan' => $diteruskan,
            ':file_path' => $db_path,
        ]);

        $pdo->commit();
        set_flash_message('success', 'Data disposisi masuk berhasil ditambahkan.');
        header("Location: disposisi.php");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = handle_system_error($e);
    }
}

// Fetch categories for guided dropdown selection
$categories = $pdo->query("SELECT id_kategori, kode_kategori, nama_kategori FROM kategori_surat ORDER BY kode_kategori ASC")->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Disposisi</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .file-upload-label {
            display: block;
            padding: 2rem;
            background-color: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 0.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-upload-label:hover {
            background-color: #e9ecef;
            border-color: #adb5bd;
        }

        .file-upload-label i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            color: #6c757d;
        }

        .file-upload input[type="file"] {
            display: none;
        }

        .preview-area {
            display: none;
            margin-top: 1rem;
        }

        .preview-area img {
            max-height: 200px;
            object-fit: contain;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-white border-bottom py-3 px-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="p-2 rounded-3 bg-primary-subtle text-primary">
                                <i class="bi bi-file-earmark-plus fs-4"></i>
                            </div>
                            <div>
                                <h5 class="card-title mb-0 fw-bold">Tambah Data Disposisi Masuk</h5>
                                <small class="text-muted">Masukkan data surat masuk ke dalam buku agenda digital</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <div class="row g-3">
                                <!-- Section 1: Informasi Surat -->
                                <div class="col-12">
                                    <div class="d-flex align-items-center gap-2 text-primary fw-bold small text-uppercase pb-1 border-bottom">
                                        <i class="bi bi-envelope-paper"></i> Informasi Surat
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="kategori_id" class="form-label fw-semibold">Kategori Surat</label>
                                        <select class="form-select" id="kategori_id" name="kategori_id" required>
                                            <option value="" disabled selected>-- Pilih Kategori Surat --</option>
                                            <?php foreach ($categories as $kat): ?>
                                                <option value="<?= $kat['id_kategori'] ?>">
                                                    <?= htmlspecialchars($kat['kode_kategori']) ?> - <?= htmlspecialchars($kat['nama_kategori']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback">
                                            Harap pilih kategori surat
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nomer_surat" class="form-label fw-semibold">Nomor Surat</label>
                                        <input type="text" class="form-control" id="nomer_surat" name="nomer_surat" placeholder="Contoh: 005/KP/2026" required>
                                        <div class="invalid-feedback">
                                            Harap isi nomor surat
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tanggal_surat" class="form-label fw-semibold">Tanggal Surat</label>
                                        <input type="date" class="form-control" id="tanggal_surat" name="tanggal_surat" required>
                                        <div class="invalid-feedback">
                                            Harap pilih tanggal surat
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tanggal_masuk" class="form-label fw-semibold">Tanggal Masuk</label>
                                        <input type="date" class="form-control" id="tanggal_masuk" name="tanggal_masuk" value="<?= date('Y-m-d') ?>" required>
                                        <div class="invalid-feedback">
                                            Harap pilih tanggal masuk
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="dari" class="form-label fw-semibold">Asal Surat (Dari)</label>
                                        <input type="text" class="form-control" id="dari" name="dari" placeholder="Nama instansi / pengirim surat" required>
                                        <div class="invalid-feedback">
                                            Harap isi asal surat
                                        </div>
                                    </div>
                                </div>

                                <!-- Section 2: Disposisi & Instruksi -->
                                <div class="col-12 mt-4">
                                    <div class="d-flex align-items-center gap-2 text-primary fw-bold small text-uppercase pb-1 border-bottom">
                                        <i class="bi bi-card-text"></i> Disposisi & Instruksi
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="perihal" class="form-label fw-semibold">Perihal Surat</label>
                                        <textarea class="form-control" id="perihal" name="perihal" rows="2" placeholder="Uraian ringkas hal / perihal surat..."></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="diteruskan" class="form-label fw-semibold">Diteruskan Kepada</label>
                                        <input type="text" class="form-control" id="diteruskan" name="diteruskan" placeholder="Nama pejabat / divisi tujuan">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="instruksi" class="form-label fw-semibold">Instruksi / Catatan</label>
                                        <input type="text" class="form-control" id="instruksi" name="instruksi" placeholder="Tindakan yang perlu diambil">
                                    </div>
                                </div>

                                <!-- Section 3: Berkas Lampiran -->
                                <div class="col-12 mt-4">
                                    <div class="d-flex align-items-center gap-2 text-primary fw-bold small text-uppercase pb-1 border-bottom">
                                        <i class="bi bi-paperclip"></i> Lampiran Berkas
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <div class="file-upload">
                                            <label class="file-upload-label">
                                                <i class="bi bi-cloud-arrow-up"></i>
                                                <span class="d-block mt-2 fw-medium">Pilih file atau drag & drop disini</span>
                                                <small class="text-muted d-block mt-1">Format didukung: PDF, JPG, JPEG, PNG (Maks. 10MB)</small>
                                                <input type="file" id="file" name="file" accept=".pdf,.jpg,.jpeg,.png">
                                            </label>
                                        </div>
                                        <div id="preview-area" class="preview-area p-3 bg-light rounded border mt-3" style="display: none;">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="d-flex align-items-center gap-2 overflow-hidden">
                                                    <i id="preview-icon" class="bi bi-file-earmark-pdf text-danger fs-3"></i>
                                                    <div>
                                                        <p id="file-name" class="fw-semibold mb-0 text-truncate" style="max-width: 320px;"></p>
                                                        <span id="file-size" class="badge bg-secondary-subtle text-secondary font-monospace"></span>
                                                    </div>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-danger" id="btn-cancel-file" title="Batalkan pilihan file">
                                                    <i class="bi bi-trash me-1"></i>Hapus
                                                </button>
                                            </div>
                                            <div class="text-center mt-2" id="image-preview-container" style="display: none;">
                                                <img id="image-preview" class="img-thumbnail" style="max-height: 140px;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                                <a href="disposisi.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
                                </a>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bi bi-check-lg me-1"></i> Simpan Disposisi
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Form validation
        (function() {
            'use strict'
            const forms = document.querySelectorAll('.needs-validation')
            Array.from(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    } else {
                        const btn = form.querySelector('button[type="submit"]');
                        if (btn) {
                            btn.disabled = true;
                            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Menyimpan...';
                        }
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()

        function formatBytes(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
        }

        const fileInput = document.getElementById('file');
        const previewArea = document.getElementById('preview-area');
        const previewIcon = document.getElementById('preview-icon');
        const imagePreview = document.getElementById('image-preview');
        const imagePreviewContainer = document.getElementById('image-preview-container');
        const fileName = document.getElementById('file-name');
        const fileSize = document.getElementById('file-size');
        const btnCancel = document.getElementById('btn-cancel-file');

        function updateFilePreview(file) {
            if (!file) {
                previewArea.style.display = 'none';
                return;
            }
            fileName.textContent = file.name;
            fileSize.textContent = formatBytes(file.size);
            previewArea.style.display = 'block';

            if (file.type.startsWith('image/')) {
                previewIcon.className = 'bi bi-file-earmark-image text-primary fs-3';
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreviewContainer.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                previewIcon.className = 'bi bi-file-earmark-pdf text-danger fs-3';
                imagePreviewContainer.style.display = 'none';
            }
        }

        fileInput.addEventListener('change', function(e) {
            updateFilePreview(e.target.files[0]);
        });

        if (btnCancel) {
            btnCancel.addEventListener('click', function() {
                fileInput.value = '';
                previewArea.style.display = 'none';
                imagePreviewContainer.style.display = 'none';
            });
        }

        // Drag and drop functionality
        const dropZone = document.querySelector('.file-upload-label');

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            dropZone.classList.add('bg-light');
            dropZone.style.borderColor = '#0d6efd';
        }

        function unhighlight(e) {
            dropZone.classList.remove('bg-light');
            dropZone.style.borderColor = '';
        }

        dropZone.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            fileInput.files = files;
            updateFilePreview(files[0]);
        }
    </script>
</body>

</html>