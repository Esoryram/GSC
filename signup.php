<?php
session_start();

include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = 'student_staff'; // Force student_staff role

    // Validate inputs
    $errors = [];
    
    if (empty($name) || empty($username) || empty($password)) {
        $errors[] = "All fields are required";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }

    // Check if username already exists
    if (empty($errors)) {
        $check = $conn->query("SELECT * FROM accounts WHERE Username='$username'");
        if ($check && $check->num_rows > 0) {
            $errors[] = "Username already exists";
        } elseif (!$check) {
            $errors[] = "Database error: " . $conn->error;
        }
    }
    
    if (empty($errors)) {
        // Hash password and create account
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $insert_result = $conn->query("INSERT INTO accounts (Name, Username, Password, Role) 
                          VALUES ('$name','$username','$hashed_password','$role')");
        
        if ($insert_result) {
            header("Location: user_login.php?success=account_created");
            exit();
        } else {
            $errors[] = "Failed to create account: " . $conn->error;
        }
    }
    
    // If there are errors, store them in session and redirect back
    if (!empty($errors)) {
        $_SESSION['signup_errors'] = $errors;
        $_SESSION['form_data'] = ['name' => $name, 'username' => $username];
        header("Location: signup.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Salle - Sign Up</title>
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

        html, body {
            height: 100%;
            width: 100%;
        }

        body {
            background: linear-gradient(135deg, #087830 0%, #3c4142 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            position: relative;
            overflow: auto;
        }

        body::before {
            content: "";
            position: fixed;
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

        .floating-shapes {
            position: fixed;
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
            margin: auto;
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
            padding: 25px 40px 20px 40px;
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

        .logo img {
            max-width: 120px;
            height: auto;
            display: block;
            margin: 0 auto 15px auto;
            width: 100%;
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

        .signup-form {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 20px;
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

        .signup-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #087830 0%, #4ec66a 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        .signup-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(8, 120, 48, 0.3);
        }

        .signup-btn:active {
            transform: translateY(0);
        }

        .login-link {
            text-align: center;
            margin-top: 25px;
            color: #3c4142;
        }

        .login-link a {
            color: #087830;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .login-link a:hover {
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

        /* Responsive adjustments */
        @media (max-width: 768px) {
            body {
                padding: 15px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .container {
                max-width: 100%;
                border-radius: 15px;
            }
            
            .signup-form {
                padding: 25px 20px;
            }
            
            header {
                padding: 20px 25px 15px 25px;
            }
            
            .logo img {
                max-width: 100px;
            }
            
            .page-title {
                font-size: 1.6rem;
            }
            
            .page-subtitle {
                font-size: 0.95rem;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .container {
                border-radius: 12px;
                max-width: 350px;
            }
            
            header {
                padding: 10px 15px 5px 15px;
            }
            
            .logo img {
                max-width: 125px;
            }
            
            .signup-form {
                padding: 15px 10x;
            }
            
            .page-title {
                font-size: 1.0rem;
            }
            
            .page-subtitle {
                font-size: 0.9rem;
            }
            
            .input-with-icon input {
                padding: 9px 35px 9px 30px;
            }
            
            .signup-btn {
                padding: 9px;
                font-size: 1rem;
            }
            
            footer {
                padding: 12px;
                font-size: 0.8rem;
            }
        }

        @media (max-height: 700px) {
            body {
                align-items: flex-start;
                padding-top: 20px;
                padding-bottom: 20px;
            }
        }

        @media (max-height: 500px) {
            body {
                align-items: flex-start;
            }
            
            .container {
                max-width: 95%;
            }
            
            header {
                padding: 15px 20px 10px 20px;
            }
            
            .signup-form {
                padding: 15px;
            }
            
            .form-group {
                margin-bottom: 15px;
            }
        }

        /* Focus styles for accessibility */
        .signup-btn:focus,
        .input-with-icon input:focus {
            outline: 2px solid #087830;
            outline-offset: 2px;
        }

        /* Reduced motion for users who prefer it */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms;
                animation-iteration-count: 1;
                transition-duration: 0.01ms;
            }
            
            .container {
                animation: none;
            }
            
            .error-message,
            .success-message {
                animation: none;
            }
        }

        /* Scrollable container for very small screens */
        @media (max-width: 350px) {
            body {
                padding: 5px;
            }
            
            .container {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>
    
    <div class="container">
        <header>
            <a href="user_login.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="logo">
                <img src="img/LSULogo.png" alt="LSU Logo">
            </div>
            <h1 class="page-title">Create Account</h1>
            <p class="page-subtitle">Join as Student/Staff</p>
        </header>
        
        <div class="signup-form">
            <?php if (isset($_SESSION['signup_errors'])): ?>
                <div class="error-message">
                    <?php 
                    foreach ($_SESSION['signup_errors'] as $error) {
                        echo "<p><i class='fas fa-exclamation-circle'></i> $error</p>";
                    }
                    unset($_SESSION['signup_errors']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['success']) && $_GET['success'] == 'account_created'): ?>
                <div class="success-message">
                    <p><i class="fas fa-check-circle"></i> Account created successfully! Please login.</p>
                </div>
            <?php endif; ?>

            <form action="signup.php" method="POST" id="signupForm">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="name" name="name" placeholder="Enter your full name" required 
                               value="<?php echo isset($_SESSION['form_data']['name']) ? htmlspecialchars($_SESSION['form_data']['name']) : ''; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-with-icon">
                        <i class="fas fa-at"></i>
                        <input type="text" id="username" name="username" placeholder="Choose a username" required
                               value="<?php echo isset($_SESSION['form_data']['username']) ? htmlspecialchars($_SESSION['form_data']['username']) : ''; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Create a password" required>
                        <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                        <i class="fas fa-eye toggle-password" id="toggleConfirmPassword"></i>
                    </div>
                </div>
                
                <button type="submit" class="signup-btn">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
            </form>
            
            <div class="login-link">
                Already have an account? <a href="user_login.php">Login here</a>
            </div>
        </div>
        
        <footer>
            <p>La Salle University &copy; 2025. All rights reserved.</p>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle password visibility for both fields
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
            const confirmPasswordInput = document.getElementById('confirm_password');
            
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
            
            toggleConfirmPassword.addEventListener('click', function() {
                const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                confirmPasswordInput.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
            
            // Add interactive effects to inputs
            const inputs = document.querySelectorAll('input');
            
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'scale(1.02)';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'scale(1)';
                });
            });
            
            // Form validation
            const form = document.getElementById('signupForm');
            form.addEventListener('submit', function(e) {
                const name = document.getElementById('name').value.trim();
                const username = document.getElementById('username').value.trim();
                const password = passwordInput.value;
                const confirmPassword = confirmPasswordInput.value;
                
                // Basic client-side validation
                if (!name || !username || !password || !confirmPassword) {
                    e.preventDefault();
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'error-message';
                    errorDiv.innerHTML = '<p><i class="fas fa-exclamation-circle"></i> Please fill in all fields</p>';
                    
                    const form = document.getElementById('signupForm');
                    const firstFormGroup = form.querySelector('.form-group');
                    form.insertBefore(errorDiv, firstFormGroup);
                    
                    setTimeout(() => {
                        errorDiv.remove();
                    }, 5000);
                    return;
                }
                
                if (password !== confirmPassword) {
                    e.preventDefault();
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'error-message';
                    errorDiv.innerHTML = '<p><i class="fas fa-exclamation-circle"></i> Passwords do not match</p>';
                    
                    const form = document.getElementById('signupForm');
                    const firstFormGroup = form.querySelector('.form-group');
                    form.insertBefore(errorDiv, firstFormGroup);
                    
                    setTimeout(() => {
                        errorDiv.remove();
                    }, 5000);
                    return;
                }
                
                if (password.length < 6) {
                    e.preventDefault();
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'error-message';
                    errorDiv.innerHTML = '<p><i class="fas fa-exclamation-circle"></i> Password must be at least 6 characters long</p>';
                    
                    const form = document.getElementById('signupForm');
                    const firstFormGroup = form.querySelector('.form-group');
                    form.insertBefore(errorDiv, firstFormGroup);
                    
                    setTimeout(() => {
                        errorDiv.remove();
                    }, 5000);
                    return;
                }
                
                const btn = this.querySelector('.signup-btn');
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
                btn.disabled = true;
            });
            
            // Clear session form data
            <?php unset($_SESSION['form_data']); ?>
            
            // Ensure the body stays centered
            function centerBody() {
                const body = document.body;
                const container = document.querySelector('.container');
                
                if (container.offsetHeight < window.innerHeight) {
                    body.style.alignItems = 'center';
                    body.style.justifyContent = 'center';
                } else {
                    body.style.alignItems = 'flex-start';
                    body.style.justifyContent = 'flex-start';
                }
            }
            
            // Call on load and resize
            centerBody();
            window.addEventListener('resize', centerBody);
        });
    </script>
</body>
</html>