<?php
session_start();
require_once('../config/database.php');
require_once('../includes/functions.php');

// Check if user is logged in and is a veterinarian
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'veterinarian') {
    header('Location: ../auth/login.php');
    exit;
}

$error_message = '';
$success_message = '';
$vet_id = $_SESSION['user_id'];

// Get appointment ID from URL
$appointment_id = isset($_GET['appointment_id']) ? intval($_GET['appointment_id']) : 0;

try {
    // Create database connection
    $database = new Database();
    $conn = $database->getConnection();
    
    // Get appointment details
    $stmt = $database->prepare("SELECT a.*, p.name AS pet_name, p.species, p.breed, 
                              CONCAT(u.first_name, ' ', u.last_name) AS owner_name, u.email AS owner_email
                              FROM appointments a
                              JOIN pets p ON a.pet_id = p.id
                              JOIN users u ON a.owner_id = u.id
                              WHERE a.id = ? AND a.vet_id = ? AND a.status = 'confirmed'");
    $stmt->bind_param("ii", $appointment_id, $vet_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        header('Location: appointments.php');
        exit;
    }
    
    $appointment = $result->fetch_assoc();
    
    // Process form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $diagnosis = $database->escapeString($_POST['diagnosis']);
        $treatment = $database->escapeString($_POST['treatment']);
        $prescription = $database->escapeString($_POST['prescription']);
        $notes = $database->escapeString($_POST['notes']);
        $follow_up_date = !empty($_POST['follow_up_date']) ? $database->escapeString($_POST['follow_up_date']) : NULL;
        
        // Insert medical record
        $insert_stmt = $database->prepare("INSERT INTO medical_records 
                                         (pet_id, appointment_id, vet_id, diagnosis, treatment, prescription, notes, follow_up_date, created_at) 
                                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $insert_stmt->bind_param("iiissssss", $appointment['pet_id'], $appointment_id, $vet_id, $diagnosis, $treatment, $prescription, $notes, $follow_up_date);
        
        if ($insert_stmt->execute()) {
            $record_id = $insert_stmt->insert_id;
            
            // Update appointment status
            $update_stmt = $database->prepare("UPDATE appointments SET status = 'completed' WHERE id = ?");
            $update_stmt->bind_param("i", $appointment_id);
            $update_stmt->execute();
            
            // Create notification for pet owner
            createNotification($appointment['owner_id'], 'Medical record added', 'A new medical record has been added for your pet ' . $appointment['pet_name'], 'medical_record', $record_id);
            
            // Send email to pet owner
            sendMedicalRecordEmail($appointment['owner_email'], $appointment['owner_name'], $appointment['pet_name'], $diagnosis, $treatment);
            
            $success_message = "Medical record added successfully!";
        } else {
            throw new Exception("Error adding medical record. Please try again.");
        }
    }
    
} catch (Exception $e) {
    $error_message = $e->getMessage();
}

function sendMedicalRecordEmail($email, $owner_name, $pet_name, $diagnosis, $treatment) {
    // Email subject
    $subject = "Vet Anywhere - Medical Record Update for $pet_name";
    
    // Email body
    $message = "
    <html>
    <head>
        <title>Medical Record Update</title>
    </head>
    <body>
        <h2>Medical Record Update for $pet_name</h2>
        <p>Hello $owner_name,</p>
        <p>We've updated the medical records for your pet $pet_name following today's appointment.</p>
        <h3>Diagnosis:</h3>
        <p>$diagnosis</p>
        <h3>Treatment Plan:</h3>
        <p>$treatment</p>
        <p>You can log in to your Vet Anywhere account to view the complete medical record including any prescriptions.</p>
        <p>Thank you for choosing Vet Anywhere for your pet's healthcare needs.</p>
        <p>Best regards,<br>The Vet Anywhere Team</p>
    </body>
    </html>
    ";
    
    // Email headers
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Vet Anywhere <noreply@vetanywhere.com>\r\n";
    
    // Send email
    mail($email, $subject, $message, $headers);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Medical Record - Vet Anywhere</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <!-- Include flatpickr for date selection -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>
<body>
    <?php include('../includes/veterinarian_header.php'); ?>
    
    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-file-medical"></i> Add Medical Record</h1>
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
            <div class="appointment-info">
                <h2>Appointment Details</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="label">Pet:</span>
                        <span class="value"><?php echo $appointment['pet_name']; ?> (<?php echo $appointment['species'] . ' - ' . $appointment['breed']; ?>)</span>
                    </div>
                    <div class="info-item">
                        <span class="label">Owner:</span>
                        <span class="value"><?php echo $appointment['owner_name']; ?></span>
                    </div>
                    <div class="info-item">
                        <span class="label">Date/Time:</span>
                        <span class="value"><?php echo date('F j, Y, g:i a', strtotime($appointment['appointment_datetime'])); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="label">Reason:</span>
                        <span class="value"><?php echo $appointment['reason']; ?></span>
                    </div>
                </div>
            </div>
            
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . '?appointment_id=' . $appointment_id); ?>">
                <div class="form-group">
                    <label for="diagnosis"><i class="fas fa-stethoscope"></i> Diagnosis</label>
                    <textarea id="diagnosis" name="diagnosis" rows="3" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="treatment"><i class="fas fa-procedures"></i> Treatment</label>
                    <textarea id="treatment" name="treatment" rows="3" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="prescription"><i class="fas fa-prescription-bottle-alt"></i> Prescription</label>
                    <textarea id="prescription" name="prescription" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="notes"><i class="fas fa-clipboard"></i> Additional Notes</label>
                    <textarea id="notes" name="notes" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="follow_up_date"><i class="fas fa-calendar-check"></i> Follow-up Date (optional)</label>
                    <input type="text" id="follow_up_date" name="follow_up_date" class="datepicker">
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Medical Record</button>
                    <a href="appointments.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Appointments</a>
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
            dateFormat: "Y-m-d"
        });
    </script>
</body>
</html>