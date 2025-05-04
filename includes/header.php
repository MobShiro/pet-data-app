<?php 
require_once $_SERVER['DOCUMENT_ROOT'] . '/vet_anywhere/config/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/vet_anywhere/includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vet Anywhere - Pet Health Management System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Animate.css for animations -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/vet_anywhere/assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand" href="/vet_anywhere/index.php">
                <img src="/vet_anywhere/assets/images/logo.png" alt="Vet Anywhere Logo" height="40" class="me-2">
                <span class="brand-text">Vet Anywhere</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isLoggedIn()): ?>
                        <?php if (hasRole('pet_owner')): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/vet_anywhere/pet_owner/dashboard.php">
                                    <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/vet_anywhere/pet_owner/pets.php">
                                    <i class="fas fa-paw me-1"></i> My Pets
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/vet_anywhere/pet_owner/appointments.php">
                                    <i class="fas fa-calendar-alt me-1"></i> Appointments
                                </a>
                            </li>
                        <?php elseif (hasRole('veterinarian')): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/vet_anywhere/veterinarian/dashboard.php">
                                    <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/vet_anywhere/veterinarian/appointments.php">
                                    <i class="fas fa-calendar-alt me-1"></i> Appointments
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/vet_anywhere/veterinarian/patients.php">
                                    <i class="fas fa-user-md me-1"></i> Patients
                                </a>
                            </li>
                        <?php elseif (hasRole('admin')): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/vet_anywhere/admin/dashboard.php">
                                    <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/vet_anywhere/admin/users.php">
                                    <i class="fas fa-users-cog me-1"></i> Users
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="/vet_anywhere/admin/settings.php">
                                    <i class="fas fa-cog me-1"></i> Settings
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-1"></i> 
                                <?php echo htmlspecialchars($_SESSION["first_name"]); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end animate__animated animate__fadeIn" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="/vet_anywhere/user/profile.php">
                                    <i class="fas fa-id-card me-2"></i> My Profile
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/vet_anywhere/auth/logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/vet_anywhere/auth/login.php">
                                <i class="fas fa-sign-in-alt me-1"></i> Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-primary rounded-pill" href="/vet_anywhere/auth/register.php">
                                <i class="fas fa-user-plus me-1"></i> Register
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="content-wrapper">
        <div class="container py-4">
            <?php
            $flash = getFlashMessage();
            if ($flash !== null): 
            ?>
                <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show animate__animated animate__fadeIn">
                    <i class="fas fa-info-circle me-2"></i> <?php echo $flash['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>