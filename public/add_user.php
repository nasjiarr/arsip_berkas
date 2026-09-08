<?php
require_once '../includes/config.php'; // Pastikan file ini sudah menginisialisasi $pdo
require_once '../includes/auth.php';
check_login('ti_admin'); // Hanya bisa diakses oleh admin TI

$current_user_role = $_SESSION['user']['role']; // Pastikan session sudah diset saat login

// Definisikan mapping role
$role_names = [
    'adminkredit' => 'Admin Kredit',
    'marketing' => 'Marketing',
    'ti_admin' => 'Admin TI',
    'teller' => 'Teller',
    'admin_dok' => 'Admin Dokumen',
    'sekre' => 'Sekretaris'
];

// Handle form submission untuk menambah user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $new_user_role = $_POST['role'];

    // Tambahkan password hashing
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        $query = "INSERT INTO users (username, password, role) VALUES (:username, :password, :role)";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR); // Gunakan hashed_password
        $stmt->bindParam(':role', $new_user_role, PDO::PARAM_STR);
        $stmt->execute();
        $message = "User berhasil ditambahkan!";
    } catch (PDOException $e) {
        $message = "Terjadi kesalahan: " . $e->getMessage();
    }
}

// Handle request untuk menghapus user
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $query = "DELETE FROM users WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        header("Location: add_user.php");
        exit();
    } catch (PDOException $e) {
        $message = "Gagal menghapus user: " . $e->getMessage();
    }
}

// Ambil daftar user dari database
$query = "SELECT id, username, role FROM users";
$stmt = $pdo->query($query);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="assets/style.css">
    <style>
        /* Main Content */
        .main-content {
            margin-left: 280px;
            padding: 2rem;
            min-height: 100vh;
            transition: all 0.3s ease;
        }

        /* Cards */
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1.5rem;
        }

        /* Table Responsive */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* Mobile Styles */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                width: 250px;
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .navbar-toggle {
                display: block;
            }

            .sidebar-backdrop.show {
                display: block;
            }
        }

        /* Backdrop */
        .sidebar-backdrop {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        /* Toggle Button */
        .navbar-toggle {
            display: none;
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 1001;
            padding: 0.5rem;
            border: none;
            background: #fff;
            border-radius: 0.25rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
    </style>
</head>

<body>
    <button class="navbar-toggle" id="sidebarToggle">
        <i class="bi bi-list"></i>
    </button>

    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <!-- Sidebar -->
    <div class="sidebar bg-white p-3" id="sidebar">
        <div class="d-flex align-items-center mb-4">
            <i class="bi bi-bank fs-2 text-primary me-2"></i>
            <h4 class="mb-0">Sistem Informasi Bank Kulon Progo</h4>
        </div>

        <hr>

        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active text-white bg-primary' : 'text-dark'; ?>" href="dashboard.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle text-dark" href="#" id="dropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-list-check"></i>
                    Cek Berkas
                </a>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                    <li><a class="dropdown-item <?php echo basename($_SERVER['PHP_SELF']) == 'cek_sk.php' ? 'active text-white bg-primary' : 'text-dark'; ?>" href="cek_sk.php">Cek SK</a></li>
                    <li><a class="dropdown-item <?php echo basename($_SERVER['PHP_SELF']) == 'cek_sop.php' ? 'active text-white bg-primary' : 'text-dark'; ?>" href="cek_sop.php">Cek SOP</a></li>
                    <?php if ($current_user_role !== 'sekre'): ?>
                        <li><a class="dropdown-item <?php echo basename($_SERVER['PHP_SELF']) == 'disposisi/disposisi.php' ? 'active text-white bg-primary' : 'text-dark'; ?>" href="disposisi/disposisi.php">Disposisi Surat Masuk</a></li>
                        <li><a class="dropdown-item <?php echo basename($_SERVER['PHP_SELF']) == 'disposisi/disposisi_keluar.php' ? 'active text-white bg-primary' : 'text-dark'; ?>" href="disposisi/disposisi_keluar.php">Disposisi Surat Keluar</a></li>
                    <?php endif; ?>
                </ul>
            </li>
            <?php if ($current_user_role === 'ti_admin'): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'add_user.php' ? 'active text-white bg-primary' : 'text-dark'; ?>" href="add_user.php">
                        <i class="bi bi-person-plus"></i> Kelola User
                    </a>
                </li>
            <?php endif; ?>
            <li class="nav-item">
                <a class="nav-link text-danger fw-bold" href="logout.php">
                    <i class="bi bi-box-arrow-right text-danger"></i> Keluar
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <h2 class="mb-4">Kelola User</h2>

            <?php if (isset($message)): ?>
                <div class="alert alert-info mb-4"><?php echo $message; ?></div>
            <?php endif; ?>

            <div class="row">
                <div class="col-12 col-lg-6 mb-4">
                    <!-- Add User Card -->
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">Tambah User</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" name="username" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="role" class="form-label">Role</label>
                                    <select class="form-select" id="role" name="role" required>
                                        <option value="" disabled selected>Pilih Role</option>
                                        <?php foreach ($role_names as $value => $label): ?>
                                            <option value="<?php echo htmlspecialchars($value); ?>">
                                                <?php echo htmlspecialchars($label); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-success" name="add_user">Tambah User</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <!-- User List Card -->
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="card-title mb-0">Daftar User</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered mb-0">
                                    <!-- Table content here -->
                                    <thead class="table-dark">
                                        <tr>

                                            <th>Username</th>
                                            <th>Role</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user): ?>
                                            <tr>

                                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                                <td><?php echo htmlspecialchars($role_names[$user['role']] ?? $user['role']); ?></td>
                                                <td>
                                                    <a href="add_user.php?delete=<?php echo $user['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus user ini?');">
                                                        <i class="bi bi-trash2"></i> Hapus
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarBackdrop = document.getElementById('sidebarBackdrop');

        function toggleSidebar() {
            sidebar.classList.toggle('show');
            sidebarBackdrop.classList.toggle('show');

            // Sembunyikan tombol hanya jika layar ≤ 768px
            if (window.innerWidth <= 768) {
                sidebarToggle.style.display = sidebar.classList.contains('show') ? 'none' : 'block';
            }
        }

        sidebarToggle.addEventListener('click', toggleSidebar);
        sidebarBackdrop.addEventListener('click', toggleSidebar);

        // Menampilkan kembali tombol saat sidebar ditutup atau layar diperbesar
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                sidebar.classList.remove('show');
                sidebarBackdrop.classList.remove('show');
                sidebarToggle.style.display = "block"; // Pastikan tombol selalu muncul di desktop
            } else if (!sidebar.classList.contains('show')) {
                sidebarToggle.style.display = "block"; // Jika sidebar tertutup di mobile, tampilkan kembali tombol
            }
        });
    </script>
</body>

</html>