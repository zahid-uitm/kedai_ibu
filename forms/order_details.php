<?php
session_start();
require '../dbconn.php';

if (!isset($_SESSION['empid'])) {
    header('Location: ../login.php');
    exit;
}

if (!isset($_GET['orderid'])) {
    header("Location: order.php");
    exit;
}

$orderid = $_GET['orderid'];

// Fetch order product data
$orderProd = [];
$query = "SELECT * FROM ORDERPRODUCT OP JOIN PRODUCT P ON OP.PRODUCTID = P.PRODID WHERE OP.ORDERID = :orderid";
$stid = oci_parse($dbconn, $query);
oci_bind_by_name($stid, ":orderid", $orderid);
oci_execute($stid);

while ($row = oci_fetch_assoc($stid)) {
    $orderProd[] = $row;
}

oci_free_statement($stid);
oci_close($dbconn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Kedai Ibu | Order Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-4">
        <h1 class="mb-4 text-center"> Order Details Form</h1>

        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Order ID</th>
                    <th>Product ID</th>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Amount</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orderProd as $ord): ?>
                    <tr>
                        <td><?= htmlspecialchars($ord['ORDERID']) ?></td>
                        <td><?= htmlspecialchars($ord['PRODUCTID']) ?></td>
                        <td><?= htmlspecialchars($ord['PRODNAME']) ?></td>
                        <td><?= htmlspecialchars($ord['QUANTITY']) ?></td>
                        <td>RM <?= number_format($ord['AMOUNT'], 2) ?></td>
                        <td>
                            <a href="order_details_update.php?opid=<?= $ord['ORDERID'] ?>&pid=<?= $ord['PRODUCTID'] ?>"
                                class="btn btn-sm btn-primary">Edit</a>
                            <a href="order_details_delete.php?opid=<?= $ord['ORDERID'] ?>&pid=<?= $ord['PRODUCTID'] ?>" class="btn btn-sm btn-danger"
                                onclick="return confirm('Are you sure to delete this order?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>