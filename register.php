<?php
require_once 'config/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect($base_url . '/index.php');
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    // Validate input
    if (empty($username)) {
        $errors[] = 'Username harus diisi';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = 'Username hanya boleh berisi huruf, angka, dan underscore';
    } else {
        // Check if username exists
        $sql = "SELECT id FROM users WHERE username = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $errors[] = 'Username sudah digunakan';
            }
            $stmt->close();
        }
    }
    
    if (empty($password)) {
        $errors[] = 'Password harus diisi';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password minimal 6 karakter';
    } elseif ($password !== $confirm_password) {
        $errors[] = 'Konfirmasi password tidak cocok';
    }
    
    if (empty($fullname)) {
        $errors[] = 'Nama lengkap harus diisi';
    }
    
    if (empty($email)) {
        $errors[] = 'Email harus diisi';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid';
    } else {
        // Check if email exists
        $sql = "SELECT id FROM users WHERE email = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $errors[] = 'Email sudah terdaftar';
            }
            $stmt->close();
        }
    }
    
    // If no errors, insert new user
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $role = 'user'; // Default role is user
        
        $sql = "INSERT INTO users (username, password, fullname, email, role) VALUES (?, ?, ?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sssss", $username, $hashed_password, $fullname, $email, $role);
            if ($stmt->execute()) {
                $success = 'Pendaftaran berhasil! Silakan login.';
                // Clear form
                $username = $fullname = $email = '';
            } else {
                $errors[] = 'Terjadi kesalahan. Silakan coba lagi nanti.';
            }
            $stmt->close();
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Pendaftaran Akun Baru</h4>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" required>
                            <div class="form-text">Gunakan huruf, angka, dan underscore (_) saja</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fullname" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="fullname" name="fullname" 
                                   value="<?php echo isset($fullname) ? htmlspecialchars($fullname) : ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div class="form-text">Minimal 6 karakter</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Daftar</button>
                    </div>
                </form>
                
                <div class="text-center mt-3">
                    <p>Sudah punya akun? <a href="<?php echo $base_url; ?>/login.php">Login disini</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
