<?php
session_start();
include("config.php");

// Check if admin is logged in
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit();
}

// If already verified, redirect to dashboard
if (isset($_SESSION['master_verified']) && $_SESSION['master_verified'] === false) {
    header("Location: admindb.php");
    exit();
}

$errors = [];

// Handle verification form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['verify'])) {
    $password = trim($_POST['password']);

    // Simple master password check
    $masterKey = "GSC";
    
    if ($password === $masterKey) {
        $_SESSION['master_verified'] = true;
        session_write_close();
        header("Location: admindb.php");
        exit();
    } else {
        $errors[] = "Incorrect Master Password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Salle - Admin Verification</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
        <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
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

        /* Logo styling */
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
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        header {
            background: linear-gradient(135deg, #087830 0%, #065a25 100%);
            color: white;
            padding: 30px 40px 25px 40px;
            text-align: center;
            position: relative;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .back-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border: none;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .page-title {
            color: #AED14F;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 8px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
            letter-spacing: -0.5px;
        }

        .page-subtitle {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.1rem;
            font-weight: 500;
            letter-spacing: 0.5px;
        }

        .verification-form {
            padding: 40px;
        }

        .instruction {
            margin-bottom: 30px;
            color: #2d3748;
            font-size: 1.1rem;
            line-height: 1.6;
            text-align: center;
            font-weight: 500;
            background: linear-gradient(135deg, #087830, #4ec66a);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .form-group {
            margin-bottom: 30px;
        }

        .form-group label {
            display: block;
            margin-bottom: 12px;
            color: #2d3748;
            font-weight: 600;
            font-size: 1rem;
            letter-spacing: 0.5px;
        }

        .input-with-icon {
            position: relative;
            transition: all 0.3s ease;
        }

        .input-with-icon i:first-child {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #087830;
            font-size: 1.2rem;
            z-index: 2;
        }

        .input-with-icon input {
            width: 100%;
            padding: 18px 60px 18px 55px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #ffffff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            font-weight: 500;
        }

        .input-with-icon input:focus {
            border-color: #087830;
            outline: none;
            box-shadow: 0 0 0 4px rgba(8, 120, 48, 0.15);
            transform: translateY(-2px);
        }

        .input-with-icon input::placeholder {
            color: #a0aec0;
            font-weight: 400;
        }

        .toggle-password {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #718096;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1.2rem;
            background: none;
            border: none;
            padding: 5px;
        }

        .toggle-password:hover {
            color: #087830;
            transform: translateY(-50%) scale(1.1);
        }

        .verify-btn {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #087830 0%, #4ec66a 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.2rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            box-shadow: 0 4px 15px rgba(8, 120, 48, 0.3);
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .verify-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(8, 120, 48, 0.4);
            background: linear-gradient(135deg, #065a25 0%, #3da85a 100%);
        }

        .verify-btn:active {
            transform: translateY(-1px);
        }

        .verify-btn i {
            margin-right: 10px;
        }

        .error-message {
            background: linear-gradient(135deg, #fed7d7 0%, #feb2b2 100%);
            color: #c53030;
            padding: 16px 20px;
            border-radius: 12px;
            margin-top: -25px;
            margin-bottom: 15px;
            border-left: 5px solid #e53e3e;
            font-size: 0.95rem;
            animation: slideIn 0.4s ease-out;
            box-shadow: 0 4px 15px rgba(229, 62, 62, 0.15);
        }

        .error-message p,
        .success-message p {
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
        }

        .success-message {
            background: linear-gradient(135deg, #c6f6d5 0%, #9ae6b4 100%);
            color: #276749;
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            border-left: 5px solid #38a169;
            font-size: 0.95rem;
            animation: slideIn 0.4s ease-out;
            box-shadow: 0 4px 15px rgba(56, 161, 105, 0.15);
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-15px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
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
                margin: 10px;
            }
            
            .verification-form {
                padding: 30px 25px;
            }
            
            .logo img {
                max-width: 100px;
            }
            
            header {
                padding: 25px 30px 20px 30px;
            }
            
            .page-title {
                font-size: 1.7rem;
            }
            
            .input-with-icon input {
                padding: 16px 50px 16px 45px;
            }
        }

        /* Loading state */
        .verify-btn.loading {
            pointer-events: none;
            opacity: 0.8;
        }

        /* Focus states for accessibility */
        .verify-btn:focus,
        .back-btn:focus,
        .toggle-password:focus {
            outline: 2px solid #087830;
            outline-offset: 2px;
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
            <a href="admin_login.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="logo">
                <img src="img/LSULogo.png" alt="LSU Logo">
            </div>
            <h1 class="page-title">Admin Verification</h1>
            <p class="page-subtitle">Additional security verification</p>
        </header>
        
        <div class="verification-form">
            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <?php 
                    foreach ($errors as $error) {
                        echo "<p><i class='fas fa-exclamation-circle'></i> $error</p>";
                    }
                    ?>
                </div>
            <?php endif; ?>

            <p class="instruction">For added security, please input your Master Password</p>
            
            <!-- CHANGED: Added action to form -->
            <form method="POST" action="admin_verification.php" id="verificationForm">
                <div class="form-group">
                    <label for="password">Enter Master Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-key"></i>
                        <input type="password" id="password" name="password" placeholder="Enter master password" required autofocus>
                        <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                    </div>
                </div>
                
                <button type="submit" name="verify" class="verify-btn">
                    <i class="fas fa-shield-alt"></i> Verify & Continue
                </button>
            </form>
        </div>
        
        <footer>
            <p>La Salle University &copy; 2025. All rights reserved.</p>
        </footer>
    </div>

    <script>
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
        });
    </script>
</body>
</html>