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
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('Token keamanan tidak valid.');
    }

    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $new_user_role = $_POST['role'];

    // Tambahkan password hashing
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        $query = "INSERT INTO users (username, password, role) VALUES (:username, :password, :role)";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);
        $stmt->bindParam(':role', $new_user_role, PDO::PARAM_STR);
        $stmt->execute();
        set_flash_message('success', 'User ' . htmlspecialchars($username) . ' berhasil ditambahkan!');
        header("Location: add_user.php");
        exit();
    } catch (PDOException $e) {
        set_flash_message('danger', 'Terjadi kesalahan: ' . $e->getMessage());
        header("Location: add_user.php");
        exit();
    }
}

// Handle request untuk menghapus user via POST + CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('Token keamanan tidak valid.');
    }

    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        set_flash_message('danger', 'ID user tidak valid.');
    } elseif ($id === (int)($_SESSION['user']['id'] ?? 0)) {
        set_flash_message('warning', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif.');
    } else {
        try {
            $query = "DELETE FROM users WHERE id = :id";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            set_flash_message('success', 'User berhasil dihapus.');
        } catch (PDOException $e) {
            set_flash_message('danger', 'Gagal menghapus user: ' . $e->getMessage());
        }
    }
    header("Location: add_user.php");
    exit();
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <?php require_once '../includes/sidebar.php'; ?>

    <div class="content">
        <div class="container-fluid">
            <h2 class="mb-4">Kelola User</h2>

            <div class="row">
                <div class="col-12 col-lg-6 mb-4">
                    <!-- Add User Card -->
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">Tambah User</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
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
                                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?');">
                                                        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                                                        <input type="hidden" name="id" value="<?php echo (int)$user['id']; ?>">
                                                        <button type="submit" name="delete_user" class="btn btn-danger btn-sm">
                                                            <i class="bi bi-trash2"></i> Hapus
                                                        </button>
                                                    </form>
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
</body>

</html>