<?php
session_start();
require '../dbconn.php';

if (!isset($_SESSION['empid'])) {
    header('Location: ../login.php');
    exit;
}

// Fetch invoice data
$invoice = [];
$query = "SELECT * FROM INVOICE";
$stid = oci_parse($dbconn, $query);
oci_execute($stid);

while ($row = oci_fetch_assoc($stid)) {
    $invoice[] = $row;
}

oci_free_statement($stid);
oci_close($dbconn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Kedai Ibu | Invoice Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-4">
        <h1 class="mb-4 text-center"> Invoice Form</h1>

        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Invoice ID</th>
                    <th>Payment Method</th>
                    <th>Total Amount</th>
                    <th>Invoice Date</th>
                    <th>Order ID</th>
                    <th>Assign Employee ID</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($invoice as $inv): ?>
                    <tr>
                        <td><?= htmlspecialchars($inv['INVOICEID']) ?></td>
                        <td><?= htmlspecialchars($inv['PAYMENTMETHOD']) ?></td>
                        <td>RM <?= number_format($inv['INVOICETOTALAMOUNT'], 2) ?></td>
                        <td><?= htmlspecialchars($inv['INVOICEDATE']) ?></td>
                        <td><?= htmlspecialchars($inv['ORDERID']) ?></td>
                        <td><?= htmlspecialchars($inv['EMPID']) ?></td>
                        <td>
                            <a href="invoice_update.php?invid=<?= $inv['INVOICEID'] ?>"
                                class="btn btn-sm btn-primary">Edit</a>
                            <a href="invoice_delete.php?invid=<?= $inv['INVOICEID'] ?>" class="btn btn-sm btn-danger"
                                onclick="return confirm('Are you sure to delete this invoice?');">Delete</a>
                            <a href="invoice_generate.php?invid=<?= $inv['INVOICEID'] ?>"
                                class="btn btn-sm btn-success">Print</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>