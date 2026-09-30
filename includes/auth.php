<?php
// ponytail: basic session hardening with Lax samesite; upgrade to Strict if cross-origin navigation is never needed.
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

function check_login($role = null)
{
    $timeout_duration = 3 * 60 * 60;
    $is_json = (!empty($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))
        || (!empty($_SERVER['CONTENT_TYPE']) && str_contains($_SERVER['CONTENT_TYPE'], 'application/json'));

    if (!isset($_SESSION['user'])) {
        if ($is_json) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }
        $login_url = file_exists('login.php') ? 'login.php' : '../login.php';
        header("Location: " . $login_url);
        exit();
    }

    if (isset($_SESSION['login_time'])) {
        $elapsed_time = time() - $_SESSION['login_time'];
        if ($elapsed_time > $timeout_duration) {
            session_unset();
            session_destroy();
            if ($is_json) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Session expired']);
                exit();
            }
            $login_url = file_exists('login.php') ? 'login.php?timeout=1' : '../login.php?timeout=1';
            header("Location: " . $login_url);
            exit();
        }
    }

    $_SESSION['login_time'] = time();

    if ($role && $_SESSION['user']['role'] !== $role) {
        if ($is_json) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Forbidden']);
            exit();
        }
        if ($_SESSION['user']['role'] === 'sekre') {
            header("Location: " . (file_exists('disposisi/disposisi.php') ? 'disposisi/disposisi.php' : 'disposisi.php'));
        } else {
            header("Location: " . (file_exists('dashboard.php') ? 'dashboard.php' : '../dashboard.php'));
        }
        exit();
    }
}

function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token)
{
    return !empty($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
