<?php
session_start();
require '../dbconn.php';

// Redirect if not logged in
if (!isset($_SESSION['empid'])) {
    header('Location: ../login.php');
    exit;
}

$payments = [];

$sql = "SELECT 
            PAYMENTMETHOD,
            COUNT(INVOICEID) AS TOTAL_TRANSACTIONS  
        FROM INVOICE
        GROUP BY PAYMENTMETHOD
        ORDER BY TOTAL_TRANSACTIONS DESC";

$stid = oci_parse($dbconn, $sql);
oci_execute($stid);

while (($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) != false) {
    $payments[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales by Payment Method</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h1 class="mb-4 text-center">Payment Method</h1>

    <?php if (count($payments) > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>PAYMENT METHOD</th>
                        <th>TOTAL TRANSACTIONS</th>
                        
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['PAYMENTMETHOD']) ?></td>
                            <td><?= htmlspecialchars($row['TOTAL_TRANSACTIONS']) ?></td>
                            
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">No payment data found.</div>
    <?php endif; ?>

    <a href="../views/dashboard.php" class="btn btn-danger mt-3">&larr; Back to Dashboard</a>
</div>
</body>
</html>
