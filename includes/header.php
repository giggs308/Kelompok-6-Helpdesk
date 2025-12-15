<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Helpdesk System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link href="<?php echo $base_url; ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?php echo $base_url; ?>/index.php">Helpdesk System</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $base_url; ?>/index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $base_url; ?>/panduan.php">
                            <i class="fas fa-question-circle me-1"></i> Panduan
                        </a>
                    </li>
                    <?php if (isLoggedIn()): ?>
                        <?php if (!isAdmin()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url; ?>/tickets/create.php">Buat Tiket Baru</a>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo isAdmin() ? $base_url . '/admin/tickets.php' : $base_url . '/tickets/index.php'; ?>">
                                <?php echo isAdmin() ? 'Kelola Tiket' : 'Daftar Tiket Saya'; ?>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?php echo $base_url; ?>/profile.php">Profil</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo $base_url; ?>/logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url; ?>/login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url; ?>/register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php 
        // Display flash messages
        if (isset($_SESSION['message'])) {
            echo showMessage();
        }
        ?>
