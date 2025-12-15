<?php
require_once '../config/config.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    setMessage('error', 'Silakan login terlebih dahulu.');
    redirect($base_url . '/login.php');
}

// Check if ticket ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    setMessage('error', 'ID tiket tidak valid.');
    redirect($base_url . '/tickets/index.php');
}

$ticket_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Get ticket details
$ticket = null;
$sql = "SELECT t.*, c.name as category_name, u.username, u.fullname, u.email 
        FROM tickets t 
        JOIN categories c ON t.category_id = c.id 
        JOIN users u ON t.user_id = u.id 
        WHERE t.id = ? AND (t.user_id = ? OR ? = (SELECT id FROM users WHERE role = 'admin' LIMIT 1))";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("iii", $ticket_id, $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $ticket = $result->fetch_assoc();
    } else {
        $stmt->close();
        setMessage('error', 'Tiket tidak ditemukan atau Anda tidak memiliki akses.');
        redirect($base_url . '/tickets/index.php');
    }
    $stmt->close();
}

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $comment = trim($_POST['comment']);
    
    if (!empty($comment)) {
        $sql = "INSERT INTO ticket_comments (ticket_id, user_id, comment) VALUES (?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("iis", $ticket_id, $user_id, $comment);
            if ($stmt->execute()) {
                // Update ticket's updated_at timestamp
                $update_sql = "UPDATE tickets SET updated_at = NOW() WHERE id = ?";
                if ($update_stmt = $conn->prepare($update_sql)) {
                    $update_stmt->bind_param("i", $ticket_id);
                    $update_stmt->execute();
                    $update_stmt->close();
                }
                
                setMessage('success', 'Komentar berhasil ditambahkan.');
                // Redirect to prevent form resubmission
                redirect($base_url . "/tickets/view.php?id=" . $ticket_id);
            } else {
                $error = 'Gagal menambahkan komentar. Silakan coba lagi.';
            }
            $stmt->close();
        }
    } else {
        $error = 'Komentar tidak boleh kosong.';
    }
}

// Handle status update (for admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status']) && isAdmin()) {
    $new_status = $_POST['status'];
    $valid_statuses = ['Open', 'Diproses', 'Selesai'];
    
    if (in_array($new_status, $valid_statuses)) {
        $sql = "UPDATE tickets SET status = ?, updated_at = NOW() WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("si", $new_status, $ticket_id);
            if ($stmt->execute()) {
                setMessage('success', 'Status tiket berhasil diperbarui.');
                // Redirect to prevent form resubmission
                redirect($base_url . "/tickets/view.php?id=" . $ticket_id);
            } else {
                $error = 'Gagal memperbarui status tiket.';
            }
            $stmt->close();
        }
    } else {
        $error = 'Status tidak valid.';
    }
}

// Get ticket comments
$comments = [];
$sql = "SELECT tc.*, u.username, u.role 
        FROM ticket_comments tc 
        JOIN users u ON tc.user_id = u.id 
        WHERE tc.ticket_id = ? 
        ORDER BY tc.created_at ASC";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $ticket_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $comments[] = $row;
    }
    $stmt->close();
}
?>

<?php include '../includes/header.php'; ?>

<div class="row">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo $base_url; ?>/tickets">Daftar Tiket</a></li>
                <li class="breadcrumb-item active" aria-current="page">Tiket #<?php echo $ticket['id']; ?></li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><?php echo htmlspecialchars($ticket['title']); ?></h5>
                <div>
                    <span class="badge 
                        <?php 
                        $status_class = '';
                        if ($ticket['status'] === 'Open') $status_class = 'bg-danger';
                        elseif ($ticket['status'] === 'Diproses') $status_class = 'bg-warning text-dark';
                        elseif ($ticket['status'] === 'Selesai') $status_class = 'bg-success';
                        echo $status_class;
                        ?>">
                        <?php echo $ticket['status']; ?>
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="ticket-meta mb-4">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Dibuat oleh:</strong> <?php echo htmlspecialchars($ticket['fullname']); ?></p>
                            <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($ticket['email']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Kategori:</strong> <?php echo htmlspecialchars($ticket['category_name']); ?></p>
                            <p class="mb-1"><strong>Prioritas:</strong> 
                                <span class="badge 
                                    <?php 
                                    $priority_class = '';
                                    if ($ticket['priority'] === 'High') $priority_class = 'bg-danger';
                                    elseif ($ticket['priority'] === 'Medium') $priority_class = 'bg-warning text-dark';
                                    elseif ($ticket['priority'] === 'Low') $priority_class = 'bg-info';
                                    echo $priority_class;
                                    ?>">
                                    <?php echo $ticket['priority']; ?>
                                </span>
                            </p>
                            <p class="mb-1"><strong>Dibuat pada:</strong> <?php echo date('d M Y H:i', strtotime($ticket['created_at'])); ?></p>
                            <?php if ($ticket['updated_at'] !== $ticket['created_at']): ?>
                                <p class="mb-1"><strong>Diperbarui:</strong> <?php echo date('d M Y H:i', strtotime($ticket['updated_at'])); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="ticket-description mb-4">
                    <h6>Deskripsi Masalah:</h6>
                    <div class="p-3 bg-light rounded">
                        <?php echo nl2br(htmlspecialchars($ticket['description'])); ?>
                    </div>
                </div>
                
                <?php if (isAdmin()): ?>
                    <div class="ticket-actions mb-4">
                        <h6>Ubah Status Tiket:</h6>
                        <form method="post" class="row g-2">
                            <div class="col-md-6">
                                <select name="status" class="form-select">
                                    <option value="Open" <?php echo $ticket['status'] === 'Open' ? 'selected' : ''; ?>>Open</option>
                                    <option value="Diproses" <?php echo $ticket['status'] === 'Diproses' ? 'selected' : ''; ?>>Diproses</option>
                                    <option value="Selesai" <?php echo $ticket['status'] === 'Selesai' ? 'selected' : ''; ?>>Selesai</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Comments Section -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Komentar</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($comments)): ?>
                    <div class="comments">
                        <?php foreach ($comments as $comment): ?>
                            <div class="comment mb-4 border-bottom pb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center">
                                        <div class="comment-author fw-bold">
                                            <?php echo htmlspecialchars($comment['username']); ?>
                                            <?php if ($comment['role'] === 'admin'): ?>
                                                <span class="badge bg-primary ms-1">Admin</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        <?php echo date('d M Y H:i', strtotime($comment['created_at'])); ?>
                                    </small>
                                </div>
                                <div class="comment-content">
                                    <?php echo nl2br(htmlspecialchars($comment['comment'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-comments fa-3x mb-3"></i>
                        <p>Belum ada komentar</p>
                    </div>
                <?php endif; ?>
                
                <!-- Add Comment Form -->
                <div class="add-comment mt-4">
                    <h6>Tambah Komentar</h6>
                    <form method="post">
                        <div class="mb-3">
                            <textarea name="comment" class="form-control" rows="3" placeholder="Tulis komentar Anda..." required></textarea>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">Kirim Komentar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Detail Tiket</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>ID Tiket:</span>
                        <span class="fw-bold">#<?php echo $ticket['id']; ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Status:</span>
                        <span class="badge <?php echo $status_class; ?>">
                            <?php echo $ticket['status']; ?>
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Prioritas:</span>
                        <span class="badge <?php echo $priority_class; ?>">
                            <?php echo $ticket['priority']; ?>
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Kategori:</span>
                        <span><?php echo htmlspecialchars($ticket['category_name']); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Dibuat:</span>
                        <span><?php echo date('d M Y H:i', strtotime($ticket['created_at'])); ?></span>
                    </li>
                    <?php if ($ticket['updated_at'] !== $ticket['created_at']): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Diperbarui:</span>
                            <span><?php echo date('d M Y H:i', strtotime($ticket['updated_at'])); ?></span>
                        </li>
                    <?php endif; ?>
                </ul>
                
                <div class="d-grid gap-2 mt-3">
                    <a href="<?php echo $base_url; ?>/tickets/" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
                    </a>
                    <?php if ($ticket['status'] !== 'Selesai'): ?>
                        <a href="#add-comment" class="btn btn-primary">
                            <i class="fas fa-comment me-1"></i> Tambah Komentar
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Bantuan Cepat</h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <a href="<?php echo $base_url; ?>/tickets/create.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-plus-circle text-primary me-2"></i> Buat Tiket Baru
                    </a>
                    <a href="<?php echo $base_url; ?>" class="list-group-item list-group-item-action">
                        <i class="fas fa-home text-primary me-2"></i> Beranda
                    </a>
                    <a href="<?php echo $base_url; ?>/tickets/" class="list-group-item list-group-item-action">
                        <i class="fas fa-list text-primary me-2"></i> Lihat Semua Tiket
                    </a>
                    <?php if (isAdmin()): ?>
                        <a href="<?php echo $base_url; ?>/admin/" class="list-group-item list-group-item-action">
                            <i class="fas fa-tachometer-alt text-primary me-2"></i> Dashboard Admin
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Scroll to comment form when clicking on "Tambah Komentar" button
document.addEventListener('DOMContentLoaded', function() {
    const addCommentBtn = document.querySelector('a[href="#add-comment"]');
    const commentForm = document.querySelector('.add-comment');
    
    if (addCommentBtn && commentForm) {
        addCommentBtn.addEventListener('click', function(e) {
            e.preventDefault();
            commentForm.scrollIntoView({ behavior: 'smooth' });
            const textarea = commentForm.querySelector('textarea');
            if (textarea) {
                textarea.focus();
            }
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>
