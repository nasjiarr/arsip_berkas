<?php
// Test Suite: Dashboard Metrics Summary (Stat Cards)
echo "=== Running Dashboard Metrics Tests ===\n";

$base_dir = dirname(__DIR__);
require_once $base_dir . '/includes/config.php';

// 1. Verify dashboard.php syntax and markup
$dashboard_content = file_get_contents($base_dir . '/public/dashboard.php');
assert(strpos($dashboard_content, 'stat-card') !== false, 'dashboard.php must contain stat-card classes');
assert(strpos($dashboard_content, 'Total Pengguna') !== false, 'dashboard.php must contain Total Pengguna stat card');
assert(strpos($dashboard_content, 'Disposisi Surat') !== false, 'dashboard.php must contain Disposisi Surat stat card');
assert(strpos($dashboard_content, 'Total Berkas SK') !== false, 'dashboard.php must contain Total Berkas SK stat card');
assert(strpos($dashboard_content, 'Total Berkas SOP') !== false, 'dashboard.php must contain Total Berkas SOP stat card');
echo "PASS: dashboard.php markup and stat card presence verified.\n";

// 2. Verify style.css stat-card and tabs definitions
$css_content = file_get_contents($base_dir . '/public/assets/style.css');
assert(strpos($css_content, '.stat-card') !== false, 'style.css must contain .stat-card');
assert(strpos($css_content, '[data-bs-theme="dark"] .stat-card') !== false, 'style.css must contain dark mode .stat-card');
assert(strpos($css_content, '.nav-tabs-custom') !== false, 'style.css must contain .nav-tabs-custom');
assert(strpos($css_content, '.quick-access-card') !== false, 'style.css must contain .quick-access-card');
echo "PASS: style.css stat-card and custom tabs rules verified.\n";

// 3. Verify Nav Tabs presence in dashboard.php
assert(strpos($dashboard_content, 'id="dashboardTab"') !== false, 'dashboard.php must contain #dashboardTab');
assert(strpos($dashboard_content, 'id="tab-kredit"') !== false, 'dashboard.php must contain #tab-kredit');
assert(strpos($dashboard_content, 'id="tab-regulasi"') !== false, 'dashboard.php must contain #tab-regulasi');
assert(strpos($dashboard_content, 'id="tab-log"') !== false, 'dashboard.php must contain #tab-log');
assert(strpos($dashboard_content, 'Pintasan Akses Cepat') !== false, 'dashboard.php must contain Quick Access section');
assert(strpos($dashboard_content, 'Aktivitas Disposisi Surat Terkini') !== false, 'dashboard.php must contain Recent Activities');
echo "PASS: dashboard.php tabbed utility structure verified.\n";

// 4. Verify Database metric queries execute cleanly
try {
    $count_users = (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $count_disp_m = (int) $pdo->query("SELECT COUNT(*) FROM disposisi_surat")->fetchColumn();
    $count_disp_k = (int) $pdo->query("SELECT COUNT(*) FROM disposisi_keluar")->fetchColumn();
    $count_sk = (int) $pdo->query("SELECT COUNT(*) FROM sk_table")->fetchColumn();
    $count_sop = (int) $pdo->query("SELECT COUNT(*) FROM sop_table")->fetchColumn();
    
    assert($count_users >= 0, 'Users count must be non-negative');
    assert($count_sk >= 0, 'SK count must be non-negative');
    assert($count_sop >= 0, 'SOP count must be non-negative');
    echo "PASS: Database queries executed successfully (Users: $count_users, Disp: " . ($count_disp_m + $count_disp_k) . ", SK: $count_sk, SOP: $count_sop).\n";
} catch (Exception $e) {
    echo "FAIL: DB query error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "ALL DASHBOARD METRICS TESTS PASSED (100%)\n";
