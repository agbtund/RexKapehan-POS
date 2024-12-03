<?php
session_start();
if ($_SESSION['role'] !== 'owner') {
  header("Location: ../login.php");
  exit;
}

require '../db/db.php';

// Function to get condition based on filter
function getCondition($filter) {
  switch ($filter) {
      case 'week':
          return "YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)";
      case 'month':
          return "MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())";
      case 'year':
          return "YEAR(created_at) = YEAR(CURDATE())";
      default:
          return "DATE(created_at) = CURDATE()";
  }
}

// Get filter
$filter = $_GET['filter'] ?? 'today';
$condition = getCondition($filter);

// Total sales
$totalSales = $pdo->query("
  SELECT COALESCE(SUM(total_amount), 0) AS total 
  FROM orders 
  WHERE $condition AND status = 'completed'
")->fetchColumn();

// Best selling item
$bestSellingItem = $pdo->query("
  SELECT m.name, SUM(od.quantity) AS total_quantity 
  FROM order_details od
  JOIN menu_items m ON od.menu_item_id = m.id
  JOIN orders o ON od.order_id = o.id
  WHERE o.status = 'completed' AND $condition
  GROUP BY od.menu_item_id
  ORDER BY total_quantity DESC
  LIMIT 1
")->fetch();

// Transactions count
$transactions = $pdo->query("SELECT COUNT(*) AS total FROM orders WHERE $condition AND status = 'completed'")->fetchColumn();

// Best buying customer
$bestCustomer = $pdo->query("
  SELECT customer_name, COUNT(*) AS total_orders 
  FROM orders 
  WHERE $condition AND status = 'completed'
  GROUP BY customer_name
  ORDER BY total_orders DESC
  LIMIT 1
")->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Rex Kapehan - Analytics</title>
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
                  <a class="nav-link active" href="#">Analytics</a>
              </li>
              <li class="nav-item">
                  <a class="nav-link" href="report.php">Reports</a>
              </li>
          </ul>
      </div>
  </div>
</nav>

<div class="container mt-4">
  <h1 class="mb-4">Analytics</h1>

  <form method="GET" class="mb-4">
      <div class="row">
          <div class="col-md-3">
              <select name="filter" class="form-select" onchange="this.form.submit()">
                  <option value="today" <?= $filter == 'today' ? 'selected' : '' ?>>Today</option>
                  <option value="week" <?= $filter == 'week' ? 'selected' : '' ?>>This Week</option>
                  <option value="month" <?= $filter == 'month' ? 'selected' : '' ?>>This Month</option>
                  <option value="year" <?= $filter == 'year' ? 'selected' : '' ?>>This Year</option>
              </select>
          </div>
      </div>
  </form>

  <div class="row mb-4">
      <div class="col-md-3 mb-4">
          <div class="card h-100">
              <div class="card-header">Total Sales</div>
              <div class="card-body">
                  <h4 class="card-title">₱<?= number_format($totalSales, 2) ?></h4>
              </div>
          </div>
      </div>
      <div class="col-md-3 mb-4">
          <div class="card h-100">
              <div class="card-header">Best Selling Item</div>
              <div class="card-body">
                  <h5 class="card-title"><?= $bestSellingItem['name'] ?? 'N/A' ?></h5>
                  <p class="card-text"><?= $bestSellingItem['total_quantity'] ?? 0 ?> sold</p>
              </div>
          </div>
      </div>
      <div class="col-md-3 mb-4">
          <div class="card h-100">
              <div class="card-header">Total Transactions</div>
              <div class="card-body">
                  <h4 class="card-title"><?= $transactions ?></h4>
              </div>
          </div>
      </div>
      <div class="col-md-3 mb-4">
          <div class="card h-100">
              <div class="card-header">Best Buying Customer</div>
              <div class="card-body">
                  <h5 class="card-title"><?= $bestCustomer['customer_name'] ?? 'N/A' ?></h5>
                  <p class="card-text"><?= $bestCustomer['total_orders'] ?? 0 ?> orders</p>
              </div>
          </div>
      </div>
  </div>

  <a href="report.php" class="btn btn-primary">Generate Detailed Report</a>
</div>

<script src="../assets/bootstrap-5.0.2-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

