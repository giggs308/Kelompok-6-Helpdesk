<?php
require_once '../config/config.php';

// Redirect if not logged in or if admin
if (!isLoggedIn()) {
    setMessage('error', 'Silakan login terlebih dahulu.');
    redirect($base_url . '/login.php');
}

// Prevent admin from creating tickets
if (isAdmin()) {
    setMessage('error', 'Admin tidak dapat membuat tiket baru.');
    redirect($base_url . '/admin/');
}

$errors = [];
$success = '';

// Get categories for dropdown
$categories = [];
$sql = "SELECT id, name FROM categories ORDER BY name";
if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    $result->free();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and process form data
    $title = trim($_POST['title'] ?? '');
    $category_id = $_POST['category_id'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $priority = $_POST['priority'] ?? 'Medium';
    
    // Validate input
    if (empty($title)) {
        $errors[] = 'Judul tiket harus diisi';
    }
    
    if (empty($category_id) || !is_numeric($category_id)) {
        $errors[] = 'Kategori harus dipilih';
    }
    
    if (empty($description)) {
        $errors[] = 'Deskripsi masalah harus diisi';
    } elseif (strlen($description) < 10) {
        $errors[] = 'Deskripsi masalah minimal 10 karakter';
    }
    
    // If no errors, insert the ticket
    if (empty($errors)) {
        $user_id = $_SESSION['user_id'];
        $status = 'Open';
        
        $sql = "INSERT INTO tickets (user_id, category_id, title, description, status, priority) 
                VALUES (?, ?, ?, ?, ?, ?)";
                
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("iissss", $user_id, $category_id, $title, $description, $status, $priority);
            
            if ($stmt->execute()) {
                $ticket_id = $stmt->insert_id;
                $stmt->close();
                
                // Set success message and redirect to ticket view
                setMessage('success', 'Tiket berhasil dibuat! Nomor tiket Anda: ' . $ticket_id);
                redirect($base_url . '/tickets/view.php?id=' . $ticket_id);
            } else {
                $errors[] = 'Terjadi kesalahan. Silakan coba lagi nanti.';
            }
        } else {
            $errors[] = 'Terjadi kesalahan. Silakan coba lagi nanti.';
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Buat Tiket Baru</h4>
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
                
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                    <div class="mb-3">
                        <label for="title" class="form-label">Judul Tiket <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" required 
                               value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
                        <div class="form-text">Jelaskan masalah Anda secara singkat</div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="category_id" class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">-- Pilih Kategori --</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" 
                                        <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="priority" class="form-label">Prioritas</label>
                            <select class="form-select" id="priority" name="priority">
                                <option value="Low" <?php echo (isset($_POST['priority']) && $_POST['priority'] === 'Low') ? 'selected' : ''; ?>>Rendah</option>
                                <option value="Medium" <?php echo (!isset($_POST['priority']) || (isset($_POST['priority']) && $_POST['priority'] === 'Medium')) ? 'selected' : ''; ?>>Sedang</option>
                                <option value="High" <?php echo (isset($_POST['priority']) && $_POST['priority'] === 'High') ? 'selected' : ''; ?>>Tinggi</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi Masalah <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="description" name="description" rows="8" required><?php 
                            echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; 
                        ?></textarea>
                        <div class="form-text">Jelaskan masalah Anda secara rinci. Sertakan langkah-langkah yang sudah dicoba.</div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="<?php echo $base_url; ?>" class="btn btn-outline-secondary me-md-2">Batal</a>
                        <button type="submit" class="btn btn-primary">Buat Tiket</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 mt-4 mt-lg-0">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">Tips Membuat Tiket yang Baik</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Gunakan judul yang deskriptif</li>
                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Jelaskan masalah secara rinci</li>
                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Sertakan pesan error jika ada</li>
                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Tuliskan langkah-langkah yang sudah dicoba</li>
                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Pilih kategori yang sesuai</li>
                </ul>
                <div class="alert alert-info">
                    <small>
                        <i class="fas fa-info-circle me-1"></i> 
                        Tim dukungan kami akan menanggapi tiket Anda secepatnya. Waktu respons bervariasi tergantung pada volume tiket dan prioritas.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
