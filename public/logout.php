<?php
// Mulai session
session_start();

// Hapus semua data session
session_unset();
session_destroy();

// Arahkan kembali ke halaman login
header("Location: login.php");
exit();
