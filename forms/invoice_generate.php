<?php
require '../dbconn.php';
session_start();

if (isset($_GET['orderid'])) {
    $orderId = $_GET['orderid'];

    // Fetch invoice details
    $invoiceQuery = "
    SELECT I.INVOICEID, I.INVOICETOTALAMOUNT, I.INVOICEDATE, I.PAYMENTMETHOD,
        E.EMPLOYEEFIRSTNAME || ' ' || E.EMPLOYEELASTNAME AS EMPNAME, TO_CHAR(O.ORDERDATETIME, 'HH24:MI:SS') AS ORDERTIME
    FROM INVOICE I
    JOIN EMPLOYEE E ON I.EMPID = E.EMPID
    JOIN ORDERS O ON O.ORDERID = I.ORDERID
    WHERE I.ORDERID = :orderid";

    $stid = oci_parse($dbconn, $invoiceQuery);
    oci_bind_by_name($stid, ":orderid", $orderId);
    oci_execute($stid);
    $invoice = oci_fetch_assoc($stid);
    oci_free_statement($stid);

    // Fetch order items
    $itemsQuery = "
    SELECT P.PRODNAME, OP.QUANTITY, OP.AMOUNT
    FROM ORDERPRODUCT OP
    JOIN PRODUCT P ON OP.PRODUCTID = P.PRODID
    WHERE OP.ORDERID = :orderid";

    $stid = oci_parse($dbconn, $itemsQuery);
    oci_bind_by_name($stid, ":orderid", $orderId);
    oci_execute($stid);

    $items = [];
    while ($row = oci_fetch_assoc($stid)) {
        $items[] = $row;
    }
    oci_free_statement($stid);

} else if (isset($_GET['invid'])) {
    $invoiceId = $_GET['invid'];

    // Fetch invoice details
    $invoiceQuery = "
    SELECT I.INVOICEID, I.INVOICETOTALAMOUNT, I.INVOICEDATE, I.PAYMENTMETHOD,
        E.EMPLOYEEFIRSTNAME || ' ' || E.EMPLOYEELASTNAME AS EMPNAME, TO_CHAR(O.ORDERDATETIME, 'HH24:MI:SS') AS ORDERTIME
    FROM INVOICE I
    LEFT JOIN EMPLOYEE E ON I.EMPID = E.EMPID
    JOIN ORDERS O ON O.ORDERID = I.ORDERID
    WHERE I.INVOICEID = :invid";

    $stid = oci_parse($dbconn, $invoiceQuery);
    oci_bind_by_name($stid, ":invid", $invoiceId);
    oci_execute($stid);
    $invoice = oci_fetch_assoc($stid);
    oci_free_statement($stid);

    // Fetch order items
    $itemsQuery = "
    SELECT P.PRODNAME, OP.QUANTITY, OP.AMOUNT
    FROM ORDERPRODUCT OP
    JOIN PRODUCT P ON OP.PRODUCTID = P.PRODID
    WHERE OP.ORDERID = (SELECT ORDERID FROM INVOICE WHERE INVOICEID = :invid)";

    $stid = oci_parse($dbconn, $itemsQuery);
    oci_bind_by_name($stid, ":invid", $invoiceId);
    oci_execute($stid);

    $items = [];
    while ($row = oci_fetch_assoc($stid)) {
        $items[] = $row;
    }
    oci_free_statement($stid);

} else {
    echo "Invalid request.";
    exit;
}

oci_close($dbconn);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Kedai Ibu | Invoice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none;
            }
        }

        body {
            padding: 20px;
        }

        .invoice-box {
            border: 1px solid #ccc;
            padding: 30px;
        }
    </style>
</head>

<body>

    <div class="invoice-box">
        <h2 class="text-center">Kedai Ibu Invoice</h2>
        <p><strong>Invoice ID:</strong> <?= $invoice['INVOICEID'] ?></p>
        <p><strong>Date:</strong> <?= date('Y-m-d', strtotime($invoice['INVOICEDATE'])) ?></p>
        <p><strong>Order Time:</strong> <?= date('  H:i:s', strtotime($invoice['ORDERTIME'])) ?></p>
        <p><strong>Cashier:</strong> <?= htmlspecialchars($invoice['EMPNAME'] ? $invoice['EMPNAME'] : 'TERMINATED') ?></p>
        <p><strong>Payment Method:</strong> <?= htmlspecialchars($invoice['PAYMENTMETHOD']) ?></p>

        <table class="table table-bordered mt-4">
            <thead class="table-light">
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Amount (RM)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['PRODNAME']) ?></td>
                        <td><?= $item['QUANTITY'] ?></td>
                        <td><?= number_format($item['AMOUNT'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" class="text-end"><strong>Total</strong></td>
                    <td><strong>RM <?= number_format($invoice['INVOICETOTALAMOUNT'], 2) ?></strong></td>
                </tr>
            </tfoot>
        </table>

        <button onclick="window.print()" class="btn btn-primary no-print">Print Invoice</button>
        <a href="order.php" class="btn btn-secondary no-print">Back</a>
    </div>

</body>

</html>