<?php
// Test Suite: Theme Switcher (Dark & Light Mode)
echo "=== Running Theme Switcher Tests ===\n";

$base_dir = dirname(__DIR__);

// 1. Verify CSS dark mode rules
$css_content = file_get_contents($base_dir . '/public/assets/style.css');
assert(strpos($css_content, '[data-bs-theme="dark"]') !== false, 'style.css must contain [data-bs-theme="dark"] overrides');
assert(strpos($css_content, '[data-bs-theme="dark"] .table-disposisi thead th') !== false, 'style.css must support dark table header');
assert(strpos($css_content, '[data-bs-theme="dark"] .form-control') !== false, 'style.css must support dark form controls');
echo "PASS: Dark mode CSS variables and overrides verified.\n";

// 2. Verify sidebar.php has theme toggle element and persistent JS logic
$sidebar_content = file_get_contents($base_dir . '/includes/sidebar.php');
assert(strpos($sidebar_content, 'id="themeToggleBtn"') !== false, 'sidebar.php must contain theme toggle button');
assert(strpos($sidebar_content, 'app_theme') !== false, 'sidebar.php must sync with localStorage app_theme');
assert(strpos($sidebar_content, 'data-bs-theme') !== false, 'sidebar.php must set data-bs-theme attribute');
echo "PASS: Sidebar theme switcher markup and localStorage logic verified.\n";

// 3. Verify login.php has theme toggle support
$login_content = file_get_contents($base_dir . '/public/login.php');
assert(strpos($login_content, 'id="themeToggleBtn"') !== false, 'login.php must contain theme toggle button');
assert(strpos($login_content, 'data-bs-theme') !== false, 'login.php must support data-bs-theme');
echo "PASS: Login page theme toggle markup and script verified.\n";

echo "ALL THEME SWITCHER TESTS PASSED (100%)\n";
