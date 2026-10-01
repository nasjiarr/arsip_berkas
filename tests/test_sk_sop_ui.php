<?php
// Test Suite: SK & SOP UI/UX Overhaul
echo "=== Running SK & SOP UI/UX Tests ===\n";

$base_dir = dirname(__DIR__);
require_once $base_dir . '/includes/config.php';

// 1. Verify files exist and have valid syntax
$files_to_check = [
    $base_dir . '/public/cek_sk.php',
    $base_dir . '/public/cek_sop.php',
    $base_dir . '/public/get_all_sk.php',
    $base_dir . '/public/get_all_sop.php',
    $base_dir . '/public/search_sk.php',
    $base_dir . '/public/search_sop.php',
    $base_dir . '/public/view_sk.php',
    $base_dir . '/public/view_sop.php',
    $base_dir . '/public/detail_sk.php',
    $base_dir . '/public/detail_sop.php'
];

foreach ($files_to_check as $file) {
    assert(file_exists($file), "$file must exist");
    exec("php -l " . escapeshellarg($file), $out, $code);
    assert($code === 0, "$file must have valid syntax");
}
echo "PASS: All 10 SK & SOP PHP files have valid syntax.\n";

// 2. Verify Table Layout & Quick View Modal markup in cek_sk.php and cek_sop.php
$sk_html = file_get_contents($base_dir . '/public/cek_sk.php');
assert(strpos($sk_html, 'id="skTable"') !== false, 'cek_sk.php must contain #skTable');
assert(strpos($sk_html, 'id="quickViewModal"') !== false, 'cek_sk.php must contain #quickViewModal');
assert(strpos($sk_html, 'id="yearFilter"') !== false, 'cek_sk.php must contain #yearFilter');
assert(strpos($sk_html, 'id="clearSearchBtn"') !== false, 'cek_sk.php must contain #clearSearchBtn');

$sop_html = file_get_contents($base_dir . '/public/cek_sop.php');
assert(strpos($sop_html, 'id="sopTable"') !== false, 'cek_sop.php must contain #sopTable');
assert(strpos($sop_html, 'id="quickViewModal"') !== false, 'cek_sop.php must contain #quickViewModal');
assert(strpos($sop_html, 'id="yearFilter"') !== false, 'cek_sop.php must contain #yearFilter');
assert(strpos($sop_html, 'id="clearSearchBtn"') !== false, 'cek_sop.php must contain #clearSearchBtn');
echo "PASS: cek_sk.php and cek_sop.php interactive table and quick view markup verified.\n";

// 3. Verify get_all_sk.php API output schema
$prev_cwd = getcwd();
chdir($base_dir . '/public');
if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
$_SESSION['user'] = ['id' => 1, 'username' => 'tester', 'role' => 'ti_admin'];

$_GET['limit'] = 5;
ob_start();
include $base_dir . '/public/get_all_sk.php';
$sk_json = ob_get_clean();
$sk_data = json_decode($sk_json, true);
assert(is_array($sk_data), 'get_all_sk must return JSON array');
assert(isset($sk_data['results']), 'get_all_sk must return results');
assert(isset($sk_data['total']), 'get_all_sk must return total');
assert(isset($sk_data['years']), 'get_all_sk must return available years list');
echo "PASS: get_all_sk.php response schema verified.\n";

// 4. Verify get_all_sop.php API output schema
ob_start();
include $base_dir . '/public/get_all_sop.php';
$sop_json = ob_get_clean();
$sop_data = json_decode($sop_json, true);
assert(is_array($sop_data), 'get_all_sop must return JSON array');
assert(isset($sop_data['results']), 'get_all_sop must return results');
assert(isset($sop_data['total']), 'get_all_sop must return total');
assert(isset($sop_data['years']), 'get_all_sop must return available years list');
echo "PASS: get_all_sop.php response schema verified.\n";

// 5. Verify search_sk.php with year filter
$_GET['term'] = 'Libur';
$_GET['year'] = $sk_data['years'][0] ?? null;
ob_start();
include $base_dir . '/public/search_sk.php';
$search_json = ob_get_clean();
$search_data = json_decode($search_json, true);
assert(is_array($search_data), 'search_sk must return JSON array');
assert(isset($search_data['results']), 'search_sk must return results');
assert(isset($search_data['total']), 'search_sk must return total');
echo "PASS: search_sk.php year-filtered search verified.\n";
chdir($prev_cwd);

// 6. Verify view_sk.php and view_sop.php download support
$view_sk_code = file_get_contents($base_dir . '/public/view_sk.php');
assert(strpos($view_sk_code, 'Content-Disposition') !== false, 'view_sk.php must send Content-Disposition');
assert(strpos($view_sk_code, 'attachment') !== false, 'view_sk.php must support download attachment');

$view_sop_code = file_get_contents($base_dir . '/public/view_sop.php');
assert(strpos($view_sop_code, 'Content-Disposition') !== false, 'view_sop.php must send Content-Disposition');
assert(strpos($view_sop_code, 'attachment') !== false, 'view_sop.php must support download attachment');
echo "PASS: view_sk.php and view_sop.php download handling verified.\n";

echo "ALL SK & SOP UI/UX TESTS PASSED (100%)\n";
