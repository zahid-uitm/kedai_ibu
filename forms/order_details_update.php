<?php
session_start();
require '../dbconn.php';

if (!isset($_GET['opid']) || !isset($_GET['pid'])) {
    header("Location: order_details.php");
    exit;
}

$orderId = $_GET['opid'];
$productId = $_GET['pid'];

// Fetch existing order product
$query = "SELECT * FROM ORDERPRODUCT WHERE ORDERID = :orderid AND PRODUCTID = :productid";
$stid = oci_parse($dbconn, $query);
oci_bind_by_name($stid, ":orderid", $orderId);
oci_bind_by_name($stid, ":productid", $productId);
oci_execute($stid);
$op = oci_fetch_assoc($stid);
oci_free_statement($stid);

if (!$op) {
    echo "Order item not found.";
    exit;
}

// Fetch product price (assumes there's a PRODUCT table)
$productQuery = "SELECT PRODPRICE FROM PRODUCT WHERE PRODID = :productid";
$prodStmt = oci_parse($dbconn, $productQuery);
oci_bind_by_name($prodStmt, ":productid", $productId);
oci_execute($prodStmt);
$product = oci_fetch_assoc($prodStmt);
oci_free_statement($prodStmt);

$price = $product['PRODPRICE'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantity = $_POST['quantity'];
    $amount = $price * $quantity;

    // Update ORDERPRODUCT with new quantity and amount
    $query = "UPDATE ORDERPRODUCT
              SET QUANTITY = :quantity, AMOUNT = :amount
              WHERE ORDERID = :orderid AND PRODUCTID = :productid";

    $stid = oci_parse($dbconn, $query);
    oci_bind_by_name($stid, ":quantity", $quantity);
    oci_bind_by_name($stid, ":amount", $amount);
    oci_bind_by_name($stid, ":orderid", $orderId);
    oci_bind_by_name($stid, ":productid", $productId);

    if (!oci_execute($stid)) {
        oci_free_statement($stid);
        oci_close($dbconn);
        echo "Failed to update order product.";
        exit;
    }

    oci_free_statement($stid);

    // Recalculate total amount for the order
    $sumQuery = "SELECT SUM(AMOUNT) AS TOTAL FROM ORDERPRODUCT WHERE ORDERID = :orderid";
    $sumStmt = oci_parse($dbconn, $sumQuery);
    oci_bind_by_name($sumStmt, ":orderid", $orderId);
    oci_execute($sumStmt);
    $row = oci_fetch_assoc($sumStmt);
    $newTotal = $row['TOTAL'];
    oci_free_statement($sumStmt);

    // Update INVOICE table
    $updateInvoice = "UPDATE INVOICE SET INVOICETOTALAMOUNT = :newtotal WHERE ORDERID = :orderid";
    $invStmt = oci_parse($dbconn, $updateInvoice);
    oci_bind_by_name($invStmt, ":newtotal", $newTotal);
    oci_bind_by_name($invStmt, ":orderid", $orderId);
    oci_execute($invStmt);
    oci_free_statement($invStmt);

    oci_close($dbconn);
    header("Location: order_details.php");
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Kedai Ibu | Update Order Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="container mt-5">
        <h2>Update Order Details Information</h2>
        <form method="POST">
            <div class="mb-3">
                <label for="quantity" class="form-label">Quantity</label>
                <input type="number" class="form-control" id="quantity" name="quantity"
                    value="<?= htmlspecialchars($op['QUANTITY']) ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
</body>

</html>