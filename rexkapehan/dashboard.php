<?php
session_start();
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

$role = $_SESSION['role'];
$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rex Kapehan - Dashboard</title>
    <link rel="stylesheet" href="assets/bootstrap-5.0.2-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/rex-kapehan-style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="#">
            <img src="/placeholder.svg?height=30&width=30" alt="Rex Kapehan Logo" class="d-inline-block align-text-top me-2">
            Rex Kapehan
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="#">Dashboard</a>
                </li>
                <?php if ($role == 'owner'): ?>
                <li class="nav-item">
                    <a class="nav-link" href="admin/analytics.php">Analytics</a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h1 class="mb-4">Welcome, <?php echo htmlspecialchars($username); ?></h1>

    <?php if ($role == 'owner'): ?>
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Menu Management</h5>
                        <p class="card-text">Add, edit, or remove items from your menu.</p>
                        <a href="admin/menu_management.php" class="btn btn-primary">Manage Menu</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Analytics</h5>
                        <p class="card-text">View sales data and performance metrics.</p>
                        <a href="admin/analytics.php" class="btn btn-primary">View Analytics</a>
                    </div>
                </div>
            </div>
        </div>
    <?php elseif ($role == 'staff'): ?>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Order Taking</h5>
                        <p class="card-text">Process new customer orders.</p>
                        <a href="staff/order_taking.php" class="btn btn-primary">Take Order</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Pending Orders</h5>
                        <p class="card-text">View and manage pending orders.</p>
                        <a href="staff/pending_orders.php" class="btn btn-primary">View Pending Orders</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Completed Orders</h5>
                        <p class="card-text">View completed orders for today.</p>
                        <a href="staff/completed_orders.php" class="btn btn-primary">View Completed Orders</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="assets/bootstrap-5.0.2-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

