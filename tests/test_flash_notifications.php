<?php
// Test Suite: Flash Message & Toast Notifications
echo "=== Running Flash Notification Tests ===\n";

$base_dir = dirname(__DIR__);
require_once $base_dir . '/includes/auth.php';

// 1. Test set_flash_message & get_flash_message (consumption)
set_flash_message('success', 'Operasi berhasil dilakukan.');
assert(isset($_SESSION['flash_message']), 'Flash message must be stored in session');
$msg = get_flash_message();
assert($msg['type'] === 'success', 'Type must be success');
assert($msg['message'] === 'Operasi berhasil dilakukan.', 'Message must match');
assert(!isset($_SESSION['flash_message']), 'Flash message must be consumed/unset after get');
echo "PASS: set_flash_message and get_flash_message single-consumption verified.\n";

// 2. Test legacy backward compatibility
$_SESSION['success'] = 'Data lama sukses';
$legacy_msg = get_flash_message();
assert($legacy_msg['type'] === 'success', 'Legacy success must map to success type');
assert($legacy_msg['message'] === 'Data lama sukses', 'Legacy message must match');
assert(!isset($_SESSION['success']), 'Legacy success must be unset');

$_SESSION['error'] = 'Data lama error';
$legacy_err = get_flash_message();
assert($legacy_err['type'] === 'danger', 'Legacy error must map to danger type');
assert($legacy_err['message'] === 'Data lama error', 'Legacy error message must match');
assert(!isset($_SESSION['error']), 'Legacy error must be unset');
echo "PASS: Legacy session success/error backward compatibility verified.\n";

// 3. Test render_flash_toast output
set_flash_message('danger', 'Gagal memproses file!');
ob_start();
render_flash_toast();
$toast_html = ob_get_clean();
assert(strpos($toast_html, 'id="flashToast"') !== false, 'Toast HTML must contain flashToast id');
assert(strpos($toast_html, 'bg-danger') !== false, 'Toast HTML must contain bg-danger');
assert(strpos($toast_html, 'Gagal memproses file!') !== false, 'Toast HTML must contain flash message text');
assert(strpos($toast_html, 'bootstrap.Toast') !== false, 'Toast HTML must initialize Bootstrap Toast');
echo "PASS: render_flash_toast generates valid Bootstrap 5 Toast markup.\n";

// 4. Test sidebar includes render_flash_toast
$sidebar_content = file_get_contents($base_dir . '/includes/sidebar.php');
assert(strpos($sidebar_content, 'render_flash_toast') !== false, 'sidebar.php must invoke render_flash_toast');
echo "PASS: sidebar.php invokes render_flash_toast automatically for all pages.\n";

echo "ALL FLASH NOTIFICATION TESTS PASSED (100%)\n";
