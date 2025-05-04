<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in and is a pet owner
requireRole('pet_owner');

// Get owner ID
$ownerId = isset($_SESSION['owner_id']) ? $_SESSION['owner_id'] : 0;

// Get owner's pets
$pets = getOwnerPets($ownerId);
$petCount = count($pets);

// Get upcoming appointments
$appointments = getOwnerAppointments($ownerId);
$appointmentCount = count($appointments);

// Get due vaccinations
$dueVaccinations = 0;
foreach ($pets as $pet) {
    $vaccinations = getPetVaccinations($pet['pet_id']);
    foreach ($vaccinations as $vaccination) {
        if (!empty($vaccination['next_due_date']) && strtotime($vaccination['next_due_date']) <= strtotime('+30 days')) {
            $dueVaccinations++;
        }
    }
}

include '../includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Pet Owner Dashboard</h1>
        <a href="pets.php?action=add" class="btn btn-primary rounded-pill">
            <i class="fas fa-plus me-2"></i> Add New Pet
        </a>
    </div>

    <!-- Welcome Card -->
    <div class="card shadow-sm mb-4 border-0 bg-primary text-white overflow-hidden">
        <div class="card-body p-0">
            <div class="row g-0">
                <div class="col-md-8 p-4">
                    <h2 class="fw-bold mb-3">Welcome back, <?php echo htmlspecialchars($_SESSION["first_name"]); ?>!</h2>
                    <p class="lead">Track your pet's health, manage appointments, and stay up-to-date with vaccinations all in one place.</p>
                    <?php if ($dueVaccinations > 0): ?>
                        <div class="alert alert-light text-primary mt-3 mb-0 d-inline-block">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            You have <?php echo $dueVaccinations; ?> vaccination<?php echo $dueVaccinations != 1 ? 's' : ''; ?> due in the next 30 days.
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-md-4 d-none d-md-block">
                    <img src="../assets/images/dashboard-illustration.svg" alt="Pet health" class="img-fluid" style="clip-path: polygon(10% 0, 100% 0, 100% 100%, 0% 100%);">
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
                        <i class="fas fa-paw"></i>
                    </div>
                    <div>
                        <h3 class="card-stats-value mb-0 counter" data-target="<?php echo $petCount; ?>">0</h3>
                        <div class="card-stats-title">Registered Pets</div
                        <h3 class="card-stats-value mb-0 counter" data-target="<?php echo $petCount; ?>">0</h3>
                        <div class="card-stats-title">Registered Pets</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card dashboard-card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-gradient-warning text-white me-3">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div>
                        <h3 class="card-stats-value mb-0 counter" data-target="<?php echo $appointmentCount; ?>">0</h3>
                        <div class="card-stats-title">Upcoming Appointments</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card dashboard-card h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-gradient-success text-white me-3">
                        <i class="fas fa-syringe"></i>
                    </div>
                    <div>
                        <h3 class="card-stats-value mb-0 counter" data-target="<?php echo $dueVaccinations; ?>">0</h3>
                        <div class="card-stats-title">Vaccinations Due</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- My Pets Section -->
        <div class="col-lg-6">
            <div class="card dashboard-card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center p-3">
                    <h5 class="mb-0">
                        <i class="fas fa-paw text-primary me-2"></i> My Pets
                    </h5>
                    <a href="pets.php" class="btn btn-sm btn-outline-primary rounded-pill">
                        View All
                    </a>
                </div>
                <div class="card-body p-0">
                    <?php if (count($pets) > 0): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($pets as $index => $pet): ?>
                                <?php if ($index < 3): // Show only 3 pets on dashboard ?>
                                    <li class="list-group-item list-group-item-action d-flex align-items-center p-3">
                                        <div class="pet-avatar me-3">
                                            <?php if (!empty($pet['profile_image'])): ?>
                                                <img src="../uploads/pets/<?php echo $pet['profile_image']; ?>" alt="<?php echo htmlspecialchars($pet['name']); ?>" class="rounded-circle" width="50" height="50">
                                            <?php else: ?>
                                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                    <i class="fas fa-paw text-primary"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0"><?php echo htmlspecialchars($pet['name']); ?></h6>
                                            <small class="text-muted"><?php echo htmlspecialchars($pet['species']); ?> · <?php echo htmlspecialchars($pet['breed']); ?></small>
                                        </div>
                                        <a href="pet_details.php?id=<?php echo $pet['pet_id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill">
                                            <i class="fas fa-eye me-1"></i> View
                                        </a>
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="text-center p-4">
                            <img src="../assets/images/empty-pets.svg" alt="No pets" class="img-fluid mb-3" style="max-width: 150px;">
                            <h5>No Pets Added Yet</h5>
                            <p class="text-muted">Start by adding your pets to manage their health records</p>
                            <a href="pets.php?action=add" class="btn btn-primary rounded-pill">
                                <i class="fas fa-plus me-2"></i> Add New Pet
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Upcoming Appointments Section -->
        <div class="col-lg-6">
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
                        <ul class="list-group list-group-flush">
                            <?php foreach ($appointments as $index => $appointment): ?>
                                <?php if ($index < 3): // Show only 3 appointments on dashboard ?>
                                    <li class="list-group-item list-group-item-action p-3">
                                        <div class="d-flex align-items-center">
                                            <div class="appointment-date text-center me-3">
                                                <div class="date-badge bg-light rounded p-2">
                                                    <div class="month text-primary"><?php echo date('M', strtotime($appointment['appointment_date'])); ?></div>
                                                    <div class="day fw-bold h4 mb-0"><?php echo date('d', strtotime($appointment['appointment_date'])); ?></div>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-0">
                                                    <?php echo htmlspecialchars($appointment['pet_name']); ?> with Dr. <?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?>
                                                </h6>
                                                <div class="d-flex align-items-center mt-1">
                                                    <small class="text-muted me-3">
                                                        <i class="fas fa-clock me-1"></i>
                                                        <?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?>
                                                    </small>
                                                    <?php if (!empty($appointment['reason'])): ?>
                                                        <small class="text-muted">
                                                            <i class="fas fa-comment-dots me-1"></i>
                                                            <?php echo htmlspecialchars(substr($appointment['reason'], 0, 50)); ?>
                                                            <?php echo (strlen($appointment['reason']) > 50) ? '...' : ''; ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="appointment-actions">
                                                <a href="appointments.php?action=view&id=<?php echo $appointment['appointment_id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill">
                                                    Details
                                                </a>
                                            </div>
                                        </div>
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="text-center p-4">
                            <img src="../assets/images/empty-appointments.svg" alt="No appointments" class="img-fluid mb-3" style="max-width: 150px;">
                            <h5>No Upcoming Appointments</h5>
                            <p class="text-muted">Schedule veterinary visits to maintain your pet's health</p>
                            <a href="appointments.php?action=schedule" class="btn btn-primary rounded-pill">
                                <i class="fas fa-calendar-plus me-2"></i> Schedule Appointment
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Pet Health Chart -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card dashboard-card">
                <div class="card-header bg-white p-3">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line text-primary me-2"></i> Pet Health Overview
                    </h5>
                </div>
                <div class="card-body">
                    <div style="height: 300px;">
                        <canvas id="petHealthChart" class="chart-canvas" data-chart-type="line"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Initialize dashboard charts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('petHealthChart').getContext('2d');
    
    // Sample data - in a production environment, this would come from PHP backend
    const petHealthData = {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [
            {
                label: 'Vet Visits',
                data: [2, 1, 0, 1, 0, 1],
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                tension: 0.4,
                fill: true
            },
            {
                label: 'Vaccinations',
                data: [1, 0, 1, 0, 0, 1],
                borderColor: '#20c997',
                backgroundColor: 'rgba(32, 201, 151, 0.1)',
                tension: 0.4,
                fill: true
            }
        ]
    };
    
    new Chart(ctx, {
        type: 'line',
        data: petHealthData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
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
});
</script>

<?php include '../includes/footer.php'; ?>