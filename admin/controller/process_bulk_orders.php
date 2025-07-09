<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Start transaction
        $pdo->beginTransaction();

        // Get common values
        $commonServiceType = $_POST['common_service_type'];
        $commonDueDate = $_POST['common_due_date'];
        
        // Get customer information (single set)
        $customerName = $_POST['customer_name'];
        $customerPhone = $_POST['customer_phone'];
        $customerAddress = $_POST['customer_address'];

        // Get arrays of values for aircon details
        $airconModelIds = $_POST['aircon_model_id'];
        $technicianId = $_POST['assigned_technician_id'];
        $additionalFee = floatval($_POST['additional_fee']);
        $discount = floatval($_POST['discount']);
        $totalPrice = floatval($_POST['total_price']);

        $successCount = 0;
        $errorCount = 0;

        // Prepare the insert statement
        $stmt = $pdo->prepare("
            INSERT INTO job_orders (
                job_order_number,
                customer_name,
                customer_phone,
                customer_address,
                service_type,
                aircon_model_id,
                assigned_technician_id,
                status,
                due_date,
                price,
                additional_fee,
                discount,
                created_at
            ) VALUES (
                CONCAT('JO-', DATE_FORMAT(NOW(), '%Y%m%d'), '-', LPAD(?, 4, '0')),
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                'pending',
                ?,
                ?,
                ?,
                ?,
                NOW()
            )
        ");

        // Process each aircon order
        for ($i = 0; $i < count($airconModelIds); $i++) {
            try {
                // Generate a unique sequence number for this order
                $sequenceNumber = $i + 1;

                $stmt->execute([
                    $sequenceNumber,
                    $customerName,
                    $customerPhone,
                    $customerAddress,
                    $commonServiceType,
                    $airconModelIds[$i],
                    $technicianId,
                    $commonDueDate,
                    $totalPrice,
                    $additionalFee,
                    $discount
                ]);

                $successCount++;
            } catch (PDOException $e) {
                $errorCount++;
                error_log("Error creating order for aircon model {$airconModelIds[$i]}: " . $e->getMessage());
            }
        }

        // Commit transaction
        $pdo->commit();

        // Set success message
        $_SESSION['success_message'] = "Successfully created $successCount orders" . 
            ($errorCount > 0 ? " ($errorCount failed)" : "");

        // Redirect back to orders page
        header('Location: ../orders.php');
        exit();

    } catch (PDOException $e) {
        // Rollback transaction on error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        
        $_SESSION['error_message'] = "Error creating orders: " . $e->getMessage();
        header('Location: ../orders.php');
        exit();
    }
} else {
    // If not POST request, redirect to orders page
    header('Location: ../orders.php');
    exit();
} 