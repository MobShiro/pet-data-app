<?php
session_start();
require_once('../config/Database.php');
require_once('../includes/functions.php');

// Check if user is logged in and is a pet owner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pet_owner') {
    header('Location: ../auth/login.php');
    exit;
}

$error_message = '';
$pet_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];

try {
    // Create database connection
    $database = new Database();
    $conn = $database->getConnection();
    
    // Get pet details
    $stmt = $database->prepare("SELECT * FROM pets WHERE id = ? AND owner_id = ?");
    $stmt->bind_param("ii", $pet_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        header('Location: my_pets.php');
        exit;
    }
    
    $pet = $result->fetch_assoc();
    
    // Get medical records
    $records_stmt = $database->prepare("SELECT mr.*, 
                                      CONCAT(u.first_name, ' ', u.last_name) AS vet_name
                                      FROM medical_records mr
                                      JOIN users u ON mr.vet_id = u.id
                                      WHERE mr.pet_id = ?
                                      ORDER BY mr.created_at DESC");
    $records_stmt->bind_param("i", $pet_id);
    $records_stmt->execute();
    $records_result = $records_stmt->get_result();
    
    // Get vaccination history
    $vaccinations_stmt = $database->prepare("SELECT * FROM vaccinations WHERE pet_id = ? ORDER BY vaccination_date DESC");
    $vaccinations_stmt->bind_param("i", $pet_id);
    $vaccinations_stmt->execute();
    $vaccinations_result = $vaccinations_stmt->get_result();
    
    // Get appointment history
    $appointments_stmt = $database->prepare("SELECT a.*, 
                                           CONCAT(u.first_name, ' ', u.last_name) AS vet_name
                                           FROM appointments a
                                           JOIN users u ON a.vet_id = u.id
                                           WHERE a.pet_id = ?
                                           ORDER BY a.appointment_datetime DESC");
    $appointments_stmt->bind_param("i", $pet_id);
    $appointments_stmt->execute();
    $appointments_result = $appointments_stmt->get_result();
    
} catch (Exception $e) {
    $error_message = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pet['name']; ?> - Vet Anywhere</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <?php include('../includes/pet_owner_header.php'); ?>
    
    <div class="container">
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <div class="page-header">
            <h1><i class="fas fa-paw"></i> <?php echo $pet['name']; ?></h1>
            <div class="action-buttons">
                <a href="edit_pet.php?id=<?php echo $pet_id; ?>" class="btn btn-primary"><i class="fas fa-edit"></i> Edit Pet</a>
                <a href="schedule_appointment.php?pet_id=<?php echo $pet_id; ?>" class="btn btn-secondary"><i class="fas fa-calendar-plus"></i> Schedule Appointment</a>
            </div>
        </div>
        
        <div class="content-row">
            <div class="content-column">
                <div class="content-card pet-profile">
                    <div class="pet-header">
                        <?php if (!empty($pet['photo'])): ?>
                            <img src="../<?php echo $pet['photo']; ?>" alt="<?php echo $pet['name']; ?>" class="pet-image">
                        <?php else: ?>
                            <div class="pet-image-placeholder">
                                <i class="fas fa-paw"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="pet-basic-info">
                            <h2><?php echo $pet['name']; ?></h2>
                            <p><?php echo $pet['species'] . ' - ' . $pet['breed']; ?></p>
                        </div>
                    </div>
                    
                    <div class="pet-details">
                        <div class="detail-item">
                            <span class="label"><i class="fas fa-venus-mars"></i> Gender:</span>
                            <span class="value"><?php echo $pet['gender']; ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="label"><i class="fas fa-birthday-cake"></i> Date of Birth:</span>
                            <span class="value"><?php echo date('F j, Y', strtotime($pet['date_of_birth'])); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="label"><i class="fas fa-weight"></i> Weight:</span>
                            <span class="value"><?php echo $pet['weight']; ?> kg</span>
                        </div>
                        <div class="detail-item">
                            <span class="label"><i class="fas fa-palette"></i> Color:</span>
                            <span class="value"><?php echo $pet['color']; ?></span>
                        </div>
                        <?php if (!empty($pet['microchip_id'])): ?>
                            <div class="detail-item">
                                <span class="label"><i class="fas fa-microchip"></i> Microchip ID:</span>
                                <span class="value"><?php echo $pet['microchip_id']; ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($pet['allergies'])): ?>
                            <div class="detail-item">
                                <span class="label"><i class="fas fa-exclamation-triangle"></i> Allergies:</span>
                                <span class="value"><?php echo $pet['allergies']; ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($pet['medical_conditions'])): ?>
                            <div class="detail-item">
                                <span class="label"><i class="fas fa-heartbeat"></i> Medical Conditions:</span>
                                <span class="value"><?php echo $pet['medical_conditions']; ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="content-column">
                <!-- Tabs for different sections -->
                <div class="tabs">
                    <button class="tab-button active" onclick="openTab(event, 'medical-records')">
                        <i class="fas fa-file-medical"></i> Medical Records
                    </button>
                    <button class="tab-button" onclick="openTab(event, 'vaccinations')">
                        <i class="fas fa-syringe"></i> Vaccinations
                    </button>
                    <button class="tab-button" onclick="openTab(event, 'appointments')">
                        <i class="fas fa-calendar-alt"></i> Appointments
                    </button>
                </div>
                
                <!-- Medical Records Tab -->
                <div id="medical-records" class="tab-content active">
                    <?php if ($records_result->num_rows > 0): ?>
                        <div class="records-list">
                            <?php while ($record = $records_result->fetch_assoc()): ?>
                                <div class="record-item">
                                    <div class="record-header">
                                        <div class="record-date">
                                            <i class="fas fa-calendar-day"></i> <?php echo date('F j, Y', strtotime($record['created_at'])); ?>
                                        </div>
                                        <div class="record-vet">
                                            <i class="fas fa-user-md"></i> Dr. <?php echo $record['vet_name']; ?>
                                        </div>
                                    </div>
                                    <div class="record-content">
                                        <div class="record-section">
                                            <h4>Diagnosis</h4>
                                            <p><?php echo $record['diagnosis']; ?></p>
                                        </div>
                                        <div class="record-section">
                                            <h4>Treatment</h4>
                                            <p><?php echo $record['treatment']; ?></p>
                                        </div>
                                        <?php if (!empty($record['prescription'])): ?>
                                            <div class="record-section">
                                                <h4>Prescription</h4>
                                                <p><?php echo $record['prescription']; ?></p>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($record['notes'])): ?>
                                            <div class="record-section">
                                                <h4>Additional Notes</h4>
                                                <p><?php echo $record['notes']; ?></p>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($record['follow_up_date'])): ?>
                                            <div class="record-footer">
                                                <span class="follow-up">
                                                    <i class="fas fa-calendar-check"></i> Follow-up: <?php echo date('F j, Y', strtotime($record['follow_up_date'])); ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="record-actions">
                                        <a href="download_record.php?id=<?php echo $record['id']; ?>" class="btn-icon" title="Download PDF">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-records">
                            <i class="fas fa-folder-open"></i>
                            <p>No medical records available for this pet.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Vaccinations Tab -->
                <div id="vaccinations" class="tab-content">
                    <?php if ($vaccinations_result->num_rows > 0): ?>
                        <div class="vaccinations-list">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Vaccine</th>
                                        <th>Next Due</th>
                                        <th>Administered By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($vaccination = $vaccinations_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo date('M j, Y', strtotime($vaccination['vaccination_date'])); ?></td>
                                            <td><?php echo $vaccination['vaccine_name']; ?></td>
                                            <td>
                                                <?php if (!empty($vaccination['next_due_date'])): ?>
                                                    <?php echo date('M j, Y', strtotime($vaccination['next_due_date'])); ?>
                                                <?php else: ?>
                                                    N/A
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $vaccination['administered_by']; ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="no-records">
                            <i class="fas fa-syringe"></i>
                            <p>No vaccination records available for this pet.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Appointments Tab -->
                <div id="appointments" class="tab-content">
                    <?php if ($appointments_result->num_rows > 0): ?>
                        <div class="appointments-list">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Date & Time</th>
                                        <th>Veterinarian</th>
                                        <th>Reason</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($appointment = $appointments_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo date('M j, Y, g:i a', strtotime($appointment['appointment_datetime'])); ?></td>
                                            <td>Dr. <?php echo $appointment['vet_name']; ?></td>
                                            <td><?php echo $appointment['reason']; ?></td>
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
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="no-records">
                            <i class="fas fa-calendar-alt"></i>
                            <p>No appointment history available for this pet.</p>
                            <a href="schedule_appointment.php?pet_id=<?php echo $pet_id; ?>" class="btn btn-primary">Schedule an Appointment</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <?php include('../includes/footer.php'); ?>
    
    <script>
        function openTab(evt, tabName) {
            // Hide all tab content
            const tabContents = document.getElementsByClassName("tab-content");
            for (let i = 0; i < tabContents.length; i++) {
                tabContents[i].classList.remove("active");
            }
            
            // Remove active class from all tab buttons
            const tabButtons = document.getElementsByClassName("tab-button");
            for (let i = 0; i < tabButtons.length; i++) {
                tabButtons[i].classList.remove("active");
            }
            
            // Show the selected tab and add active class to the button
            document.getElementById(tabName).classList.add("active");
            evt.currentTarget.classList.add("active");
        }
    </script>
</body>
</html>