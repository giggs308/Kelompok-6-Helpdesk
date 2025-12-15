<?php
require_once '../config/config.php';

// Redirect if not logged in or not admin
if (!isLoggedIn() || !isAdmin()) {
    setMessage('error', 'Anda tidak memiliki akses ke halaman ini.');
    redirect($base_url . '/index.php');
}

// Set default filter values
$status = $_GET['status'] ?? '';
$category = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

// Build the query
$sql = "SELECT t.*, u.username, c.name as category_name 
        FROM tickets t 
        LEFT JOIN users u ON t.user_id = u.id 
        LEFT JOIN categories c ON t.category_id = c.id 
        WHERE 1=1";
$params = [];
$types = '';

// Apply filters
if (!empty($status)) {
    $sql .= " AND t.status = ?";
    $params[] = $status;
    $types .= 's';
}

if (!empty($category)) {
    $sql .= " AND t.category_id = ?";
    $params[] = $category;
    $types .= 'i';
}

if (!empty($search)) {
    $sql .= " AND (t.title LIKE ? OR t.description LIKE ? OR u.username LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'sss';
}

// Apply sorting
switch ($sort) {
    case 'oldest':
        $sql .= " ORDER BY t.created_at ASC";
        break;
    case 'priority_high':
        $sql .= " ORDER BY FIELD(t.priority, 'Tinggi', 'Sedang', 'Rendah')";
        break;
    case 'priority_low':
        $sql .= " ORDER BY FIELD(t.priority, 'Rendah', 'Sedang', 'Tinggi')";
        break;
    default: // newest
        $sql .= " ORDER BY t.created_at DESC";
}

// Prepare and execute the query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$tickets = $result->fetch_all(MYSQLI_ASSOC);

// Get categories for filter
$categories = $conn->query("SELECT * FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);

$pageTitle = "Kelola Tiket";
include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?php echo $pageTitle; ?></h1>
        <div>
            <a href="export_tickets.php" class="d-none d-sm-inline-block btn btn-sm btn-success shadow-sm me-2">
                <i class="fas fa-file-excel fa-sm text-white-50"></i> Ekspor ke Excel
            </a>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Tiket</h6>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="Open" <?php echo $status === 'Open' ? 'selected' : ''; ?>>Open</option>
                        <option value="Diproses" <?php echo $status === 'Diproses' ? 'selected' : ''; ?>>Diproses</option>
                        <option value="Selesai" <?php echo $status === 'Selesai' ? 'selected' : ''; ?>>Selesai</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="category" class="form-label">Kategori</label>
                    <select name="category" id="category" class="form-select">
                        <option value="">Semua Kategori</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="sort" class="form-label">Urutkan</label>
                    <select name="sort" id="sort" class="form-select">
                        <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Terbaru</option>
                        <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Terlama</option>
                        <option value="priority_high" <?php echo $sort === 'priority_high' ? 'selected' : ''; ?>>Prioritas (Tinggi ke Rendah)</option>
                        <option value="priority_low" <?php echo $sort === 'priority_low' ? 'selected' : ''; ?>>Prioritas (Rendah ke Tinggi)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="search" class="form-label">Cari</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Cari tiket...">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                    <a href="tickets.php" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tickets Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Tiket</h6>
            <span class="badge bg-primary"><?php echo count($tickets); ?> tiket ditemukan</span>
        </div>
        <div class="card-body">
            <?php if (empty($tickets)): ?>
                <div class="alert alert-info">Tidak ada tiket yang ditemukan.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Judul</th>
                                <th>Pelapor</th>
                                <th>Kategori</th>
                                <th>Status</th>
                                <th>Prioritas</th>
                                <th>Tanggal Dibuat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tickets as $ticket): ?>
                                <tr>
                                    <td>#<?php echo $ticket['id']; ?></td>
                                    <td>
                                        <a href="../tickets/view.php?id=<?php echo $ticket['id']; ?>">
                                            <?php echo htmlspecialchars($ticket['title']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($ticket['username']); ?></td>
                                    <td><?php echo htmlspecialchars($ticket['category_name'] ?? 'Tidak Diketahui'); ?></td>
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
                                    <td>
                                        <?php 
                                        $priorityClass = [
                                            'Tinggi' => 'bg-danger',
                                            'Sedang' => 'bg-warning',
                                            'Rendah' => 'bg-success'
                                        ][$ticket['priority']] ?? 'bg-secondary';
                                        ?>
                                        <span class="badge <?php echo $priorityClass; ?>">
                                            <?php echo $ticket['priority']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d M Y H:i', strtotime($ticket['created_at'])); ?></td>
                                    <td>
                                        <a href="../tickets/view.php?id=<?php echo $ticket['id']; ?>" class="btn btn-sm btn-primary" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="#" class="btn btn-sm btn-warning" title="Edit Status" data-bs-toggle="modal" data-bs-target="#statusModal" data-id="<?php echo $ticket['id']; ?>" data-status="<?php echo $ticket['status']; ?>">
                                            <i class="fas fa-edit"></i>
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

<!-- Status Update Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusModalLabel">Update Status Tiket</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="../tickets/update_status.php" method="post">
                <div class="modal-body">
                    <input type="hidden" name="ticket_id" id="ticketId">
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="Open">Open</option>
                            <option value="Diproses">Diproses</option>
                            <option value="Selesai">Selesai</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Initialize status modal
var statusModal = document.getElementById('statusModal');
if (statusModal) {
    statusModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var ticketId = button.getAttribute('data-id');
        var status = button.getAttribute('data-status');
        
        var modal = this;
        modal.querySelector('#ticketId').value = ticketId;
        modal.querySelector('#status').value = status;
    });
}
</script>

<?php include '../includes/footer.php'; ?>
