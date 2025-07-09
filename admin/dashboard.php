<?php
session_start();
require_once('../config/database.php');

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get admin details
    $stmt = $pdo->prepare("SELECT name, profile_picture FROM admins WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get counts for dashboard cards
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
        FROM job_orders
    ");
    $counts = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get all technicians for the dropdown
    $stmt = $pdo->query("SELECT id, name FROM technicians ORDER BY name ASC");
    $technicians = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get monthly statistics for the chart
    $stmt = $pdo->query("
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            COUNT(*) as total_orders,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_orders,
            SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_orders,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
            AVG(CASE 
                WHEN status = 'completed' 
                THEN TIMESTAMPDIFF(HOUR, created_at, completed_at)
                ELSE NULL 
            END) as avg_completion_time
        FROM job_orders 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month ASC
    ");
    $monthlyStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get technician performance statistics
    $stmt = $pdo->query("
        SELECT 
            t.name as technician_name,
            COUNT(jo.id) as total_orders,
            SUM(CASE WHEN jo.status = 'completed' THEN 1 ELSE 0 END) as completed_orders,
            AVG(CASE 
                WHEN jo.status = 'completed' 
                THEN TIMESTAMPDIFF(HOUR, jo.created_at, jo.completed_at)
                ELSE NULL 
            END) as avg_completion_time
        FROM technicians t
        LEFT JOIN job_orders jo ON t.id = jo.assigned_technician_id
        WHERE jo.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY t.id, t.name
        ORDER BY completed_orders DESC
        LIMIT 5
    ");
    $technicianStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
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
                        <h4 class="mb-0">Dashboard</h4>
                        <p class="text-muted mb-0">Overview of job orders and system statistics</p>
                    </div>
                </div>

                <!-- Alert Messages -->
                <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $_SESSION['success_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success_message']); endif; ?>

                <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $_SESSION['error_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error_message']); endif; ?>

                <!-- Dashboard Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="card total-orders">
                            <div class="card-body text-white">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="card-title mb-0">Total Orders</h5>
                                    <i class="fas fa-clipboard-list fa-2x"></i>
                                </div>
                                <h2 class="card-text mb-2"><?= $counts['total'] ?? 0 ?></h2>
                                <p class="card-text mb-0"><small>All time</small></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="card completed-orders">
                            <div class="card-body text-white">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="card-title mb-0">Completed</h5>
                                    <i class="fas fa-check-circle fa-2x"></i>
                                </div>
                                <h2 class="card-text mb-2"><?= $counts['completed'] ?? 0 ?></h2>
                                <p class="card-text mb-0"><small>Successfully done</small></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="card in-progress-orders">
                            <div class="card-body text-white">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="card-title mb-0">In Progress</h5>
                                    <i class="fas fa-clock fa-2x"></i>
                                </div>
                                <h2 class="card-text mb-2"><?= $counts['in_progress'] ?? 0 ?></h2>
                                <p class="card-text mb-0"><small>Currently working</small></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="card pending-orders">
                            <div class="card-body text-white">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="card-title mb-0">Pending</h5>
                                    <i class="fas fa-hourglass-half fa-2x"></i>
                                </div>
                                <h2 class="card-text mb-2"><?= $counts['pending'] ?? 0 ?></h2>
                                <p class="card-text mb-0"><small>Awaiting action</small></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="row g-3">
                    <div class="col-12 col-lg-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5 class="card-title mb-0">Orders Overview</h5>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-primary active">Monthly</button>
                                        <button type="button" class="btn btn-sm btn-outline-primary">Weekly</button>
                                    </div>
                                </div>
                                <div class="chart-container">
                                    <canvas id="ordersChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5 class="card-title mb-0">Technician Performance</h5>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Last 30 Days
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#">Last 7 Days</a></li>
                                            <li><a class="dropdown-item" href="#">Last 30 Days</a></li>
                                            <li><a class="dropdown-item" href="#">Last 90 Days</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="chart-container">
                                    <canvas id="technicianChart"></canvas>
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
    <script src="../js/dashboard.js"></script>
    <script>
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })

        // Common chart options
        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20
                    }
                }
            }
        };

        // Orders Overview Chart
        const ordersCtx = document.getElementById('ordersChart').getContext('2d');
        const monthlyData = <?= json_encode($monthlyStats) ?>;
        
        new Chart(ordersCtx, {
            type: 'line',
            data: {
                labels: monthlyData.map(item => {
                    const date = new Date(item.month + '-01');
                    return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                }),
                datasets: [{
                    label: 'Total Orders',
                    data: monthlyData.map(item => item.total_orders),
                    borderColor: '#1a237e',
                    backgroundColor: 'rgba(26, 35, 126, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 2,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#1a237e',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }, {
                    label: 'Completed',
                    data: monthlyData.map(item => item.completed_orders),
                    borderColor: '#4caf50',
                    backgroundColor: 'rgba(76, 175, 80, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 2,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#4caf50',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }, {
                    label: 'In Progress',
                    data: monthlyData.map(item => item.in_progress_orders),
                    borderColor: '#2196f3',
                    backgroundColor: 'rgba(33, 150, 243, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 2,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#2196f3',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }, {
                    label: 'Pending',
                    data: monthlyData.map(item => item.pending_orders),
                    borderColor: '#ff9800',
                    backgroundColor: 'rgba(255, 152, 0, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 2,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#ff9800',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                ...commonOptions,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false,
                            color: 'rgba(0, 0, 0, 0.03)'
                        },
                        ticks: {
                            padding: 10,
                            stepSize: 1
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            padding: 10
                        }
                    }
                }
            }
        });

        // Technician Performance Chart
        const techCtx = document.getElementById('technicianChart').getContext('2d');
        const techData = <?= json_encode($technicianStats) ?>;
        
        new Chart(techCtx, {
            type: 'bar',
            data: {
                labels: techData.map(item => item.technician_name),
                datasets: [{
                    label: 'Total Orders',
                    data: techData.map(item => item.total_orders),
                    backgroundColor: 'rgba(26, 35, 126, 0.7)',
                    borderColor: '#1a237e',
                    borderWidth: 1
                }, {
                    label: 'Completed',
                    data: techData.map(item => item.completed_orders),
                    backgroundColor: 'rgba(76, 175, 80, 0.7)',
                    borderColor: '#4caf50',
                    borderWidth: 1
                }]
            },
            options: {
                ...commonOptions,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false,
                            color: 'rgba(0, 0, 0, 0.03)'
                        },
                        ticks: {
                            padding: 10,
                            stepSize: 1
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            padding: 10
                        }
                    }
                }
            }
        });
    </script>
</body>
</html> 