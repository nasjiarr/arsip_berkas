<?php
// Test Suite: Theme Switcher (Dark & Light Mode)
echo "=== Running Theme Switcher Tests ===\n";

$base_dir = dirname(__DIR__);

// 1. Verify CSS dark and light mode rules
$css_content = file_get_contents($base_dir . '/public/assets/style.css');
assert(strpos($css_content, '.sidebar') !== false, 'style.css must contain .sidebar rules');
assert(strpos($css_content, '[data-bs-theme="dark"] .sidebar') !== false, 'style.css must contain [data-bs-theme="dark"] .sidebar overrides');
assert(strpos($css_content, '[data-bs-theme="dark"] .table-disposisi thead th') !== false, 'style.css must support dark table header');
assert(strpos($css_content, '[data-bs-theme="dark"] .form-control') !== false, 'style.css must support dark form controls');
echo "PASS: Light and dark mode sidebar CSS rules verified.\n";

// 2. Verify sidebar.php has theme toggle element and persistent JS logic
$sidebar_content = file_get_contents($base_dir . '/includes/sidebar.php');
assert(strpos($sidebar_content, 'id="themeToggleBtn"') !== false, 'sidebar.php must contain theme toggle button');
assert(strpos($sidebar_content, 'app_theme') !== false, 'sidebar.php must sync with localStorage app_theme');
assert(strpos($sidebar_content, 'data-bs-theme') !== false, 'sidebar.php must set data-bs-theme attribute');
echo "PASS: Sidebar theme switcher markup and localStorage logic verified.\n";

// 3. Verify login.php has theme toggle support, no hotlinked assets, and loading state
$login_content = file_get_contents($base_dir . '/public/login.php');
assert(strpos($login_content, 'id="themeToggleBtn"') !== false, 'login.php must contain theme toggle button');
assert(strpos($login_content, 'data-bs-theme') !== false, 'login.php must support data-bs-theme');
assert(strpos($login_content, 'bankkulonprogo.co.id/bpr/wp-content') === false, 'login.php must not hotlink external background images');
assert(strpos($login_content, 'date(\'Y\')') !== false, 'login.php must have dynamic copyright year');
assert(strpos($login_content, 'btnSubmitLogin') !== false, 'login.php must have loading submit protection');
assert(strpos($login_content, 'batik-micro-light.svg') !== false, 'login.php must link to local batik micro light SVG asset');
assert(file_exists($base_dir . '/public/assets/images/batik-micro-light.svg'), 'batik-micro-light.svg must exist');
assert(file_exists($base_dir . '/public/assets/images/batik-micro-dark.svg'), 'batik-micro-dark.svg must exist');
echo "PASS: Login seamless micro-texture, theme toggle, and batik geblek verified.\n";

// 4. Verify Search Bar and Input Group Dark Theme Harmonization
assert(strpos($css_content, '[data-bs-theme="dark"] .input-group-text') !== false, 'style.css must contain dark theme .input-group-text overrides');
assert(strpos($css_content, '[data-bs-theme="dark"] .input-group:focus-within') !== false, 'style.css must contain dark theme input-group:focus-within harmonization');
assert(strpos($css_content, '[data-bs-theme="dark"] .btn-outline-secondary') !== false, 'style.css must contain dark theme .btn-outline-secondary styling');
assert(strpos($css_content, '[data-bs-theme="dark"] .form-control::placeholder') !== false, 'style.css must contain dark theme placeholder styling');
$disp_code_curr = file_get_contents($base_dir . '/public/disposisi/disposisi.php');
assert(strpos($disp_code_curr, 'input-group-text bg-white') === false, 'disposisi.php must eliminate hardcoded bg-white on search input group');
assert(strpos($disp_code_curr, 'input-group-text bg-transparent') !== false, 'disposisi.php must use bg-transparent on search icon');
echo "PASS: Search bar and input group dark theme harmonization verified.\n";

// 5. Verify Floating Label Placeholder Hidden in Dark Mode (No Duplicate Text)
assert(strpos($css_content, '[data-bs-theme="dark"] .form-floating > .form-control::placeholder') !== false, 'style.css must keep form-floating placeholder transparent in dark theme');
assert(strpos($login_content, '.form-floating > .form-control::placeholder') !== false, 'login.php must ensure form-floating placeholder is transparent');
echo "PASS: Floating label placeholder transparency in dark mode verified.\n";

echo "ALL THEME SWITCHER TESTS PASSED (100%)\n";
