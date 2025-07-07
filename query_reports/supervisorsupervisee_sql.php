<?php
session_start();
require '../dbconn.php';

if (!isset($_SESSION['empid'])) {
    header('Location: ../login.php');
    exit;
}

$supervisors = []; // Fixed variable name

$sql = "SELECT 
    M.EMPID AS SupervisorID,
    M.EMPLOYEEFIRSTNAME || ' ' || M.EMPLOYEELASTNAME AS SUPERVISERNAME,
    E.EMPID AS SuperviseeID,
    E.EMPLOYEEFIRSTNAME || ' ' || E.EMPLOYEELASTNAME AS SUPERVISEENAME
FROM EMPLOYEE E 
JOIN EMPLOYEE M ON E.MANAGERID = M.EMPID
ORDER BY M.EMPID";

$stid = oci_parse($dbconn, $sql);
oci_execute($stid);

// Fetch results into $supervisors array
while (($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) != false) {
    $supervisors[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kedai Ibu | Supervisor Supervisee Query</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
   
    <div class="container mt-4">
        <h1 class="mb-5 text-center">List of Supervisors with Their Supervisees</h1>

        <?php if (count($supervisors) > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>SUPERVISOR ID</th>
                            <th>SUPERVISOR NAME</th>
                            <th>SUPERVISEE ID</th>
                            <th>SUPERVISEE NAME</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($supervisors as $sup): ?>
                            <tr>
                                <?php foreach ($sup as $val): ?>
                                    <td><?= htmlspecialchars($val ?? '') ?></td>
                                <?php endforeach; ?>
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
