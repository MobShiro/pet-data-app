<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in and is a veterinarian
requireRole('veterinarian');

// Get vet ID
$vetId = isset($_SESSION['vet_id']) ? $_SESSION['vet_id'] : 0;

// Get today's appointments
$today = date('Y-m-d');
$todayQuery = "SELECT COUNT(*) as count FROM appointments WHERE vet_id = $vetId AND appointment_date = '$today'";
$todayResult = mysqli_query($conn, $todayQuery);
$todayAppointments = mysqli_fetch_assoc($todayResult)['count'];

// Get total patients count
$patientsQuery = "SELECT COUNT(DISTINCT p.pet_id) as count 
                  FROM appointments a 
                  JOIN pets p ON a.pet_id = p.pet_id 
                  WHERE a.vet_id = $vetId";
$patientsResult = mysqli_query($conn, $patientsQuery);
$totalPatients = mysqli_fetch_assoc($patientsResult)['count'];

// Get upcoming appointments
$appointmentsQuery = "SELECT a.*, p.name as pet_name, p.species, p.breed, 
                      po.owner_id, u.first_name, u.last_name, u.phone
                      FROM appointments a
                      JOIN pets p ON a.pet_id = p.pet_id
                      JOIN pet_owners po ON a.owner_id = po.owner_id
                      JOIN users u ON po.user_id = u.user_id
                      WHERE a.vet_id = $vetId 
                      AND a.status = 'Scheduled'
                      AND a.appointment_date >= CURDATE()
                      ORDER BY a.appointment_date, a.appointment_time
                      LIMIT 5";
$appointmentsResult = mysqli_query($conn, $appointmentsQuery);
$appointments = [];
while ($row = mysqli_fetch_assoc($appointmentsResult)) {
    $appointments[] = $row;
}

// Get recent patients
$recentPatientsQuery = "SELECT DISTINCT p.pet_id, p.name, p.species, p.breed, p.gender,
                        MAX(mr.visit_date) as last_visit
                        FROM medical_records mr
                        JOIN pets p ON mr.pet_id = p.pet_id
                        WHERE mr.vet_id = $vetId
                        GROUP BY p.pet_id, p.name, p.species, p.breed, p.gender
                        ORDER BY last_visit DESC
                        LIMIT 5";
$recentPatientsResult = mysqli_query($conn, $recentPatientsQuery);
$recentPatients = [];
while ($row = mysqli_fetch_assoc($recentPatientsResult)) {
    $recentPatients[] = $row;
}

include '../includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <h1 class="mb-0">Veterinarian Dashboard</h1>
        <div class="d-flex">
            <a href="appointments.php?action=view_today" class="btn btn-outline-primary rounded-pill me-2">
                <i class="fas fa-calendar-day me-2"></i> Today's Schedule
            </a>
            <a href="medical_records.php?action=add" class="btn btn-primary rounded-pill">
                <i class="fas fa-plus me-2"></i> New Medical Record
            </a>
        </div>
    </div>

    <!-- Welcome Card -->
    <div class="card shadow-sm mb-4 border-0 bg-primary text-white overflow-hidden">
        <div class="card-body p-0">
            <div class="row g-0">
                <div class="col-md-8 p-4">
                    <h2 class="fw-bold mb-3">Welcome, Dr. <?php echo htmlspecialchars($_SESSION["last_name"]); ?>!</h2>
                    <p class="lead">Manage your appointments, patient records, and communicate with pet owners efficiently.</p>
                    <?php if ($todayAppointments > 0): ?>
                        <div class="alert alert-light text-primary mt-3 mb-0 d-inline-block">
                            <i class="fas fa-calendar-check me-2"></i>
                            You have <?php echo $todayAppointments; ?> appointment<?php echo $todayAppointments != 1 ? 's' : ''; ?> scheduled for today.
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-md-4 d-none d-md-block">
                    <img src="../assets/images/vet-illustration.svg" alt="Veterinarian" class="img-fluid" style="clip-path: polygon(10% 0, 100% 0, 100% 100%, 0% 100%);">
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card dashboard-card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-gradient-primary text-white me-3">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div>
                        <h3 class="card-stats-value mb-0 counter" data-target="<?php echo $todayAppointments; ?>">0</h3>
                        <div class="card-stats-title">Today's Appointments</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card dashboard-card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-gradient-success text-white me-3">
                        <i class="fas fa-paw"></i>
                    </div>
                    <div>
                        <h3 class="card-stats-value mb-0 counter" data-target="<?php echo $totalPatients; ?>">0</h3>
                        <div class="card-stats-title">Total Patients</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card dashboard-card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-gradient-warning text-white me-3">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div>
                        <h3 class="card-stats-value mb-0"><?php echo date('M Y'); ?></h3>
                        <div class="card-stats-title">Monthly Overview</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Upcoming Appointments Section -->
        <div class="col-lg-7">
            <div class="card dashboard-card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center p-3">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-check text-primary me-2"></i> Upcoming Appointments
                    </h5>
                    <a href="appointments.php" class="btn btn-sm btn-outline-primary rounded-pill">
                        View All
                    </a>
                </div>
                <div class="card-body p-0">
                    <?php if (count($appointments) > 0): ?>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Date & Time</th>
                                        <th>Patient</th>
                                        <th>Owner</th>
                                        <th>Reason</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($appointments as $appointment): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold"><?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?></div>
                                                <div class="small text-muted"><?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></div>
                                            </td>
                                            <td>
                                                <div class="fw-bold"><?php echo htmlspecialchars($appointment['pet_name']); ?></div>
                                                <div class="small text-muted">
                                                    <?php echo htmlspecialchars($appointment['species']); ?> · 
                                                    <?php echo htmlspecialchars($appointment['breed']); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div><?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?></div>
                                                <?php if (!empty($appointment['phone'])): ?>
                                                    <div class="small text-muted"><?php echo htmlspecialchars($appointment['phone']); ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($appointment['reason'])): ?>
                                                    <span class="text-truncate d-inline-block" style="max-width: 150px;" title="<?php echo htmlspecialchars($appointment['reason']); ?>">
                                                        <?php echo htmlspecialchars($appointment['reason']); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">Not specified</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="appointments.php?action=view&id=<?php echo $appointment['appointment_id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="appointments.php?action=complete&id=<?php echo $appointment['appointment_id']; ?>" class="btn btn-sm btn-outline-success">
                                                        <i class="fas fa-check"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center p-4">
                            <img src="../assets/images/empty-schedule.svg" alt="No appointments" class="img-fluid mb-3" style="max-width: 150px;">
                            <h5>No Upcoming Appointments</h5>
                            <p class="text-muted">You have no scheduled appointments in the near future</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Recent Patients Section -->
        <div class="col-lg-5">
            <div class="card dashboard-card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center p-3">
                    <h5 class="mb-0">
                        <i class="fas fa-user-md text-primary me-2"></i> Recent Patients
                    </h5>
                    <a href="patients.php" class="btn btn-sm btn-outline-primary rounded-pill">
                        View All
                    </a>
                </div>
                <div class="card-body p-0">
                    <?php if (count($recentPatients) > 0): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($recentPatients as $patient): ?>
                                <li class="list-group-item list-group-item-action p-3">
                                    <div class="d-flex">
                                        <div class="pet-avatar me-3">
                                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                <i class="fas fa-paw text-primary"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0"><?php echo htmlspecialchars($patient['name']); ?></h6>
                                            <div class="text-muted small">
                                                <?php echo htmlspecialchars($patient['species']); ?> · 
                                                <?php echo htmlspecialchars($patient['breed']); ?> · 
                                                <?php echo htmlspecialchars($patient['gender']); ?>
                                            </div>
                                            <div class="small mt-1">
                                                <span class="text-primary">
                                                    <i class="fas fa-calendar-check me-1"></i> Last visit: <?php echo date('M d, Y', strtotime($patient['last_visit'])); ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div>
                                            <a href="patients.php?action=view&id=<?php echo $patient['pet_id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill">
                                                View Records
                                            </a>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="text-center p-4">
                            <img src="../assets/images/empty-patients.svg" alt="No patients" class="img-fluid mb-3" style="max-width: 150px;">
                            <h5>No Recent Patients</h5>
                            <p class="text-muted">You haven't seen any patients recently</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Stats Chart -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card dashboard-card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center p-3">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar text-primary me-2"></i> Monthly Statistics
                    </h5>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-primary active" data-chart-period="weekly">Weekly</button>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-chart-period="monthly">Monthly</button>
                    </div>
                </div>
                <div class="card-body">
                    <div style="height: 300px;">
                        <canvas id="vetStatsChart" class="chart-canvas"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Initialize dashboard charts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('vetStatsChart').getContext('2d');
    
    // Sample data - in a production environment, this would come from PHP backend
    const weeklyData = {
        labels: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
        datasets: [
            {
                label: 'Appointments',
                data: [5, 3, 7, 4, 6, 2, 0],
                backgroundColor: 'rgba(13, 110, 253, 0.5)',
                borderColor: '#0d6efd',
                borderWidth: 1
            },
            {
                label: 'New Patients',
                data: [2, 1, 3, 0, 1, 0, 0],
                backgroundColor: 'rgba(32, 201, 151, 0.5)',
                borderColor: '#20c997',
                borderWidth: 1
            }
        ]
    };
    
    const monthlyData = {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [
            {
                label: 'Appointments',
                data: [15, 19, 25, 32, 28, 23, 18, 20, 22, 27, 30, 35],
                backgroundColor: 'rgba(13, 110, 253, 0.5)',
                borderColor: '#0d6efd',
                borderWidth: 1
            },
            {
                label: 'New Patients',
                data: [7, 8, 12, 14, 10, 9, 6, 8, 10, 12, 13, 15],
                backgroundColor: 'rgba(32, 201, 151, 0.5)',
                borderColor: '#20c997',
                borderWidth: 1
            }
        ]
    };
    
    let vetStatsChart = new Chart(ctx, {
        type: 'bar',
        data: weeklyData,
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
            }
        }
    });
    
    // Switch between weekly and monthly data
    document.querySelectorAll('[data-chart-period]').forEach(button => {
        button.addEventListener('click', function() {
            const period = this.dataset.chartPeriod;
            
            // Update active button
            document.querySelectorAll('[data-chart-period]').forEach(btn => {
                btn.classList.remove('active');
            });
            this.classList.add('active');
            
            // Update chart data
            if (period === 'weekly') {
                vetStatsChart.data = weeklyData;
            } else {
                vetStatsChart.data = monthlyData;
            }
            
            vetStatsChart.update();
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>