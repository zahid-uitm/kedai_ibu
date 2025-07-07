<?php
session_start();
require '../dbconn.php';

if (isset($_GET['opid']) && isset($_GET['pid'])) {
    $orderid = $_GET['opid'];
    $productid = $_GET['pid'];

    $query = "DELETE FROM ORDERPRODUCT WHERE ORDERID = :orderid AND PRODUCTID = :productid";
    $stid = oci_parse($dbconn, $query);
    oci_bind_by_name($stid, ":orderid", $orderid);
    oci_bind_by_name($stid, ":productid", $productid);

    oci_execute($stid);
    oci_free_statement($stid);

    // Recalculate total amount for the order
    $sumQuery = "SELECT SUM(AMOUNT) AS TOTAL FROM ORDERPRODUCT WHERE ORDERID = :orderid";
    $sumStmt = oci_parse($dbconn, $sumQuery);
    oci_bind_by_name($sumStmt, ":orderid", $orderid);
    oci_execute($sumStmt);
    $row = oci_fetch_assoc($sumStmt);
    $total = $row['TOTAL'];
    oci_free_statement($sumStmt);

    // Update the invoice with the new total
    $updateInvoice = "UPDATE INVOICE SET INVOICETOTALAMOUNT = :total WHERE ORDERID = :orderid";
    $invStmt = oci_parse($dbconn, $updateInvoice);
    oci_bind_by_name($invStmt, ":total", $total);
    oci_bind_by_name($invStmt, ":orderid", $orderid);
    oci_execute($invStmt);
}

oci_close($dbconn);
header("Location: order_details.php");
exit;
?>