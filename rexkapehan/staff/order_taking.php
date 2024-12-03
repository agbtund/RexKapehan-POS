<?php
session_start();
if ($_SESSION['role'] !== 'staff') {
    header("Location: ../login.php");
    exit;
}

require '../db/db.php';

$menuItems = $pdo->query("SELECT * FROM menu_items WHERE hidden = 0")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerName = $_POST['customer_name'];
    $menuItemsInput = $_POST['menu_items'];
    $paymentMethod = $_POST['payment_method'];

    // Calculate total
    $total = 0;
    foreach ($menuItemsInput as $id => $quantity) {
        if ($quantity > 0) {
            $item = $pdo->query("SELECT price FROM menu_items WHERE id = $id")->fetch();
            $total += $item['price'] * $quantity;
        }
    }

    // Save the order
    $stmt = $pdo->prepare("INSERT INTO orders (customer_name, total_amount, payment_method) VALUES (?, ?, ?)");
    $stmt->execute([$customerName, $total, $paymentMethod]);

    // Save each item in the order
    $orderId = $pdo->lastInsertId();
    foreach ($menuItemsInput as $id => $quantity) {
        if ($quantity > 0) {
            $stmt = $pdo->prepare("INSERT INTO order_details (order_id, menu_item_id, quantity) VALUES (?, ?, ?)");
            $stmt->execute([$orderId, $id, $quantity]);
        }
    }

    header("Location: pending_orders.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rex Kapehan - Order Taking</title>
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
                    <a class="nav-link active" href="#">Order Taking</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="pending_orders.php">Pending Orders</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="completed_orders.php">Completed Orders</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h1 class="mb-4">Order Taking</h1>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Customer Name</label>
            <input type="text" name="customer_name" class="form-control" required>
        </div>

        <div class="mb-3">
            <h3>Menu Items</h3>
            <?php foreach ($menuItems as $item): ?>
                <div class="d-flex align-items-center mb-2">
                    <label class="me-3"><?= $item['name'] ?> (₱<?= number_format($item['price'], 2) ?>):</label>
                    <input type="number" name="menu_items[<?= $item['id'] ?>]" class="form-control w-25" placeholder="Quantity" min="0">
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mb-3">
            <h3>Payment Method</h3>
            <select name="payment_method" class="form-control" required>
                <option value="cash">Cash</option>
                <option value="mobile">Mobile (Gcash/Paymaya)</option>
            </select>
        </div>

        <button type="submit" class="btn btn-success w-100">Submit Order</button>
    </form>
</div>

<script src="../assets/bootstrap-5.0.2-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>