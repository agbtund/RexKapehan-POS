<?php
session_start();
if ($_SESSION['role'] !== 'owner') {
    header("Location: ../login.php");
    exit;
}

require '../db/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];

    // Fetch sales data between specific dates
    $stmt = $pdo->prepare("
        SELECT DATE(o.created_at) as date, SUM(o.total_amount) as total_sales, COUNT(*) as transaction_count
        FROM orders o
        WHERE o.created_at BETWEEN ? AND ? AND o.status = 'completed'
        GROUP BY DATE(o.created_at)
        ORDER BY date
    ");
    $stmt->execute([$startDate, $endDate]);
    $reportData = $stmt->fetchAll();

    // Fetch best-selling items
    $stmt = $pdo->prepare("
        SELECT m.name, SUM(od.quantity) as total_quantity
        FROM order_details od
        JOIN menu_items m ON od.menu_item_id = m.id
        JOIN orders o ON od.order_id = o.id
        WHERE o.created_at BETWEEN ? AND ? AND o.status = 'completed'
        GROUP BY od.menu_item_id
        ORDER BY total_quantity DESC
        LIMIT 5
    ");
    $stmt->execute([$startDate, $endDate]);
    $bestSellingItems = $stmt->fetchAll();

    // Fetch best customers
    $stmt = $pdo->prepare("
        SELECT customer_name, COUNT(*) as order_count, SUM(total_amount) as total_spent
        FROM orders
        WHERE created_at BETWEEN ? AND ? AND status = 'completed'
        GROUP BY customer_name
        ORDER BY total_spent DESC
        LIMIT 5
    ");
    $stmt->execute([$startDate, $endDate]);
    $bestCustomers = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rex Kapehan - Report</title>
    <link rel="stylesheet" href="../assets/bootstrap-5.0.2-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/rex-kapehan-style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="#">
            <img src="../assets/img/460.jpg" height="30" width="40" alt="Rex Kapehan Logo" class="d-inline-block align-text-top me-2">
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
                    <a class="nav-link" href="analytics.php">Analytics</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="#">Reports</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h1 class="mb-4">Generate Report</h1>

    <form method="POST">
        <div class="row mb-3">
            <div class="col-md-5">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" name="start_date" id="start_date" class="form-control" required>
            </div>
            <div class="col-md-5">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" name="end_date" id="end_date" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">Generate</button>
            </div>
        </div>
    </form>

    <?php if (isset($reportData)): ?>
        <h2 class="my-4">Sales Report</h2>
        <div class="table-responsive">
            <table class="table">
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Total Sales</th>
                    <th>Transaction Count</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($reportData as $data): ?>
                    <tr>
                        <td><?= $data['date'] ?></td>
                        <td>₱<?= number_format($data['total_sales'], 2) ?></td>
                        <td><?= $data['transaction_count'] ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <h2 class="my-4">Best Selling Items</h2>
        <div class="table-responsive">
            <table class="table">
                <thead>
                <tr>
                    <th>Item</th>
                    <th>Quantity Sold</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($bestSellingItems as $item): ?>
                    <tr>
                        <td><?= $item['name'] ?></td>
                        <td><?= $item['total_quantity'] ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <h2 class="my-4">Best Customers</h2>
        <div class="table-responsive">
            <table class="table">
                <thead>
                <tr>
                    <th>Customer</th>
                    <th>Order Count</th>
                    <th>Total Spent</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($bestCustomers as $customer): ?>
                    <tr>
                        <td><?= $customer['customer_name'] ?></td>
                        <td><?= $customer['order_count'] ?></td>
                        <td>₱<?= number_format($customer['total_spent'], 2) ?></td>
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

