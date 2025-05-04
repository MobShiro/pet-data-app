<?php
session_start();
require_once('../config/database.php');
require_once('../includes/functions.php');

$error_message = '';
$success_message = '';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

// Process registration form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Create database connection
        $database = new Database();
        $conn = $database->getConnection();
        
        // Get form data and sanitize
        $username = $database->escapeString($_POST['username']);
        $email = $database->escapeString($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $first_name = $database->escapeString($_POST['first_name']);
        $last_name = $database->escapeString($_POST['last_name']);
        $phone = $database->escapeString($_POST['phone']);
        $role = 'pet_owner'; // Default role for registration
        
        // Validate data
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format.");
        }
        
        if (strlen($password) < 8) {
            throw new Exception("Password must be at least 8 characters.");
        }
        
        if ($password !== $confirm_password) {
            throw new Exception("Passwords do not match.");
        }
        
        // Check if username or email already exists
        $stmt = $database->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            throw new Exception("Username or email already exists. Please choose a different one.");
        }
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Generate verification token
        $verification_token = bin2hex(random_bytes(32));
        
        // Insert new user
        $stmt = $database->prepare("INSERT INTO users (username, email, password, first_name, last_name, phone, role, status, verification_token, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $status = 'pending'; // User needs verification
        $stmt->bind_param("sssssssss", $username, $email, $hashed_password, $first_name, $last_name, $phone, $role, $status, $verification_token);
        
        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;
            
            // Send verification email
            sendVerificationEmail($email, $verification_token, $username);
            
            $success_message = "Registration successful! Please check your email to verify your account.";
        } else {
            throw new Exception("Error creating account. Please try again.");
        }
        
        $stmt->close();
        $database->closeConnection();
        
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

function sendVerificationEmail($email, $token, $username) {
    // Build verification URL
    $verification_link = "http://localhost/vet_anywhere/auth/verify.php?token=$token";
    
    // Email subject
    $subject = "Vet Anywhere - Verify Your Email";
    
    // Email body
    $message = "
    <html>
    <head>
        <title>Verify Your Email</title>
    </head>
    <body>
        <h2>Welcome to Vet Anywhere!</h2>
        <p>Hello $username,</p>
        <p>Thank you for registering. Please click the link below to verify your email address:</p>
        <p><a href='$verification_link'>Verify My Email</a></p>
        <p>Or copy and paste this link into your browser:</p>
        <p>$verification_link</p>
        <p>This link will expire in 24 hours.</p>
        <p>Thank you,<br>The Vet Anywhere Team</p>
    </body>
    </html>";
    
    // Log email instead of sending (for development)
    error_log("DEV MODE - Email would be sent to: $email");
    error_log("Subject: $subject");
    error_log("Message: " . substr($message, 0, 100) . "...");
    
    // For development - automatically verify the user instead of sending email
    // Remove this in production
    $db = new Database();
    $conn = $db->getConnection();
    $stmt = $db->prepare("UPDATE users SET status = 'active' WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    
    return true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Vet Anywhere</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="auth-page">
        <div class="auth-container">
            <!-- Left side with image and overlay text -->
            <div class="auth-image-side">
                <div class="overlay"></div>
                <div class="auth-welcome">
                    <h2 class="slide-in-right">Welcome to Vet Anywhere</h2>
                    <p class="slide-in-right delay-1">Your trusted partner for pet healthcare</p>
                    <div class="auth-features slide-in-right delay-2">
                        <div class="auth-feature">
                            <i class="fas fa-stethoscope"></i>
                            <span>Expert Veterinarians</span>
                        </div>
                        <div class="auth-feature">
                            <i class="fas fa-video"></i>
                            <span>Virtual Consultations</span>
                        </div>
                        <div class="auth-feature">
                            <i class="fas fa-calendar-check"></i>
                            <span>Easy Scheduling</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right side with form -->
            <div class="auth-form-side fade-in">
                <div class="auth-form-container">
                    <div class="auth-logo">
                        <img src="../assets/images/logo.png" alt="Vet Anywhere Logo" class="pulse-on-hover">
                        <h1>Create Your Account</h1>
                    </div>
                    
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger slide-in">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo $error_message; ?>
                            <span class="alert-close">&times;</span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success slide-in">
                            <i class="fas fa-check-circle"></i>
                            <?php echo $success_message; ?>
                            <span class="alert-close">&times;</span>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="modern-form">
                        <div class="form-row">
                            <div class="form-floating animate-field">
                                <input type="text" id="first_name" name="first_name" class="form-control" required placeholder="First Name">
                                <label for="first_name"><i class="fas fa-user"></i> First Name</label>
                                <div class="form-focus-border"></div>
                            </div>
                            
                            <div class="form-floating animate-field">
                                <input type="text" id="last_name" name="last_name" class="form-control" required placeholder="Last Name">
                                <label for="last_name"><i class="fas fa-user"></i> Last Name</label>
                                <div class="form-focus-border"></div>
                            </div>
                        </div>
                        
                        <div class="form-floating animate-field">
                            <input type="text" id="username" name="username" class="form-control" required placeholder="Username">
                            <label for="username"><i class="fas fa-user-circle"></i> Username</label>
                            <div class="form-focus-border"></div>
                        </div>
                        
                        <div class="form-floating animate-field">
                            <input type="email" id="email" name="email" class="form-control" required placeholder="Email">
                            <label for="email"><i class="fas fa-envelope"></i> Email</label>
                            <div class="form-focus-border"></div>
                        </div>
                        
                        <div class="form-floating animate-field">
                            <input type="tel" id="phone" name="phone" class="form-control" required placeholder="Phone">
                            <label for="phone"><i class="fas fa-phone"></i> Phone</label>
                            <div class="form-focus-border"></div>
                        </div>
                        
                        <div class="form-floating animate-field">
                            <input type="password" id="password" name="password" class="form-control" required minlength="8" placeholder="Password">
                            <label for="password"><i class="fas fa-lock"></i> Password</label>
                            <i class="toggle-password fas fa-eye-slash"></i>
                            <div class="form-focus-border"></div>
                        </div>
                        
                        <div class="form-floating animate-field">
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required minlength="8" placeholder="Confirm Password">
                            <label for="confirm_password"><i class="fas fa-lock"></i> Confirm Password</label>
                            <i class="toggle-password fas fa-eye-slash"></i>
                            <div class="form-focus-border"></div>
                        </div>
                        
                        <div class="form-group checkbox-group">
                            <label class="custom-checkbox">
                                <input type="checkbox" name="terms" required>
                                <span class="checkmark"></span>
                                <span>I agree to the <a href="../terms.php">Terms of Service</a> and <a href="../privacy.php">Privacy Policy</a></span>
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block btn-gradient">
                                <span>Create Account</span>
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                        
                        <div class="form-links text-center">
                            <p>Already have an account? <a href="login.php" class="animated-link">Sign In</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Password toggle visibility
            const togglePasswords = document.querySelectorAll('.toggle-password');
            togglePasswords.forEach(icon => {
                icon.addEventListener('click', function() {
                    const input = this.previousElementSibling.previousElementSibling;
                    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                    input.setAttribute('type', type);
                    this.classList.toggle('fa-eye');
                    this.classList.toggle('fa-eye-slash');
                });
            });
            
            // Password validation with visual feedback
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            
            function validatePassword() {
                if(password.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity('Passwords do not match');
                    confirmPassword.parentElement.classList.add('error');
                    confirmPassword.parentElement.classList.remove('success');
                } else {
                    confirmPassword.setCustomValidity('');
                    confirmPassword.parentElement.classList.remove('error');
                    if(password.value) {
                        confirmPassword.parentElement.classList.add('success');
                    }
                }
            }
            
            password.addEventListener('input', validatePassword);
            confirmPassword.addEventListener('input', validatePassword);
            
            // Close alert buttons
            const alertCloseButtons = document.querySelectorAll('.alert-close');
            alertCloseButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const alert = this.parentElement;
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        alert.style.display = 'none';
                    }, 300);
                });
            });
            
            // Form field animation on focus/blur
            const formFields = document.querySelectorAll('.form-control');
            formFields.forEach(field => {
                // Check if field has value on page load
                if(field.value !== '') {
                    field.parentElement.classList.add('active');
                }
                
                field.addEventListener('focus', function() {
                    this.parentElement.classList.add('active', 'focused');
                });
                
                field.addEventListener('blur', function() {
                    this.parentElement.classList.remove('focused');
                    if(this.value === '') {
                        this.parentElement.classList.remove('active');
                    }
                });
            });
            
            // Add animation classes on page load
            document.querySelectorAll('.animate-field').forEach((el, index) => {
                el.classList.add('fade-in');
                el.style.animationDelay = (index * 0.1) + 's';
            });
        });
    </script>
</body>
</html>