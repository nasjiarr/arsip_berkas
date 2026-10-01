<?php
// Test Suite: UI Consistency & Asset Cleanup
echo "=== Running UI Consistency & Asset Cleanup Tests ===\n";

$base_dir = dirname(__DIR__);

// 1. Test sidebar.php exists and has valid syntax
$sidebar_path = $base_dir . '/includes/sidebar.php';
assert(file_exists($sidebar_path), 'includes/sidebar.php must exist');
exec("php -l " . escapeshellarg($sidebar_path), $out, $code);
assert($code === 0, 'includes/sidebar.php must have valid syntax');
echo "PASS: includes/sidebar.php valid syntax.\n";

// 2. Test centralized sidebar inclusion
$pages_with_sidebar = [
    $base_dir . '/public/dashboard.php',
    $base_dir . '/public/cek_sk.php',
    $base_dir . '/public/cek_sop.php',
    $base_dir . '/public/detail_sk.php',
    $base_dir . '/public/detail_sop.php',
    $base_dir . '/public/add_user.php',
    $base_dir . '/public/disposisi/disposisi.php',
    $base_dir . '/public/disposisi/disposisi_keluar.php'
];

foreach ($pages_with_sidebar as $page) {
    $content = file_get_contents($page);
    assert(strpos($content, 'sidebar.php') !== false, basename($page) . ' must include sidebar.php');
}
echo "PASS: All core pages include centralized sidebar.php.\n";

// 3. Test asset cleanup: No deprecated font-awesome or duplicate bootstrap-icons 1.7.2
$all_public_files = glob($base_dir . '/public/*.php');
$disp_public_files = glob($base_dir . '/public/disposisi/*.php');
$checked_files = array_merge($all_public_files, $disp_public_files);

foreach ($checked_files as $file) {
    $content = file_get_contents($file);
    assert(strpos($content, 'font-awesome') === false, basename($file) . ' must not load font-awesome');
    assert(strpos($content, 'bootstrap-icons@1.7.2') === false, basename($file) . ' must not load bootstrap-icons@1.7.2');
}
echo "PASS: Redundant Font Awesome and duplicate Bootstrap Icons 1.7.2 completely removed.\n";

// 4. Test typography, palette & style.css
$css_content = file_get_contents($base_dir . '/public/assets/style.css');
assert(strpos($css_content, 'Inter') !== false, 'style.css must configure modern Inter typography');
assert(strpos($css_content, '.table-disposisi') !== false, 'style.css must have .table-disposisi styles');
assert(strpos($css_content, '--brand-primary') !== false, 'style.css must define brand palette variables');
assert(strpos($css_content, '--slate-900') !== false, 'style.css must define slate palette variables');
assert(strpos($css_content, '.sidebar-close-btn') !== false, 'style.css must define .sidebar-close-btn');
assert(strpos($css_content, 'padding-top: 3.85rem') !== false, 'style.css must provide mobile clearance for navbar-toggle');
echo "PASS: Typography, corporate palette variables and table styling present in style.css.\n";

// 5. Test sidebar drawer and close button components
$sidebar_content = file_get_contents($sidebar_path);
assert(strpos($sidebar_content, 'id="sidebarCloseBtn"') !== false, 'sidebar.php must contain sidebarCloseBtn');
assert(strpos($sidebar_content, 'id="sidebarToggle"') !== false, 'sidebar.php must contain sidebarToggle');
assert(strpos($sidebar_content, 'id="sidebarBackdrop"') !== false, 'sidebar.php must contain sidebarBackdrop');
assert(strpos($sidebar_content, 'd-md-none sidebar-close-btn') === false, 'sidebarCloseBtn must not be hidden on desktop');
assert(strpos($css_content, 'body.sidebar-collapsed .sidebar') !== false, 'style.css must support collapsible sidebar on desktop');
assert(strpos($css_content, 'body.sidebar-collapsed .content') !== false, 'style.css must support fullscreen content on desktop collapse');
assert(strpos($css_content, 'width: 100% !important') !== false, 'style.css must expand content width to 100% when collapsed');
assert(strpos($css_content, 'body.sidebar-collapsed .navbar-toggle') !== false, 'style.css must display toggle when sidebar collapsed on desktop');
assert(strpos($css_content, 'body.sidebar-open .navbar-toggle') !== false, 'style.css must hide toggle when mobile drawer is open');
assert(strpos($sidebar_content, "sidebarToggle.classList.add('d-none')") !== false, 'sidebar.php must hide toggle when opening mobile drawer');
echo "PASS: Sidebar responsive drawer, close button, and desktop collapse verified.\n";

echo "ALL UI CONSISTENCY TESTS PASSED (100%)\n";
