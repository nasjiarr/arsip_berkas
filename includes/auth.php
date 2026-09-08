<?php
session_start();
function check_login($role = null)
{
    $timeout_duration = 3 * 60 * 60;

    if (!isset($_SESSION['user'])) {
        header("Location: login.php");
        exit();
    }

    if (isset($_SESSION['login_time'])) {
        $elapsed_time = time() - $_SESSION['login_time'];
        if ($elapsed_time > $timeout_duration) {
            session_unset();
            session_destroy();
            header("Location: login.php?timeout=1");
            exit();
        }
    }

    $_SESSION['login_time'] = time();

    if ($role && $_SESSION['user']['role'] !== $role) {
        if ($_SESSION['user']['role'] === 'sekre') {
            header("Location: ../public/disposisi/disposisi.php");
        } else {
            header("Location: dashboard.php");
        }
        exit();
    }
}
