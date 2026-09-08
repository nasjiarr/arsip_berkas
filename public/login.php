<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

$error = null;

// Proses login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Cegah serangan XSS
    $username = htmlspecialchars($username);

    // Query untuk mencari user berdasarkan username
    $query = "SELECT * FROM users WHERE username = :username";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();

    // Cek apakah user ditemukan
    if ($stmt->rowCount() === 1) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (password_verify($password, $user['password'])) {
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
    <style>
        body {
            height: 100vh;
            display: flex;
            align-items: center;
            padding-top: 40px;
            padding-bottom: 40px;
            background-image: url(https://bankkulonprogo.co.id/bpr/wp-content/uploads/2021/06/gedunghead2.jpg);
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            z-index: -1;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
            box-shadow: inset 0 0 0 2000px rgba(0, 0, 0, 0.5);

        }

        .form-signin {
            background: rgba(255, 255, 255, 0.95);
            width: 90%;
            /* Changed from 100% */
            max-width: 400px;
            padding: 2rem;
            margin: auto;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .form-signin .form-floating {
            margin-bottom: 1rem;
        }

        .form-signin input {
            height: 50px;
            border-radius: 8px;
            border: 1px solid #ced4da;
            transition: all 0.3s ease;
        }

        .form-signin input:focus {
            border-color: #4A55FF;
            box-shadow: 0 0 0 0.2rem rgba(74, 85, 255, 0.25);
        }

        .logo {
            width: 80px;
            height: 80px;
            margin-bottom: 1.5rem;
            color: #4A55FF;
            animation: float 2s ease-in-out infinite;
        }

        @keyframes float {
            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
            }

            100% {
                transform: translateY(0px);
            }
        }

        .btn-primary {
            background: #4A55FF;
            border: none;
            height: 50px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: #3440FF;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(74, 85, 255, 0.4);
        }

        .toggle-password {
            border: none;
            background: transparent;
            color: #4A55FF;
            font-size: 0.9rem;
            padding: 0;
            margin-top: 0.5rem;
            transition: color 0.3s ease;
        }

        .toggle-password:hover {
            color: #3440FF;
        }

        .alert {
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .form-floating label {
            color: #6c757d;
        }

        .copyright {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
        }

        h1 {
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }

        /* Add these new media queries for mobile responsiveness */
        @media (max-width: 576px) {
            .form-signin {
                width: 95%;
                padding: 1.5rem;
                margin: 1rem auto;
            }

            .logo {
                width: 60px;
                /* Smaller logo for mobile */
                height: 60px;
                margin-bottom: 1rem;
            }

            h1 {
                font-size: 1.75rem;
                margin-bottom: 1rem;
            }

            .form-signin input {
                height: 45px;
                /* Slightly smaller input fields */
            }

            .btn-primary {
                height: 45px;
            }

            .copyright {
                font-size: 0.8rem;
            }
        }

        /* Add this for better padding on very small devices */
        @media (max-width: 320px) {
            .form-signin {
                padding: 1rem;
            }

            .form-floating {
                margin-bottom: 0.75rem;
            }
        }
    </style>
</head>

<body class="text-center bg-light">
    <main class="form-signin">
        <form method="POST">
            <svg class="logo" viewBox="0 0 24 24">
                <path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z" />
            </svg>

            <h1>Welcome</h1>

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

            <div class="form-floating">
                <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
                <label for="username">Username</label>
            </div>

            <div class="form-floating">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                <label for="password">Password</label>
            </div>

            <button type="button" class="toggle-password btn btn-link" id="togglePassword">Lihat Password</button>

            <button class="w-100 btn btn-primary mt-3" type="submit">Masuk</button>

            <p class="mt-4 copyright text-dark">&copy; 2025</p>
        </form>
    </main>

    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordField = document.getElementById('password');
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            this.textContent = type === 'password' ? 'Lihat Password' : 'Sembunyikan Password';
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>