<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

$error = null;

// Proses login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = "Token sesi tidak valid. Silakan coba lagi.";
    } else {
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        // Query untuk mencari user berdasarkan username
        $query = "SELECT * FROM users WHERE username = :username";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();

        // Cek apakah user ditemukan
        if ($stmt->rowCount() === 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'role' => $user['role']
                ];
                $_SESSION['login_time'] = time();

                // Add role-based redirection
                if ($user['role'] === 'sekre') {
                    header("Location: disposisi/disposisi.php");
                } else {
                    header("Location: dashboard.php");
                }
                exit();
            }
        }

        // Error jika username/password salah
        $error = "Username atau password salah!";
    }
}

// Tampilkan pesan logout jika ada
$logout_message = isset($_GET['logout']) ? "Anda telah berhasil logout." : null;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Informasi Bank Kulon Progo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body.login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            background-color: #f8fafc !important;
            background-image: 
                radial-gradient(circle 600px at 50% 45%, rgba(2, 132, 199, 0.09) 0%, transparent 70%),
                url("data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M20 0L40 20L20 40L0 20Z' fill='none' stroke='rgba(148,163,184,0.22)' stroke-width='1'/%3E%3C/svg%3E") !important;
            background-size: 100% 100%, 40px 40px !important;
            background-repeat: no-repeat, repeat !important;
            transition: background-color 0.3s ease;
        }

        [data-bs-theme="dark"] body.login-page {
            background-color: #0b0f19 !important;
            background-image: 
                radial-gradient(circle 600px at 50% 45%, rgba(2, 132, 199, 0.22) 0%, transparent 75%),
                url("data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M20 0L40 20L20 40L0 20Z' fill='none' stroke='rgba(56,189,248,0.14)' stroke-width='1'/%3E%3C/svg%3E") !important;
            background-size: 100% 100%, 40px 40px !important;
            background-repeat: no-repeat, repeat !important;
        }

        .form-signin {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            width: 100%;
            max-width: 420px;
            padding: 2.25rem;
            border-radius: 16px;
            border: 1px solid rgba(226, 232, 240, 0.9);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.04);
            transition: all 0.3s ease;
        }

        [data-bs-theme="dark"] .form-signin {
            background-color: rgba(15, 23, 42, 0.92) !important;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(56, 189, 248, 0.22) !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7), 0 0 25px rgba(2, 132, 199, 0.12) !important;
            color: #e2e8f0;
        }

        .brand-badge {
            width: 64px;
            height: 64px;
            background: rgba(2, 132, 199, 0.12);
            color: var(--brand-primary, #0284c7);
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        [data-bs-theme="dark"] .brand-badge {
            background: rgba(2, 132, 199, 0.2);
            color: #38bdf8;
        }

        .brand-title {
            color: var(--slate-900, #0f172a);
            font-weight: 700;
            letter-spacing: -0.3px;
        }

        [data-bs-theme="dark"] .brand-title {
            color: #f8fafc !important;
        }

        .form-signin .form-floating {
            margin-bottom: 1rem;
        }

        .form-signin input {
            height: 50px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            transition: all 0.2s ease;
        }

        .form-signin input:focus {
            border-color: var(--brand-primary, #0284c7);
            box-shadow: 0 0 0 0.25rem rgba(2, 132, 199, 0.2);
        }

        [data-bs-theme="dark"] .form-signin input {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }

        [data-bs-theme="dark"] .form-signin input:focus {
            background-color: #1e293b !important;
            border-color: var(--brand-primary, #0284c7) !important;
            color: #ffffff !important;
            box-shadow: 0 0 0 0.25rem rgba(2, 132, 199, 0.25) !important;
        }

        [data-bs-theme="dark"] .form-floating label {
            color: #94a3b8 !important;
        }

        .btn-login {
            background-color: var(--brand-primary, #0284c7);
            border-color: var(--brand-primary, #0284c7);
            color: #ffffff;
            height: 48px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-login:hover {
            background-color: var(--brand-primary-hover, #0369a1);
            border-color: var(--brand-primary-hover, #0369a1);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.35);
        }

        .alert {
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .form-floating label {
            color: #64748b;
        }

        .copyright {
            color: var(--slate-500, #64748b);
            font-size: 0.8rem;
        }

        [data-bs-theme="dark"] .copyright {
            color: #94a3b8 !important;
        }

        @media (max-width: 576px) {
            .form-signin {
                padding: 1.5rem;
            }
        }
    </style>
    <script>
        (function() {
            const stored = localStorage.getItem('app_theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-bs-theme', stored);
        })();
    </script>
</head>

<body class="text-center login-page">
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 1050;">
        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill d-flex align-items-center gap-1 shadow-sm bg-body bg-opacity-75" id="themeToggleBtn" title="Ganti Tema">
            <i class="bi bi-moon-stars" id="themeIcon"></i>
            <span id="themeLabel" class="small">Tema</span>
        </button>
    </div>

    <main class="form-signin">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
            <div class="brand-badge mb-3">
                <i class="bi bi-bank fs-2"></i>
            </div>

            <h4 class="brand-title mb-1">Sistem Informasi Arsip Berkas</h4>
            <p class="text-muted small mb-4">PT BPR Bank Kulon Progo</p>

            <?php if ($logout_message): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo $logout_message; ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="username" name="username" placeholder="Username" required autofocus>
                <label for="username">Username</label>
            </div>

            <div class="form-floating position-relative mb-3">
                <input type="password" class="form-control pe-5" id="password" name="password" placeholder="Password" required autocomplete="current-password">
                <label for="password">Password</label>
                <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-muted text-decoration-none pe-3 z-3" id="togglePassword" aria-label="Lihat password" style="border:none; background:transparent;">
                    <i class="bi bi-eye fs-5" id="togglePasswordIcon"></i>
                </button>
            </div>

            <button class="w-100 btn btn-login mt-2" type="submit" id="btnSubmitLogin">Masuk</button>

            <p class="mt-4 copyright mb-0">&copy; <?= date('Y') ?> PT BPR Bank Kulon Progo</p>
        </form>
    </main>

    <script>
        const toggleBtn = document.getElementById('togglePassword');
        const passwordField = document.getElementById('password');
        const toggleIcon = document.getElementById('togglePasswordIcon');

        if (toggleBtn && passwordField && toggleIcon) {
            toggleBtn.addEventListener('click', function() {
                const isPassword = passwordField.getAttribute('type') === 'password';
                passwordField.setAttribute('type', isPassword ? 'text' : 'password');
                toggleIcon.className = isPassword ? 'bi bi-eye-slash fs-5' : 'bi bi-eye fs-5';
            });
        }

        // Prevent double submit with loading state
        const loginForm = document.querySelector('form');
        const submitBtn = document.getElementById('btnSubmitLogin');
        if (loginForm && submitBtn) {
            loginForm.addEventListener('submit', function() {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Memverifikasi...';
            });
        }

        // Theme toggle logic
        const themeBtn = document.getElementById('themeToggleBtn');
        const themeIcon = document.getElementById('themeIcon');
        const themeLabel = document.getElementById('themeLabel');

        function updateLoginThemeUI(t) {
            if (themeIcon && themeLabel) {
                themeIcon.className = t === 'dark' ? 'bi bi-sun-fill text-warning' : 'bi bi-moon-stars';
                themeLabel.textContent = t === 'dark' ? 'Terang' : 'Gelap';
            }
        }

        const initialTheme = document.documentElement.getAttribute('data-bs-theme') || 'light';
        updateLoginThemeUI(initialTheme);

        if (themeBtn) {
            themeBtn.addEventListener('click', function() {
                const cur = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
                document.documentElement.setAttribute('data-bs-theme', cur);
                localStorage.setItem('app_theme', cur);
                updateLoginThemeUI(cur);
            });
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>