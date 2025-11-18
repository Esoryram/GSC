<?php
session_start();
include("config.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Salle Login Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
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
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            position: relative;
            overflow: auto;
            min-height: 100vh;
        }

        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle at 25% 25%, rgba(174, 209, 101, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 75% 75%, rgba(103, 208, 170, 0.1) 0%, transparent 50%);
            z-index: -1;
        }

        .container {
            width: 100%;
            max-width: 1000px;
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            margin: auto;
        }

        header {
            background-color: #087830;
            color: white;
            padding: 25px 30px;
            text-align: center;
            flex-shrink: 0;
        }

        header img {
            max-width: 250px; 
            height: auto;
            display: block;
            margin: 0 auto;
            width: 100%;
        }

        .login-options {
            display: flex;
            flex-wrap: wrap;
            padding: 30px;
            gap: 25px;
            justify-content: center;
            overflow: visible;
            flex-grow: 1;
        }

        .option-card {
            flex: 1;
            min-width: 280px;
            max-width: 400px;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .option-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            padding: 25px 20px;
            text-align: center;
            color: white;
            flex-shrink: 0;
        }

        .admin .card-header,
        .user .card-header {
            background: linear-gradient(135deg, #3c4142 0%, #087830 100%);
        }

        .card-icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }

        .card-title {
            font-size: 1.6rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .card-description {
            font-size: 0.95rem;
            opacity: 0.9;
            line-height: 1.4;
        }

        .card-content {
            padding: 25px 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .login-btn {
            display: block;
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease, transform 0.2s ease;
            text-align: center;
            text-decoration: none;
        }

        .admin .login-btn,
        .user .login-btn {
            background: #3c4142;
            color: white;
        }

        .admin .login-btn:hover,
        .user .login-btn:hover {
            background: #087830;
            transform: scale(1.02);
        }

        .login-btn:active {
            transform: scale(0.98);
        }

        footer {
            background-color: #3c4142;
            color: white;
            text-align: center;
            padding: 18px;
            font-size: 0.85rem;
            flex-shrink: 0;
        }

        @media (max-width: 768px) {
            body {
                padding: 15px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .container {
                border-radius: 15px;
            }
            
            .login-options {
                flex-direction: column;
                padding: 25px 20px;
                gap: 20px;
            }
            
            .option-card {
                min-width: 100%;
                max-width: 100%;
            }
            
            header {
                padding: 20px 25px;
            }
            
            header img {
                max-width: 200px;
            }
            
            .card-header {
                padding: 20px 15px;
            }
            
            .card-content {
                padding: 20px 15px;
            }
            
            .card-icon {
                font-size: 2.8rem;
            }
            
            .card-title {
                font-size: 1.5rem;
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
                max-height: 700px;
            }
            
            header {
                padding: 15px 20px;
            }
            
            header img {
                max-width: 125px;
            }
            
            .login-options {
                padding: 20px 15px;
                gap: 15px;
            }
            
            .card-header {
                padding: 12px 9px;
            }
            
            .card-content {
                padding: 12px 9px;
            }
            
            .card-icon {
                font-size: 2.0rem;
            }
            
            .card-title {
                font-size: 1.0rem;
            }
            
            .login-btn {
                padding: 10px;
                font-size: 0.95rem;
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
                padding: 10px 15px;
            }
            
            .login-options {
                padding: 15px;
                gap: 10px;
            }
            
            .card-header {
                padding: 12px 10px;
            }
            
            .card-content {
                padding: 12px 10px;
            }
        }

        .login-btn:focus {
            outline: 2px solid #087830;
            outline-offset: 2px;
        }

        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms;
                animation-iteration-count: 1;
                transition-duration: 0.01ms;
            }
            
            .option-card:hover {
                transform: none;
            }
        }

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
    <div class="container">
        <header>
            <img src="img/LSULogo.png" alt="La Salle University Logo">
        </header>        
        <div class="login-options">
            <div class="option-card admin">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <h2 class="card-title">Administrator</h2>
                    <p class="card-description">Access administrative functions and system management</p>
                </div>
                <div class="card-content">
                    <a href="admin_login.php" class="login-btn" aria-label="Login as Administrator">
                        <i class="fas fa-sign-in-alt"></i> Login as Administrator
                    </a>
                </div>
            </div>
            
            <div class="option-card user">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h2 class="card-title">User</h2>
                    <p class="card-description">Access student and staff accounts and resources</p>
                </div>
                <div class="card-content">
                    <a href="user_login.php" class="login-btn" aria-label="Login as User">
                        <i class="fas fa-sign-in-alt"></i> Login as User
                    </a>
                </div>
            </div>
        </div>
        
        <footer>
            <p>La Salle University &copy; 2025. All rights reserved.</p>
        </footer>
    </div>

    <script>
        // Enhanced animation for the cards on page load
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.option-card');
            
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 200);
            });
            
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