<?php
// ponytail: basic session hardening with Lax samesite; upgrade to Strict if cross-origin navigation is never needed.
if (session_status() === PHP_SESSION_NONE) {
    if (!headers_sent()) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'domain'   => '',
            'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        session_start();
    } else {
        @session_start();
    }
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

function set_flash_message(string $type, string $message): void
{
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

function get_flash_message(): ?array
{
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    // Backward compatibility with legacy $_SESSION['success'] and $_SESSION['error']
    if (isset($_SESSION['success'])) {
        $msg = $_SESSION['success'];
        unset($_SESSION['success']);
        return ['type' => 'success', 'message' => $msg];
    }
    if (isset($_SESSION['error'])) {
        $msg = $_SESSION['error'];
        unset($_SESSION['error']);
        return ['type' => 'danger', 'message' => $msg];
    }
    return null;
}

function render_flash_toast(): void
{
    $flash = get_flash_message();
    if (!$flash) {
        return;
    }

    $type = htmlspecialchars($flash['type'] ?? 'info');
    $msg = htmlspecialchars($flash['message'] ?? '');

    $bg_class = match ($type) {
        'success' => 'bg-success text-white',
        'danger', 'error' => 'bg-danger text-white',
        'warning' => 'bg-warning text-dark',
        default => 'bg-primary text-white'
    };

    $icon_class = match ($type) {
        'success' => 'bi-check-circle-fill',
        'danger', 'error' => 'bi-exclamation-triangle-fill',
        'warning' => 'bi-exclamation-circle-fill',
        default => 'bi-info-circle-fill'
    };

    echo '
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100;">
        <div id="flashToast" class="toast align-items-center ' . $bg_class . ' border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2">
                    <i class="bi ' . $icon_class . ' fs-5"></i>
                    <span>' . $msg . '</span>
                </div>
                <button type="button" class="btn-close ' . ($type === 'warning' ? '' : 'btn-close-white') . ' me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var toastEl = document.getElementById("flashToast");
            if (toastEl && typeof bootstrap !== "undefined") {
                var toast = new bootstrap.Toast(toastEl, { delay: 4000 });
                toast.show();
            }
        });
    </script>';
}
