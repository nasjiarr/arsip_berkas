<?php
require_once 'includes/auth.php';

// Periksa apakah pengguna sudah login
if (isset($_SESSION['user'])) {
    // Jika sudah login, arahkan ke dashboard
    header("Location: public/dashboard.php");
    exit();
} else {
    // Jika belum login, arahkan ke halaman login
    header("Location: public/login.php");
    exit();
}
