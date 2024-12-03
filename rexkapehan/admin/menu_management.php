<?php
session_start();
if ($_SESSION['role'] !== 'owner') {
    header("Location: ../login.php");
    exit;
}

require '../db/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_item'])) {
        $name = $_POST['name'];
        $price = $_POST['price'];
        $stmt = $pdo->prepare("INSERT INTO menu_items (name, price) VALUES (?, ?)");
        $stmt->execute([$name, $price]);
    } elseif (isset($_POST['update_item'])) {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $price = $_POST['price'];
        $hidden = isset($_POST['hidden']) ? 1 : 0;
        $stmt = $pdo->prepare("UPDATE menu_items SET name = ?, price = ?, hidden = ? WHERE id = ?");
        $stmt->execute([$name, $price, $hidden, $id]);
    } elseif (isset($_POST['delete_item'])) {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM menu_items WHERE id = ?");
        $stmt->execute([$id]);
    }
}

$items = $pdo->query("SELECT * FROM menu_items")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Menu Management</title>
    <link rel="stylesheet" href="../assets/bootstrap-5.0.2-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/rex-kapehan-style.css">
</head>
<body>
<div class="container">
    <h1 class="my-4">Menu Management</h1>
    <form method="POST">
        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Price</label>
            <input type="number" step="0.01" name="price" class="form-control" required>
        </div>
        <button type="submit" name="add_item" class="btn btn-success">Add Item</button>
    </form>

    <h2 class="my-4">Menu Items</h2>
    <table class="table">
        <thead>
        <tr>
            <th>Name</th>
            <th>Price</th>
            <th>Hidden</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $item): ?>
            <tr>
                <form method="POST">
                    <td><input type="text" name="name" value="<?= $item['name'] ?>" class="form-control"></td>
                    <td><input type="number" step="0.01" name="price" value="<?= $item['price'] ?>" class="form-control"></td>
                    <td><input type="checkbox" name="hidden" <?= $item['hidden'] ? 'checked' : '' ?>></td>
                    <td>
                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                        <button type="submit" name="update_item" class="btn btn-primary btn-sm">Update</button>
                        <button type="submit" name="delete_item" class="btn btn-danger btn-sm">Delete</button>
                    </td>
                </form>
            </tr>
        <?php endforeach; ?>
        </tbody>
        
    </table>
    <a href="../dashboard.php" class="btn btn-secondary mb-3">Back to Dashboard</a>

</div>
</body>
</html>
