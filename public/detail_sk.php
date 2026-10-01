<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
check_login();

$sk_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$sk_id) {
    header('Location: cek_sk.php');
    exit;
}

$query = "SELECT * FROM sk_table WHERE id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$sk_id]);
$sk = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$sk) {
    header('Location: cek_sk.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($sk['judul_sk']) ?> - Detail SK</title>
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
                <!-- Navigation & Action Buttons -->
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
                    <a href="cek_sk.php" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
                        <i class="bi bi-arrow-left"></i> Kembali ke Pangkalan Data SK
                    </a>
                    <div class="d-flex align-items-center gap-2">
                        <a href="view_sk.php?id=<?= htmlspecialchars($sk['id']) ?>" target="_blank" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
                            <i class="bi bi-box-arrow-up-right"></i> Buka di Tab Baru
                        </a>
                        <a href="view_sk.php?id=<?= htmlspecialchars($sk['id']) ?>&download=1" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
                            <i class="bi bi-download"></i> Unduh PDF
                        </a>
                    </div>
                </div>

                <!-- Informasi SK Card -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0 d-flex align-items-center">
                            <i class="bi bi-file-earmark-text me-2"></i> Detail Surat Keputusan
                        </h5>
                        <span class="badge bg-white text-primary font-monospace"><?= htmlspecialchars($sk['nomor_sk']) ?></span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-3 text-muted fw-semibold">Nomor SK</div>
                            <div class="col-md-9 font-monospace fw-bold text-primary"><?= htmlspecialchars($sk['nomor_sk']) ?></div>

                            <div class="col-md-3 text-muted fw-semibold">Judul Keputusan</div>
                            <div class="col-md-9 fw-semibold"><?= htmlspecialchars($sk['judul_sk']) ?></div>

                            <div class="col-md-3 text-muted fw-semibold">Tahun Disahkan</div>
                            <div class="col-md-9">
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">
                                    <?= htmlspecialchars($sk['tahun_disahkan']) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Preview PDF Card -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <span class="fw-semibold text-muted small"><i class="bi bi-eye me-1"></i> Penampil Dokumen PDF</span>
                    </div>
                    <div class="card-body p-0">
                        <object
                            data="view_sk.php?id=<?= htmlspecialchars($sk['id']) ?>#toolbar=1"
                            type="application/pdf"
                            width="100%"
                            height="750px">
                            <div class="p-5 text-center text-muted">
                                <i class="bi bi-file-earmark-pdf fs-1 d-block mb-3 text-primary"></i>
                                <h6>Browser Anda tidak mendukung preview PDF langsung.</h6>
                                <p class="small text-muted mb-3">Silahkan gunakan tombol di bawah untuk mengunduh atau membuka di tab baru.</p>
                                <a href="view_sk.php?id=<?= htmlspecialchars($sk['id']) ?>" target="_blank" class="btn btn-primary btn-sm me-2">
                                    <i class="bi bi-box-arrow-up-right me-1"></i> Buka di Tab Baru
                                </a>
                                <a href="view_sk.php?id=<?= htmlspecialchars($sk['id']) ?>&download=1" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-download me-1"></i> Unduh PDF
                                </a>
                            </div>
                        </object>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
