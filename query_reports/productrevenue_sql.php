<?php
session_start();
require '../dbconn.php';

// Redirect if not logged in
if (!isset($_SESSION['empid'])) {
    header('Location: ../login.php');
    exit;
}

$revenues = [];

$sql = "SELECT 
            P.PRODID,
            P.PRODNAME,
            P.PRODPRICE,
            SUM(OP.QUANTITY) AS TOTAL_QUANTITY_SOLD,
            SUM(OP.AMOUNT) AS TOTAL_REVENUE
        FROM PRODUCT P
        JOIN ORDERPRODUCT OP ON P.PRODID = OP.PRODUCTID
        GROUP BY P.PRODID, P.PRODNAME, P.PRODPRICE
        ORDER BY TOTAL_REVENUE DESC";

$stid = oci_parse($dbconn, $sql);
oci_execute($stid);

while (($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) != false) {
    $revenues[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Total Revenue Per Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h1 class="mb-4 text-center">Total Revenue Per Product</h1>

    <?php if (count($revenues) > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>PRODUCT ID</th>
                        <th>PRODUCT NAME</th>
                        <th>UNIT PRICE (RM)</th>
                        <th>TOTAL QUANTITY SOLD</th>
                        <th>TOTAL REVENUE (RM)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($revenues as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['PRODID']) ?></td>
                            <td><?= htmlspecialchars($row['PRODNAME']) ?></td>
                            <td>RM <?= number_format($row['PRODPRICE'], 2) ?></td>
                            <td><?= htmlspecialchars($row['TOTAL_QUANTITY_SOLD']) ?></td>
                            <td>RM <?= number_format($row['TOTAL_REVENUE'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">No product revenue data found.</div>
    <?php endif; ?>

    <a href="../views/dashboard.php" class="btn btn-danger mt-3">&larr; Back to Dashboard</a>
</div>
</body>
</html>
