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
<div class="sidebar p-3 d-flex flex-column justify-content-between" id="sidebar">
    <div>
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="d-flex align-items-center">
                <div class="brand-icon-box me-2">
                    <i class="bi bi-bank fs-4"></i>
                </div>
                <div>
                    <h5 class="mb-0 brand-title" style="font-size: 0.95rem; line-height: 1.2;">Sistem Informasi</h5>
                    <small class="brand-subtitle" style="font-size: 0.72rem;">Bank Kulon Progo</small>
                </div>
            </div>
            <button type="button" class="btn-close d-md-none sidebar-close-btn" id="sidebarCloseBtn" aria-label="Tutup"></button>
        </div>

        <div class="px-2 py-1 mb-3 user-badge rounded small d-flex align-items-center justify-content-between">
            <span class="text-truncate me-1"><i class="bi bi-person-circle me-1" style="color: var(--brand-primary);"></i><?= htmlspecialchars($user_name) ?></span>
            <span class="badge badge-role border text-uppercase" style="font-size: 0.65rem;"><?= htmlspecialchars($user_role) ?></span>
        </div>

        <hr class="my-2">

        <ul class="nav flex-column gap-1">
            <?php if ($user_role === 'sekre'): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $is_active_disp_masuk ? 'active' : '' ?>" href="<?= $disp_url ?>disposisi.php">
                        <i class="bi bi-envelope-arrow-down me-2"></i> Surat Masuk
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $is_active_disp_keluar ? 'active' : '' ?>" href="<?= $disp_url ?>disposisi_keluar.php">
                        <i class="bi bi-envelope-arrow-up me-2"></i> Surat Keluar
                    </a>
                </li>
            <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link <?= $is_active_dashboard ? 'active' : '' ?>" href="<?= $base_url ?>dashboard.php">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                </li>
            <?php endif; ?>

            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle <?= $is_active_berkas_dropdown ? 'active' : '' ?>" href="#" id="dropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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
                    <a class="nav-link <?= $is_active_user ? 'active' : '' ?>" href="<?= $base_url ?>add_user.php">
                        <i class="bi bi-people me-2"></i> Kelola User
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </div>

    <div>
        <hr class="my-2">
        <button type="button" class="btn btn-sm w-100 d-flex align-items-center justify-content-between mb-2 text-start px-3 py-2 theme-btn" id="themeToggleBtn" title="Ganti Tema">
            <span class="d-flex align-items-center gap-2 small">
                <i class="bi bi-moon-stars" id="themeIcon"></i>
                <span id="themeLabel">Mode Gelap</span>
            </span>
            <i class="bi bi-circle-half text-muted"></i>
        </button>
        <a class="nav-link text-danger fw-semibold d-flex align-items-center" href="<?= $base_url ?>logout.php">
            <i class="bi bi-box-arrow-right me-2 fs-5"></i> Keluar
        </a>
    </div>
</div>

<?php
if (function_exists('render_flash_toast')) {
    render_flash_toast();
}
?>

<script>
// Theme Manager: Dark & Light Mode
(function() {
    function getPreferredTheme() {
        const storedTheme = localStorage.getItem('app_theme');
        if (storedTheme) {
            return storedTheme;
        }
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    function setTheme(theme) {
        document.documentElement.setAttribute('data-bs-theme', theme);
        localStorage.setItem('app_theme', theme);
        updateThemeUI(theme);
    }

    function updateThemeUI(theme) {
        const themeIcon = document.getElementById('themeIcon');
        const themeLabel = document.getElementById('themeLabel');
        if (themeIcon && themeLabel) {
            if (theme === 'dark') {
                themeIcon.className = 'bi bi-sun-fill text-warning';
                themeLabel.textContent = 'Mode Terang';
            } else {
                themeIcon.className = 'bi bi-moon-stars text-primary';
                themeLabel.textContent = 'Mode Gelap';
            }
        }
    }

    // Apply immediately to prevent flash
    const currentTheme = getPreferredTheme();
    document.documentElement.setAttribute('data-bs-theme', currentTheme);

    document.addEventListener('DOMContentLoaded', function() {
        updateThemeUI(currentTheme);
        const toggleBtn = document.getElementById('themeToggleBtn');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                const activeTheme = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
                setTheme(activeTheme);
            });
        }
    });
})();

document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarBackdrop = document.getElementById('sidebarBackdrop');
    const sidebarCloseBtn = document.getElementById('sidebarCloseBtn');

    if (sidebar && sidebarToggle && sidebarBackdrop) {
        function openSidebar() {
            sidebar.classList.add('show');
            sidebarBackdrop.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebar() {
            sidebar.classList.remove('show');
            sidebarBackdrop.classList.remove('show');
            document.body.style.overflow = '';
        }

        sidebarToggle.addEventListener('click', openSidebar);
        sidebarBackdrop.addEventListener('click', closeSidebar);
        if (sidebarCloseBtn) {
            sidebarCloseBtn.addEventListener('click', closeSidebar);
        }

        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                closeSidebar();
            }
        });
    }
});
</script>
