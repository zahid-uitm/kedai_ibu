<?php
session_start();
require '../dbconn.php';

if (isset($_GET['invid'])) {
    $invid = $_GET['invid'];

    $orderQuery = "SELECT ORDERID FROM INVOICE WHERE INVOICEID = :invid";
    $orderStid = oci_parse($dbconn, $orderQuery);
    oci_bind_by_name($orderStid, ":invid", $invid);
    oci_execute($orderStid);
    $orderid = oci_fetch_assoc($orderStid);
    oci_free_statement($orderStid);

    $query = "DELETE FROM ORDERS WHERE ORDERID = :orderid";
    $stid = oci_parse($dbconn, $query);
    oci_bind_by_name($stid, ":orderid", $orderid['ORDERID']);

    oci_execute($stid);
    oci_free_statement($stid);
}

oci_close($dbconn);
header("Location: invoice.php");
exit;
?>