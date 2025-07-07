<?php
session_start();
require '../dbconn.php';

// Redirect if not logged in
if (!isset($_SESSION['empid'])) {
    header('Location: ../login.php');
    exit;
}

$products = [];

$sql = "SELECT 
            P.PRODID,
            P.PRODNAME,
            P.PRODPRICE,
            C.CATEGORYNAME
        FROM PRODUCT P
        JOIN CATEGORY C ON P.CATEGORYID = C.CATEGORYID
        ORDER BY P.PRODID";

$stid = oci_parse($dbconn, $sql);
oci_execute($stid);

while (($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) != false) {
    $products[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Product and Category Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-4">
        <h1 class="mb-4 text-center">Product with Their Category</h1>

        <?php if (count($products) > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>CATEGORY</th>
                            <th>PRODUCT ID</th>
                            <th>PRODUCT NAME</th>
                            <th>PRODUCT PRICE (RM)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['CATEGORYNAME']) ?></td>
                                <td><?= htmlspecialchars($row['PRODID']) ?></td>
                                <td><?= htmlspecialchars($row['PRODNAME']) ?></td>
                                <td>RM <?= number_format($row['PRODPRICE'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">No product data found.</div>
        <?php endif; ?>

        <a href="../views/dashboard.php" class="btn btn-danger mt-3">&larr; Back to Dashboard</a>
    </div>
</body>

</html>