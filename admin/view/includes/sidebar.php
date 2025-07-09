<?php
// Get current page filename
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Sidebar -->
<nav id="sidebar" class="text-white">
    <div class="sidebar-header">
        <div class="text-center mb-3">
            <img src="../../images/logo.png" alt="Logo" style="width: 70px; height: 70px; margin-bottom: 10px; border-radius: 50%; border: 2px solid #4A90E2; box-shadow: 0 0 10px rgba(74, 144, 226, 0.5); display: block; margin-left: auto; margin-right: auto;">
        </div>
    </div>

    <ul class="list-unstyled components">
        <li class="<?= $current_page === 'dashboard.php' ? 'active' : '' ?>">
            <a href="../dashboard.php" class="<?= $current_page === 'dashboard.php' ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt"></i>
                Dashboard
            </a>
        </li>
        <li class="<?= in_array($current_page, ['orders.php', 'archived.php']) ? 'active' : '' ?>">
            <a href="#jobOrdersSubmenu" data-bs-toggle="collapse" aria-expanded="<?= in_array($current_page, ['orders.php', 'archived.php']) ? 'true' : 'false' ?>" class="dropdown-toggle <?= in_array($current_page, ['orders.php', 'archived.php']) ? 'active' : '' ?>">
                <i class="fas fa-clipboard-list"></i>
                Job Orders
            </a>
            <ul class="collapse list-unstyled <?= in_array($current_page, ['orders.php', 'archived.php']) ? 'show' : '' ?>" id="jobOrdersSubmenu">
                <li class="<?= $current_page == 'orders.php' ? 'active' : '' ?>">
                    <a href="../orders.php">
                        <i class="fas fa-file-alt"></i>
                        Orders
                    </a>
                </li>
                <li class="<?= $current_page == '../archived.php' ? 'active' : '' ?>">
                    <a href="../archived.php">
                        <i class="fas fa-archive"></i>
                        Archived
                    </a>
                </li>
            </ul>
        </li>
        <li class="<?= $current_page === 'technicians.php' ? 'active' : '' ?>">
            <a href="../technicians.php" class="<?= $current_page === 'technicians.php' ? 'active' : '' ?>">
                <i class="fas fa-users-cog"></i>
                Technicians
            </a>
        </li>
        <li class="<?= $current_page === 'reports.php' ? 'active' : '' ?>">
            <a href="../reports.php" class="<?= $current_page === 'reports.php' ? 'active' : '' ?>">
                <i class="fas fa-chart-bar"></i>
                Reports
            </a>
        </li>
        <li>
            <a href="#settingsSubmenu" data-bs-toggle="collapse" aria-expanded="<?= in_array($current_page, ['settings/index.php', 'aircon_models.php']) ? 'true' : 'false' ?>" class="dropdown-toggle <?= in_array($current_page, ['settings/index.php', 'aircon_models.php']) ? 'active' : '' ?>">
                <i class="fas fa-cog"></i>
                Settings
            </a>
            <ul class="collapse list-unstyled <?= in_array($current_page, ['settings/index.php', 'aircon_models.php']) ? 'show' : '' ?>" id="settingsSubmenu">
                <li>
                    <a href="../settings/index.php" class="<?= $current_page === 'settings/index.php' ? 'active' : '' ?>">
                        <i class="fas fa-user-shield"></i>
                        Admin Settings
                    </a>
                </li>
                <li>
                    <a href="../aircon_models.php" class="<?= $current_page === 'aircon_models.php' ? 'active' : '' ?>">
                        <i class="fas fa-snowflake"></i>
                        Aircon Models
                    </a>
                </li>
            </ul>
        </li>
    </ul>
</nav>