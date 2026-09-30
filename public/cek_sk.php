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
    <title>Cek SK</title>
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

                <!-- Cek SK -->
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-primary text-white d-flex align-items-center">
                                <i class="bi bi-search me-2"></i>
                                <h3 class="card-title mb-0">Cek SK</h3>
                            </div>
                            <div class="card-body p-4">
                                <div class="search-container">
                                    <input
                                        type="text"
                                        id="searchInputSK"
                                        class="form-control form-control-lg"
                                        placeholder="Ketik kata kunci..."
                                        style="font-size: 14px;"
                                        autocomplete="off">
                                    <div id="searchSuggestionsSK" class="search-suggestions"></div>
                                </div>
                                <div id="searchResultsSK" class="mt-4"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInputSK = document.getElementById('searchInputSK');
            const searchSuggestionsSK = document.getElementById('searchSuggestionsSK');
            const searchResultsSK = document.getElementById('searchResultsSK');
            const alertContainer = document.getElementById('alertContainer');
            const paginationContainer = document.createElement('div');
            paginationContainer.id = 'paginationContainer';
            searchResultsSK.parentNode.insertBefore(paginationContainer, searchResultsSK.nextSibling);
            let typingTimerSK;
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

            // Fungsi untuk membuat card SK
            function createSkCard(sk) {
                const nomorSk = escapeHtml(sk.nomor_sk);
                const tahunDisahkan = escapeHtml(sk.tahun_disahkan);
                const judulSk = escapeHtml(sk.judul_sk);
                const skId = encodeURIComponent(sk.id);
                let deleteButton = '';
                <?php if ($role === 'admin_dok' || $role === 'ti_admin'): ?>
                    deleteButton = `
                        <button class="btn btn-danger ms-2 delete-sk" data-sk-id="${skId}" data-sk-nomor="${nomorSk}">
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
                                            <span class="text-primary">${nomorSk}</span> | 
                                            <span>${tahunDisahkan}</span>
                                        </div>
                                    </div>
                                    <h5 class="mb-3">${judulSk}</h5>
                                    <div>
                                        <a href="detail_sk.php?id=${skId}" class="btn btn-primary">
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

            // Fungsi untuk memuat data SK
            function loadSK(page, searchTerm = '') {
                const url = searchTerm ? `search_sk.php?term=${encodeURIComponent(searchTerm)}&page=${page}` : `get_all_sk.php?page=${page}`;
                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        if (data.results.length > 0) {
                            searchResultsSK.innerHTML = data.results.map(sk => createSkCard(sk)).join('');
                            paginationContainer.innerHTML = createPagination(data.currentPage, data.totalPages);
                            currentPage = data.currentPage;
                            totalPages = data.totalPages;
                        } else {
                            searchResultsSK.innerHTML = '<div class="alert alert-info">Tidak ditemukan SK yang sesuai dengan kata kunci.</div>';
                            paginationContainer.innerHTML = '';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        searchResultsSK.innerHTML = '<div class="alert alert-danger">Terjadi kesalahan saat memuat data.</div>';
                        paginationContainer.innerHTML = '';
                    });
            }

            // Function to handle SK deletion
            function deleteSK(skId) {
                fetch('delete_sk.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            id: skId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showAlert('SK berhasil dihapus');
                            loadSK(currentPage, searchInputSK.value.trim());
                        } else {
                            throw new Error(data.message || 'Gagal menghapus SK');
                        }
                    })
                    .catch(error => {
                        showAlert(error.message, 'danger');
                    });
            }

            // Handle delete button click
            document.addEventListener('click', function(e) {
                if (e.target.closest('.delete-sk')) {
                    const button = e.target.closest('.delete-sk');
                    const skId = button.dataset.skId;
                    const skNomor = button.dataset.skNomor;

                    if (confirm(`Apakah Anda yakin ingin menghapus SK dengan nomor ${skNomor}?`)) {
                        deleteSK(skId);
                    }
                }
            });

            // Handle input pencarian
            searchInputSK.addEventListener('input', function() {
                clearTimeout(typingTimerSK);
                typingTimerSK = setTimeout(() => {
                    const searchTerm = this.value.trim();
                    if (searchTerm.length > 2) {
                        loadSK(1, searchTerm);
                    } else {
                        loadSK(1);
                    }
                }, 500);
            });

            // Handle klik pagination
            paginationContainer.addEventListener('click', function(e) {
                if (e.target.tagName === 'A') {
                    e.preventDefault();
                    const page = parseInt(e.target.getAttribute('data-page'));
                    loadSK(page, searchInputSK.value.trim());
                }
            });

            // Load semua SK saat pertama kali
            loadSK(1);
        });
    </script>
</body>

</html>