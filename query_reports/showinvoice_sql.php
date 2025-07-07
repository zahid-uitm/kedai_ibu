<?php
session_start();
require '../dbconn.php';

$invoices = [];
$empid_input = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $empid_input = strtoupper(trim($_POST['empid'])); // Get and sanitize input

    if (!empty($empid_input)) {
        $sql = "SELECT 
                    I.INVOICEID,
                    I.INVOICEDATE,
                    I.INVOICETOTALAMOUNT,
                    I.PAYMENTMETHOD,
                    E.EMPID,
                    E.EMPLOYEEFIRSTNAME || ' ' || E.EMPLOYEELASTNAME AS EMPLOYEENAME
                FROM INVOICE I
                JOIN EMPLOYEE E ON I.EMPID = E.EMPID
                WHERE E.EMPID = :empid
                ORDER BY I.INVOICEDATE DESC";

        $stid = oci_parse($dbconn, $sql);
        oci_bind_by_name($stid, ":empid", $empid_input);
        oci_execute($stid);

        while (($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) != false) {
            $invoices[] = $row;
        }

        if (empty($invoices)) {
            $error = "No invoices found for Employee ID '$empid_input'.";
        }
    } else {
        $error = "Please enter a valid Employee ID.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoices by Employee</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h1 class="mb-4 text-center">Find Invoices by Employee ID</h1>

    <form method="post" class="mb-4">
        <div class="input-group mb-3">
            <input type="text" name="empid" class="form-control" placeholder="Enter Employee ID (e.g., 100)" value="<?= htmlspecialchars($empid_input) ?>">
            <button type="submit" class="btn btn-primary">Search</button>
        </div>
    </form>

    <?php if ($error): ?>
        <div class="alert alert-warning"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (count($invoices) > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <?php foreach (array_keys($invoices[0]) as $col): ?>
                            <th><?= htmlspecialchars($col) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoices as $inv): ?>
                        <tr>
                            <?php foreach ($inv as $val): ?>
                                <td><?= htmlspecialchars($val ?? '') ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <a href="../views/dashboard.php" class="btn btn-danger mt-3">&larr; Back to Dashboard</a>
</div>
</body>
</html>
