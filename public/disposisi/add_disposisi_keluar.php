<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
check_login('sekre');

$current_user_role = $_SESSION['user']['role']; // Pastikan session sudah diset saat login

// Define network path for PDF uploads
define('NETWORK_PDF_PATH', PATH_DISPOSISI);
define('LOCAL_IMAGE_PATH', PATH_DISPOSISI);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('Token keamanan tidak valid.');
    }
    try {
        // Mulai transaction
        $pdo->beginTransaction();

        // Dapatkan nomor urut terakhir
        $stmt = $pdo->query("SELECT MAX(no) as max_no FROM disposisi_keluar");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $next_no = ($row['max_no'] ?? 0) + 1;

        // Validate input
        $kode = $_POST['kode'];
        $tanggal = $_POST['tanggal'];
        $nomor_surat = $_POST['nomor_surat'];
        $perihal = $_POST['perihal'];
        $ke = $_POST['ke'];
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

            $ext = $allowed_mime_to_ext[$mime_type];
            $filename = uniqid('disp_', true) . '.' . $ext;

            // Ensure upload directory exists
            if (!is_dir(PATH_DISPOSISI)) {
                if (!@mkdir(PATH_DISPOSISI, 0755, true)) {
                    throw new Exception('Gagal membuat direktori upload');
                }
            }

            $upload_path = PATH_DISPOSISI . $filename;
            $db_path = PATH_DISPOSISI . $filename;

            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
                throw new Exception('Gagal mengupload file');
            }
        }

        // Lookup kategori_id
        $stmt_kat = $pdo->prepare("SELECT id_kategori FROM kategori_surat WHERE kode_kategori = :kode OR id_kategori = :kode_int LIMIT 1");
        $stmt_kat->execute([':kode' => $kode, ':kode_int' => (int)$kode]);
        $kategori_row = $stmt_kat->fetch(PDO::FETCH_ASSOC);
        $kategori_id = $kategori_row ? (int)$kategori_row['id_kategori'] : 23;

        // Insert into database using PDO
        $query = "INSERT INTO disposisi_keluar (no, kode, kategori_id, tanggal, nomor_surat, perihal, ke, file_path) 
          VALUES (:no, :kode, :kategori_id, :tanggal, :nomor_surat, :perihal, :ke, :file_path)";

        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':no' => $next_no,
            ':kode' => $kode,
            ':kategori_id' => $kategori_id,
            ':tanggal' => $tanggal,
            ':nomor_surat' => $nomor_surat,
            ':perihal' => $perihal,
            ':ke' => $ke,
            ':file_path' => $db_path,
        ]);

        // Commit transaction
        $pdo->commit();
        set_flash_message('success', 'Data disposisi keluar berhasil ditambahkan.');
        header("Location: disposisi_keluar.php");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = handle_system_error($e);
    }
}
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
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-plus-lg me-2"></i>
                                Tambah Data Disposisi Keluar
                            </h5>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <div class="row g-4">
                                <!-- Kode & Nomor Surat -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="kode" class="form-label">Kode</label>
                                        <input type="text" class="form-control" id="kode" name="kode" required>
                                        <div class="invalid-feedback">
                                            Harap isi kode surat
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nomor_surat" class="form-label">Nomor Surat</label>
                                        <input type="text" class="form-control" id="nomor_surat" name="nomor_surat" required>
                                        <div class="invalid-feedback">
                                            Harap isi nomor surat
                                        </div>
                                    </div>
                                </div>

                                <!-- Tanggal -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tanggal" class="form-label">Tanggal</label>
                                        <input type="date" class="form-control" id="tanggal" name="tanggal" required>
                                        <div class="invalid-feedback">
                                            Harap pilih tanggal surat
                                        </div>
                                    </div>
                                </div>

                                <!-- Perihal -->
                                <div class="col-6">
                                    <div class="form-group">
                                        <label for="perihal" class="form-label">Perihal</label>
                                        <input type="text" class="form-control" id="perihal" name="perihal">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="ke" class="form-label">Ke</label>
                                        <input type="text" class="form-control" id="ke" name="ke">
                                    </div>
                                </div>

                                <!-- File Upload -->
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="form-label">Upload File (PDF/Gambar)</label>
                                        <div class="file-upload">
                                            <label class="file-upload-label">
                                                <i class="bi bi-cloud-arrow-up"></i>
                                                <span class="d-block mt-2">Pilih file atau drag & drop disini</span>
                                                <small class="text-muted d-block mt-1">Format yang didukung: PDF, JPG, JPEG, PNG (Maks. 10MB)</small>
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

                            <!-- Buttons -->
                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <a href="disposisi_keluar.php" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left me-1"></i>
                                    Kembali
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-floppy me-1"></i>
                                    Simpan Data
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