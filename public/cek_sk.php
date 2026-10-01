<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
check_login();

$user_role = $_SESSION['user']['role'] ?? '';
$can_manage = in_array($user_role, ['admin_dok', 'ti_admin']);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pangkalan Data Surat Keputusan (SK) - Bank Kulon Progo</title>
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
                <!-- Page Header -->
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
                    <div>
                        <h4 class="mb-1 fw-bold d-flex align-items-center">
                            <i class="bi bi-file-earmark-text text-primary me-2"></i>
                            Pangkalan Data Surat Keputusan (SK)
                        </h4>
                        <p class="text-muted small mb-0">Daftar keputusan direksi dan regulasi resmi PT BPR Bank Kulon Progo</p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fs-7" id="totalBadge">
                            <i class="bi bi-database me-1"></i> <span id="totalCount">Memuat...</span> Dokumen
                        </span>
                        <?php if ($can_manage): ?>
                            <a href="dashboard.php?tab=regulasi" class="btn btn-primary btn-sm d-flex align-items-center gap-1 shadow-sm">
                                <i class="bi bi-cloud-arrow-up"></i> Upload SK Baru
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Search & Filter Toolbar -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-3">
                        <div class="row g-2 align-items-center">
                            <!-- Search Bar with Icon and Clear Button -->
                            <div class="col-md-8 col-lg-7">
                                <div class="position-relative">
                                    <div class="input-group">
                                        <span class="input-group-text bg-transparent border-end-0 text-muted">
                                            <i class="bi bi-search" id="searchIcon"></i>
                                            <span class="spinner-border spinner-border-sm text-primary d-none" id="searchSpinner" role="status"></span>
                                        </span>
                                        <input
                                            type="text"
                                            id="searchInputSK"
                                            class="form-control border-start-0 border-end-0 ps-0"
                                            placeholder="Cari nomor SK, perihal, atau kata kunci..."
                                            autocomplete="off">
                                        <button class="btn btn-outline-secondary border-start-0 text-muted d-none" type="button" id="clearSearchBtn" title="Hapus pencarian">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>
                                    <div id="searchSuggestionsSK" class="search-suggestions shadow-sm"></div>
                                </div>
                            </div>

                            <!-- Year Filter -->
                            <div class="col-6 col-md-3 col-lg-3">
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent text-muted small"><i class="bi bi-calendar3"></i></span>
                                    <select class="form-select form-select-sm" id="yearFilter">
                                        <option value="">Semua Tahun</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Refresh Button -->
                            <div class="col-6 col-md-1 col-lg-2 text-end">
                                <button type="button" class="btn btn-outline-secondary btn-sm w-100" id="resetFilterBtn" title="Reset Filter">
                                    <i class="bi bi-arrow-counterclockwise"></i> <span class="d-none d-lg-inline">Reset</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SK Data Table Card -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="skTable" style="font-size: 0.9rem;">
                                <thead class="table-light border-bottom">
                                    <tr>
                                        <th class="ps-3 text-center" style="width: 55px;">No</th>
                                        <th style="min-width: 170px;">Nomor SK</th>
                                        <th style="min-width: 280px;">Judul / Peraturan</th>
                                        <th class="text-center" style="width: 120px;">Tahun</th>
                                        <th class="pe-3 text-center" style="width: 170px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="skTableBody">
                                    <!-- Dynamic rows loaded via JS -->
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                            Memuat pangkalan data SK...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Table Footer: Info & Pagination -->
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 px-3 py-3 border-top bg-body-tertiary">
                            <div class="small text-muted" id="paginationInfo">
                                Menampilkan 0 data
                            </div>
                            <div id="paginationContainer"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick View PDF Modal -->
    <div class="modal fade" id="quickViewModal" tabindex="-1" aria-labelledby="quickViewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content shadow border-0">
                <div class="modal-header bg-primary text-white py-3">
                    <div class="d-flex align-items-center gap-2 flex-grow-1 overflow-hidden me-2">
                        <i class="bi bi-file-earmark-pdf fs-4 flex-shrink-0"></i>
                        <div class="overflow-hidden">
                            <h6 class="modal-title fw-bold text-truncate mb-0" id="quickViewModalLabel">Preview Dokumen SK</h6>
                            <small class="text-white-50 font-monospace" id="quickViewDocNumber">-</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                        <a href="#" target="_blank" class="btn btn-light btn-sm text-primary fw-medium" id="quickViewTabBtn">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Tab Baru
                        </a>
                        <a href="#" class="btn btn-light btn-sm text-primary fw-medium" id="quickViewDownloadBtn">
                            <i class="bi bi-download me-1"></i> Unduh
                        </a>
                        <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body p-0" style="min-height: 550px; height: 75vh; background: #525659;">
                    <iframe id="quickViewFrame" src="" style="width: 100%; height: 100%; border: none;"></iframe>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <?php if ($can_manage): ?>
        <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content border-0 shadow">
                    <div class="modal-body text-center p-4">
                        <div class="text-danger mb-3">
                            <i class="bi bi-exclamation-triangle fs-1"></i>
                        </div>
                        <h6 class="fw-bold mb-2">Hapus Berkas SK?</h6>
                        <p class="text-muted small mb-4" id="deleteDocNumberText">Dokumen yang dihapus tidak dapat dipulihkan kembali.</p>
                        <div class="d-flex justify-content-center gap-2">
                            <button type="button" class="btn btn-light btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-danger btn-sm px-3" id="confirmDeleteBtn">Ya, Hapus</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInputSK');
            const clearSearchBtn = document.getElementById('clearSearchBtn');
            const searchSpinner = document.getElementById('searchSpinner');
            const searchIcon = document.getElementById('searchIcon');
            const searchSuggestions = document.getElementById('searchSuggestionsSK');
            const yearFilter = document.getElementById('yearFilter');
            const resetFilterBtn = document.getElementById('resetFilterBtn');
            const tableBody = document.getElementById('skTableBody');
            const totalCountEl = document.getElementById('totalCount');
            const paginationInfo = document.getElementById('paginationInfo');
            const paginationContainer = document.getElementById('paginationContainer');

            // Modals
            const quickViewModalEl = document.getElementById('quickViewModal');
            const quickViewModal = new bootstrap.Modal(quickViewModalEl);
            const quickViewLabel = document.getElementById('quickViewModalLabel');
            const quickViewDocNumber = document.getElementById('quickViewDocNumber');
            const quickViewFrame = document.getElementById('quickViewFrame');
            const quickViewTabBtn = document.getElementById('quickViewTabBtn');
            const quickViewDownloadBtn = document.getElementById('quickViewDownloadBtn');

            <?php if ($can_manage): ?>
                const deleteModalEl = document.getElementById('deleteConfirmModal');
                const deleteModal = new bootstrap.Modal(deleteModalEl);
                const deleteDocNumberText = document.getElementById('deleteDocNumberText');
                const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
                let pendingDeleteId = null;
            <?php endif; ?>

            let currentPage = 1;
            let totalPages = 1;
            let totalDataCount = 0;
            let typingTimer;
            const itemsPerPage = 10;

            function escapeHtml(str) {
                if (str === null || str === undefined) return '';
                return String(str)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function showToast(type, message) {
                let toastContainer = document.querySelector('.toast-container');
                if (!toastContainer) {
                    toastContainer = document.createElement('div');
                    toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
                    toastContainer.style.zIndex = '1100';
                    document.body.appendChild(toastContainer);
                }

                const bgClass = (type === 'success') ? 'bg-success text-white' : 'bg-danger text-white';
                const iconClass = (type === 'success') ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill';
                const toastHtml = `
                    <div class="toast align-items-center ${bgClass} border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body d-flex align-items-center gap-2">
                                <i class="bi ${iconClass} fs-5"></i>
                                <span>${escapeHtml(message)}</span>
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                `;
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = toastHtml;
                const toastEl = tempDiv.firstElementChild;
                toastContainer.appendChild(toastEl);
                const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
                toast.show();
            }

            function updateYearOptions(years) {
                if (!years || !Array.isArray(years)) return;
                const currentSelected = yearFilter.value;
                const existingYears = Array.from(yearFilter.options).map(o => o.value).filter(v => v !== '');
                if (existingYears.length === 0 && years.length > 0) {
                    years.forEach(y => {
                        const opt = document.createElement('option');
                        opt.value = y;
                        opt.textContent = 'Tahun ' + y;
                        yearFilter.appendChild(opt);
                    });
                    if (currentSelected) yearFilter.value = currentSelected;
                }
            }

            function renderTable(results, page, total) {
                if (!results || results.length === 0) {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="bi bi-file-earmark-x text-muted fs-1 d-block mb-2"></i>
                                <span class="text-muted fw-medium">Tidak ada dokumen SK yang sesuai dengan filter atau kata kunci.</span>
                            </td>
                        </tr>
                    `;
                    paginationInfo.textContent = 'Menampilkan 0 data';
                    paginationContainer.innerHTML = '';
                    return;
                }

                const startIdx = (page - 1) * itemsPerPage + 1;
                const endIdx = Math.min(page * itemsPerPage, total);
                paginationInfo.textContent = `Menampilkan ${startIdx}–${endIdx} dari ${total} dokumen`;

                tableBody.innerHTML = results.map((sk, index) => {
                    const rowNumber = startIdx + index;
                    const nomorSk = escapeHtml(sk.nomor_sk);
                    const judulSk = escapeHtml(sk.judul_sk);
                    const tahun = escapeHtml(sk.tahun_disahkan || '-');
                    const skId = encodeURIComponent(sk.id);

                    let deleteBtnHtml = '';
                    <?php if ($can_manage): ?>
                        deleteBtnHtml = `
                            <button type="button" class="btn btn-outline-danger btn-sm btn-delete-sk" data-sk-id="${skId}" data-sk-nomor="${nomorSk}" title="Hapus SK">
                                <i class="bi bi-trash"></i>
                            </button>
                        `;
                    <?php endif; ?>

                    return `
                        <tr>
                            <td class="ps-3 text-center text-muted fw-semibold">${rowNumber}</td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2 py-1">
                                    ${nomorSk}
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold text-truncate" style="max-width: 440px;" title="${judulSk}">
                                    ${judulSk}
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">
                                    ${tahun}
                                </span>
                            </td>
                            <td class="pe-3 text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-primary btn-preview-sk" data-sk-id="${skId}" data-sk-nomor="${nomorSk}" data-sk-judul="${judulSk}" title="Preview Dokumen">
                                        <i class="bi bi-eye me-1"></i> Lihat
                                    </button>
                                    <a href="view_sk.php?id=${skId}&download=1" class="btn btn-outline-secondary" title="Unduh PDF">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    ${deleteBtnHtml}
                                </div>
                            </td>
                        </tr>
                    `;
                }).join('');
            }

            function renderPagination(currPage, totalPgs) {
                if (totalPgs <= 1) {
                    paginationContainer.innerHTML = '';
                    return;
                }

                let html = '<ul class="pagination pagination-sm mb-0">';
                html += `<li class="page-item ${currPage <= 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${currPage - 1}" aria-label="Previous">&laquo;</a>
                </li>`;

                const maxVisible = 5;
                let startPage = Math.max(1, currPage - Math.floor(maxVisible / 2));
                let endPage = Math.min(totalPgs, startPage + maxVisible - 1);
                if (endPage - startPage + 1 < maxVisible) {
                    startPage = Math.max(1, endPage - maxVisible + 1);
                }

                for (let i = startPage; i <= endPage; i++) {
                    html += `<li class="page-item ${i === currPage ? 'active' : ''}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                    </li>`;
                }

                html += `<li class="page-item ${currPage >= totalPgs ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${currPage + 1}" aria-label="Next">&raquo;</a>
                </li>`;
                html += '</ul>';

                paginationContainer.innerHTML = html;
            }

            function loadData(page = 1) {
                const term = searchInput.value.trim();
                const year = yearFilter.value;

                searchIcon.classList.add('d-none');
                searchSpinner.classList.remove('d-none');

                const params = new URLSearchParams();
                params.set('page', page);
                params.set('limit', itemsPerPage);
                if (term) params.set('term', term);
                if (year) params.set('year', year);

                const endpoint = term ? `search_sk.php?${params.toString()}` : `get_all_sk.php?${params.toString()}`;

                fetch(endpoint)
                    .then(res => res.json())
                    .then(data => {
                        currentPage = data.currentPage || page;
                        totalPages = data.totalPages || 1;
                        totalDataCount = data.total !== undefined ? data.total : (data.results ? data.results.length : 0);

                        totalCountEl.textContent = Number(totalDataCount).toLocaleString('id-ID');
                        if (data.years) {
                            updateYearOptions(data.years);
                        }

                        renderTable(data.results || [], currentPage, totalDataCount);
                        renderPagination(currentPage, totalPages);
                    })
                    .catch(err => {
                        console.error('Error fetching SK data:', err);
                        tableBody.innerHTML = `
                            <tr>
                                <td colspan="5" class="text-center py-4 text-danger">
                                    <i class="bi bi-exclamation-circle me-1"></i> Terjadi kesalahan saat memuat pangkalan data SK.
                                </td>
                            </tr>
                        `;
                    })
                    .finally(() => {
                        searchSpinner.classList.add('d-none');
                        searchIcon.classList.remove('d-none');
                    });
            }

            // Quick View Trigger
            tableBody.addEventListener('click', function(e) {
                const previewBtn = e.target.closest('.btn-preview-sk');
                if (previewBtn) {
                    const skId = previewBtn.dataset.skId;
                    const skNomor = previewBtn.dataset.skNomor;
                    const skJudul = previewBtn.dataset.skJudul;

                    quickViewLabel.textContent = skJudul;
                    quickViewDocNumber.textContent = skNomor;
                    quickViewFrame.src = `view_sk.php?id=${skId}#toolbar=1`;
                    quickViewTabBtn.href = `view_sk.php?id=${skId}`;
                    quickViewDownloadBtn.href = `view_sk.php?id=${skId}&download=1`;

                    quickViewModal.show();
                    return;
                }

                <?php if ($can_manage): ?>
                    const deleteBtn = e.target.closest('.btn-delete-sk');
                    if (deleteBtn) {
                        pendingDeleteId = deleteBtn.dataset.skId;
                        const docNomor = deleteBtn.dataset.skNomor;
                        deleteDocNumberText.innerHTML = `Anda akan menghapus dokumen <strong>${escapeHtml(docNomor)}</strong>.`;
                        deleteModal.show();
                    }
                <?php endif; ?>
            });

            quickViewModalEl.addEventListener('hidden.bs.modal', function() {
                quickViewFrame.src = '';
            });

            // Confirm Delete Handler
            <?php if ($can_manage): ?>
                confirmDeleteBtn.addEventListener('click', function() {
                    if (!pendingDeleteId) return;

                    confirmDeleteBtn.disabled = true;
                    confirmDeleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menghapus...';

                    fetch('delete_sk.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: pendingDeleteId })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            deleteModal.hide();
                            showToast('success', 'Dokumen SK berhasil dihapus.');
                            loadData(currentPage);
                        } else {
                            throw new Error(data.message || 'Gagal menghapus dokumen');
                        }
                    })
                    .catch(err => {
                        showToast('danger', err.message);
                    })
                    .finally(() => {
                        confirmDeleteBtn.disabled = false;
                        confirmDeleteBtn.innerHTML = 'Ya, Hapus';
                        pendingDeleteId = null;
                    });
                });
            <?php endif; ?>

            // Search Input with Debounce & Toggle Clear Button
            searchInput.addEventListener('input', function() {
                const val = this.value.trim();
                clearSearchBtn.classList.toggle('d-none', val.length === 0);

                clearTimeout(typingTimer);
                typingTimer = setTimeout(() => {
                    loadData(1);
                }, 350);
            });

            clearSearchBtn.addEventListener('click', function() {
                searchInput.value = '';
                clearSearchBtn.classList.add('d-none');
                loadData(1);
            });

            yearFilter.addEventListener('change', function() {
                loadData(1);
            });

            resetFilterBtn.addEventListener('click', function() {
                searchInput.value = '';
                clearSearchBtn.classList.add('d-none');
                yearFilter.value = '';
                loadData(1);
            });

            paginationContainer.addEventListener('click', function(e) {
                const link = e.target.closest('a.page-link');
                if (link && !link.parentElement.classList.contains('disabled')) {
                    e.preventDefault();
                    const page = parseInt(link.dataset.page);
                    if (page && page !== currentPage) {
                        loadData(page);
                    }
                }
            });

            // Initial load
            loadData(1);
        });
    </script>
</body>

</html>
