<?php
session_start();
require '../dbconn.php';

if (isset($_GET['orderid'])) {
    $orderid = $_GET['orderid'];

    $query = "DELETE FROM ORDERS WHERE ORDERID = :orderid";
    $stid = oci_parse($dbconn, $query);
    oci_bind_by_name($stid, ":orderid", $orderid);

    oci_execute($stid);
    oci_free_statement($stid);
}

oci_close($dbconn);
header("Location: order.php");
exit;
?>