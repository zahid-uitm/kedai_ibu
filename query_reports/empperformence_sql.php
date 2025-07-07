<?php
session_start();
require '../dbconn.php';

// Redirect if not logged in
if (!isset($_SESSION['empid'])) {
    header('Location: ../login.php');
    exit;
}

$employees = [];

$sql = "SELECT 
            E.EMPID,
            E.EMPLOYEEFIRSTNAME || ' ' || E.EMPLOYEELASTNAME AS EMPLOYEENAME,
            COUNT(I.INVOICEID) AS TOTAL_INVOICES,
            SUM(I.INVOICETOTALAMOUNT) AS TOTAL_SALES
        FROM EMPLOYEE E
        JOIN INVOICE I ON E.EMPID = I.EMPID
        GROUP BY E.EMPID, E.EMPLOYEEFIRSTNAME, E.EMPLOYEELASTNAME
        ORDER BY TOTAL_SALES DESC";

$stid = oci_parse($dbconn, $sql);
oci_execute($stid);

while (($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) != false) {
    $employees[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employee Performance Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h1 class="mb-4 text-center">Employee Performance Report</h1>

    <?php if (count($employees) > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>EMPLOYEE ID</th>
                        <th>EMPLOYEE NAME</th>
                        <th>TOTAL INVOICES</th>
                        <th>TOTAL SALES (RM)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employees as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['EMPID']) ?></td>
                            <td><?= htmlspecialchars($row['EMPLOYEENAME']) ?></td>
                            <td><?= htmlspecialchars($row['TOTAL_INVOICES']) ?></td>
                            <td>RM <?= number_format($row['TOTAL_SALES'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">No employee sales data found.</div>
    <?php endif; ?>

    <a href="../views/dashboard.php" class="btn btn-danger mt-3">&larr; Back to Dashboard</a>
</div>
</body>
</html>
