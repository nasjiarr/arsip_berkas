<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
check_login('sekre');

$current_user_role = $_SESSION['user']['role']; // Pastikan session sudah diset saat login

// Define network path for PDF uploads
define('NETWORK_PDF_PATH', '\\\\172.16.34.5\\ftp\\DISPOSISI SURAT\\');
define('LOCAL_IMAGE_PATH', '\\\\172.16.34.5\\ftp\\DISPOSISI SURAT\\');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
        if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
            $file = $_FILES['file'];
            $file_type = $file['type'];

            // Validate file type
            if (!in_array($file_type, ALLOWED_TYPES)) {
                throw new Exception('Tipe file tidak diizinkan');
            }

            // Generate unique filename
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $ext;

            // Determine upload path based on file type
            if ($file_type == 'application/pdf') {
                // Create network directory if it doesn't exist
                if (!is_dir(NETWORK_PDF_PATH)) {
                    if (!mkdir(NETWORK_PDF_PATH, 0755, true)) {
                        throw new Exception('Gagal membuat direktori network');
                    }
                }

                $upload_path = NETWORK_PDF_PATH . $filename;
                $db_path = '\\\\172.16.34.5\\ftp\\DISPOSISI SURAT\\' . $filename;
            } else {
                // For images, keep using local storage
                if (!is_dir(LOCAL_IMAGE_PATH)) {
                    if (!mkdir(LOCAL_IMAGE_PATH, 0755, true)) {
                        throw new Exception('Gagal membuat direktori lokal');
                    }
                }

                $upload_path = LOCAL_IMAGE_PATH . $filename;
                $db_path = '\\\\172.16.34.5\\ftp\\DISPOSISI SURAT\\' . $filename;
            }

            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
                throw new Exception('Gagal mengupload file');
            }
        }

        // Insert into database using PDO
        $query = "INSERT INTO disposisi_keluar (no, kode, tanggal, nomor_surat, perihal, ke, file_path) 
          VALUES (:no, :kode, :tanggal, :nomor_surat, :perihal, :ke, :file_path)";

        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':no' => $next_no,
            ':kode' => $kode,
            ':tanggal' => $tanggal,
            ':nomor_surat' => $nomor_surat,
            ':perihal' => $perihal,
            ':ke' => $ke,
            ':file_path' => $db_path,
        ]);

        // Commit transaction
        $pdo->commit();
        header("Location: disposisi_keluar.php");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Error: " . $e->getMessage();
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
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
                                <i class="fas fa-plus me-2"></i>
                                Tambah Data Disposisi Keluar
                            </h5>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
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
                                                <i class="fas fa-cloud-upload-alt"></i>
                                                <span class="d-block mt-2">Pilih file atau drag & drop disini</span>
                                                <small class="text-muted d-block mt-1">Format yang didukung: PDF, JPG, JPEG, PNG</small>
                                                <input type="file" id="file" name="file" accept=".pdf,.jpg,.jpeg,.png">
                                            </label>
                                        </div>
                                        <div id="preview-area" class="preview-area">
                                            <img id="image-preview" class="img-fluid rounded">
                                            <p id="file-name" class="mt-2 mb-0"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Buttons -->
                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <a href="disposisi_keluar.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-1"></i>
                                    Kembali
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>
                                    Simpan Data
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Form validation
        (function() {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()

        // File upload preview
        document.getElementById('file').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const previewArea = document.getElementById('preview-area');
            const imagePreview = document.getElementById('image-preview');
            const fileName = document.getElementById('file-name');

            if (file) {
                fileName.textContent = file.name;
                previewArea.style.display = 'block';

                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        imagePreview.src = e.target.result;
                        imagePreview.style.display = 'block';
                    }
                    reader.readAsDataURL(file);
                } else {
                    imagePreview.style.display = 'none';
                }
            } else {
                previewArea.style.display = 'none';
            }
        });

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
        }

        function unhighlight(e) {
            dropZone.classList.remove('bg-light');
        }

        dropZone.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            const fileInput = document.getElementById('file');

            fileInput.files = files;
            fileInput.dispatchEvent(new Event('change'));
        }
    </script>
</body>

</html>