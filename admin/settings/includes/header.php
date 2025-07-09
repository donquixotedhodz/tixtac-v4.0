<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Job Order System</title>
    <link rel="icon" href="../../images/logo-favicon.ico" type="image/x-icon">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        #sidebar .components li a[aria-expanded="true"] {
            background: rgba(255, 255, 255, 0.1);
        }
        #sidebar .components li .collapse {
            padding-left: 1rem;
        }
        #sidebar .components li .collapse a {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }
        #sidebar .components li .collapse a:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        .technician-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            color: #fff;
            text-decoration: none;
            transition: all 0.3s;
        }
        .technician-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }
        .technician-item img {
            width: 24px;
            height: 24px;
            border-radius: 50%;
        }
        .technician-item .name {
            font-size: 0.9rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        /* New responsive styles */
        .card {
            height: 100%;
            margin-bottom: 1rem;
        }
        .card-body {
            padding: 1.25rem;
        }
        .chart-container {
            position: relative;
            height: 250px;
            width: 100%;
        }
        @media (max-width: 768px) {
            .chart-container {
                height: 200px;
            }
            .card-body {
                padding: 1rem;
            }
            .card-title {
                font-size: 1rem;
            }
            .card-text h2 {
                font-size: 1.5rem;
            }
        }
        @media (max-width: 576px) {
            .chart-container {
                height: 180px;
            }
        }
        .dropdown-menu {
            border-radius: 10px;
            padding: 0.5rem;
        }
        
        .dropdown-item {
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        
        .dropdown-item:hover {
            background-color: #f8f9fa;
            transform: translateX(5px);
        }
        
        .dropdown-item.text-danger:hover {
            background-color: #dc3545;
            color: white !important;
        }
        
        .dropdown-item i {
            width: 20px;
            text-align: center;
        }
        
        .dropdown-divider {
            margin: 0.5rem 0;
            opacity: 0.1;
        }
        
        #userDropdown {
            transition: all 0.2s ease;
        }
        
        #userDropdown:hover {
            opacity: 0.8;
        }
        
        #userDropdown img {
            border: 2px solid #4A90E2;
            transition: all 0.2s ease;
        }
        
        #userDropdown:hover img {
            transform: scale(1.1);
        }

        /* Settings Page Styles */
        .settings-container {
            padding: 2rem;
        }
        
        .settings-title {
            color: #4A90E2;
            font-weight: 600;
            margin-bottom: 2rem;
        }
        
        .selection-card {
            background: #fff;
            border-radius: 15px;
            padding: 2rem;
            height: 100%;
            transition: all 0.3s ease;
            border: 1px solid #e0e0e0;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .selection-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, rgba(74, 144, 226, 0.1), rgba(74, 144, 226, 0.05));
            opacity: 0;
            transition: all 0.3s ease;
        }
        
        .selection-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(74, 144, 226, 0.2);
            border-color: #4A90E2;
        }
        
        .selection-card:hover::before {
            opacity: 1;
        }
        
        .selection-card i {
            font-size: 3rem;
            color: #4A90E2;
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
            transition: all 0.3s ease;
        }
        
        .selection-card:hover i {
            transform: scale(1.1);
        }
        
        .selection-card h5 {
            color: #333;
            font-weight: 600;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }
        
        .selection-card p {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0;
            position: relative;
            z-index: 1;
        }
        
        /* Modal Styles */
        .modal-content {
            border-radius: 15px;
            border: none;
        }
        
        .modal-header {
            background: white;
            color: #4A90E2;
            border-radius: 15px 15px 0 0;
            padding: 1.5rem;
        }
        
        .modal-title {
            font-weight: 600;
        }
        
        .modal-body {
            padding: 2rem;
        }
        
        .form-label {
            font-weight: 500;
            color: #333;
        }
        
        .form-control {
            border-radius: 8px;
            padding: 0.75rem;
            border: 1px solid #e0e0e0;
        }
        
        .form-control:focus {
            border-color: #4A90E2;
            box-shadow: 0 0 0 0.2rem rgba(74, 144, 226, 0.25);
        }
        
        .btn-primary {
            background: #4A90E2;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
        }
        
        .btn-primary:hover {
            background: #357ABD;
        }
        
        .btn-secondary {
            background: #e0e0e0;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            color: #333;
        }
        
        .btn-secondary:hover {
            background: #d0d0d0;
        }
        
        .input-group .btn-outline-secondary {
            border-color: #e0e0e0;
            color: #666;
        }
        
        .input-group .btn-outline-secondary:hover {
            background: #f8f9fa;
            color: #4A90E2;
        }

        /* Change Password Modal Specific Styles */
        #changePasswordModal .modal-content {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        #changePasswordModal .modal-header {
            border-bottom: 2px solid #f0f0f0;
            padding: 1.5rem 2rem;
        }

        #changePasswordModal .modal-body {
            padding: 2rem;
        }

        #changePasswordModal .form-label {
            font-size: 0.95rem;
            margin-bottom: 0.5rem;
            color: #555;
        }

        #changePasswordModal .form-control {
            height: 45px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        #changePasswordModal .form-control:focus {
            border-color: #4A90E2;
            box-shadow: 0 0 0 0.2rem rgba(74, 144, 226, 0.15);
        }

        #changePasswordModal .input-group {
            margin-bottom: 1.5rem;
        }

        #changePasswordModal .input-group .btn {
            height: 45px;
            width: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-color: #e0e0e0;
            background: #f8f9fa;
        }

        #changePasswordModal .input-group .btn:hover {
            background: #e9ecef;
            border-color: #4A90E2;
            color: #4A90E2;
        }

        #changePasswordModal .modal-footer {
            border-top: 2px solid #f0f0f0;
            padding: 1.5rem 2rem;
        }

        #changePasswordModal .btn {
            padding: 0.6rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        #changePasswordModal .btn-primary {
            background: #4A90E2;
            border: none;
        }

        #changePasswordModal .btn-primary:hover {
            background: #357ABD;
            transform: translateY(-1px);
        }

        #changePasswordModal .btn-secondary {
            background: #f8f9fa;
            color: #555;
            border: 1px solid #e0e0e0;
        }

        #changePasswordModal .btn-secondary:hover {
            background: #e9ecef;
            color: #333;
        }

        /* Password strength indicator */
        #changePasswordModal .password-strength {
            height: 4px;
            margin-top: 0.5rem;
            border-radius: 2px;
            background: #e0e0e0;
            overflow: hidden;
        }

        #changePasswordModal .password-strength-bar {
            height: 100%;
            width: 0;
            transition: all 0.3s ease;
        }

        #changePasswordModal .password-strength-text {
            font-size: 0.8rem;
            margin-top: 0.25rem;
            color: #666;
        }
    </style>
</head>