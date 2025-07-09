<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is a technician
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'technician') {
    header('Location: ../index.php');
    exit();
}

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get technician details
    $stmt = $pdo->prepare("SELECT * FROM technicians WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $technician = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get only ongoing orders (pending and in_progress)
    $stmt = $pdo->prepare("
        SELECT 
            jo.*,
            COALESCE(am.model_name, 'Not Specified') as model_name 
        FROM job_orders jo 
        LEFT JOIN aircon_models am ON jo.aircon_model_id = am.id 
        WHERE jo.assigned_technician_id = ? 
        AND jo.status IN ('pending', 'in_progress')
        AND jo.status != 'cancelled'
        ORDER BY 
            CASE 
                WHEN jo.status = 'pending' THEN 1
                WHEN jo.status = 'in_progress' THEN 2
            END,
            jo.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
}
// Include header
require_once 'includes/header.php';
?>
<body>
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
                                <img src="<?= !empty($technician['profile_picture']) ? '../' . htmlspecialchars($technician['profile_picture']) : 'https://ui-avatars.com/api/?name=' . urlencode($technician['name'] ?: 'Technician') . '&background=1a237e&color=fff' ?>" 
                                     alt="Technician" 
                                     class="rounded-circle me-2" 
                                     width="32" 
                                     height="32"
                                     style="object-fit: cover; border: 2px solid #4A90E2;">
                                <span class="me-3">Welcome, <?= htmlspecialchars($technician['name'] ?: 'Technician') ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0" style="min-width: 200px;">
                                <li>
                                    <a class="dropdown-item d-flex align-items-center py-2" href="profile.php">
                                        <i class="fas fa-user me-2 text-primary"></i>
                                        <span>Profile</span>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider my-2"></li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center py-2 text-danger" href="../admin/logout.php">
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
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Ongoing Orders</h1>
                </div>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <?= $_SESSION['error'] ?>
                        <?php unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?= $_SESSION['success'] ?>
                        <?php unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <?php if (empty($orders)): ?>
                            <p class="text-muted">No ongoing orders found.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Order #</th>
                                        <th>Customer</th>
                                        <th>Service Type</th>
                                        <th>Model</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>Due Date</th>
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
                                                    <div class="fw-semibold"><?= htmlspecialchars($order['customer_name']) ?></div>
                                                    <div class="small text-muted">
                                                        <i class="fas fa-phone me-1"></i><?= htmlspecialchars($order['customer_phone']) ?><br>
                                                        <i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($order['customer_address']) ?>
                                                    </div>
                                                </td>
                                                <td>
                                            <span class="badge bg-info bg-opacity-10 text-info">
                                                <?= ucfirst(htmlspecialchars($order['service_type'])) ?>
                                            </span>
                                        </td>
                                                <td><?= htmlspecialchars($order['model_name']) ?></td>
                                                <td>₱<?= number_format($order['price'], 2) ?></td>
                                                <td>
                                                    <span class="badge bg-<?= $order['status'] === 'pending' ? 'warning' : 'info' ?>">
                                                        <?= ucfirst($order['status']) ?>
                                                    </span>
                                                </td>
                                                <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                                                <td>
                                                    <div class="d-flex justify-content-center gap-2">
                                                        <a href="view-order.php?id=<?= $order['id'] ?>" 
                                                           class="btn btn-sm btn-light" 
                                                           data-bs-toggle="tooltip" 
                                                           title="View Details">
                                                            <i class="fas fa-eye text-primary"></i>
                                                        </a>
                                                        <?php if ($order['status'] === 'pending'): ?>
                                                            <a href="update-status.php?id=<?= $order['id'] ?>&status=in_progress" 
                                                               class="btn btn-sm btn-light" 
                                                               data-bs-toggle="tooltip" 
                                                               title="Start Work">
                                                                <i class="fas fa-play text-success"></i>
                                                            </a>
                                                            <a href="update-status.php?id=<?= $order['id'] ?>&status=cancelled" 
                                                               class="btn btn-sm btn-light" 
                                                               data-bs-toggle="tooltip" 
                                                               title="Cancel Order">
                                                                <i class="fas fa-times text-danger"></i>
                                                            </a>
                                                        <?php elseif ($order['status'] === 'in_progress'): ?>
                                                            <a href="update-status.php?id=<?= $order['id'] ?>&status=completed" 
                                                               class="btn btn-sm btn-light" 
                                                               data-bs-toggle="tooltip" 
                                                               title="Mark as Completed">
                                                                <i class="fas fa-check text-success"></i>
                                                            </a>
                                                            <a href="update-status.php?id=<?= $order['id'] ?>&status=cancelled" 
                                                               class="btn btn-sm btn-light" 
                                                               data-bs-toggle="tooltip" 
                                                               title="Cancel Order">
                                                                <i class="fas fa-times text-danger"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
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
    </script>
</body>
</html> 