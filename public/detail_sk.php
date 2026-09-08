<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
check_login();

// Ambil ID SK dari parameter URL
$sk_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$sk_id) {
    header('Location: cek_sk.php');
    exit;
}

// Query untuk mengambil detail SK
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
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($sk['judul_sk']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="assets/style.css">
</head>

<body class="bg-light">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Header dengan judul -->
                <div class="text-center mb-4">
                    <h2 class="mb-4"><?= htmlspecialchars($sk['judul_sk']) ?></h2>
                </div>

                <!-- Tombol Kembali dan Unduh -->
                <div class="mb-4">
                    <a href="cek_sk.php" class="btn btn-primary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>

                    <a href="download_sk.php?id=<?= htmlspecialchars($sk['id']) ?>" class="btn btn-outline-primary">
                        <i class="bi bi-download"></i> Unduh
                    </a>

                </div>

                <!-- Informasi SK -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-3 text-muted">Judul Peraturan</div>
                            <div class="col-md-9"><?= htmlspecialchars($sk['judul_sk']) ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3 text-muted">Tahun Terbit</div>
                            <div class="col-md-9"><?= htmlspecialchars($sk['tahun_disahkan']) ?></div>
                        </div>
                        <div class="row">
                            <div class="col-md-3 text-muted">Nomor</div>
                            <div class="col-md-9"><?= htmlspecialchars($sk['nomor_sk']) ?></div>
                        </div>
                    </div>
                </div>

                <!-- Preview PDF -->
                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <object
                            data="view_sk.php?id=<?= htmlspecialchars($sk['id']) ?>"
                            type="application/pdf"
                            width="100%"
                            height="800px">
                            <p class="text-center p-4">
                                Browser Anda tidak mendukung preview PDF.
                                <br>
                                <a href="view_sk.php?id=<?= htmlspecialchars($sk['id']) ?>" target="_blank" class="btn btn-primary btn-sm mt-2">
                                    <i class="bi bi-box-arrow-up-right"></i> Buka di Tab Baru
                                </a>
                            </p>
                        </object>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>