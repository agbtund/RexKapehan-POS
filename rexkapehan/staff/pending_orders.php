<?php
session_start();
if ($_SESSION['role'] !== 'staff') {
    header("Location: ../login.php");
    exit;
}

require '../db/db.php';

// Fetch pending orders
$stmt = $pdo->query("
    SELECT o.id, o.customer_name, o.total_amount, o.created_at,
           GROUP_CONCAT(CONCAT(od.quantity, 'x ', mi.name) SEPARATOR ', ') AS order_items
    FROM orders o
    JOIN order_details od ON o.id = od.order_id
    JOIN menu_items mi ON od.menu_item_id = mi.id
    WHERE o.status = 'pending'
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$pendingOrders = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = $_POST['order_id'];
    $action = $_POST['action'];

    if ($action === 'complete') {
        $stmt = $pdo->prepare("UPDATE orders SET status = 'completed' WHERE id = ?");
        $stmt->execute([$orderId]);
    } elseif ($action === 'cancel') {
        $stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
        $stmt->execute([$orderId]);
    }

    // Redirect to refresh the page
    header("Location: pending_orders.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rex Kapehan - Pending Orders</title>
    <link rel="stylesheet" href="../assets/bootstrap-5.0.2-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/rex-kapehan-style.css">
    <style>
        /* Ensure text is visible */
        .table-striped tbody tr {
            color: white;
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0,0,0,0.5);
        }
        .table-striped tbody tr:nth-of-type(even) {
            background-color: rgba(0,0,0,0.3);
        }
        .table-striped tbody tr .btn {
            color: white;
        }
    </style>
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
                    <a class="nav-link active" href="#">Pending Orders</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="completed_orders.php">Completed Orders</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h1 class="mb-4 text-white">Pending Orders</h1>

    <?php if (empty($pendingOrders)): ?>
        <p class="text-white">No pending orders at the moment.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th class="text-white">Order ID</th>
                        <th class="text-white">Customer Name</th>
                        <th class="text-white">Order Items</th>
                        <th class="text-white">Total Amount</th>
                        <th class="text-white">Order Time</th>
                        <th class="text-white">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingOrders as $order): ?>
                        <tr>
                            <td><?= $order['id'] ?></td>
                            <td><?= htmlspecialchars($order['customer_name']) ?></td>
                            <td><?= htmlspecialchars($order['order_items']) ?></td>
                            <td>₱<?= number_format($order['total_amount'], 2) ?></td>
                            <td><?= date('Y-m-d H:i:s', strtotime($order['created_at'])) ?></td>
                            <td>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <button type="submit" name="action" value="complete" class="btn btn-success btn-sm">Complete</button>
                                    <button type="submit" name="action" value="cancel" class="btn btn-danger btn-sm">Cancel</button>
                                </form>
                            </td>
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