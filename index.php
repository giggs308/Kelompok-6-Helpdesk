<?php
require_once 'config/config.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    redirect($base_url . '/login.php');
}

// Redirect admin to admin dashboard
if (isAdmin()) {
    redirect($base_url . '/admin/');
}

// Get user's open tickets count
$open_tickets = 0;
$user_id = $_SESSION['user_id'];
$sql = "SELECT COUNT(*) as count FROM tickets WHERE user_id = ? AND status = 'Open'";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $open_tickets = $row['count'];
    $stmt->close();
}
?>

<?php include 'includes/header.php'; ?>

<div class="row">
    <div class="col-12 mb-4">
        <h2>Selamat Datang, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
        <p class="lead">Ini adalah sistem Helpdesk untuk mengelola tiket dukungan.</p>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title">Tiket Baru</h5>
                        <p class="card-text">Ajukan tiket dukungan baru</p>
                    </div>
                    <i class="fas fa-plus-circle fa-3x"></i>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0">
                <a href="<?php echo $base_url; ?>/tickets/create.php" class="btn btn-light btn-sm">Buat Tiket</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card bg-warning text-dark h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title">Tiket Terbuka</h5>
                        <p class="card-text"><?php echo $open_tickets; ?> tiket menunggu</p>
                    </div>
                    <i class="fas fa-ticket-alt fa-3x"></i>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0">
                <a href="<?php echo $base_url; ?>/tickets/index.php" class="btn btn-dark btn-sm">Lihat Semua</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card bg-success text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title">Bantuan</h5>
                        <p class="card-text">Panduan penggunaan</p>
                    </div>
                    <i class="fas fa-question-circle fa-3x"></i>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0">
                <a href="<?php echo $base_url; ?>/panduan.php" class="btn btn-light btn-sm">Lihat Panduan</a>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Tiket Terbaru</h5>
            </div>
            <div class="card-body">
                <?php
                $sql = "SELECT t.id, t.title, t.status, t.created_at, c.name as category 
                        FROM tickets t 
                        JOIN categories c ON t.category_id = c.id 
                        WHERE t.user_id = ? 
                        ORDER BY t.created_at DESC 
                        LIMIT 5";
                
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        echo '<div class="list-group">';
                        while ($ticket = $result->fetch_assoc()) {
                            $status_class = '';
                            if ($ticket['status'] === 'Open') $status_class = 'bg-danger';
                            elseif ($ticket['status'] === 'Diproses') $status_class = 'bg-warning text-dark';
                            elseif ($ticket['status'] === 'Selesai') $status_class = 'bg-success';
                            
                            echo '<a href="' . $base_url . '/tickets/view.php?id=' . $ticket['id'] . '" class="list-group-item list-group-item-action">';
                            echo '  <div class="d-flex w-100 justify-content-between">';
                            echo '    <h6 class="mb-1">' . htmlspecialchars($ticket['title']) . '</h6>';
                            echo '    <small class="badge ' . $status_class . ' rounded-pill">' . $ticket['status'] . '</small>';
                            echo '  </div>';
                            echo '  <small class="text-muted">' . $ticket['category'] . ' • ' . date('d M Y H:i', strtotime($ticket['created_at'])) . '</small>';
                            echo '</a>';
                        }
                        echo '</div>';
                    } else {
                        echo '<p class="text-muted">Belum ada tiket yang dibuat.</p>';
                    }
                    $stmt->close();
                }
                ?>
            </div>
            <div class="card-footer bg-white">
                <a href="<?php echo $base_url; ?>/tickets/index.php" class="btn btn-outline-primary btn-sm">Lihat Semua Tiket</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0">Status Sistem</h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Status Server</span>
                            <span class="badge bg-success rounded-pill">Online</span>
                        </div>
                    </div>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Waktu Server</span>
                            <span class="text-muted"><?php echo date('d M Y H:i:s'); ?></span>
                        </div>
                    </div>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Versi Sistem</span>
                            <span class="text-muted">1.0.0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Kontak Dukungan</h5>
            </div>
            <div class="card-body">
                <p class="card-text">
                    <i class="fas fa-envelope me-2"></i> support@example.com<br>
                    <i class="fas fa-phone me-2"></i> (021) 12345678
                </p>
                <p class="card-text small text-muted">
                    Senin - Jumat: 08:00 - 17:00 WIB<br>
                    Sabtu: 08:00 - 12:00 WIB
                </p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
