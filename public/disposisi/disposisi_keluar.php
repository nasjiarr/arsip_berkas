<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
check_login();
$role = $_SESSION['user']['role'];

function getFileUrl($file_path)
{
    // Check if it's a network path
    if (strpos($file_path, 'DISPOSISI SURAT') !== false) {
        // Convert network path to web-accessible URL
        // Create a URL that points to a script that will serve the file
        return 'serve_file.php?path=' . urlencode($file_path);
    }
    // Return original path for local files
    return $file_path;
}

// Initialize pagination variables
$rows_per_page = isset($_GET['rows']) ? (int)$_GET['rows'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $rows_per_page;

// Get filter parameters
$selected_bulan = isset($_GET['bulan']) ? $_GET['bulan'] : '';
$selected_tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
$selected_kategori = isset($_GET['kategori']) ? $_GET['kategori'] : '';
$search_query = isset($_GET['search']) ? $_GET['search'] : '';

try {
    // Build the base query
    $base_query = "FROM disposisi_keluar WHERE 1=1";
    $params = [];

    // Add filter conditions
    if (!empty($selected_bulan)) {
        $base_query .= " AND MONTH(tanggal) = :bulan";
        $params[':bulan'] = $selected_bulan;
    }
    if (!empty($selected_tahun)) {
        $base_query .= " AND YEAR(tanggal) = :tahun";
        $params[':tahun'] = $selected_tahun;
    }

    if (!empty($selected_kategori)) {
        if ($selected_kategori === 'BI') {
            $base_query .= " AND kode = '1'";
        } elseif ($selected_kategori === 'OJK') {
            $base_query .= " AND kode = '2'";
        } elseif ($selected_kategori === 'UMUM') {
            $base_query .= " AND kode NOT IN ('1', '2')";
        }
    }
    // Tambahkan kondisi pencarian
    if (!empty($search_query)) {
        $base_query .= " AND (nomor_surat LIKE :search 
                    OR perihal LIKE :search 
                    OR ke LIKE :search)";
        $params[':search'] = "%$search_query%";
    }

    // Get total rows for pagination
    $count_query = "SELECT COUNT(*) as count " . $base_query;
    $stmt = $pdo->prepare($count_query);
    $stmt->execute($params);
    $total_rows = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    $total_pages = ceil($total_rows / $rows_per_page);

    // Get the filtered data with pagination
    $query = "SELECT * " . $base_query . " ORDER BY tanggal DESC, id DESC LIMIT :offset, :rows";
    $stmt = $pdo->prepare($query);

    // Bind all parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':rows', $rows_per_page, PDO::PARAM_INT);

    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Modify export URL to include current filters
    $export_url = "export_excel_keluar.php";
    $filter_params = [];
    if (!empty($selected_bulan)) $filter_params[] = "bulan=" . $selected_bulan;
    if (!empty($selected_tahun)) $filter_params[] = "tahun=" . $selected_tahun;
    if (!empty($selected_kategori)) $filter_params[] = "kategori=" . urlencode($selected_kategori);
    if (!empty($filter_params)) {
        $export_url .= "?" . implode("&", $filter_params);
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}
// Function to get month name in Indonesian
function getNamaBulan($bulan)
{
    $bulan_list = [
        '01' => 'Januari',
        '02' => 'Februari',
        '03' => 'Maret',
        '04' => 'April',
        '05' => 'Mei',
        '06' => 'Juni',
        '07' => 'Juli',
        '08' => 'Agustus',
        '09' => 'September',
        '10' => 'Oktober',
        '11' => 'November',
        '12' => 'Desember'
    ];
    return isset($bulan_list[$bulan]) ? $bulan_list[$bulan] : '';
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Disposisi Surat Keluar</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>

<body>
    <?php require_once '../../includes/sidebar.php'; ?>

    <div class="container-fluid p-0">
        <div class="row g-0">
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 content">
                <div class="user-welcome">
                    <h1>Selamat Datang, <?= htmlspecialchars($_SESSION['user']['username']); ?></h1>
                </div>
                <div class="container-fluid ">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1 class="h2">Disposisi Surat Keluar</h1>
                        <div class="d-flex gap-2">
                            <?php if ($role === 'sekre'): ?>
                                <a href="export_excel_keluar.php<?= !empty($_GET) ? '?' . http_build_query($_GET) : '' ?>" class="btn btn-success">
                                    <i class="fas fa-file-excel me-1"></i> Export Excel
                                </a>

                                <a href="add_disposisi_keluar.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i> Tambah Data
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>


                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <div class="row mb-3 align-items-end">
                                <div class="col-md-4">
                                    <form method="get" class="d-flex align-items-center">
                                        <label for="rows" class="me-2">Tampilkan:</label>
                                        <select name="rows" id="rows" class="form-select w-auto" onchange="this.form.submit()">
                                            <option value="10" <?= $rows_per_page == 10 ? 'selected' : '' ?>>10</option>
                                            <option value="50" <?= $rows_per_page == 50 ? 'selected' : '' ?>>50</option>
                                            <option value="100" <?= $rows_per_page == 100 ? 'selected' : '' ?>>100</option>
                                            <option value="250" <?= $rows_per_page == 250 ? 'selected' : '' ?>>250</option>
                                            <option value="500" <?= $rows_per_page == 500 ? 'selected' : '' ?>>500</option>
                                            <option value="1000" <?= $rows_per_page == 1000 ? 'selected' : '' ?>>1000</option>
                                        </select>
                                        <span class="ms-2">baris</span>
                                    </form>
                                </div>
                                <div class="col-md-6">
                                    <form method="get" id="filterForm" class="d-flex flex-wrap align-items-end gap-2">
                                        <!-- Hidden input untuk mempertahankan filter lain -->
                                        <input type="hidden" name="rows" value="<?= isset($_GET['rows']) ? $_GET['rows'] : 10 ?>">
                                        <input type="hidden" name="page" value="<?= isset($_GET['page']) ? $_GET['page'] : 1 ?>">

                                        <div class="flex-grow-1">
                                            <label for="bulan" class="form-label">Bulan:</label>
                                            <select name="bulan" id="bulan" class="form-select w-100" onchange="document.getElementById('filterForm').submit();">
                                                <option value="">Semua Bulan</option>
                                                <?php
                                                $bulan_list = [
                                                    '01' => 'Januari',
                                                    '02' => 'Februari',
                                                    '03' => 'Maret',
                                                    '04' => 'April',
                                                    '05' => 'Mei',
                                                    '06' => 'Juni',
                                                    '07' => 'Juli',
                                                    '08' => 'Agustus',
                                                    '09' => 'September',
                                                    '10' => 'Oktober',
                                                    '11' => 'November',
                                                    '12' => 'Desember'
                                                ];
                                                $selected_bulan = isset($_GET['bulan']) ? $_GET['bulan'] : '';

                                                foreach ($bulan_list as $value => $nama) {
                                                    $selected = ($selected_bulan == $value) ? 'selected' : '';
                                                    echo "<option value='{$value}' {$selected}>{$nama}</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <div class="flex-grow-1">
                                            <label for="tahun" class="form-label">Tahun:</label>
                                            <select name="tahun" id="tahun" class="form-select w-100" onchange="document.getElementById('filterForm').submit();">
                                                <?php
                                                $current_year = date('Y');
                                                $year_query = "SELECT DISTINCT YEAR(tanggal) as year FROM disposisi_keluar ORDER BY year DESC";
                                                $years = $pdo->query($year_query)->fetchAll(PDO::FETCH_COLUMN);
                                                if (empty($years)) {
                                                    $years = [$current_year];
                                                }

                                                $selected_tahun = isset($_GET['tahun']) ? $_GET['tahun'] : $current_year;
                                                foreach ($years as $year) {
                                                    echo "<option value='{$year}' " . ($selected_tahun == $year ? 'selected' : '') . ">{$year}</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <div class="flex-grow-1">
                                            <label for="kategori" class="form-label">Kategori Surat:</label>
                                            <select name="kategori" id="kategori" class="form-select w-100" onchange="document.getElementById('filterForm').submit();">
                                                <option value="">Semua Kategori</option>
                                                <option value="BI" <?= $selected_kategori == 'BI' ? 'selected' : '' ?>>Surat Keluar BI</option>
                                                <option value="OJK" <?= $selected_kategori == 'OJK' ? 'selected' : '' ?>>Surat Keluar OJK</option>
                                                <option value="UMUM" <?= $selected_kategori == 'UMUM' ? 'selected' : '' ?>>Surat Keluar Umum</option>
                                            </select>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-end">
                                                <?php if (!empty($selected_bulan) || !empty($selected_kategori) || !empty($search_query)): ?>
                                                    <a href="?tahun=<?= $selected_tahun ?>" class="btn btn-outline-secondary">
                                                        <i class="fas fa-times me-1"></i>
                                                        Reset Filter
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </form>

                                </div>

                            </div>

                            <form method="get" class="d-flex gap-2 mt-3" id="searchForm">
                                <!-- Hidden inputs untuk mempertahankan filter lain -->
                                <input type="hidden" name="rows" value="<?= isset($_GET['rows']) ? $_GET['rows'] : 10 ?>">
                                <input type="hidden" name="page" value="1">
                                <input type="hidden" name="bulan" value="<?= $selected_bulan ?>">
                                <input type="hidden" name="tahun" value="<?= $selected_tahun ?>">
                                <input type="hidden" name="kategori" value="<?= $selected_kategori ?>">

                                <div class="input-group">
                                    <input type="text"
                                        class="form-control"
                                        placeholder="Cari surat..."
                                        name="search"
                                        value="<?= htmlspecialchars($search_query) ?>"
                                        aria-label="Search">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    <?php if (!empty($search_query)): ?>
                                        <a href="<?= '?' . http_build_query(array_diff_key($_GET, ['search' => ''])) ?>"
                                            class="btn btn-outline-secondary">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </form>
                            <!-- Tampilkan info pencarian jika ada -->
                            <?php if (!empty($search_query)): ?>
                                <div class="alert alert-info mt-3">
                                    Menampilkan hasil pencarian untuk: "<?= htmlspecialchars($search_query) ?>"
                                    <?php if ($total_rows > 0): ?>
                                        (<?= $total_rows ?> hasil ditemukan)
                                    <?php else: ?>
                                        (Tidak ada hasil ditemukan)
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <div class="table-responsive shadow-sm rounded border mt-3">
                                <table class="table table-hover table-bordered table-disposisi align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width: 45px;">No</th>
                                            <th class="text-center" style="width: 65px;">Kode</th>
                                            <th class="text-center" style="width: 80px;">Kategori</th>
                                            <th class="text-center" style="width: 100px;">Tanggal</th>
                                            <th class="text-center" style="width: 130px;">Nomor Surat</th>
                                            <th>Perihal</th>
                                            <th style="width: 140px;">Tujuan (Ke)</th>
                                            <th class="text-center" style="width: 75px;">Berkas</th>
                                            <?php if ($role === 'sekre' || $role === 'ti_admin'): ?>
                                                <th class="text-center" style="width: 100px;">Aksi</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $nomor = ($page - 1) * $rows_per_page + 1;
                                        foreach ($result as $row):
                                            $kode = trim($row['kode'] ?? '');
                                            $kategori_label = 'Surat Umum';
                                            $kategori_badge = '<span class="badge badge-umum">Umum</span>';
                                            if ($kode === '1' || $kode === '001') {
                                                $kategori_label = 'Surat BI';
                                                $kategori_badge = '<span class="badge badge-bi"><i class="bi bi-bank me-1"></i>BI</span>';
                                            } elseif ($kode === '2' || $kode === '002') {
                                                $kategori_label = 'Surat OJK';
                                                $kategori_badge = '<span class="badge badge-ojk"><i class="bi bi-shield-check me-1"></i>OJK</span>';
                                            }

                                            $tgl_fmt = !empty($row['tanggal']) ? date('d/m/Y', strtotime($row['tanggal'])) : '-';
                                            $file_url = !empty($row['file_path']) ? getFileUrl($row['file_path']) : '';
                                        ?>
                                            <tr>
                                                <td class="text-center text-muted fw-semibold"><?= $nomor++ ?></td>
                                                <td class="text-center font-monospace"><span class="badge bg-light text-dark border"><?= htmlspecialchars($row['kode'] ?? '-') ?></span></td>
                                                <td class="text-center"><?= $kategori_badge ?></td>
                                                <td class="text-center cell-date"><?= $tgl_fmt ?></td>
                                                <td class="text-center font-monospace fw-medium"><?= htmlspecialchars($row['nomor_surat'] ?? '-') ?></td>
                                                <td><span class="cell-truncate" data-bs-toggle="tooltip" title="<?= htmlspecialchars($row['perihal'] ?? '') ?>"><?= htmlspecialchars($row['perihal'] ?? '-') ?></span></td>
                                                <td><span class="cell-truncate-sm" data-bs-toggle="tooltip" title="<?= htmlspecialchars($row['ke'] ?? '') ?>"><?= htmlspecialchars($row['ke'] ?? '-') ?></span></td>
                                                <td class="text-center">
                                                    <?php
                                                    if (!empty($file_url)) {
                                                        $ext = strtolower(pathinfo($row['file_path'], PATHINFO_EXTENSION));
                                                        if ($ext === 'pdf') {
                                                            echo '<button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="previewPDF(\'' . htmlspecialchars($file_url, ENT_QUOTES) . '\')" title="Lihat PDF">
                                                                    <i class="bi bi-file-earmark-pdf"></i> PDF
                                                                  </button>';
                                                        } elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                                                            echo '<img src="' . htmlspecialchars($file_url) . '" alt="Gambar" class="img-thumbnail rounded p-0" 
                                                                  onclick="showModal(this)" style="cursor: zoom-in; height: 30px; width: 30px; object-fit: cover;" 
                                                                  data-bs-toggle="tooltip" title="Klik perbesar">';
                                                        }
                                                    } else {
                                                        echo '<span class="text-muted small">-</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <?php if ($role === 'sekre' || $role === 'ti_admin'): ?>
                                                    <td class="text-center">
                                                        <div class="btn-group btn-group-sm" role="group">
                                                            <button type="button" class="btn btn-outline-info" title="Detail Lengkap" onclick='showRowDetail(<?= htmlspecialchars(json_encode([
                                                                "nomor_surat" => $row["nomor_surat"] ?? "-",
                                                                "kategori" => $kategori_label,
                                                                "tanggal" => $tgl_fmt,
                                                                "ke" => $row["ke"] ?? "-",
                                                                "perihal" => $row["perihal"] ?? "-",
                                                                "file_url" => $file_url
                                                            ]), ENT_QUOTES) ?>)'>
                                                                <i class="bi bi-eye"></i>
                                                            </button>
                                                            <a href="edit_disposisi_keluar.php?id=<?= htmlspecialchars($row['id'] ?? '') ?>" class="btn btn-outline-warning" title="Edit">
                                                                <i class="bi bi-pencil"></i>
                                                            </a>
                                                            <form method="POST" action="delete_disposisi_keluar.php" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                                                <input type="hidden" name="id" value="<?= htmlspecialchars($row['id'] ?? '') ?>">
                                                                <button type="submit" class="btn btn-outline-danger" title="Hapus" style="border-top-left-radius:0;border-bottom-left-radius:0;">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                <?php endif; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Modal Preview PDF (Placed outside table to prevent DOM duplication) -->
                            <div class="modal fade" id="pdfPreviewModal" tabindex="-1">
                                <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title fs-6"><i class="bi bi-file-earmark-pdf text-danger me-2"></i>Preview PDF</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-0">
                                            <div class="ratio ratio-16x9">
                                                <iframe id="pdfViewer" class="embed-responsive-item" style="border: none;"></iframe>
                                            </div>
                                        </div>
                                        <div class="modal-footer py-2">
                                            <a id="downloadPdf" href="#" class="btn btn-sm btn-primary" download>
                                                <i class="bi bi-download me-1"></i> Download PDF
                                            </a>
                                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                                                Tutup
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Detail Lengkap Disposisi Keluar -->
                            <div class="modal fade" id="rowDetailModal" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content shadow">
                                        <div class="modal-header bg-light py-2">
                                            <h5 class="modal-title fs-6 fw-bold"><i class="bi bi-card-text me-2 text-primary"></i>Detail Disposisi Surat Keluar</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body p-3">
                                            <dl class="row mb-0 small">
                                                <dt class="col-sm-4 text-muted">Nomor Surat</dt>
                                                <dd class="col-sm-8 fw-semibold" id="dtNomorSurat">-</dd>
                                                <dt class="col-sm-4 text-muted">Kategori</dt>
                                                <dd class="col-sm-8" id="dtKategori">-</dd>
                                                <dt class="col-sm-4 text-muted">Tanggal</dt>
                                                <dd class="col-sm-8" id="dtTanggal">-</dd>
                                                <dt class="col-sm-4 text-muted">Tujuan (Ke)</dt>
                                                <dd class="col-sm-8" id="dtAsalTujuan">-</dd>
                                                <dt class="col-sm-4 text-muted">Perihal</dt>
                                                <dd class="col-sm-8 text-break" id="dtPerihal">-</dd>
                                                <dt class="col-sm-4 text-muted">Berkas</dt>
                                                <dd class="col-sm-8" id="dtBerkas">-</dd>
                                            </dl>
                                        </div>
                                        <div class="modal-footer py-1">
                                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <nav aria-label="Page navigation">
                                <!-- Pagination untuk Desktop -->
                                <ul class="pagination justify-content-center d-none d-md-flex mb-0" id="pagination">
                                    <?php
                                    // Ambil parameter filter yang ada
                                    $query_params = $_GET;
                                    unset($query_params['page']); // Hapus parameter page agar bisa diperbarui

                                    // Tombol Previous
                                    if ($page > 1):
                                        $query_params['page'] = $page - 1;
                                        $prev_url = '?' . http_build_query($query_params);
                                    ?>
                                        <li class="page-item">
                                            <a class="page-link" href="<?= $prev_url ?>" aria-label="Previous">
                                                <span aria-hidden="true">&laquo;</span>
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <?php
                                    // Tentukan range halaman yang akan ditampilkan
                                    $start = max(1, $page - 2); // Mulai dari halaman saat ini - 2
                                    $end = min($total_pages, $page + 2); // Sampai halaman saat ini + 2

                                    // Jika halaman saat ini dekat dengan awal, tampilkan 5 halaman pertama
                                    if ($page <= 3) {
                                        $start = 1;
                                        $end = min(5, $total_pages);
                                    }

                                    // Jika halaman saat ini dekat dengan akhir, tampilkan 5 halaman terakhir
                                    if ($page >= $total_pages - 2) {
                                        $start = max(1, $total_pages - 4);
                                        $end = $total_pages;
                                    }

                                    // Tampilkan tombol pagination dalam range yang ditentukan
                                    for ($i = $start; $i <= $end; $i++):
                                        $query_params['page'] = $i;
                                        $page_url = '?' . http_build_query($query_params);
                                    ?>
                                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                            <a class="page-link" href="<?= $page_url ?>" data-page="<?= $i ?>">
                                                <?= $i ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <!-- Tombol Next -->
                                    <?php if ($page < $total_pages):
                                        $query_params['page'] = $page + 1;
                                        $next_url = '?' . http_build_query($query_params);
                                    ?>
                                        <li class="page-item">
                                            <a class="page-link" href="<?= $next_url ?>" aria-label="Next">
                                                <span aria-hidden="true">&raquo;</span>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>

                                <!-- Dropdown Pagination untuk Mobile -->
                                <div class="d-md-none text-center">
                                    <select class="form-select w-50 mx-auto" onchange="location = this.value;">
                                        <?php
                                        for ($i = 1; $i <= $total_pages; $i++):
                                            $query_params['page'] = $i;
                                            $page_url = '?' . http_build_query($query_params);
                                        ?>
                                            <option value="<?= $page_url ?>" <?= $i == $page ? 'selected' : '' ?>>
                                                Halaman <?= $i ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </nav>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <!-- Image Preview Modal -->
    <div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-labelledby="imagePreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imagePreviewModalLabel">Preview Gambar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center position-relative p-0">
                    <div class="d-flex justify-content-center align-items-center" style="min-height: 70vh; background-color: #f8f9fa;">
                        <div class="loading-spinner position-absolute" style="display: none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        <img id="previewImage" class="img-fluid" style="max-height: 70vh; max-width: 100%; transition: transform 0.3s; object-fit: contain;">
                    </div>
                    <div class="zoom-controls py-3 bg-white border-top">
                        <div class="btn-group">
                            <button class="btn btn-outline-primary" onclick="zoomOut()">
                                <i class="fas fa-minus"></i>
                            </button>
                            <button class="btn btn-outline-primary" disabled id="zoomLevel">100%</button>
                            <button class="btn btn-outline-primary" onclick="zoomIn()">
                                <i class="fas fa-plus"></i>
                            </button>
                            <button class="btn btn-outline-secondary" onclick="resetZoom()">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Initialize variables
        let currentScale = 1;
        const imagePreviewModal = document.getElementById('imagePreviewModal');
        const previewImage = document.getElementById('previewImage');
        const zoomLevelBtn = document.getElementById('zoomLevel');
        const loadingSpinner = document.querySelector('.loading-spinner');
        const modal = new bootstrap.Modal(imagePreviewModal);

        // Show modal with image
        function showModal(imgElement) {
            loadingSpinner.style.display = 'block';
            previewImage.style.display = 'none';
            previewImage.src = imgElement.src;
            modal.show();

            previewImage.onload = function() {
                loadingSpinner.style.display = 'none';
                previewImage.style.display = 'block';
                resetZoom();
            };
        }

        // Zoom functions
        function updateZoom() {
            previewImage.style.transform = `scale(${currentScale})`;
            zoomLevelBtn.textContent = `${Math.round(currentScale * 100)}%`;
        }

        function zoomIn() {
            currentScale = Math.min(4, currentScale * 1.2); // Max zoom: 400%
            updateZoom();
        }

        function zoomOut() {
            currentScale = Math.max(0.5, currentScale / 1.2); // Min zoom: 50%
            updateZoom();
        }

        function resetZoom() {
            currentScale = 1;
            previewImage.style.transform = '';
            updateZoom();
        }

        // Keyboard controls
        document.addEventListener('keydown', function(e) {
            if (imagePreviewModal.classList.contains('show')) {
                switch (e.key) {
                    case 'Escape':
                        modal.hide();
                        break;
                    case '+':
                    case '=':
                        zoomIn();
                        break;
                    case '-':
                        zoomOut();
                        break;
                    case '0':
                        resetZoom();
                        break;
                }
            }
        });

        // Mouse wheel zoom
        imagePreviewModal.addEventListener('wheel', function(e) {
            if (imagePreviewModal.classList.contains('show')) {
                e.preventDefault();
                if (e.deltaY < 0) {
                    zoomIn();
                } else {
                    zoomOut();
                }
            }
        });

        // Fungsi untuk memuat konten melalui AJAX
        function loadPage(url) {
            fetch(url)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

                    const newTable = doc.querySelector('.table-responsive');
                    const curTable = document.querySelector('.table-responsive');
                    if (newTable && curTable) {
                        curTable.innerHTML = newTable.innerHTML;
                    }

                    const newNav = doc.querySelector('nav[aria-label="Page navigation"]');
                    const curNav = document.querySelector('nav[aria-label="Page navigation"]');
                    if (newNav && curNav) {
                        curNav.innerHTML = newNav.innerHTML;
                    }

                    window.history.pushState({}, '', url);
                    attachPaginationListener();
                })
                .catch(error => {
                    console.error('Error loading page:', error);
                    window.location.href = url;
                });
        }

        // Fungsi untuk memasang event listener pada pagination
        function attachPaginationListener() {
            const pagination = document.getElementById('pagination');
            if (pagination) {
                pagination.addEventListener('click', function(event) {
                    if (event.target.tagName === 'A') {
                        event.preventDefault(); // Mencegah perilaku default
                        const url = event.target.getAttribute('href'); // Ambil URL
                        loadPage(url); // Muat halaman baru
                    }
                });
            }

            // Re-init bootstrap tooltips
            const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            tooltips.forEach(el => new bootstrap.Tooltip(el));
        }

        // Tampilkan modal detail lengkap baris
        function showRowDetail(data) {
            document.getElementById('dtNomorSurat').textContent = data.nomor_surat || '-';
            document.getElementById('dtKategori').textContent = data.kategori || '-';
            document.getElementById('dtTanggal').textContent = data.tanggal || '-';
            if (document.getElementById('dtLabelAsal')) {
                document.getElementById('dtLabelAsal').textContent = 'Tujuan (Ke)';
                document.getElementById('dtAsalTujuan').textContent = data.ke || '-';
            }
            document.getElementById('dtPerihal').textContent = data.perihal || '-';
            const berkasEl = document.getElementById('dtBerkas');
            if (data.file_url) {
                berkasEl.innerHTML = '<a href="' + data.file_url + '" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-box-arrow-up-right me-1"></i>Buka Berkas</a>';
            } else {
                berkasEl.textContent = 'Tidak ada file lampiran';
            }
            const modal = new bootstrap.Modal(document.getElementById('rowDetailModal'));
            modal.show();
        }

        // Pasang event listener saat halaman pertama kali dimuat
        document.addEventListener('DOMContentLoaded', function() {
            attachPaginationListener();
        });



        // Reset zoom when modal is closed
        imagePreviewModal.addEventListener('hidden.bs.modal', function() {
            resetZoom();
        });

        // Handle search form submission
        const searchForm = document.getElementById('searchForm');
            const searchInput = searchForm.querySelector('input[name="search"]');

            searchForm.addEventListener('submit', function(e) {
                if (searchInput.value.trim() === '') {
                    e.preventDefault();
                    searchInput.focus();
                }
            });

            // Auto-submit form when filter changes
            document.querySelectorAll('select[name="bulan"], select[name="tahun"], select[name="kategori"]').forEach(select => {
                select.addEventListener('change', function() {
                    document.getElementById('filterForm').submit();
                });
            });
        });

        function previewPDF(url) {
            const modal = new bootstrap.Modal(document.getElementById('pdfPreviewModal'));
            const viewer = document.getElementById('pdfViewer');
            const downloadBtn = document.getElementById('downloadPdf');

            viewer.src = url;
            downloadBtn.href = url;
            modal.show();
        }
    </script>
</body>

</html>