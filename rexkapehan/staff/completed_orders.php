<?php
session_start();
if ($_SESSION['role'] !== 'staff') {
    header("Location: ../login.php");
    exit;
}

require '../db/db.php';

// Fetch completed orders for today
$stmt = $pdo->prepare("
    SELECT o.id, o.customer_name, o.total_amount, o.created_at,
           GROUP_CONCAT(CONCAT(od.quantity, 'x ', mi.name) SEPARATOR ', ') AS order_items
    FROM orders o
    JOIN order_details od ON o.id = od.order_id
    JOIN menu_items mi ON od.menu_item_id = mi.id
    WHERE o.status = 'completed' AND DATE(o.created_at) = CURDATE()
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$stmt->execute();
$completedOrders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rex Kapehan - Completed Orders</title>
    <link rel="stylesheet" href="../assets/bootstrap-5.0.2-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/rex-kapehan-style.css">
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
                    <a class="nav-link" href="../dashboard.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="order_taking.php">Order Taking</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="pending_orders.php">Pending Orders</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="#">Completed Orders</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h1 class="mb-4">Completed Orders (Today)</h1>

    <?php if (empty($completedOrders)): ?>
        <p>No completed orders for today.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer Name</th>
                        <th>Order Items</th>
                        <th>Total Amount</th>
                        <th>Completed Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($completedOrders as $order): ?>
                        <tr>
                            <td><?= $order['id'] ?></td>
                            <td><?= htmlspecialchars($order['customer_name']) ?></td>
                            <td><?= htmlspecialchars($order['order_items']) ?></td>
                            <td>₱<?= number_format($order['total_amount'], 2) ?></td>
                            <td><?= date('Y-m-d H:i:s', strtotime($order['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script src="../assets/bootstrap-5.0.2-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>