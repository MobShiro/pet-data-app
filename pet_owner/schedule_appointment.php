<?php
session_start();
require_once('../config/database.php');
require_once('../includes/functions.php');

// Check if user is logged in and is a pet owner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pet_owner') {
    header('Location: ../auth/login.php');
    exit;
}

$error_message = '';
$success_message = '';
$user_id = $_SESSION['user_id'];

try {
    // Create database connection
    $database = new Database();
    $conn = $database->getConnection();
    
    // Get user's pets
    $stmt = $database->prepare("SELECT id, name, species, breed FROM pets WHERE owner_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $pets_result = $stmt->get_result();
    
    // Get available veterinarians
    $vets_query = "SELECT u.id, u.first_name, u.last_name, v.specialization 
                  FROM users u 
                  JOIN veterinarians v ON u.id = v.user_id 
                  WHERE u.role = 'veterinarian' AND u.status = 'active'
                  ORDER BY u.last_name, u.first_name";
    $vets_result = $database->query($vets_query);
    
    // Process form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $pet_id = $database->escapeString($_POST['pet_id']);
        $vet_id = $database->escapeString($_POST['vet_id']);
        $appointment_date = $database->escapeString($_POST['appointment_date']);
        $appointment_time = $database->escapeString($_POST['appointment_time']);
        $reason = $database->escapeString($_POST['reason']);
        
        // Combine date and time
        $appointment_datetime = $appointment_date . ' ' . $appointment_time . ':00';
        
        // Check if appointment datetime is in the future
        $now = date('Y-m-d H:i:s');
        if ($appointment_datetime <= $now) {
            throw new Exception("Appointment date and time must be in the future.");
        }
        
        // Check if veterinarian is available at the selected time
        $check_stmt = $database->prepare("SELECT id FROM appointments 
                                         WHERE vet_id = ? 
                                         AND appointment_datetime = ? 
                                         AND status != 'cancelled'");
        $check_stmt->bind_param("is", $vet_id, $appointment_datetime);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            throw new Exception("The selected veterinarian is already booked at this time. Please choose another time.");
        }
        
        // Insert appointment
        $insert_stmt = $database->prepare("INSERT INTO appointments 
                                         (pet_id, vet_id, owner_id, appointment_datetime, reason, status, created_at) 
                                         VALUES (?, ?, ?, ?, ?, 'pending', NOW())");
        $insert_stmt->bind_param("iiiss", $pet_id, $vet_id, $user_id, $appointment_datetime, $reason);
        
        if ($insert_stmt->execute()) {
            $appointment_id = $insert_stmt->insert_id;
            
            // Create notification for veterinarian
            createNotification($vet_id, 'New appointment scheduled', 'You have a new appointment request.', 'appointment', $appointment_id);
            
            $success_message = "Appointment scheduled successfully! The veterinarian will confirm your appointment shortly.";
        } else {
            throw new Exception("Error scheduling appointment. Please try again.");
        }
    }
    
} catch (Exception $e) {
    $error_message = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Appointment - Vet Anywhere</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <!-- Include flatpickr for date/time selection -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>
<body>
    <?php include('../includes/pet_owner_header.php'); ?>
    
    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-calendar-plus"></i> Schedule an Appointment</h1>
        </div>
        
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <div class="content-card">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="form-group">
                    <label for="pet_id"><i class="fas fa-paw"></i> Select Pet</label>
                    <select id="pet_id" name="pet_id" required>
                        <option value="">-- Select Pet --</option>
                        <?php while ($pet = $pets_result->fetch_assoc()): ?>
                            <option value="<?php echo $pet['id']; ?>"><?php echo $pet['name'] . ' (' . $pet['species'] . ' - ' . $pet['breed'] . ')'; ?></option>
                        <?php endwhile; ?>
                    </select>
                    <div class="helper-text">
                        <a href="add_pet.php"><i class="fas fa-plus-circle"></i> Add a New Pet</a>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="vet_id"><i class="fas fa-user-md"></i> Select Veterinarian</label>
                    <select id="vet_id" name="vet_id" required>
                        <option value="">-- Select Veterinarian --</option>
                        <?php while ($vet = $vets_result->fetch_assoc()): ?>
                            <option value="<?php echo $vet['id']; ?>">Dr. <?php echo $vet['first_name'] . ' ' . $vet['last_name'] . ' (' . $vet['specialization'] . ')'; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="appointment_date"><i class="fas fa-calendar-day"></i> Date</label>
                        <input type="text" id="appointment_date" name="appointment_date" class="datepicker" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="appointment_time"><i class="fas fa-clock"></i> Time</label>
                        <input type="text" id="appointment_time" name="appointment_time" class="timepicker" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="reason"><i class="fas fa-comment-medical"></i> Reason for Visit</label>
                    <textarea id="reason" name="reason" rows="4" required></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-calendar-check"></i> Schedule Appointment</button>
                    <a href="dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
                </div>
            </form>
        </div>
    </div>
    
    <?php include('../includes/footer.php'); ?>
    
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        // Initialize date picker
        flatpickr(".datepicker", {
            minDate: "today",
            dateFormat: "Y-m-d",
            disable: [
                function(date) {
                    // Disable weekends if needed
                    // return (date.getDay() === 0 || date.getDay() === 6);
                }
            ]
        });
        
        // Initialize time picker
        flatpickr(".timepicker", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            minTime: "08:00",
            maxTime: "18:00",
            minuteIncrement: 30
        });
    </script>
</body>
</html>