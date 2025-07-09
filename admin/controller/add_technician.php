<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Get form data
        $name = trim($_POST['name']);
        $username = trim($_POST['username']);
        $phone = trim($_POST['phone']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Validate input
        $errors = [];

        if (empty($name)) {
            $errors[] = "Name is required";
        }

        if (empty($username)) {
            $errors[] = "Username is required";
        } else {
            // Check if username already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM technicians WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetchColumn() > 0) {
                $errors[] = "Username already exists";
            }
        }

        if (empty($phone)) {
            $errors[] = "Phone number is required";
        }

        if (empty($password)) {
            $errors[] = "Password is required";
        } elseif (strlen($password) < 6) {
            $errors[] = "Password must be at least 6 characters long";
        }

        if ($password !== $confirm_password) {
            $errors[] = "Passwords do not match";
        }

        // If no errors, proceed with insertion
        if (empty($errors)) {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert the new technician
            $stmt = $pdo->prepare("
                INSERT INTO technicians (name, username, phone, password, created_at) 
                VALUES (?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([$name, $username, $phone, $hashed_password]);

            // Redirect back to technicians page with success message
            $_SESSION['success_message'] = "Technician added successfully!";
            header('Location: ../technicians.php');
            exit();
        } else {
            // Redirect back with errors
            $_SESSION['error_message'] = implode("<br>", $errors);
            header('Location: ../technicians.php');
            exit();
        }

    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Database error: " . $e->getMessage();
        header('Location: ../technicians.php');
        exit();
    }
} else {
    // If not POST request, redirect to technicians page
    header('Location: ../technicians.php');
    exit();
} 