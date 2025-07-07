<?php
session_start();
require '../dbconn.php';

if (!isset($_SESSION['empid'])) {
    header('Location: ../login.php');
    exit;
}

$supervisors = []; // Fixed variable name

$sql = "SELECT 
            E.EMPID, 
            E.EMPLOYEEFIRSTNAME || ' ' || E.EMPLOYEELASTNAME AS EMPLOYEE_NAME, 
            P.HOURLYRATE, 
            P.HOURSWORK,
            (P.HOURLYRATE * P.HOURSWORK) AS MONTHLY_EARNING
        FROM PARTTIME P 
        JOIN EMPLOYEE E ON P.EMPID = E.EMPID";


$stid = oci_parse($dbconn, $sql);
oci_execute($stid);

// Fetch results into $supervisors array
while (($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) != false) {
    $salary[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kedai Ibu | PartTime earning Query</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    
    <div class="container mt-4">
        <h1 class="mb-5 text-center">List of Part-time Employee Earnings</h1>

        <?php if (count($salary) > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
            <tr>
                <th>EMPLOYEE ID</th>
                <th>EMPLOYEE NAME</th>
                <th>HOURLY RATE (RM)</th>
                <th>HOURS WORKED</th>
                <th>MONTHLY EARNING (RM)</th>
            </tr>
                </thead>

<tbody>
    <?php foreach ($salary as $row): ?>
    <tr>
        <td><?= htmlspecialchars($row['EMPID']) ?></td>
        <td><?= htmlspecialchars($row['EMPLOYEE_NAME']) ?></td>
        <td>RM <?= number_format($row['HOURLYRATE'], 2) ?></td>
        <td><?= htmlspecialchars($row['HOURSWORK']) ?></td>
        <td>RM <?= number_format($row['MONTHLY_EARNING'], 2) ?></td>
    </tr>
    <?php endforeach; ?>
</tbody>

                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">No employee data found.</div>
        <?php endif; ?>
        <a href="../views/dashboard.php" class="btn btn-danger mt-3">&larr; Back to Dashboard</a>
    </div>
</body>
</html>
