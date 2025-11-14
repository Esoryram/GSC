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

        .logo img {
            max-width: 120px;
            height: auto;
            display: block;
            margin: 0 auto 10px auto;
        }

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
            border-radius: 18px;
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.2);
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
            padding: 20px 30px 15px 30px;
            text-align: center;
            position: relative;
        }

        .back-btn {
            position: absolute;
            top: 15px;
            left: 15px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.3s ease;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .page-title {
            color: #AED14F;
            font-size: 1.6rem;
            font-weight: 600;
            margin-bottom: 3px;
        }

        .page-subtitle {
            color: white;
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .signup-form {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 12px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #3c4142;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .input-with-icon {
            position: relative;
        }

        .input-with-icon i:first-child {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #087830;
            font-size: 0.9rem;
        }

        .input-with-icon input {
            width: 100%;
            padding: 12px 40px 12px 35px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            height: 44px;
            background-color: white;
        }

        .input-with-icon input:focus {
            border-color: #087830;
            outline: none;
            box-shadow: 0 0 0 3px rgba(8, 120, 48, 0.1);
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            cursor: pointer;
            transition: color 0.3s ease;
            font-size: 0.9rem;
        }

        .toggle-password:hover {
            color: #087830;
        }

        .signup-btn {
            width: 100%;
            padding: 10px;
            background: linear-gradient(135deg, #087830 0%, #4ec66a 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 8px;
            height: 40px;
        }

        .signup-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(8, 120, 48, 0.3);
        }

        .login-link {
            text-align: center;
            margin-top: 15px;
            margin-bottom: -10px;
            color: #3c4142;
            font-size: 0.9rem;
        }

        .login-link a {
            color: #087830;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
            font-size: 0.9rem;
        }

        .login-link a:hover {
            color: #4ec66a;
            text-decoration: underline;
        }

        .error-message {
            background: #ffebee;
            color: #c62828;
            padding: 10px 12px;
            border-radius: 6px;
            margin-bottom: 15px;
            border-left: 3px solid #c62828;
            font-size: 0.85rem;
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
            padding: 10px 12px;
            border-radius: 6px;
            margin-bottom: 15px;
            border-left: 3px solid #2e7d32;
            font-size: 0.85rem;
        }

        footer {
            background-color: #3c4142;
            color: white;
            text-align: center;
            padding: 15px;
            font-size: 0.9rem;
        }

        @media (max-width: 480px) {
            .container {
                max-width: 100%;
            }
            
            .signup-form {
                padding: 25px 20px;
            }
            
            .logo img {
                max-width: 80px;
            }
            
            header {
                padding: 15px 25px 12px 25px;
            }
            
            .page-title {
                font-size: 1.4rem;
            }
            
            .page-subtitle {
                font-size: 0.8rem;
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

            <form action="signup.php" method="POST">
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
            
            // Form validation
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                const password = passwordInput.value;
                const confirmPassword = confirmPasswordInput.value;
                
                if (password !== confirmPassword) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Passwords Do Not Match',
                        text: 'Please make sure both passwords are the same.',
                        icon: 'warning',
                        confirmButtonColor: '#087830',
                        confirmButtonText: 'OK'
                    });
                    return;
                }
                
                if (password.length < 6) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Password Too Short',
                        text: 'Password must be at least 6 characters long.',
                        icon: 'warning',
                        confirmButtonColor: '#087830',
                        confirmButtonText: 'OK'
                    });
                    return;
                }
                
                const btn = this.querySelector('.signup-btn');
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
                btn.disabled = true;
            });
            
            // Clear session form data
            <?php unset($_SESSION['form_data']); ?>
        });
    </script>
</body>
</html>