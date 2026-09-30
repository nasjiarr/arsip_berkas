<?php
// ponytail: centralized responsive sidebar navigation with dynamic base url and role-based ACL.
$current_page = basename($_SERVER['PHP_SELF']);
$is_subfolder = str_contains(str_replace('\\', '/', dirname($_SERVER['PHP_SELF'])), '/disposisi');
$base_url = $is_subfolder ? '../' : '';
$disp_url = $is_subfolder ? '' : 'disposisi/';
$user_role = $_SESSION['user']['role'] ?? '';
$user_name = $_SESSION['user']['username'] ?? 'User';

$is_active_dashboard = ($current_page === 'dashboard.php');
$is_active_sk = ($current_page === 'cek_sk.php');
$is_active_sop = ($current_page === 'cek_sop.php');
$is_active_disp_masuk = in_array($current_page, ['disposisi.php', 'add_disposisi.php', 'edit_disposisi.php']);
$is_active_disp_keluar = in_array($current_page, ['disposisi_keluar.php', 'add_disposisi_keluar.php', 'edit_disposisi_keluar.php']);
$is_active_user = ($current_page === 'add_user.php');
$is_active_berkas_dropdown = ($is_active_sk || $is_active_sop || ($user_role !== 'sekre' && ($is_active_disp_masuk || $is_active_disp_keluar)));
?>

<!-- Toggle Button for Mobile -->
<button class="navbar-toggle" id="sidebarToggle" type="button" aria-label="Toggle navigation">
    <i class="bi bi-list fs-4"></i>
</button>

<!-- Backdrop for mobile -->
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<!-- Sidebar -->
<div class="sidebar bg-white p-3 d-flex flex-column justify-content-between" id="sidebar">
    <div>
        <div class="d-flex align-items-center mb-3">
            <i class="bi bi-bank fs-2 text-primary me-2"></i>
            <div>
                <h5 class="mb-0 fw-bold text-dark" style="font-size: 1rem; line-height: 1.2;">Sistem Informasi</h5>
                <small class="text-muted" style="font-size: 0.75rem;">Bank Kulon Progo</small>
            </div>
        </div>

        <div class="px-2 py-1 mb-3 bg-light rounded text-muted small d-flex align-items-center justify-content-between border">
            <span class="text-truncate me-1"><i class="bi bi-person-circle me-1 text-primary"></i><?= htmlspecialchars($user_name) ?></span>
            <span class="badge bg-secondary-subtle text-secondary border text-uppercase" style="font-size: 0.65rem;"><?= htmlspecialchars($user_role) ?></span>
        </div>

        <hr class="my-2">

        <ul class="nav flex-column gap-1">
            <?php if ($user_role === 'sekre'): ?>
                <li class="nav-item">
                    <a class="nav-link rounded px-3 py-2 <?= $is_active_disp_masuk ? 'active text-white bg-primary' : 'text-dark' ?>" href="<?= $disp_url ?>disposisi.php">
                        <i class="bi bi-envelope-arrow-down me-2"></i> Surat Masuk
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded px-3 py-2 <?= $is_active_disp_keluar ? 'active text-white bg-primary' : 'text-dark' ?>" href="<?= $disp_url ?>disposisi_keluar.php">
                        <i class="bi bi-envelope-arrow-up me-2"></i> Surat Keluar
                    </a>
                </li>
            <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link rounded px-3 py-2 <?= $is_active_dashboard ? 'active text-white bg-primary' : 'text-dark' ?>" href="<?= $base_url ?>dashboard.php">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                </li>
            <?php endif; ?>

            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle rounded px-3 py-2 <?= $is_active_berkas_dropdown ? 'active text-white bg-primary' : 'text-dark' ?>" href="#" id="dropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-folder2-open me-2"></i> Cek Berkas
                </a>
                <ul class="dropdown-menu shadow border-0" aria-labelledby="dropdownMenuLink">
                    <li><a class="dropdown-item py-2 <?= $is_active_sk ? 'active' : '' ?>" href="<?= $base_url ?>cek_sk.php"><i class="bi bi-file-earmark-text me-2"></i>Cek SK</a></li>
                    <li><a class="dropdown-item py-2 <?= $is_active_sop ? 'active' : '' ?>" href="<?= $base_url ?>cek_sop.php"><i class="bi bi-file-earmark-ruled me-2"></i>Cek SOP</a></li>
                    <?php if ($user_role !== 'sekre'): ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item py-2 <?= $is_active_disp_masuk ? 'active' : '' ?>" href="<?= $disp_url ?>disposisi.php"><i class="bi bi-envelope-arrow-down me-2"></i>Disposisi Masuk</a></li>
                        <li><a class="dropdown-item py-2 <?= $is_active_disp_keluar ? 'active' : '' ?>" href="<?= $disp_url ?>disposisi_keluar.php"><i class="bi bi-envelope-arrow-up me-2"></i>Disposisi Keluar</a></li>
                    <?php endif; ?>
                </ul>
            </li>

            <?php if ($user_role === 'ti_admin'): ?>
                <li class="nav-item">
                    <a class="nav-link rounded px-3 py-2 <?= $is_active_user ? 'active text-white bg-primary' : 'text-dark' ?>" href="<?= $base_url ?>add_user.php">
                        <i class="bi bi-people me-2"></i> Kelola User
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </div>

    <div>
        <hr class="my-2">
        <a class="nav-link text-danger fw-bold rounded px-3 py-2 d-flex align-items-center" href="<?= $base_url ?>logout.php">
            <i class="bi bi-box-arrow-right text-danger me-2 fs-5"></i> Keluar
        </a>
    </div>
</div>

<?php
if (function_exists('render_flash_toast')) {
    render_flash_toast();
}
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarBackdrop = document.getElementById('sidebarBackdrop');

    if (sidebar && sidebarToggle && sidebarBackdrop) {
        function toggleSidebar() {
            sidebar.classList.toggle('show');
            sidebarBackdrop.classList.toggle('show');
            if (window.innerWidth <= 768) {
                sidebarToggle.style.display = sidebar.classList.contains('show') ? 'none' : 'block';
            }
        }

        sidebarToggle.addEventListener('click', toggleSidebar);
        sidebarBackdrop.addEventListener('click', toggleSidebar);

        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                sidebar.classList.remove('show');
                sidebarBackdrop.classList.remove('show');
                sidebarToggle.style.display = 'block';
            } else if (!sidebar.classList.contains('show')) {
                sidebarToggle.style.display = 'block';
            }
        });
    }
});
</script>
