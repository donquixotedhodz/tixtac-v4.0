<?php
session_start();
require_once '../config/database.php';

// Store the session data before destroying it
$username = $_SESSION['username'] ?? '';
$role = $_SESSION['role'] ?? '';

// Get admin's full name from database
$adminName = 'User';
if ($username && $role === 'admin') {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $pdo->prepare("SELECT name FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $result = $stmt->fetch();
        
        if ($result) {
            $adminName = $result['name'];
        }
    } catch (PDOException $e) {
        // If there's an error, just use the username
        $adminName = $username;
    }
}

// Clear all session data
session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging Out - Job Order System</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-blue: #1a237e;
            --light-blue: #f8f9fa;
            --hover-blue: #283593;
        }

        body {
            background: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
            margin: 0;
            overflow: hidden;
        }

        .logout-container {
            text-align: center;
            position: relative;
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .aircon-icon {
            font-size: 4rem;
            color: var(--primary-blue);
            margin-bottom: 1rem;
            animation: cool 2s infinite;
        }

        .goodbye-text {
            font-size: 1.5rem;
            color: var(--primary-blue);
            margin-bottom: 0.5rem;
            opacity: 0;
            animation: fadeIn 0.5s ease forwards;
        }

        .message {
            color: #666;
            margin-bottom: 1rem;
            opacity: 0;
            animation: fadeIn 0.5s ease forwards 0.3s;
        }

        .redirect-text {
            color: #999;
            font-size: 0.9rem;
            opacity: 0;
            animation: fadeIn 0.5s ease forwards 0.6s;
        }

        @keyframes cool {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .cooling-effect {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }

        .cooling-effect::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(180deg, rgba(26, 35, 126, 0.1) 0%, rgba(248, 249, 250, 0) 100%);
            animation: coolDown 2s infinite;
        }

        @keyframes coolDown {
            0% { opacity: 0.3; }
            50% { opacity: 0.1; }
            100% { opacity: 0.3; }
        }
    </style>
</head>
<body>
    <div class="cooling-effect"></div>
    <div class="logout-container">
        <i class="fas fa-snowflake aircon-icon"></i>
        <h1 class="goodbye-text">Goodbye, <?= htmlspecialchars($adminName) ?>!</h1>
        <p class="message">Thank you for using the Job Order System</p>
        <p class="redirect-text">Redirecting to login page...</p>
    </div>

    <script>
        // Redirect after animation
        setTimeout(() => {
            window.location.href = '../index.php';
        }, 3000);
    </script>
</body>
</html> 