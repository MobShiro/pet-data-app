<?php

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check user role
function hasRole($role) {
    if (!isLoggedIn()) {
        return false;
    }
    return $_SESSION['user_type'] == $role;
}

// Redirect to login if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: /vet_anywhere/auth/login.php");
        exit;
    }
}

// Redirect to login if not the required role
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        header("Location: /vet_anywhere/auth/login.php?error=unauthorized");
        exit;
    }
}

// Sanitize input
function sanitize($data) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($data));
}

// Flash messages
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Get user details
function getUserDetails($userId) {
    global $conn;
    $userId = (int)$userId;
    
    $query = "SELECT * FROM users WHERE user_id = $userId";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return false;
}

// Get all pets for an owner
function getOwnerPets($ownerId) {
    global $conn;
    $ownerId = (int)$ownerId;
    
    $query = "SELECT * FROM pets WHERE owner_id = $ownerId ORDER BY name";
    $result = mysqli_query($conn, $query);
    
    $pets = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $pets[] = $row;
    }
    
    return $pets;
}

// Get all medical records for a pet
function getPetMedicalRecords($petId) {
    global $conn;
    $petId = (int)$petId;
    
    $query = "SELECT mr.*, u.first_name, u.last_name 
              FROM medical_records mr
              JOIN veterinarians v ON mr.vet_id = v.vet_id
              JOIN users u ON v.user_id = u.user_id
              WHERE mr.pet_id = $petId
              ORDER BY mr.visit_date DESC";
    
    $result = mysqli_query($conn, $query);
    
    $records = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $records[] = $row;
    }
    
    return $records;
}

// Get all vaccinations for a pet
function getPetVaccinations($petId) {
    global $conn;
    $petId = (int)$petId;
    
    $query = "SELECT v.*, u.first_name, u.last_name 
              FROM vaccinations v
              LEFT JOIN veterinarians vet ON v.vet_id = vet.vet_id
              LEFT JOIN users u ON vet.user_id = u.user_id
              WHERE v.pet_id = $petId
              ORDER BY v.vaccination_date DESC";
    
    $result = mysqli_query($conn, $query);
    
    $vaccinations = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $vaccinations[] = $row;
    }
    
    return $vaccinations;
}

// Get upcoming appointments for pet owner
function getOwnerAppointments($ownerId) {
    global $conn;
    $ownerId = (int)$ownerId;
    
    $query = "SELECT a.*, p.name as pet_name, u.first_name, u.last_name 
              FROM appointments a
              JOIN pets p ON a.pet_id = p.pet_id
              JOIN veterinarians v ON a.vet_id = v.vet_id
              JOIN users u ON v.user_id = u.user_id
              WHERE a.owner_id = $ownerId AND a.status = 'Scheduled'
              ORDER BY a.appointment_date, a.appointment_time";
    
    $result = mysqli_query($conn, $query);
    
    $appointments = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $appointments[] = $row;
    }
    
    return $appointments;
}

// Get upcoming appointments for veterinarian
function getVeterinarianAppointments($vetId) {
    global $conn;
    $vetId = (int)$vetId;
    
    $query = "SELECT a.*, p.name as pet_name, p.species, p.breed, u.first_name as owner_first_name, u.last_name as owner_last_name
              FROM appointments a
              JOIN pets p ON a.pet_id = p.pet_id
              JOIN pet_owners po ON a.owner_id = po.owner_id
              JOIN users u ON po.user_id = u.user_id
              WHERE a.vet_id = $vetId AND a.status = 'Scheduled'
              ORDER BY a.appointment_date, a.appointment_time";
    
    $result = mysqli_query($conn, $query);
    
    $appointments = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $appointments[] = $row;
    }
    
    return $appointments;
}

/**
 * Log user activity in the database
 *
 * @param int $user_id User ID
 * @param string $action Action performed (login, logout, etc.)
 * @param string $details Additional details about the action
 * @return bool True if successful, false otherwise
 */
function logUserActivity($user_id, $action, $details = '') {
    try {
        $database = new Database();
        $conn = $database->getConnection();
        
        $stmt = $database->prepare("INSERT INTO user_activity_logs (user_id, action, details, ip_address, created_at) VALUES (?, ?, ?, ?, NOW())");
        
        // Get user IP address
        $ip_address = $_SERVER['REMOTE_ADDR'];
        
        $stmt->bind_param("isss", $user_id, $action, $details, $ip_address);
        $result = $stmt->execute();
        
        $stmt->close();
        $database->closeConnection();
        
        return $result;
    } catch (Exception $e) {
        // Log the error but don't stop execution
        error_log("Error logging user activity: " . $e->getMessage());
        return false;
    }
}

/**
 * Log failed login attempts
 *
 * @param string $username Username that failed login
 * @return void
 */
function logFailedLoginAttempt($username) {
    try {
        $database = new Database();
        $conn = $database->getConnection();
        
        // Get user IP address
        $ip_address = $_SERVER['REMOTE_ADDR'];
        
        // Check if user exists
        $stmt = $database->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $user_id = null;
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            $user_id = $user['id'];
        }
        
        $stmt->close();
        
        // Log the failed attempt
        $stmt = $database->prepare("INSERT INTO login_attempts (user_id, username, ip_address, status, created_at) VALUES (?, ?, ?, 'failed', NOW())");
        $stmt->bind_param("iss", $user_id, $username, $ip_address);
        $stmt->execute();
        
        $stmt->close();
        $database->closeConnection();
        
    } catch (Exception $e) {
        // Log the error but don't stop execution
        error_log("Error logging failed login attempt: " . $e->getMessage());
    }
}
?>