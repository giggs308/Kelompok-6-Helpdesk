<?php
require_once '../config/config.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    setMessage('error', 'Silakan login terlebih dahulu.');
    redirect($base_url . '/login.php');
}

// Get filter parameters
$status = $_GET['status'] ?? '';
$category_id = $_GET['category_id'] ?? '';
$search = trim($_GET['search'] ?? '');

// Build the query
if (isAdmin()) {
    $query = "SELECT t.*, c.name as category_name, u.username 
              FROM tickets t 
              JOIN categories c ON t.category_id = c.id 
              JOIN users u ON t.user_id = u.id ";
} else {    
    $query = "SELECT t.*, c.name as category_name, u.username 
              FROM tickets t 
              JOIN categories c ON t.category_id = c.id 
              JOIN users u ON t.user_id = u.id ";
}

$query .= " WHERE t.user_id = ?";

$params = [$_SESSION['user_id']];
$types = 'i';

// Add status filter
if (in_array($status, ['Open', 'Diproses', 'Selesai'])) {
    $query .= " AND t.status = ?";
    $params[] = $status;
    $types .= 's';
}

// Add category filter
if (is_numeric($category_id)) {
    $query .= " AND t.category_id = ?";
    $params[] = $category_id;
    $types .= 'i';
}

// Add search filter
if (!empty($search)) {
    $query .= " AND (t.title LIKE ? OR t.description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

// Add sorting
$query .= " ORDER BY ";
$sort = $_GET['sort'] ?? 'created_at';
$order = isset($_GET['order']) && strtoupper($_GET['order']) === 'ASC' ? 'ASC' : 'DESC';

// Validate sort column
$allowed_sort_columns = ['created_at', 'updated_at', 'title', 'status', 'priority'];
$sort = in_array($sort, $allowed_sort_columns) ? $sort : 'created_at';
$query .= " t.$sort $order";

// Prepare and execute the query
$tickets = [];
if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $tickets[] = $row;
    }
    $stmt->close();
}

// Get categories for filter dropdown
$categories = [];
$sql = "SELECT id, name FROM categories ORDER BY name";
if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    $result->free();
}

// Get counts for status tabs
$status_counts = [
    'all' => 0,
    'Open' => 0,
    'Diproses' => 0,
    'Selesai' => 0
];

$sql = "SELECT status, COUNT(*) as count FROM tickets WHERE user_id = ? GROUP BY status";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $total = 0;
    while ($row = $result->fetch_assoc()) {
        $status_counts[$row['status']] = $row['count'];
        $total += $row['count'];
    }
    $status_counts['all'] = $total;
    $stmt->close();
}
?>

<?php include '../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Daftar Tiket Saya</h2>
    <a href="create.php" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Tiket Baru
    </a>
</div>

<div class="card mb-4">
    <div class="card-header bg-white">
        <ul class="nav nav-tabs card-header-tabs" id="ticketTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link <?php echo empty($status) ? 'active' : ''; ?>" 
                   href="?status=">
                    Semua <span class="badge bg-secondary"><?php echo $status_counts['all']; ?></span>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link <?php echo $status === 'Open' ? 'active' : ''; ?>" 
                   href="?status=Open">
                    Open <span class="badge bg-danger"><?php echo $status_counts['Open']; ?></span>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link <?php echo $status === 'Diproses' ? 'active' : ''; ?>" 
                   href="?status=Diproses">
                    Diproses <span class="badge bg-warning text-dark"><?php echo $status_counts['Diproses']; ?></span>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link <?php echo $status === 'Selesai' ? 'active' : ''; ?>" 
                   href="?status=Selesai">
                    Selesai <span class="badge bg-success"><?php echo $status_counts['Selesai']; ?></span>
                </a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-8">
                <form action="" method="get" class="row g-2">
                    <?php if (!empty($status)): ?>
                        <input type="hidden" name="status" value="<?php echo htmlspecialchars($status); ?>">
                    <?php endif; ?>
                    <div class="col-md-4">
                        <select name="category_id" class="form-select" onchange="this.form.submit()">
                            <option value="">Semua Kategori</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" 
                                    <?php echo ($category_id == $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Cari tiket..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <?php if (empty($tickets)): ?>
            <div class="text-center py-5">
                <div class="mb-3">
                    <i class="fas fa-ticket-alt fa-4x text-muted"></i>
                </div>
                <h5 class="text-muted">Tidak ada tiket yang ditemukan</h5>
                <p class="text-muted">
                    <?php if (!empty($status) || !empty($category_id) || !empty($search)): ?>
                        Coba ubah filter pencarian Anda atau <a href="index.php">lihat semua tiket</a>.
                    <?php else: ?>
                        Anda belum membuat tiket apapun. <a href="create.php">Buat tiket baru</a> untuk memulai.
                    <?php endif; ?>
                </p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>
                                <a href="?<?php 
                                    $params = $_GET;
                                    $params['sort'] = 'title';
                                    $params['order'] = ($sort === 'title' && $order === 'ASC') ? 'DESC' : 'ASC';
                                    echo http_build_query($params);
                                ?>" class="text-decoration-none text-dark">
                                    Judul
                                    <?php if ($sort === 'title'): ?>
                                        <i class="fas fa-sort-<?php echo $order === 'ASC' ? 'up' : 'down'; ?>"></i>
                                    <?php else: ?>
                                        <i class="fas fa-sort"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>Kategori</th>
                            <th>Status</th>
                            <th>Prioritas</th>
                            <th>
                                <a href="?<?php 
                                    $params = $_GET;
                                    $params['sort'] = 'created_at';
                                    $params['order'] = ($sort === 'created_at' && $order === 'ASC') ? 'DESC' : 'ASC';
                                    echo http_build_query($params);
                                ?>" class="text-decoration-none text-dark">
                                    Dibuat
                                    <?php if ($sort === 'created_at'): ?>
                                        <i class="fas fa-sort-<?php echo $order === 'ASC' ? 'up' : 'down'; ?>"></i>
                                    <?php else: ?>
                                        <i class="fas fa-sort"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>
                                <a href="?<?php 
                                    $params = $_GET;
                                    $params['sort'] = 'updated_at';
                                    $params['order'] = ($sort === 'updated_at' && $order === 'ASC') ? 'DESC' : 'ASC';
                                    echo http_build_query($params);
                                ?>" class="text-decoration-none text-dark">
                                    Diperbarui
                                    <?php if ($sort === 'updated_at'): ?>
                                        <i class="fas fa-sort-<?php echo $order === 'ASC' ? 'up' : 'down'; ?>"></i>
                                    <?php else: ?>
                                        <i class="fas fa-sort"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets as $ticket): 
                            $status_class = '';
                            if ($ticket['status'] === 'Open') $status_class = 'bg-danger';
                            elseif ($ticket['status'] === 'Diproses') $status_class = 'bg-warning text-dark';
                            elseif ($ticket['status'] === 'Selesai') $status_class = 'bg-success';
                            
                            $priority_class = '';
                            if ($ticket['priority'] === 'High') $priority_class = 'bg-danger';
                            elseif ($ticket['priority'] === 'Medium') $priority_class = 'bg-warning text-dark';
                            elseif ($ticket['priority'] === 'Low') $priority_class = 'bg-info';
                        ?>
                            <tr style="cursor: pointer;" onclick="window.location='view.php?id=<?php echo $ticket['id']; ?>'">
                                <td><?php echo $ticket['id']; ?></td>
                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($ticket['title']); ?></div>
                                    <small class="text-muted">#<?php echo $ticket['id']; ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($ticket['category_name']); ?></td>
                                <td><span class="badge <?php echo $status_class; ?>"><?php echo $ticket['status']; ?></span></td>
                                <td><span class="badge <?php echo $priority_class; ?>"><?php echo $ticket['priority']; ?></span></td>
                                <td><?php echo date('d M Y H:i', strtotime($ticket['created_at'])); ?></td>
                                <td><?php echo date('d M Y H:i', strtotime($ticket['updated_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
