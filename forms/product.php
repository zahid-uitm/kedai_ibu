<?php
session_start();
require '../dbconn.php';

if (!isset($_SESSION['empid'])) {
    header('Location: ../login.php');
    exit;
}

// Fetch products
$products = [];
$query = "SELECT * FROM PRODUCT";
$stid = oci_parse($dbconn, $query);
oci_execute($stid);

while ($row = oci_fetch_assoc($stid)) {
    $products[] = $row;
}

oci_free_statement($stid);
oci_close($dbconn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Kedai Ibu | Product Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-4">
        <h1 class="mb-4 text-center">Product Form</h1>
        <div class="d-flex align-items-end mb-3">
            <a href="product_create.php" class="btn btn-success">+ Create Product</a>
        </div>

        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Product ID</th>
                    <th>Product Name</th>
                    <th>Price</th>
                    <th>Category ID</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $prod): ?>
                    <tr>
                        <td><?= htmlspecialchars($prod['PRODID']) ?></td>
                        <td><?= htmlspecialchars($prod['PRODNAME']) ?></td>
                        <td>RM <?= number_format($prod['PRODPRICE'], 2) ?></td>
                        <td><?= htmlspecialchars($prod['CATEGORYID']) ?></td>
                        <td>
                            <a href="product_update.php?prodid=<?= $prod['PRODID'] ?>"
                                class="btn btn-sm btn-primary">Edit</a>
                            <a href="product_delete.php?prodid=<?= $prod['PRODID'] ?>" class="btn btn-sm btn-danger"
                                onclick="return confirm('Are you sure to delete this product?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>