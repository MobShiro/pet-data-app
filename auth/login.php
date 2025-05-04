<?php
session_start();
require_once('../config/database.php');
require_once('../includes/functions.php');

$error_message = '';
$success_message = '';

// Temporary function definitions if they don't exist in functions.php
if (!function_exists('logUserActivity')) {
    function logUserActivity($user_id, $action, $details = '') {
        // Temporary implementation - just log to error_log
        error_log("User Activity: User ID: $user_id, Action: $action, Details: $details");
        return true;
    }
}

if (!function_exists('logFailedLoginAttempt')) {
    function logFailedLoginAttempt($username) {
        // Temporary implementation - just log to error_log
        error_log("Failed Login Attempt: Username: $username");
    }
}

// Redirect if already logged in - FIXED VERSION
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    // Only redirect if we're not already on the login page
    $current_page = basename($_SERVER['PHP_SELF']);
    if ($current_page == 'login.php') {
        redirectBasedOnRole($_SESSION['role']);
        exit; // Make sure to exit after redirecting
    }
}

// Process login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Create database connection
        $database = new Database();
        $conn = $database->getConnection();
        
        // Get form data and sanitize
        $username = $database->escapeString($_POST['username']);
        $password = $_POST['password'];
        
        // Prepare SQL statement to prevent SQL injection
        $stmt = $database->prepare("SELECT id, username, password, role, status FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Check if user account is active
                if ($user['status'] != 'active') {
                    $error_message = "Your account is not active. Please check your email to verify your account.";
                } else {
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    
                    // Log successful login
                    logUserActivity($user['id'], 'login', 'User logged in successfully');
                    
                    // Redirect based on role
                    redirectBasedOnRole($user['role']);
                }
            } else {
                $error_message = "Invalid password. Please try again.";
                // Log failed login attempt
                logFailedLoginAttempt($username);
            }
        } else {
            $error_message = "Username not found. Please check your credentials.";
            // Log failed login attempt
            logFailedLoginAttempt($username);
        }
        
        $stmt->close();
        $database->closeConnection();
        
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

function redirectBasedOnRole($role) {
    // Safety check to prevent infinite redirects
    static $redirectCount = 0;
    $redirectCount++;
    
    if ($redirectCount > 1) {
        // We've already tried to redirect once, so let's stop here
        error_log("Multiple redirects detected. Breaking redirect chain.");
        echo "Error: Too many redirects. Please contact support.";
        exit;
    }
    
    switch ($role) {
        case 'admin':
            header('Location: ../admin/dashboard.php');
            break;
        case 'veterinarian':
            header('Location: ../veterinarian/dashboard.php');
            break;
        case 'pet_owner':
            header('Location: ../pet_owner/dashboard.php');
            break;
        default:
            header('Location: ../index.php');
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Vet Anywhere</title>
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
                    <h2 class="slide-in-right">Welcome Back!</h2>
                    <p class="slide-in-right delay-1">Log in to access your pet care services</p>
                    <div class="auth-features slide-in-right delay-2">
                        <div class="auth-feature">
                            <i class="fas fa-paw"></i>
                            <span>Track Your Pet's Health</span>
                        </div>
                        <div class="auth-feature">
                            <i class="fas fa-user-md"></i>
                            <span>Connect with Veterinarians</span>
                        </div>
                        <div class="auth-feature">
                            <i class="fas fa-clipboard-list"></i>
                            <span>Manage Appointments</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right side with form -->
            <div class="auth-form-side fade-in">
                <div class="auth-form-container">
                    <div class="auth-logo">
                        <img src="../assets/images/logo.png" alt="Vet Anywhere Logo" class="pulse-on-hover">
                        <h1>Welcome Back</h1>
                        <p>Sign in to your account</p>
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
                        <div class="form-floating animate-field">
                            <input type="text" id="username" name="username" class="form-control" required placeholder="Username">
                            <label for="username"><i class="fas fa-user-circle"></i> Username</label>
                            <div class="form-focus-border"></div>
                        </div>
                        
                        <div class="form-floating animate-field">
                            <input type="password" id="password" name="password" class="form-control" required placeholder="Password">
                            <label for="password"><i class="fas fa-lock"></i> Password</label>
                            <i class="toggle-password fas fa-eye-slash"></i>
                            <div class="form-focus-border"></div>
                        </div>
                        
                        <div class="form-group checkbox-group">
                            <label class="custom-checkbox">
                                <input type="checkbox" name="remember">
                                <span class="checkmark"></span>
                                <span>Remember me</span>
                            </label>
                            <a href="forgot_password.php" class="forgot-password animated-link">Forgot Password?</a>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block btn-gradient">
                                <span>Sign In</span>
                                <i class="fas fa-sign-in-alt"></i>
                            </button>
                        </div>
                        
                        <div class="form-separator">
                            <span>OR</span>
                        </div>
                        
                        <div class="social-login">
                            <button type="button" class="btn btn-outline social-btn">
                                <i class="fab fa-google"></i>
                                <span>Login with Google</span>
                            </button>
                        </div>
                        
                        <div class="form-links text-center">
                            <p>Don't have an account? <a href="register.php" class="animated-link">Create Account</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/scripts.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Password toggle visibility
            const togglePassword = document.querySelector('.toggle-password');
            if (togglePassword) {
                togglePassword.addEventListener('click', function() {
                    const input = this.previousElementSibling.previousElementSibling;
                    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                    input.setAttribute('type', type);
                    this.classList.toggle('fa-eye');
                    this.classList.toggle('fa-eye-slash');
                });
            }
            
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