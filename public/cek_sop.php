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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <?php require_once '../includes/sidebar.php'; ?>

    <div class="container-fluid p-0">
        <div class="row g-0">
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

                    function escapeHtml(str) {
                        if (str === null || str === undefined) return '';
                        return String(str)
                            .replace(/&/g, '&amp;')
                            .replace(/</g, '&lt;')
                            .replace(/>/g, '&gt;')
                            .replace(/"/g, '&quot;')
                            .replace(/'/g, '&#039;');
                    }

                    // Fungsi untuk membuat card SOP
                    function createSopCard(sop) {
                        const nomorSop = escapeHtml(sop.nomor_sop);
                        const tahunDisahkan = escapeHtml(sop.tahun_disahkan);
                        const judulSop = escapeHtml(sop.judul_sop);
                        const sopId = encodeURIComponent(sop.id);
                        let deleteButton = '';
                        <?php if ($role === 'admin_dok' || $role === 'ti_admin'): ?>
                            deleteButton = `
                        <button class="btn btn-danger ms-2 delete-sop" data-sop-id="${sopId}" data-sop-nomor="${nomorSop}">
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
                                            <span class="text-primary">${nomorSop}</span> | 
                                            <span>${tahunDisahkan}</span>
                                        </div>
                                    </div>
                                    <h5 class="mb-3">${judulSop}</h5>
                                    <div>
                                        <a href="detail_sop.php?id=${sopId}" class="btn btn-primary">
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
                });
            </script>
</body>

</html>