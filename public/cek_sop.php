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
    <title>Cek SOP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
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
                        <?php if ($role === 'sekre'): ?>
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'disposisi/disposisi.php' ? 'active text-white bg-primary' : 'text-dark'; ?>" href="disposisi/disposisi.php">
                                <i class="bi bi-envelope-arrow-down"></i> Disposisi Surat Masuk
                            </a>
                        <?php else: ?>
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active text-white bg-primary' : 'text-dark'; ?>" href="dashboard.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        <?php endif; ?>
                    </li>
                    <li class="nav-item">
                        <?php if ($role === 'sekre'): ?>
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'disposisi/disposisi_keluar.php' ? 'active text-white bg-primary' : 'text-dark'; ?>" href="disposisi/disposisi_keluar.php">
                                <i class="bi bi-envelope-arrow-up"></i> Disposisi Surat Keluar
                            </a>
                        <?php endif; ?>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-dark <?php echo basename($_SERVER['PHP_SELF']) == 'cek_sk.php' || 'cek_sop.php' ? 'active text-white bg-primary' : 'text-dark'; ?>" href="#" id="dropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-list-check"></i>
                            Cek Berkas
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                            <li><a class="dropdown-item <?php echo basename($_SERVER['PHP_SELF']) == 'cek_sk.php' ? 'active text-white bg-primary' : 'text-dark'; ?>" href="cek_sk.php">Cek SK</a></li>
                            <li><a class="dropdown-item <?php echo basename($_SERVER['PHP_SELF']) == 'cek_sop.php' ? 'active text-white bg-primary' : 'text-dark'; ?>" href="cek_sop.php">Cek SOP</a></li>
                            <?php if ($role !== 'sekre'): ?>
                                <li><a class="dropdown-item" href="disposisi/disposisi.php">Disposisi Surat Masuk</a></li>
                                <li><a class="dropdown-item" href="disposisi/disposisi_keluar.php">Disposisi Surat Keluar</a></li>
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

                <!-- Alert Container -->
                <div id="alertContainer"></div>

                <!-- Cek SOP -->
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-primary text-white d-flex align-items-center">
                                <i class="bi bi-search me-2"></i>
                                <h3 class="card-title mb-0">Cek SOP</h3>
                            </div>
                            <div class="card-body p-4">
                                <div class="search-container">
                                    <input
                                        type="text"
                                        id="searchInputSOP"
                                        class="form-control form-control-lg"
                                        placeholder="Ketik kata kunci..."
                                        style="font-size: 14px;"
                                        autocomplete="off">
                                    <div id="searchSuggestionsSOP" class="search-suggestions"></div>
                                </div>
                                <div id="searchResultsSOP" class="mt-4"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const searchInputSOP = document.getElementById('searchInputSOP');
                    const searchSuggestionsSOP = document.getElementById('searchSuggestionsSOP');
                    const searchResultsSOP = document.getElementById('searchResultsSOP');
                    const alertContainer = document.getElementById('alertContainer');
                    const paginationContainer = document.createElement('div');
                    paginationContainer.id = 'paginationContainer';
                    searchResultsSOP.parentNode.insertBefore(paginationContainer, searchResultsSOP.nextSibling);
                    let typingTimerSOP;
                    let currentPage = 1;
                    let totalPages = 1;

                    // Function to show alert
                    function showAlert(message, type = 'success') {
                        const alert = document.createElement('div');
                        alert.className = `alert alert-${type} alert-dismissible fade show`;
                        alert.innerHTML = `
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                        alertContainer.appendChild(alert);

                        // Auto dismiss after 5 seconds
                        setTimeout(() => {
                            alert.remove();
                        }, 5000);
                    }

                    // Fungsi untuk membuat card SOP
                    function createSopCard(sop) {
                        let deleteButton = '';
                        <?php if ($role === 'admin_dok' || $role === 'ti_admin'): ?>
                            deleteButton = `
                        <button class="btn btn-danger ms-2 delete-sop" data-sop-id="${sop.id}" data-sop-nomor="${sop.nomor_sop}">
                            <i class="bi bi-trash me-1"></i> Hapus
                        </button>
                    `;
                        <?php endif; ?>

                        return `
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-start">
                                <div class="me-3">
                                    <i class="bi bi-file-text text-primary" style="font-size: 2rem;"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <span class="text-primary">${sop.nomor_sop}</span> | 
                                            <span>${sop.tahun_disahkan}</span>
                                        </div>
                                    </div>
                                    <h5 class="mb-3">${sop.judul_sop}</h5>
                                    <div>
                                        <a href="detail_sop.php?id=${sop.id}" class="btn btn-primary">
                                            <i class="bi bi-search me-1"></i> Selengkapnya
                                        </a>
                                        ${deleteButton}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                    }

                    // Function to handle SOP deletion
                    function deleteSOP(sopId) {
                        fetch('delete_sop.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    id: sopId
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    showAlert('SOP berhasil dihapus');
                                    loadSOP(currentPage, searchInputSOP.value.trim());
                                } else {
                                    throw new Error(data.message || 'Gagal menghapus SOP');
                                }
                            })
                            .catch(error => {
                                showAlert(error.message, 'danger');
                            });
                    }

                    // Handle delete button click
                    document.addEventListener('click', function(e) {
                        if (e.target.closest('.delete-sop')) {
                            const button = e.target.closest('.delete-sop');
                            const sopId = button.dataset.sopId;
                            const sopNomor = button.dataset.sopNomor;

                            if (confirm(`Apakah Anda yakin ingin menghapus SOP dengan nomor ${sopNomor}?`)) {
                                deleteSOP(sopId);
                            }
                        }
                    });

                    // Fungsi untuk membuat pagination
                    function createPagination(currentPage, totalPages) {
                        let paginationHTML = '<nav><ul class="pagination justify-content-center">';
                        if (currentPage > 1) {
                            paginationHTML += `<li class="page-item"><a class="page-link" href="#" data-page="${currentPage - 1}"> << </a></li>`;
                        }
                        for (let i = 1; i <= totalPages; i++) {
                            paginationHTML += `<li class="page-item ${i === currentPage ? 'active' : ''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
                        }
                        if (currentPage < totalPages) {
                            paginationHTML += `<li class="page-item"><a class="page-link" href="#" data-page="${currentPage + 1}"> >> </a></li>`;
                        }
                        paginationHTML += '</ul></nav>';
                        return paginationHTML;
                    }

                    // Fungsi untuk memuat data SOP
                    function loadSOP(page, searchTerm = '') {
                        const url = searchTerm ? `search_sop.php?term=${encodeURIComponent(searchTerm)}&page=${page}` : `get_all_sop.php?page=${page}`;
                        fetch(url)
                            .then(response => response.json())
                            .then(data => {
                                if (data.results.length > 0) {
                                    searchResultsSOP.innerHTML = data.results.map(sop => createSopCard(sop)).join('');
                                    paginationContainer.innerHTML = createPagination(data.currentPage, data.totalPages);
                                } else {
                                    searchResultsSOP.innerHTML = '<div class="alert alert-info">Tidak ditemukan SOP yang sesuai dengan kata kunci.</div>';
                                    paginationContainer.innerHTML = '';
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                searchResultsSOP.innerHTML = '<div class="alert alert-danger">Terjadi kesalahan saat memuat data.</div>';
                                paginationContainer.innerHTML = '';
                            });
                    }

                    // Handle input pencarian
                    searchInputSOP.addEventListener('input', function() {
                        clearTimeout(typingTimerSOP);
                        typingTimerSOP = setTimeout(() => {
                            const searchTerm = this.value.trim();
                            if (searchTerm.length > 2) {
                                loadSOP(1, searchTerm);
                            } else {
                                loadSOP(1);
                            }
                        }, 500);
                    });

                    // Handle klik pagination
                    paginationContainer.addEventListener('click', function(e) {
                        if (e.target.tagName === 'A') {
                            e.preventDefault();
                            const page = e.target.getAttribute('data-page');
                            loadSOP(page, searchInputSOP.value.trim());
                        }
                    });

                    // Load semua SOP saat pertama kali
                    loadSOP(1);

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
            </script>
</body>

</html>