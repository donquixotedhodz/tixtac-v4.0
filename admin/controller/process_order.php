<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Generate job order number (format: YYYYNNNNN)
        $year = date('Y');
        $stmt = $pdo->query("SELECT MAX(CAST(SUBSTRING(job_order_number, 5) AS UNSIGNED)) as max_num 
                            FROM job_orders 
                            WHERE job_order_number LIKE '$year%'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $next_num = ($result['max_num'] ?? 0) + 1;
        $job_order_number = $year . str_pad($next_num, 5, '0', STR_PAD_LEFT);

        // Prepare the SQL statement
        $stmt = $pdo->prepare("
            INSERT INTO job_orders (
                job_order_number,
                customer_name,
                customer_address,
                customer_phone,
                service_type,
                aircon_model_id,
                assigned_technician_id,
                status,
                price,
                created_by,
                due_date
            ) VALUES (
                :job_order_number,
                :customer_name,
                :customer_address,
                :customer_phone,
                :service_type,
                :aircon_model_id,
                :assigned_technician_id,
                :status,
                :price,
                :created_by,
                :due_date
            )
        ");

        // Bind parameters using bindValue instead of bindParam
        $stmt->bindValue(':job_order_number', $job_order_number);
        $stmt->bindValue(':customer_name', $_POST['customer_name']);
        $stmt->bindValue(':customer_address', $_POST['customer_address']);
        $stmt->bindValue(':customer_phone', $_POST['customer_phone']);
        $stmt->bindValue(':service_type', $_POST['service_type']);
        $stmt->bindValue(':aircon_model_id', $_POST['aircon_model_id'] ?: null);
        $stmt->bindValue(':assigned_technician_id', $_POST['assigned_technician_id'] ?: null);
        $stmt->bindValue(':status', 'pending');
        $stmt->bindValue(':price', $_POST['price']);
        $stmt->bindValue(':created_by', $_SESSION['user_id']);
        $stmt->bindValue(':due_date', $_POST['due_date']);

        // Execute the statement
        $stmt->execute();

        // Redirect back to orders page with success message
        $_SESSION['success_message'] = "Job order #$job_order_number has been created successfully.";
        header('Location: ../orders.php');
        exit();

    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error creating job order: " . $e->getMessage();
        header('Location: ../orders.php');
        exit();
    }
} else {
    // If not POST request, redirect to orders page
    header('Location: ../orders.php');
    exit();
} 