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

        /* Background pattern */
        body::before {
            content: "";
            position: absolute;
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
        }

        header {
            background-color: #087830;
            color: white;
            padding: 30px 40px;
            text-align: center;
        }

        /* Logo sizing - Adjust these values as needed */
        header img {
            max-width: 250px; /* Change this value to resize */
            height: auto;
            display: block;
            margin: 0 auto;
        }

        .login-options {
            display: flex;
            flex-wrap: wrap;
            padding: 40px;
            gap: 30px;
            justify-content: center;
        }

        .option-card {
            flex: 1;
            min-width: 300px;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .option-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            padding: 25px;
            text-align: center;
            color: white;
        }

        /* Same admin color for both cards */
        .admin .card-header,
        .user .card-header {
            background: linear-gradient(135deg, #3c4142 0%, #087830 100%);
        }

        .card-icon {
            font-size: 3.5rem;
            margin-bottom: 15px;
        }

        .card-title {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .card-description {
            font-size: 1rem;
            opacity: 0.9;
        }

        .card-content {
            padding: 30px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .login-btn {
            display: block;
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
            text-align: center;
            text-decoration: none;
        }

        /* Same admin button color for both */
        .admin .login-btn,
        .user .login-btn {
            background: #3c4142;
            color: white;
        }

        .admin .login-btn:hover,
        .user .login-btn:hover {
            background: #087830;
        }

        footer {
            background-color: #3c4142;
            color: white;
            text-align: center;
            padding: 20px;
            font-size: 0.9rem;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .login-options {
                flex-direction: column;
                padding: 30px 20px;
            }
            
            .option-card {
                min-width: 100%;
            }
            
            header {
                padding: 20px 30px;
            }
            
            header img {
                max-width: 200px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <img src="img/LSULogo.png" alt="LSU Logo">
        </header>        
        <div class="login-options">
            <div class="option-card admin">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <h2 class="card-title">Administrator</h2>
                    <p class="card-description">Access administrative functions</p>
                </div>
                <div class="card-content">
                    <a href="admin_login.php" class="login-btn">
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
                    <p class="card-description">Access student/staff account</p>
                </div>
                <div class="card-content">
                    <a href="user_login.php" class="login-btn">
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
        // Simple animation for the cards on page load
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
        });
    </script>
</body>
</html>