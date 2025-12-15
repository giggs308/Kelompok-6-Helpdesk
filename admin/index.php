<?php
require_once '../config/config.php';

// Redirect if not logged in or not admin
if (!isLoggedIn() || !isAdmin()) {
    setMessage('error', 'Anda tidak memiliki akses ke halaman ini.');
    redirect($base_url . '/index.php');
}

// Get counts for dashboard
$stats = [
    'total_tickets' => 0,
    'open_tickets' => 0,
    'in_progress_tickets' => 0,
    'closed_tickets' => 0,
    'monthly_tickets' => 0
];

// Get ticket counts with more detailed status breakdown
$sql = "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'Open' THEN 1 ELSE 0 END) as open,
            SUM(CASE WHEN status = 'Diproses' THEN 1 ELSE 0 END) as in_progress,
            SUM(CASE WHEN status = 'Selesai' THEN 1 ELSE 0 END) as closed
        FROM tickets";

if ($result = $conn->query($sql)) {
    $row = $result->fetch_assoc();
    $stats['total_tickets'] = $row['total'];
    $stats['open_tickets'] = $row['open'] ?: 0;
    $stats['in_progress_tickets'] = $row['in_progress'] ?: 0;
    $stats['closed_tickets'] = $row['closed'] ?: 0;
    $result->free();
}

// Get monthly tickets count
$current_month = date('Y-m');
$sql = "SELECT COUNT(*) as count FROM tickets WHERE DATE_FORMAT(created_at, '%Y-%m') = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("s", $current_month);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $stats['monthly_tickets'] = $row['count'];
    }
    $stmt->close();
}

// Get recent tickets
$recentTickets = [];
$sql = "SELECT t.*, u.username, c.name as category_name 
        FROM tickets t 
        LEFT JOIN users u ON t.user_id = u.id 
        LEFT JOIN categories c ON t.category_id = c.id 
        ORDER BY t.created_at DESC 
        LIMIT 5";
$recentTickets = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

// Get recent users
$recentUsers = [];
$sql = "SELECT id, username, email, created_at 
        FROM users 
        ORDER BY created_at DESC 
        LIMIT 5";

if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $recentUsers[] = $row;
    }
}

include '../includes/header.php'; ?>

<!-- Begin Page Content -->
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard Admin</h1>
        <a href="tickets.php" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-ticket-alt fa-sm text-white-50"></i> Kelola Semua Tiket
        </a>
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- Total Tickets Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Tiket</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($stats['total_tickets']); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-ticket-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Open Tickets Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Tiket Baru</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($stats['open_tickets']); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-plus-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- In Progress Tickets Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Dalam Proses</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($stats['in_progress_tickets']); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-spinner fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Closed Tickets Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Tiket Selesai</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($stats['closed_tickets']); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Tickets -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Tiket Terbaru</h6>
                    <a href="tickets.php" class="btn btn-sm btn-primary">Lihat Semua</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recentTickets)): ?>
                        <div class="alert alert-info">Tidak ada tiket terbaru.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Judul</th>
                                        <th>Pelapor</th>
                                        <th>Status</th>
                                        <th>Tanggal</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentTickets as $ticket): ?>
                                        <tr>
                                            <td>#<?php echo $ticket['id']; ?></td>
                                            <td><?php echo htmlspecialchars($ticket['title']); ?></td>
                                            <td><?php echo htmlspecialchars($ticket['username']); ?></td>
                                            <td>
                                                <?php 
                                                $statusClass = [
                                                    'Open' => 'bg-warning',
                                                    'Diproses' => 'bg-info',
                                                    'Selesai' => 'bg-success'
                                                ][$ticket['status']] ?? 'bg-secondary';
                                                ?>
                                                <span class="badge <?php echo $statusClass; ?>">
                                                    <?php echo $ticket['status']; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d M Y', strtotime($ticket['created_at'])); ?></td>
                                            <td>
                                                <a href="../tickets/view.php?id=<?php echo $ticket['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i> Lihat
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
