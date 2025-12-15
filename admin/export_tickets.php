<?php
require_once '../config/config.php';

// Ensure user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    setMessage('error', 'Akses ditolak. Hanya admin yang diizinkan.');
    redirect($base_url . '/login.php');
}

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="export_tickets_' . date('Y-m-d') . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

// Get all tickets with user and category information
$sql = "SELECT 
            t.id, 
            t.title, 
            t.description, 
            t.status, 
            t.priority, 
            t.created_at, 
            t.updated_at,
            u.username as created_by,
            c.name as category_name
        FROM tickets t
        LEFT JOIN users u ON t.user_id = u.id
        LEFT JOIN categories c ON t.category_id = c.id
        ORDER BY t.created_at DESC";

$tickets = [];
if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $tickets[] = $row;
    }
}

// Start HTML table for Excel
$html = "<table border='1'>
            <tr>
                <th>ID</th>
                <th>Judul</th>
                <th>Kategori</th>
                <th>Status</th>
                <th>Prioritas</th>
                <th>Dibuat Oleh</th>
                <th>Tanggal Dibuat</th>
                <th>Terakhir Diupdate</th>
            </tr>";

// Add data rows
foreach ($tickets as $ticket) {
    $html .= "<tr>";
    $html .= "<td>" . htmlspecialchars($ticket['id']) . "</td>";
    $html .= "<td>" . htmlspecialchars($ticket['title']) . "</td>";
    $html .= "<td>" . htmlspecialchars($ticket['category_name']) . "</td>";
    $html .= "<td>" . htmlspecialchars($ticket['status']) . "</td>";
    $html .= "<td>" . htmlspecialchars($ticket['priority']) . "</td>";
    $html .= "<td>" . htmlspecialchars($ticket['created_by']) . "</td>";
    $html .= "<td>" . date('d/m/Y H:i', strtotime($ticket['created_at'])) . "</td>";
    $html .= "<td>" . date('d/m/Y H:i', strtotime($ticket['updated_at'])) . "</td>";
    $html .= "</tr>";
}

$html .= "</table>";

echo $html;
exit;
