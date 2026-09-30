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
echo "PASS: Typography, corporate palette variables and table styling present in style.css.\n";

echo "ALL UI CONSISTENCY TESTS PASSED (100%)\n";
