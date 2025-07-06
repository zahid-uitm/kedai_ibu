<?php
session_start();
require '../dbconn.php';

if (!isset($_GET['orderid'])) {
    header("Location: order.php");
    exit;
}

$orderid = $_GET['orderid'];

// Fetch existing order
$query = "SELECT ORDERID, TO_CHAR(ORDERDATETIME, 'YYYY-MM-DD HH24:MI:SS') AS ORDERDATETIME FROM ORDERS WHERE ORDERID = :orderid";
$stid = oci_parse($dbconn, $query);
oci_bind_by_name($stid, ":orderid", $orderid);
oci_execute($stid);
$order = oci_fetch_assoc($stid);
oci_free_statement($stid);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderDateTime = $_POST['order_date_time'];  // e.g., "2025-07-05T14:30"

    // Convert to "YYYY-MM-DD HH24:MI:SS" format for Oracle
    $formattedDateTime = date('Y-m-d H:i:s', strtotime($orderDateTime));

    $query = "UPDATE ORDERS
              SET ORDERDATETIME = TO_DATE(:orderDateTime, 'YYYY-MM-DD HH24:MI:SS')
              WHERE ORDERID = :orderid";

    $stid = oci_parse($dbconn, $query);
    oci_bind_by_name($stid, ":orderDateTime", $formattedDateTime);
    oci_bind_by_name($stid, ":orderid", $orderid);

    if (oci_execute($stid)) {
        header("Location: order.php");
        exit;
    }

    oci_free_statement($stid);
}

oci_close($dbconn);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Kedai Ibu | Update Order</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="container mt-5">
        <h2>Update Order Information</h2>
        <form method="POST">
            <div class="mb-3"><label>Order Date and Time</label><input type="datetime-local" name="order_date_time"
                    class="form-control" value="<?= date('Y-m-d\TH:i', strtotime($order['ORDERDATETIME'])) ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
</body>

</html>