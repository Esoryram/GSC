<?php
session_start();
include("config.php");

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Validate inputs
    $errors = [];
    
    if (empty($username) || empty($password)) {
        $errors[] = "Please fill in both username and password fields";
    }
    
    if (empty($errors)) {
        // Check if user exists
        $sql = "SELECT * FROM accounts WHERE Username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['Password'])) {
                // Check if user is student_staff
                if ($user['Role'] == 'student_staff') {
                    // Password is correct and role is student_staff, set session variables
                    $_SESSION['accountid'] = $user['AccountID'];
                    $_SESSION['username'] = $user['Username'];
                    $_SESSION['name'] = $user['Name'];
                    $_SESSION['role'] = $user['Role'];
                    
                    // Redirect to student/staff dashboard
                    header("Location: userdb.php");
                    exit();
                } else {
                    // User is not student_staff
                    $errors[] = "This account cannot access the student/staff portal";
                }
            } else {
                // Invalid password
                $errors[] = "The username or password you entered is incorrect";
            }
        } else {
            // User not found
            $errors[] = "No account found with this username";
        }
        
        $stmt->close();
    }
    
    // If there are errors, store them in session
    if (!empty($errors)) {
        $_SESSION['login_errors'] = $errors;
        $_SESSION['form_data'] = ['username' => $username];
        header("Location: user_login.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Salle - Student/Staff Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
            font-weight: 400;
        }

        body {
            background: linear-gradient(135deg, #087830 0%, #3c4142 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        /* Enhanced background with geometric patterns */
        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle at 20% 80%, rgba(174, 209, 101, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(103, 208, 170, 0.15) 0%, transparent 50%),
                linear-gradient(45deg, transparent 49%, rgba(78, 198, 106, 0.05) 50%, transparent 51%),
                linear-gradient(-45deg, transparent 49%, rgba(78, 198, 106, 0.05) 50%, transparent 51%);
            background-size: 100% 100%, 100% 100%, 50px 50px, 50px 50px;
            z-index: -1;
        }

        /* Logo styling - Made smaller */
        .logo img {
            max-width: 120px;
            height: auto;
            display: block;
            margin: 0 auto 15px auto;
        }

        /* Floating shapes animation */
        .floating-shapes {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }

        .shape {
            position: absolute;
            opacity: 0.1;
            border-radius: 50%;
            background: #AED14F;
            animation: float 15s infinite linear;
        }

        .shape:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 120px;
            height: 120px;
            top: 60%;
            left: 80%;
            background: #67D0AA;
            animation-delay: -5s;
        }

        .shape:nth-child(3) {
            width: 60px;
            height: 60px;
            top: 80%;
            left: 20%;
            background: #4ec66a;
            animation-delay: -10s;
        }

        .shape:nth-child(4) {
            width: 100px;
            height: 100px;
            top: 10%;
            left: 70%;
            background: #087830;
            animation-delay: -7s;
        }

        @keyframes float {
            0% {
                transform: translateY(0) rotate(0deg);
            }
            50% {
                transform: translateY(-20px) rotate(180deg);
            }
            100% {
                transform: translateY(0) rotate(360deg);
            }
        }

        .container {
            width: 100%;
            max-width: 450px;
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        header {
            background-color: #087830;
            color: white;
            padding: 15px 40px 10px 40px;
            text-align: center;
            position: relative;
        }

        .back-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.3s ease;
            text-decoration: none;
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .page-title {
            color: #AED14F;
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .page-subtitle {
            color: white;
            font-size: 1rem;
            opacity: 0.9;
        }

        .login-form {
            padding: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #3c4142;
            font-weight: 600;
        }

        .input-with-icon {
            position: relative;
        }

        .input-with-icon i:first-child {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #087830;
        }

        .input-with-icon input {
            width: 100%;
            padding: 15px 50px 15px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .input-with-icon input:focus {
            border-color: #087830;
            outline: none;
            box-shadow: 0 0 0 3px rgba(8, 120, 48, 0.1);
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .toggle-password:hover {
            color: #087830;
        }

        .login-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #087830 0%, #4ec66a 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(8, 120, 48, 0.3);
        }

        .signup-link {
            text-align: center;
            margin-top: 25px;
            color: #3c4142;
        }

        .signup-link a {
            color: #087830;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .signup-link a:hover {
            color: #4ec66a;
            text-decoration: underline;
        }

        .error-message {
            background: #ffebee;
            color: #c62828;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #c62828;
            font-size: 0.9rem;
            animation: slideIn 0.3s ease-out;
        }

        .error-message p,
        .success-message p {
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .success-message {
            background: #e8f5e8;
            color: #2e7d32;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #2e7d32;
            font-size: 0.9rem;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        footer {
            background-color: #3c4142;
            color: white;
            text-align: center;
            padding: 20px;
            font-size: 0.9rem;
        }

        @media (max-width: 480px) {
            .container {
                max-width: 100%;
            }
            
            .login-form {
                padding: 30px 25px;
            }
            
            .logo img {
                max-width: 100px;
            }
            
            header {
                padding: 20px 30px 15px 30px;
            }
        }
    </style>
</head>
<body>
    <!-- Enhanced background with floating shapes -->
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>
    
    <div class="container">
        <header>
            <a href="index.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="logo">
                <img src="img/LSULogo.png" alt="LSU Logo">
            </div>
            <h1 class="page-title">User Login</h1>
            <p class="page-subtitle">Access your student or staff account</p>
        </header>
        
        <div class="login-form">
            <?php if (isset($_SESSION['login_errors'])): ?>
                <div class="error-message">
                    <?php 
                    foreach ($_SESSION['login_errors'] as $error) {
                        echo "<p><i class='fas fa-exclamation-circle'></i> $error</p>";
                    }
                    unset($_SESSION['login_errors']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['success']) && $_GET['success'] == 'account_created'): ?>
                <div class="success-message">
                    <p><i class="fas fa-check-circle"></i> Account created successfully! Please login.</p>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['success']) && $_GET['success'] == 'logged_out'): ?>
                <div class="success-message">
                    <p><i class="fas fa-info-circle"></i> You have been successfully logged out.</p>
                </div>
            <?php endif; ?>

            <form action="user_login.php" method="POST" id="loginForm">
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" placeholder="Enter your username" required 
                               value="<?php echo isset($_SESSION['form_data']['username']) ? htmlspecialchars($_SESSION['form_data']['username']) : ''; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                        <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                    </div>
                </div>
                
                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i> User Login
                </button>
            </form>
            
            <div class="signup-link">
                Don't have an account? <a href="signup.php">Sign Up</a>
            </div>
        </div>
        
        <footer>
            <p>La Salle University &copy; 2025. All rights reserved.</p>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Show/Hide Password Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            
            // Toggle password visibility
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
            
            // Add some interactive effects
            const inputs = document.querySelectorAll('input');
            
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'scale(1.02)';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'scale(1)';
                });
            });
            
            // Form submission animation
            const form = document.getElementById('loginForm');
            form.addEventListener('submit', function(e) {
                const username = document.getElementById('username').value.trim();
                const password = document.getElementById('password').value.trim();
                
                // Basic client-side validation
                if (!username || !password) {
                    e.preventDefault();
                    // Show inline error instead of SweetAlert
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'error-message';
                    errorDiv.innerHTML = '<p><i class="fas fa-exclamation-circle"></i> Please fill in both username and password fields</p>';
                    
                    // Insert error message at the top of the form
                    const form = document.getElementById('loginForm');
                    const firstFormGroup = form.querySelector('.form-group');
                    form.insertBefore(errorDiv, firstFormGroup);
                    
                    // Remove error after 5 seconds
                    setTimeout(() => {
                        errorDiv.remove();
                    }, 5000);
                    return;
                }
                
                const btn = this.querySelector('.login-btn');
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';
                btn.disabled = true;
            });
            
            // Clear session form data
            <?php unset($_SESSION['form_data']); ?>
        });
    </script>
</body>
</html>