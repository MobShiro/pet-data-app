<?php
session_start();
require_once('../config/database.php');
require_once('../includes/functions.php');

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

try {
    // Create database connection
    $database = new Database();
    $conn = $database->getConnection();
    
    // Get system statistics
    
    // Total users by role
    $role_stats_query = "SELECT role, COUNT(*) as count FROM users GROUP BY role";
    $role_stats_result = $database->query($role_stats_query);
    $role_stats = [];
    
    while ($row = $role_stats_result->fetch_assoc()) {
        $role_stats[$row['role']] = $row['count'];
    }
    
    // Appointment statistics
    $appointment_stats_query = "SELECT status, COUNT(*) as count FROM appointments GROUP BY status";
    $appointment_stats_result = $database->query($appointment_stats_query);
    $appointment_stats = [];
    
    while ($row = $appointment_stats_result->fetch_assoc()) {
        $appointment_stats[$row['status']] = $row['count'];
    }
    
    // Recent registrations
    $recent_users_query = "SELECT id, username, email, role, status, created_at 
                          FROM users 
                          ORDER BY created_at DESC 
                          LIMIT 5";
    $recent_users_result = $database->query($recent_users_query);
    
    // Upcoming appointments
    $upcoming_appointments_query = "SELECT a.id, a.appointment_datetime, a.status,
                                  p.name AS pet_name, 
                                  CONCAT(o.first_name, ' ', o.last_name) AS owner_name,
                                  CONCAT(v.first_name, ' ', v.last_name) AS vet_name
                                  FROM appointments a
                                  JOIN pets p ON a.pet_id = p.id
                                  JOIN users o ON a.owner_id = o.id
                                  JOIN users v ON a.vet_id = v.id
                                  WHERE a.appointment_datetime >= NOW()
                                  ORDER BY a.appointment_datetime
                                  LIMIT 5";
    $upcoming_appointments_result = $database->query($upcoming_appointments_query);
    
} catch (Exception $e) {
    $error_message = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Vet Anywhere</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <!-- Include Chart.js for statistics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include('../includes/admin_header.php'); ?>
    
    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h1>
        </div>
        
        <div class="dashboard-overview">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3>Total Users</h3>
                    <p class="stat-number"><?php echo array_sum($role_stats); ?></p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-user-md"></i>
                </div>
                <div class="stat-info">
                    <h3>Veterinarians</h3>
                    <p class="stat-number"><?php echo isset($role_stats['veterinarian']) ? $role_stats['veterinarian'] : 0; ?></p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-info">
                    <h3>Appointments</h3>
                    <p class="stat-number"><?php echo array_sum($appointment_stats); ?></p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-paw"></i>
                </div>
                <div class="stat-info">
                    <h3>Pet Owners</h3>
                    <p class="stat-number"><?php echo isset($role_stats['pet_owner']) ? $role_stats['pet_owner'] : 0; ?></p>
                </div>
            </div>
        </div>
        
        <div class="dashboard-charts">
            <div class="chart-container">
                <h2>User Distribution</h2>
                <canvas id="userChart"></canvas>
            </div>
            
            <div class="chart-container">
                <h2>Appointment Status</h2>
                <canvas id="appointmentChart"></canvas>
            </div>
        </div>
        
        <div class="dashboard-tables">
            <div class="table-container">
                <h2><i class="fas fa-user-plus"></i> Recent Registrations</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $recent_users_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $user['username']; ?></td>
                                <td><?php echo $user['email']; ?></td>
                                <td><span class="badge badge-<?php echo $user['role']; ?>"><?php echo ucfirst($user['role']); ?></span></td>
                                <td>
                                    <?php if ($user['status'] == 'active'): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php elseif ($user['status'] == 'pending'): ?>
                                        <span class="badge badge-warning">Pending</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <a href="user_details.php?id=<?php echo $user['id']; ?>" class="btn-icon" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <div class="table-footer">
                    <a href="users.php" class="btn btn-secondary">View All Users</a>
                </div>
            </div>
            
            <div class="table-container">
                <h2><i class="fas fa-calendar"></i> Upcoming Appointments</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date/Time</th>
                            <th>Pet</th>
                            <th>Owner</th>
                            <th>Veterinarian</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($appointment = $upcoming_appointments_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('M j, Y, g:i a', strtotime($appointment['appointment_datetime'])); ?></td>
                                <td><?php echo $appointment['pet_name']; ?></td>
                                <td><?php echo $appointment['owner_name']; ?></td>
                                <td><?php echo $appointment['vet_name']; ?></td>
                                <td>
                                    <?php if ($appointment['status'] == 'confirmed'): ?>
                                        <span class="badge badge-success">Confirmed</span>
                                    <?php elseif ($appointment['status'] == 'pending'): ?>
                                        <span class="badge badge-warning">Pending</span>
                                    <?php elseif ($appointment['status'] == 'cancelled'): ?>
                                        <span class="badge badge-danger">Cancelled</span>
                                    <?php else: ?>
                                        <span class="badge badge-info">Completed</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="appointment_details.php?id=<?php echo $appointment['id']; ?>" class="btn-icon" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <div class="table-footer">
                    <a href="appointments.php" class="btn btn-secondary">View All Appointments</a>
                </div>
            </div>
        </div>
    </div>
    
    <?php include('../includes/footer.php'); ?>
    
    <script>
        // User distribution chart
        var userCtx = document.getElementById('userChart').getContext('2d');
        var userChart = new Chart(userCtx, {
            type: 'pie',
            data: {
                labels: [
                    'Pet Owners', 
                    'Veterinarians', 
                    'Administrators'
                ],
                datasets: [{
                    data: [
                        <?php echo isset($role_stats['pet_owner']) ? $role_stats['pet_owner'] : 0; ?>,
                        <?php echo isset($role_stats['veterinarian']) ? $role_stats['veterinarian'] : 0; ?>,
                        <?php echo isset($role_stats['admin']) ? $role_stats['admin'] : 0; ?>
                    ],
                    backgroundColor: [
                        '#4e73df',
                        '#1cc88a',
                        '#36b9cc'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        
        // Appointment status chart
        var appointmentCtx = document.getElementById('appointmentChart').getContext('2d');
        var appointmentChart = new Chart(appointmentCtx, {
            type: 'bar',
            data: {
                labels: [
                    'Pending', 
                    'Confirmed', 
                    'Completed', 
                    'Cancelled'
                ],
                datasets: [{
                    label: 'Appointments',
                    data: [
                        <?php echo isset($appointment_stats['pending']) ? $appointment_stats['pending'] : 0; ?>,
                        <?php echo isset($appointment_stats['confirmed']) ? $appointment_stats['confirmed'] : 0; ?>,
                        <?php echo isset($appointment_stats['completed']) ? $appointment_stats['completed'] : 0; ?>,
                        <?php echo isset($appointment_stats['cancelled']) ? $appointment_stats['cancelled'] : 0; ?>
                    ],
                    backgroundColor: [
                        '#f6c23e',
                        '#1cc88a',
                        '#4e73df',
                        '#e74a3b'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    </script>
</body>
</html>