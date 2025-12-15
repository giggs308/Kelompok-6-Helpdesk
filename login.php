<?php
require_once 'config/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect($base_url . '/index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi';
    } else {
        $sql = "SELECT id, username, password, role FROM users WHERE username = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $username);
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($id, $username, $hashed_password, $role);
                    if ($stmt->fetch()) {
                        if (password_verify($password, $hashed_password)) {
                            // Password is correct, start a new session
                            $_SESSION['user_id'] = $id;
                            $_SESSION['username'] = $username;
                            $_SESSION['user_role'] = $role;
                            
                            // Redirect based on role
                            if ($role === 'admin') {
                                setMessage('success', 'Login berhasil! Selamat datang di Dashboard Admin.');
                                redirect($base_url . '/admin/');
                            } else {
                                setMessage('success', 'Login berhasil!');
                                redirect($base_url . '/index.php');
                            }
                        } else {
                            $error = 'Password yang Anda masukkan salah.';
                        }
                    }
                } else {
                    $error = 'Username tidak ditemukan.';
                }
            } else {
                $error = 'Terjadi kesalahan. Silakan coba lagi nanti.';
            }
            $stmt->close();
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Login</h4>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>
                </form>
                
                <div class="text-center mt-3">
                    <p>Belum punya akun? <a href="<?php echo $base_url; ?>/register.php">Daftar disini</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
