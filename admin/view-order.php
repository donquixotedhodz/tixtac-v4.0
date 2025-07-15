<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Check if order ID is provided
if (!isset($_GET['id'])) {
    header('Location: orders.php');
    exit();
}

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get admin details
    $stmt = $pdo->prepare("SELECT name, profile_picture FROM admins WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get order details
    $stmt = $pdo->prepare("
        SELECT 
            jo.*,
            COALESCE(am.model_name, 'Not Specified') as model_name,
            t.name as technician_name,
            t.phone as technician_phone
        FROM job_orders jo 
        LEFT JOIN aircon_models am ON jo.aircon_model_id = am.id 
        LEFT JOIN technicians t ON jo.assigned_technician_id = t.id
        WHERE jo.id = ?
    ");
    $stmt->execute([$_GET['id']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        header('Location: orders.php');
        exit();
    }

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
require_once 'includes/header.php';
?>
<body></body>
 <div class="wrapper">
        <?php
        // Include sidebar
        require_once 'includes/sidebar.php';
        ?>


        <!-- Page Content -->
        <div id="content">
            <nav class="navbar navbar-expand-lg navbar-light bg-white">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="ms-auto d-flex align-items-center">
                        <div class="dropdown">
                            <a class="d-flex align-items-center text-decoration-none dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <img src="<?= !empty($admin['profile_picture']) ? '../' . htmlspecialchars($admin['profile_picture']) : 'https://ui-avatars.com/api/?name=' . urlencode($admin['name'] ?? $_SESSION['username']) . '&background=1a237e&color=fff' ?>" 
                                     alt="Admin" 
                                     class="rounded-circle me-2" 
                                     width="32" 
                                     height="32"
                                     style="object-fit: cover;">
                                <span class="me-3">Welcome, <?= htmlspecialchars($admin['name'] ?? $_SESSION['username']) ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="view/profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="container-fluid">
                <!-- Print Header (hidden by default, shown only when printing) -->
                <div class="print-header" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <img src="images/logo.png" alt="Company Logo" style="height: 60px; width: auto;">
                        </div>
                        <div class="text-end">
                            <div style="font-size: 14px; font-weight: bold; color: #2c3e50;">Date Generated:</div>
                            <div style="font-size: 12px; color: #7f8c8d;"><?= date('F j, Y \a\t g:i A') ?></div>
                        </div>
                    </div>
                </div>

                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-0">Job Order Details</h4>
                        <p class="text-muted mb-0">Order #<?= htmlspecialchars($order['job_order_number']) ?></p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="<?= $_SESSION['role'] === 'admin' ? 'orders.php' : 'technician/orders.php' ?>" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Orders
                        </a>
                        <button type="button" class="btn btn-outline-primary" onclick="window.print()">
                            <i class="fas fa-print me-2"></i>Print
                        </button>
                    </div>
                </div>

                <!-- Order Details -->
                <div class="row">
                    <div class="col-md-8">
                        <!-- Customer Information -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Customer Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-muted">Customer Name</label>
                                        <p class="mb-0"><?= htmlspecialchars($order['customer_name']) ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-muted">Phone Number</label>
                                        <p class="mb-0"><?= htmlspecialchars($order['customer_phone']) ?></p>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label text-muted">Address</label>
                                        <p class="mb-0"><?= htmlspecialchars($order['customer_address']) ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Service Information -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Service Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-muted">Service Type</label>
                                        <p class="mb-0">
                                            <span class="badge bg-info bg-opacity-10 text-info">
                                                <?= ucfirst(htmlspecialchars($order['service_type'])) ?>
                                            </span>
                                        </p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-muted">Aircon Model</label>
                                        <p class="mb-0"><?= htmlspecialchars($order['model_name']) ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-muted">Price</label>
                                        <p class="mb-0 fw-semibold">₱<?= number_format($order['price'], 2) ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label text-muted">Due Date</label>
                                        <p class="mb-0"><?= date('M d, Y', strtotime($order['due_date'])) ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <!-- Status Information -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Status Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Current Status</label>
                                    <p class="mb-0">
                                        <span class="badge bg-<?= $order['status'] === 'completed' ? 'success' : 
                                            ($order['status'] === 'in_progress' ? 'warning' : 
                                            ($order['status'] === 'pending' ? 'danger' : 'secondary')) ?> bg-opacity-10 text-<?= $order['status'] === 'completed' ? 'success' : 
                                            ($order['status'] === 'in_progress' ? 'warning' : 
                                            ($order['status'] === 'pending' ? 'danger' : 'secondary')) ?>">
                                            <?= ucfirst(str_replace('_', ' ', $order['status'])) ?>
                                        </span>
                                    </p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-muted">Created Date</label>
                                    <p class="mb-0"><?= date('M d, Y H:i', strtotime($order['created_at'])) ?></p>
                                </div>
                                <?php if ($order['completed_at']): ?>
                                <div class="mb-3">
                                    <label class="form-label text-muted">Completed Date</label>
                                    <p class="mb-0"><?= date('M d, Y H:i', strtotime($order['completed_at'])) ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Technician Information -->
                        <?php if ($order['technician_name']): ?>
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Assigned Technician</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($order['technician_name']) ?>&background=1a237e&color=fff" 
                                         alt="<?= htmlspecialchars($order['technician_name']) ?>" 
                                         class="rounded-circle me-3" 
                                         width="48" height="48">
                                    <div>
                                        <h6 class="mb-1"><?= htmlspecialchars($order['technician_name']) ?></h6>
                                        <p class="text-muted mb-0"><?= htmlspecialchars($order['technician_phone']) ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Action Buttons -->
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Actions</h5>
                                <div class="d-grid gap-2">
                                    <?php if ($_SESSION['role'] === 'admin'): ?>
                                        <?php if ($order['status'] !== 'completed'): ?>
                                        <a href="controller/edit-order.php?id=<?= $order['id'] ?>" class="btn btn-warning">
                                            <i class="fas fa-edit me-2"></i>Edit Order
                                        </a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <?php if ($order['status'] === 'pending'): ?>
                                        <a href="controller/update-status.php?id=<?= $order['id'] ?>&status=in_progress" class="btn btn-warning">
                                            <i class="fas fa-play me-2"></i>Start Work
                                        </a>
                                        <?php elseif ($order['status'] === 'in_progress'): ?>
                                        <a href="controller/update-status.php?id=<?= $order['id'] ?>&status=completed" class="btn btn-success">
                                            <i class="fas fa-check me-2"></i>Mark as Completed
                                        </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-outline-primary" onclick="window.print()">
                                        <i class="fas fa-print me-2"></i>Print Details
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="js/dashboard.js"></script>

    <style>
        @media print {
            /* Hide screen elements */
            .navbar, .sidebar, .btn, .card-header, .d-flex.gap-2 {
                display: none !important;
            }
            
            /* Show print header */
            .print-header {
                display: block !important;
                margin-bottom: 30px !important;
                padding-bottom: 20px !important;
                border-bottom: 2px solid #34495e !important;
                page-break-after: avoid !important;
            }
            
            .print-header img {
                display: block !important;
                max-height: 60px !important;
                width: auto !important;
            }
            
            .print-header .text-end {
                text-align: right !important;
            }
            
            /* Reset page layout */
            body {
                margin: 0 !important;
                padding: 20px !important;
                font-family: 'Arial', sans-serif !important;
                font-size: 12px !important;
                line-height: 1.4 !important;
                color: #000 !important;
                background: white !important;
            }
            
            /* Header styling */
            .container-fluid {
                max-width: none !important;
                padding: 0 !important;
            }
            
            /* Card styling for print */
            .card {
                border: 1px solid #ddd !important;
                box-shadow: none !important;
                margin-bottom: 20px !important;
                page-break-inside: avoid !important;
            }
            
            .card-body {
                padding: 15px !important;
            }
            
            .card-title {
                font-size: 14px !important;
                font-weight: bold !important;
                color: #2c3e50 !important;
                margin-bottom: 10px !important;
            }
            
            /* Hide profile images in print */
            .card img {
                display: none !important;
            }
            
            /* Status badges for print */
            .badge {
                display: inline-block !important;
                padding: 4px 8px !important;
                font-size: 10px !important;
                font-weight: bold !important;
                border-radius: 3px !important;
                border: 1px solid !important;
            }
            
            .badge.bg-success {
                background: #d4edda !important;
                color: #155724 !important;
                border-color: #c3e6cb !important;
            }
            
            .badge.bg-warning {
                background: #fff3cd !important;
                color: #856404 !important;
                border-color: #ffeaa7 !important;
            }
            
            .badge.bg-danger {
                background: #f8d7da !important;
                color: #721c24 !important;
                border-color: #f5c6cb !important;
            }
            
            .badge.bg-info {
                background: #d1ecf1 !important;
                color: #0c5460 !important;
                border-color: #bee5eb !important;
            }
            
            .badge.bg-secondary {
                background: #e2e3e5 !important;
                color: #383d41 !important;
                border-color: #d6d8db !important;
            }
            
            /* Form labels and text */
            .form-label {
                font-weight: bold !important;
                color: #2c3e50 !important;
                font-size: 11px !important;
            }
            
            .text-muted {
                color: #7f8c8d !important;
            }
            
            .fw-semibold {
                font-weight: bold !important;
                color: #2c3e50 !important;
            }
            
            /* Page breaks */
            .row {
                page-break-inside: avoid !important;
            }
        }
    </style>
</body>
</html> 