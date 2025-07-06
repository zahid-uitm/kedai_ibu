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
}

oci_close($dbconn);
header("Location: order_details.php");
exit;
?>