<?php
// Test Suite: Role-Adaptive Dashboard Interface
echo "=== Running Role-Adaptive Dashboard Tests ===\n";

$base_dir = dirname(__DIR__);
require_once $base_dir . '/includes/config.php';

function simulate_dashboard_render(string $role, string $username = 'TestUser'): string {
    global $base_dir, $pdo;
    
    $prev_cwd = getcwd();
    chdir($base_dir . '/public');

    // Setup simulated session
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_unset();
    } else {
        @session_start();
    }
    $_SESSION['user'] = [
        'id' => 999,
        'username' => $username,
        'role' => $role
    ];
    
    ob_start();
    include $base_dir . '/public/dashboard.php';
    $out = ob_get_clean();

    chdir($prev_cwd);
    return $out;
}

// 1. Test Role: admin_dok
$html_dok = simulate_dashboard_render('admin_dok', 'dokumen_staff');
assert(strpos($html_dok, 'id="tab-kredit-tab"') === false, 'admin_dok must NOT have tab-kredit-tab');
assert(strpos($html_dok, 'id="tab-regulasi-tab"') !== false, 'admin_dok must have tab-regulasi-tab');
assert(strpos($html_dok, 'Upload Berkas SK') !== false, 'admin_dok must have Upload Berkas SK');
assert(strpos($html_dok, 'Upload Berkas SOP') !== false, 'admin_dok must have Upload Berkas SOP');
assert(strpos($html_dok, 'Upload Berkas Kredit') === false, 'admin_dok must NOT have Upload Berkas Kredit');
assert(strpos($html_dok, 'stat-card') === false, 'admin_dok must NOT have stat-card (only ti_admin)');
assert(strpos($html_dok, 'Aktivitas Disposisi Surat Terkini') === false, 'admin_dok must NOT have Aktivitas Disposisi');
assert(strpos($html_dok, 'Status Sistem & Lingkungan') === false, 'admin_dok must NOT have Status Sistem');
echo "PASS: Role admin_dok adaptive dashboard verified.\n";

// 2. Test Role: teller
$html_teller = simulate_dashboard_render('teller', 'teller_utama');
assert(strpos($html_teller, 'id="tab-kredit-tab"') !== false, 'teller must have tab-kredit-tab');
assert(strpos($html_teller, 'id="tab-regulasi-tab"') === false, 'teller must NOT have tab-regulasi-tab');
assert(strpos($html_teller, 'Regulasi (SK & SOP)') === false, 'teller must NOT have Regulasi tab');
assert(strpos($html_teller, 'Arsip SK') === false, 'teller must NOT have Arsip SK in Quick Access');
assert(strpos($html_teller, 'Arsip SOP') === false, 'teller must NOT have Arsip SOP in Quick Access');
assert(strpos($html_teller, 'Spesimen Tanda Tangan') !== false, 'teller Tab 1 must be titled Spesimen Tanda Tangan');
assert(strpos($html_teller, 'Upload Spesimen Tanda Tangan') !== false, 'teller must have Upload Spesimen');
assert(strpos($html_teller, 'Cek Spesimen Tanda Tangan') !== false, 'teller must have Cek Spesimen');
assert(strpos($html_teller, 'Upload Berkas Kredit') === false, 'teller must NOT have Upload Kredit');
assert(strpos($html_teller, 'Cek Berkas Kredit') === false, 'teller must NOT have Cek Kredit');
assert(strpos($html_teller, 'stat-card') === false, 'teller must NOT have stat-card (only ti_admin)');
assert(strpos($html_teller, 'Aktivitas Disposisi Surat Terkini') === false, 'teller must NOT have Aktivitas Disposisi');
assert(strpos($html_teller, 'Status Sistem & Lingkungan') === false, 'teller must NOT have Status Sistem');
echo "PASS: Role teller adaptive dashboard verified.\n";

// 3. Test Role: adminkredit
$html_kredit = simulate_dashboard_render('adminkredit', 'kredit_admin');
assert(strpos($html_kredit, 'id="tab-kredit-tab"') !== false, 'adminkredit must have tab-kredit-tab');
assert(strpos($html_kredit, 'id="tab-regulasi-tab"') !== false, 'adminkredit must have tab-regulasi-tab');
assert(strpos($html_kredit, 'Upload Berkas Kredit') !== false, 'adminkredit must have Upload Kredit');
assert(strpos($html_kredit, 'Cek Berkas Kredit') !== false, 'adminkredit must have Cek Kredit');
assert(strpos($html_kredit, 'Upload Spesimen Tanda Tangan') === false, 'adminkredit must NOT have Upload Spesimen');
assert(strpos($html_kredit, 'stat-card') === false, 'adminkredit must NOT have stat-card (only ti_admin)');
assert(strpos($html_kredit, 'Aktivitas Disposisi Surat Terkini') === false, 'adminkredit must NOT have Aktivitas Disposisi');
assert(strpos($html_kredit, 'Status Sistem & Lingkungan') === false, 'adminkredit must NOT have Status Sistem');
echo "PASS: Role adminkredit adaptive dashboard verified.\n";

// 4. Test Role: marketing
$html_mkt = simulate_dashboard_render('marketing', 'marketing_officer');
assert(strpos($html_mkt, 'id="tab-kredit-tab"') !== false, 'marketing must have tab-kredit-tab');
assert(strpos($html_mkt, 'id="tab-regulasi-tab"') === false, 'marketing must NOT have tab-regulasi-tab');
assert(strpos($html_mkt, 'Regulasi (SK & SOP)') === false, 'marketing must NOT have Regulasi tab');
assert(strpos($html_mkt, 'Arsip SK') === false, 'marketing must NOT have Arsip SK in Quick Access');
assert(strpos($html_mkt, 'Arsip SOP') === false, 'marketing must NOT have Arsip SOP in Quick Access');
assert(strpos($html_mkt, 'Pencarian Berkas Kredit') !== false, 'marketing must have centered Pencarian Berkas Kredit');
assert(strpos($html_mkt, 'Upload Berkas Kredit') === false, 'marketing must NOT have Upload Kredit');
assert(strpos($html_mkt, 'Akses upload dan kelola berkas regulasi dibatasi') === false, 'marketing must NOT see restrictive error message');
assert(strpos($html_mkt, 'stat-card') === false, 'marketing must NOT have stat-card (only ti_admin)');
assert(strpos($html_mkt, 'Aktivitas Disposisi Surat Terkini') === false, 'marketing must NOT have Aktivitas Disposisi');
assert(strpos($html_mkt, 'Status Sistem & Lingkungan') === false, 'marketing must NOT have Status Sistem');
echo "PASS: Role marketing adaptive dashboard verified.\n";

// 5. Test Role: ti_admin
$html_ti = simulate_dashboard_render('ti_admin', 'super_admin');
assert(strpos($html_ti, 'stat-card') !== false, 'ti_admin must have stat-card');
assert(strpos($html_ti, 'Total Pengguna') !== false, 'ti_admin must have Total Pengguna');
assert(strpos($html_ti, 'Upload Berkas Kredit') !== false, 'ti_admin must have Upload Kredit');
assert(strpos($html_ti, 'Upload Spesimen Tanda Tangan') !== false, 'ti_admin must have Upload Spesimen');
assert(strpos($html_ti, 'Upload Berkas SK') !== false, 'ti_admin must have Upload SK');
assert(strpos($html_ti, 'Upload Berkas SOP') !== false, 'ti_admin must have Upload SOP');
assert(strpos($html_ti, 'Kelola User') !== false, 'ti_admin must have Kelola User');
assert(strpos($html_ti, 'Aktivitas Disposisi Surat Terkini') !== false, 'ti_admin must have Aktivitas Disposisi');
assert(strpos($html_ti, 'Status Sistem & Lingkungan') !== false, 'ti_admin must have Status Sistem');
echo "PASS: Role ti_admin adaptive dashboard verified.\n";

echo "ALL ROLE-ADAPTIVE DASHBOARD TESTS PASSED (100%)\n";
