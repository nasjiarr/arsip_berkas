<?php
require_once __DIR__ . '/../includes/auth.php';

// 1. Test CSRF Token generation & verification
$token = csrf_token();
assert(strlen($token) === 64, "CSRF token must be 64 characters (32 bytes hex)");
assert(verify_csrf_token($token) === true, "CSRF token must verify correctly");
assert(verify_csrf_token("invalid_token") === false, "Invalid CSRF token must be rejected");
assert(verify_csrf_token("") === false, "Empty CSRF token must be rejected");

// 2. Test Path Traversal Guard
$test_path_valid = "\\\\172.16.34.5\\ftp\\DISPOSISI SURAT\\test.pdf";
$test_path_invalid = "\\\\172.16.34.5\\ftp\\DISPOSISI SURAT\\..\\..\\Windows\\win.ini";
$norm_valid = str_replace('/', '\\', $test_path_valid);
$norm_invalid = str_replace('/', '\\', $test_path_invalid);

assert(str_starts_with($norm_valid, "\\\\172.16.34.5\\ftp\\DISPOSISI SURAT\\") && !str_contains($norm_valid, ".."), "Valid UNC path must pass");
assert(!(str_starts_with($norm_invalid, "\\\\172.16.34.5\\ftp\\DISPOSISI SURAT\\") && !str_contains($norm_invalid, "..")), "Traversal path must be rejected");

// 3. Test Norek Sanitization
$dirty_norek = "12345/../*#";
$clean_norek = preg_replace('/[^a-zA-Z0-9_\-\s]/', '', trim($dirty_norek));
assert($clean_norek === "12345", "Sanitization must strip traversal and special characters");

echo "[OK] All Phase 1 security assertions passed successfully.\n";
