<?php
session_start();
require '../dbconn.php';

if (!isset($_GET['invid'])) {
    header("Location: invoice.php");
    exit;
}

$invid = $_GET['invid'];

// Fetch existing invoice
$query = "SELECT * FROM INVOICE WHERE INVOICEID = :invid";
$stid = oci_parse($dbconn, $query);
oci_bind_by_name($stid, ":invid", $invid);
oci_execute($stid);
$invoice = oci_fetch_assoc($stid);
oci_free_statement($stid);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paymentMethod = $_POST['PAYMENTMETHOD'];
    $invoiceDate = $_POST['INVOICEDATE'];
    $empId = $_POST['EMPID'];

    $query = "UPDATE INVOICE
              SET PAYMENTMETHOD = :paymentMethod,
                  INVOICEDATE = :invoiceDate,
                  EMPID = :empId
              WHERE INVOICEID = :invid";
    $stid = oci_parse($dbconn, $query);
    oci_bind_by_name($stid, ":paymentMethod", $paymentMethod);
    oci_bind_by_name($stid, ":invoiceDate", $invoiceDate);
    oci_bind_by_name($stid, ":empId", $empId);
    oci_bind_by_name($stid, ":invid", $invid);

    if (oci_execute($stid)) {
        header("Location: invoice.php");
        exit;
    }

    oci_free_statement($stid);
}

oci_close($dbconn);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Kedai Ibu | Update Invoice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="container mt-5">
        <h2>Update Invoice Information</h2>
        <form method="POST">
            <div class="mb-3">
                <label for="paymentMethod" class="form-label mb-1 me-2">Payment Method:</label>
                <select id="paymentMethod" class="form-select form-select-sm w-auto" name="PAYMENTMETHOD">
                    <option value="Cash" <?= $invoice['PAYMENTMETHOD'] === 'Cash' ? 'selected' : '' ?>>Cash</option>
                    <option value="Online Transfer" <?= $invoice['PAYMENTMETHOD'] === 'Online Transfer' ? 'selected' : '' ?>>Online Transfer</option>
                </select>
            </div>
            <div class="mb-3"><label>Invoice Date</label><input type="text" name="INVOICEDATE" class="form-control"
                    value="<?= $invoice['INVOICEDATE'] ?>" required></div>
            <div class="mb-3"><label>Employee ID</label><input type="text" name="EMPID" class="form-control"
                    value="<?= $invoice['EMPID'] ?>" required></div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
</body>

</html>