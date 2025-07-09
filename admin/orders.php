<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Get filter parameters from the request
$search_customer = $_GET['search_customer'] ?? '';
$filter_service = $_GET['filter_service'] ?? '';
$filter_technician = $_GET['filter_technician'] ?? '';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get admin details
    $stmt = $pdo->prepare("SELECT name, profile_picture FROM admins WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get all ongoing job orders (pending and in_progress)
    $sql = "
        SELECT 
            jo.*,
            COALESCE(am.model_name, 'Not Specified') as model_name,
            t.name as technician_name
        FROM job_orders jo 
        LEFT JOIN aircon_models am ON jo.aircon_model_id = am.id 
        LEFT JOIN technicians t ON jo.assigned_technician_id = t.id
        WHERE jo.status IN ('pending', 'in_progress')
    ";

    $params = [];

    if (!empty($search_customer)) {
        $sql .= " AND jo.customer_name LIKE ?";
        $params[] = '%' . $search_customer . '%';
    }

    if (!empty($filter_service)) {
        $sql .= " AND jo.service_type = ?";
        $params[] = $filter_service;
    }

    if (!empty($filter_technician)) {
        $sql .= " AND jo.assigned_technician_id = ?";
        $params[] = $filter_technician;
    }

    $sql .= "
        ORDER BY 
            CASE 
                WHEN jo.status = 'pending' THEN 1
                WHEN jo.status = 'in_progress' THEN 2
            END,
            jo.created_at DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get technicians for dropdown
    $stmt = $pdo->query("SELECT id, name FROM technicians");
    $technicians = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get aircon models for dropdown
    $stmt = $pdo->query("SELECT id, model_name, brand, price FROM aircon_models");
    $airconModels = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
                                <img src="<?= !empty($admin['profile_picture']) ? '../' . htmlspecialchars($admin['profile_picture']) : 'https://ui-avatars.com/api/?name=' . urlencode($admin['name'] ?: 'Admin') . '&background=1a237e&color=fff' ?>" 
                                     alt="Admin" 
                                     class="rounded-circle me-2" 
                                     width="32" 
                                     height="32"
                                     style="object-fit: cover; border: 2px solid #4A90E2;">
                                <span class="me-3">Welcome, <?= htmlspecialchars($admin['name'] ?: 'Admin') ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0" style="min-width: 200px;">
                                <li>
                                    <a class="dropdown-item d-flex align-items-center py-2" href="view/profile.php">
                                        <i class="fas fa-user me-2 text-primary"></i>
                                        <span>Profile</span>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider my-2"></li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center py-2 text-danger" href="logout.php">
                                        <i class="fas fa-sign-out-alt me-2"></i>
                                        <span>Logout</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="container-fluid">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-0">Orders</h4>
                        <p class="text-muted mb-0">Manage and track all job orders</p>
                    </div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#orderTypeModal">
                        <i class="fas fa-plus me-2"></i>Add Job Order
                    </button>
                </div>

                <!-- Search and Filter Form -->
                <form method="GET" action="" class="mb-4">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="search_customer" class="form-label">Search Customer</label>
                            <input type="text" class="form-control" id="search_customer" name="search_customer" value="<?= htmlspecialchars($search_customer) ?>" placeholder="Enter customer name">
                        </div>
                        <div class="col-md-3">
                            <label for="filter_service" class="form-label">Service Type</label>
                            <select class="form-select" id="filter_service" name="filter_service">
                                <option value="">All Service Types</option>
                                <option value="installation" <?= $filter_service === 'installation' ? 'selected' : '' ?>>Installation</option>
                                <option value="repair" <?= $filter_service === 'repair' ? 'selected' : '' ?>>Repair</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filter_technician" class="form-label">Technician</label>
                            <select class="form-select" id="filter_technician" name="filter_technician">
                                <option value="">All Technicians</option>
                                <?php foreach ($technicians as $tech): ?>
                                    <option value="<?= $tech['id'] ?>" <?= (string)$filter_technician === (string)$tech['id'] ? 'selected' : '' ?>><?= htmlspecialchars($tech['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-secondary w-100">Apply Filters</button>
                        </div>
                    </div>
                </form>

                <!-- Orders Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Ticket Number</th>
                                        <th>Customer</th>
                                        <th>Service Type</th>
                                        <th>Model</th>
                                        <th>Technician</th>
                                        <th>Status</th>
                                        <th>Due Date</th>
                                        <th>Price</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>
                                            <span class="fw-semibold"><?= htmlspecialchars($order['job_order_number']) ?></span>
                                        </td>
                                        <td>
                                            <div class="fw-medium"><?= htmlspecialchars($order['customer_name']) ?></div>
                                            <small class="text-muted d-block"><?= htmlspecialchars($order['customer_phone']) ?></small>
                                            <small class="text-muted d-block text-truncate" style="max-width: 200px;"><?= htmlspecialchars($order['customer_address']) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info bg-opacity-10 text-info">
                                                <?= ucfirst(htmlspecialchars($order['service_type'])) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($order['model_name']) ?></td>
                                        <td>
                                            <?php if ($order['technician_name']): ?>
                                                <div class="d-flex align-items-center">
                                                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($order['technician_name']) ?>&background=1a237e&color=fff" 
                                                         alt="<?= htmlspecialchars($order['technician_name']) ?>" 
                                                         class="rounded-circle me-2" 
                                                         width="24" height="24">
                                                    <?= htmlspecialchars($order['technician_name']) ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">Not Assigned</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $order['status'] === 'completed' ? 'success' : 
                                                ($order['status'] === 'in_progress' ? 'warning' : 
                                                ($order['status'] === 'pending' ? 'danger' : 'secondary')) ?> bg-opacity-10 text-<?= $order['status'] === 'completed' ? 'success' : 
                                                ($order['status'] === 'in_progress' ? 'warning' : 
                                                ($order['status'] === 'pending' ? 'danger' : 'secondary')) ?>">
                                                <?= ucfirst(str_replace('_', ' ', $order['status'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-medium"><?= date('M d, Y', strtotime($order['due_date'])) ?></div>
                                            <?php
                                            $due_date = new DateTime($order['due_date']);
                                            $today = new DateTime();
                                            $interval = $today->diff($due_date);
                                            $days_left = $interval->days;
                                            if ($due_date < $today) {
                                                echo '<small class="text-danger">Overdue by ' . $days_left . ' days</small>';
                                            } elseif ($days_left <= 3) {
                                                echo '<small class="text-warning">Due in ' . $days_left . ' days</small>';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <div class="fw-semibold">₱<?= number_format($order['price'], 2) ?></div>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="view-order.php?id=<?= $order['id'] ?>" 
                                                   class="btn btn-sm btn-light" 
                                                   data-bs-toggle="tooltip" 
                                                   title="View Details">
                                                    <i class="fas fa-eye text-primary"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-sm btn-light edit-order-btn" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editOrderModal"
                                                        data-id="<?= $order['id'] ?>"
                                                        data-customer-name="<?= htmlspecialchars($order['customer_name']) ?>"
                                                        data-customer-phone="<?= htmlspecialchars($order['customer_phone']) ?>"
                                                        data-customer-address="<?= htmlspecialchars($order['customer_address']) ?>"
                                                        data-service-type="<?= htmlspecialchars($order['service_type']) ?>"
                                                        data-aircon-model="<?= $order['aircon_model_id'] ?>"
                                                        data-technician="<?= $order['assigned_technician_id'] ?>"
                                                        data-due-date="<?= date('Y-m-d', strtotime($order['due_date'])) ?>"
                                                        data-price="<?= $order['price'] ?>"
                                                        data-status="<?= htmlspecialchars($order['status']) ?>"
                                                        title="Edit Order">
                                                    <i class="fas fa-edit text-warning"></i>
                                                </button>
                                                <?php if ($order['status'] !== 'completed'): ?>
                                                <button type="button" 
                                                        class="btn btn-sm btn-light complete-order-btn" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#completeOrderModal"
                                                        data-id="<?= $order['id'] ?>"
                                                        data-order-number="<?= htmlspecialchars($order['job_order_number']) ?>"
                                                        data-customer-name="<?= htmlspecialchars($order['customer_name']) ?>"
                                                        title="Mark as Completed">
                                                    <i class="fas fa-check text-success"></i>
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Type Selection Modal -->
    <div class="modal fade" id="orderTypeModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Select Order Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card h-100 order-type-card" data-order-type="single">
                                <div class="card-body text-center p-4">
                                    <i class="fas fa-file-alt fa-3x text-primary mb-3"></i>
                                    <h5 class="card-title">Single Order</h5>
                                    <p class="card-text text-muted">Create a single job order for one customer</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100 order-type-card" data-order-type="bulk">
                                <div class="card-body text-center p-4">
                                    <i class="fas fa-layer-group fa-3x text-primary mb-3"></i>
                                    <h5 class="card-title">Bulk Orders</h5>
                                    <p class="card-text text-muted">Create multiple job orders at once</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Job Order Modal -->
    <div class="modal fade" id="addJobOrderModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Job Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="controller/process_order.php" method="POST">
                    <div class="modal-body">
                        <div class="row g-3">
                            <!-- Customer Information -->
                            <div class="col-md-6">
                                <label class="form-label">Customer Name</label>
                                <input type="text" class="form-control" name="customer_name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" name="customer_phone" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <textarea class="form-control" name="customer_address" rows="2" required></textarea>
                            </div>

                            <!-- Service Information -->
                            <div class="col-md-6">
                                <label class="form-label">Service Type</label>
                                <select class="form-select" name="service_type" required>
                                    <option value="">Select Service Type</option>
                                    <option value="installation">Installation</option>
                                    <option value="repair">Repair</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Aircon Model</label>
                                <select class="form-select" name="aircon_model_id">
                                    <option value="">Select Model</option>
                                    <?php foreach ($airconModels as $model): ?>
                                    <option value="<?= $model['id'] ?>"><?= htmlspecialchars($model['brand'] . ' - ' . $model['model_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Assignment Information -->
                            <div class="col-md-6">
                                <label class="form-label">Assign Technician</label>
                                <select class="form-select" name="assigned_technician_id">
                                    <option value="">Select Technician</option>
                                    <?php foreach ($technicians as $tech): ?>
                                    <option value="<?= $tech['id'] ?>"><?= htmlspecialchars($tech['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Due Date</label>
                                <input type="date" class="form-control" name="due_date" required>
                            </div>

                            <!-- Price -->
                            <div class="col-md-4">
                                <label class="form-label">Base Price (₱)</label>
                                <input type="number" class="form-control" name="base_price" id="base_price" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Additional Fee (₱)</label>
                                <input type="number" class="form-control" name="additional_fee" id="additional_fee" value="0" min="0" step="0.01">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Discount (₱)</label>
                                <input type="number" class="form-control" name="discount" id="discount" value="0" min="0" step="0.01">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Total Price (₱)</label>
                                <input type="number" class="form-control" name="price" id="total_price" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Job Order</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Order Modal -->
    <div class="modal fade" id="editOrderModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Job Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="controller/process_edit.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="order_id" id="edit_order_id">
                        <div class="row g-3">
                            <!-- Customer Information -->
                            <div class="col-md-6">
                                <label class="form-label">Customer Name</label>
                                <input type="text" class="form-control" name="customer_name" id="edit_customer_name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" name="customer_phone" id="edit_customer_phone" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <textarea class="form-control" name="customer_address" id="edit_customer_address" rows="2" required></textarea>
                            </div>

                            <!-- Service Information -->
                            <div class="col-md-6">
                                <label class="form-label">Service Type</label>
                                <select class="form-select" name="service_type" id="edit_service_type" required>
                                    <option value="installation">Installation</option>
                                    <option value="repair">Repair</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Aircon Model</label>
                                <select class="form-select" name="aircon_model_id" id="edit_aircon_model">
                                    <option value="">Select Model</option>
                                    <?php foreach ($airconModels as $model): ?>
                                    <option value="<?= $model['id'] ?>"><?= htmlspecialchars($model['brand'] . ' - ' . $model['model_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Assignment Information -->
                            <div class="col-md-6">
                                <label class="form-label">Assign Technician</label>
                                <select class="form-select" name="assigned_technician_id" id="edit_technician">
                                    <option value="">Select Technician</option>
                                    <?php foreach ($technicians as $tech): ?>
                                    <option value="<?= $tech['id'] ?>"><?= htmlspecialchars($tech['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Due Date</label>
                                <input type="date" class="form-control" name="due_date" id="edit_due_date" required>
                            </div>

                            <!-- Price -->
                            <div class="col-md-6">
                                <label class="form-label">Price (₱)</label>
                                <input type="number" class="form-control" name="price" id="edit_price" step="0.01" required>
                            </div>

                            <!-- Status -->
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status" id="edit_status" required>
                                    <option value="pending">Pending</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Complete Order Modal -->
    <div class="modal fade" id="completeOrderModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Complete Job Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="controller/complete_order.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="order_id" id="complete_order_id">
                        <p>Are you sure you want to mark this job order as completed?</p>
                        <div class="alert alert-info">
                            <strong>Order #:</strong> <span id="complete_order_number"></span><br>
                            <strong>Customer:</strong> <span id="complete_customer_name"></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Mark as Completed</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bulk Orders Modal -->
    <div class="modal fade" id="bulkOrdersModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title">
                        <i class="fas fa-layer-group me-2 text-primary"></i>
                        Create Bulk Orders
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="controller/process_bulk_orders.php" method="POST" id="bulkOrderForm">
                    <div class="modal-body">
                        <!-- Common Settings Card -->
                        <div class="card mb-4 border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title mb-3">
                                    <i class="fas fa-cog me-2 text-primary"></i>
                                    Common Settings
                                </h6>
                                <div class="row g-3">
                                    <!-- Customer Information -->
                                    <div class="col-md-4">
                                        <label class="form-label fw-medium">Customer Name</label>
                                        <input type="text" class="form-control" name="common_customer_name" id="commonCustomerName" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-medium">Phone Number</label>
                                        <input type="tel" class="form-control" name="common_customer_phone" id="commonCustomerPhone" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-medium">Address</label>
                                        <textarea class="form-control" name="common_customer_address" id="commonCustomerAddress" rows="1" required></textarea>
                                    </div>
                                    <!-- Service and Technician -->
                                    <div class="col-md-4">
                                        <label class="form-label fw-medium">Service Type</label>
                                        <select class="form-select" name="common_service_type" id="commonServiceType" required>
                                            <option value="">Select Service Type</option>
                                            <option value="installation">Installation</option>
                                            <option value="repair">Repair</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-medium">Assign Technician</label>
                                        <select class="form-select" name="common_technician" id="commonTechnician" required>
                                            <option value="">Select Technician</option>
                                            <?php foreach ($technicians as $tech): ?>
                                            <option value="<?= $tech['id'] ?>"><?= htmlspecialchars($tech['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-medium">Due Date</label>
                                        <input type="date" class="form-control" name="common_due_date" id="commonDueDate" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Orders Table Card -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-list me-2 text-primary"></i>
                                        Order Details
                                    </h6>
                                    <button type="button" class="btn btn-success btn-sm" id="addRow">
                                        <i class="fas fa-plus me-2"></i>Add Order
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle" id="ordersTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 30%">Aircon Model</th>
                                                <th style="width: 15%">Additional Fee</th>
                                                <th style="width: 15%">Discount</th>
                                                <th style="width: 15%">Total Price</th>
                                                <th style="width: 5%">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="order-row">
                                                <td>
                                                    <select class="form-select form-select-sm aircon-model" name="aircon_model_id[]" required>
                                                        <option value="">Select Model</option>
                                                        <?php foreach ($airconModels as $model): ?>
                                                        <option value="<?= $model['id'] ?>" data-price="<?= $model['price'] ?>">
                                                            <?= htmlspecialchars($model['brand'] . ' - ' . $model['model_name']) ?>
                                                        </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </td>
                                                <td>
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text">₱</span>
                                                        <input type="number" class="form-control additional-fee" name="additional_fee[]" value="0" min="0" step="0.01">
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text">₱</span>
                                                        <input type="number" class="form-control discount" name="discount[]" value="0" min="0" step="0.01">
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text">₱</span>
                                                        <input type="number" class="form-control total-price" name="price[]" readonly>
                                                    </div>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-danger btn-sm remove-row">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Create Orders
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="../js/dashboard.js"></script>
    <script>
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })

        // Handle order type selection
        document.addEventListener('DOMContentLoaded', function() {
            const orderTypeCards = document.querySelectorAll('.order-type-card');
            const orderTypeModal = document.getElementById('orderTypeModal');
            const addJobOrderModal = document.getElementById('addJobOrderModal');
            const bulkOrdersModal = document.getElementById('bulkOrdersModal');

            orderTypeCards.forEach(card => {
                card.addEventListener('click', function() {
                    const orderType = this.getAttribute('data-order-type');
                    
                    // Close the order type modal
                    const orderTypeModalInstance = bootstrap.Modal.getInstance(orderTypeModal);
                    orderTypeModalInstance.hide();

                    if (orderType === 'single') {
                        // Show the regular job order form
                        const addJobOrderModalInstance = new bootstrap.Modal(addJobOrderModal);
                        addJobOrderModalInstance.show();
                    } else {
                        // Show the bulk orders modal
                        const bulkOrdersModalInstance = new bootstrap.Modal(bulkOrdersModal);
                        bulkOrdersModalInstance.show();
                    }
                });
            });

            // Add hover effect to order type cards
            orderTypeCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.cursor = 'pointer';
                    this.style.transform = 'translateY(-5px)';
                    this.style.transition = 'transform 0.2s ease-in-out';
                });

                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });

            // Handle bulk orders table
            const ordersTable = document.getElementById('ordersTable');
            const addRowBtn = document.getElementById('addRow');
            const commonServiceType = document.getElementById('commonServiceType');
            const commonDueDate = document.getElementById('commonDueDate');

            // Function to calculate total price for a row
            function calculateTotalPrice(row) {
                const airconModel = row.querySelector('.aircon-model');
                const additionalFee = parseFloat(row.querySelector('.additional-fee').value) || 0;
                const discount = parseFloat(row.querySelector('.discount').value) || 0;
                const totalPriceInput = row.querySelector('.total-price');

                if (airconModel.value) {
                    const selectedOption = airconModel.options[airconModel.selectedIndex];
                    const basePrice = parseFloat(selectedOption.dataset.price) || 0;
                    const total = basePrice + additionalFee - discount;
                    totalPriceInput.value = total.toFixed(2);
                } else {
                    totalPriceInput.value = '0.00';
                }
            }

            // Add new row
            addRowBtn.addEventListener('click', function() {
                const tbody = ordersTable.querySelector('tbody');
                const newRow = tbody.querySelector('.order-row').cloneNode(true);
                
                // Clear input values
                newRow.querySelectorAll('input').forEach(input => {
                    if (input.type === 'number') {
                        input.value = '0';
                    } else {
                        input.value = '';
                    }
                });
                newRow.querySelectorAll('select').forEach(select => {
                    select.selectedIndex = 0;
                });
                newRow.querySelectorAll('textarea').forEach(textarea => {
                    textarea.value = '';
                });

                // Add event listeners to new row
                addRowEventListeners(newRow);
                tbody.appendChild(newRow);
            });

            // Add event listeners to a row
            function addRowEventListeners(row) {
                // Remove row button
                row.querySelector('.remove-row').addEventListener('click', function() {
                    if (ordersTable.querySelectorAll('.order-row').length > 1) {
                        row.remove();
                    } else {
                        alert('You must have at least one order row.');
                    }
                });

                // Price calculation events
                row.querySelector('.aircon-model').addEventListener('change', () => calculateTotalPrice(row));
                row.querySelector('.additional-fee').addEventListener('input', () => calculateTotalPrice(row));
                row.querySelector('.discount').addEventListener('input', () => calculateTotalPrice(row));
            }

            // Add event listeners to initial row
            addRowEventListeners(ordersTable.querySelector('.order-row'));

            // Apply common values to all rows
            function applyCommonValues() {
                const serviceType = commonServiceType.value;
                const dueDate = commonDueDate.value;

                if (serviceType || dueDate) {
                    ordersTable.querySelectorAll('.order-row').forEach(row => {
                        if (serviceType) {
                            row.querySelector('select[name="service_type[]"]').value = serviceType;
                        }
                        if (dueDate) {
                            row.querySelector('input[name="due_date[]"]').value = dueDate;
                        }
                    });
                }
            }

            // Add event listeners for common values
            commonServiceType.addEventListener('change', applyCommonValues);
            commonDueDate.addEventListener('change', applyCommonValues);

            // Store aircon model prices
            const airconPrices = <?php 
                $prices = [];
                foreach ($airconModels as $model) {
                    $prices[$model['id']] = $model['price'];
                }
                echo json_encode($prices);
            ?>;

            // Handle price calculations for single order
            const airconModelSelect = document.querySelector('select[name="aircon_model_id"]');
            const basePriceInput = document.getElementById('base_price');
            const additionalFeeInput = document.getElementById('additional_fee');
            const discountInput = document.getElementById('discount');
            const totalPriceInput = document.getElementById('total_price');

            // Function to calculate total price for single order
            function calculateSingleTotalPrice() {
                const basePrice = parseFloat(basePriceInput.value) || 0;
                const additionalFee = parseFloat(additionalFeeInput.value) || 0;
                const discount = parseFloat(discountInput.value) || 0;
                
                const total = basePrice + additionalFee - discount;
                totalPriceInput.value = total.toFixed(2);
            }

            // Update base price when aircon model is selected
            airconModelSelect.addEventListener('change', function() {
                const selectedModelId = this.value;
                if (selectedModelId && airconPrices[selectedModelId]) {
                    const price = airconPrices[selectedModelId];
                    basePriceInput.value = parseFloat(price).toFixed(2);
                } else {
                    basePriceInput.value = '0.00';
                }
                calculateSingleTotalPrice();
            });

            // Update total price when additional fee or discount changes
            additionalFeeInput.addEventListener('input', calculateSingleTotalPrice);
            discountInput.addEventListener('input', calculateSingleTotalPrice);

            // Initialize price if model is pre-selected
            if (airconModelSelect.value) {
                const event = new Event('change');
                airconModelSelect.dispatchEvent(event);
            }
        });
    </script>
</body>
</html> 