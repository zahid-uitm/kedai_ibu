<?php
session_start();
require '../dbconn.php';

if (isset($_GET['catid'])) {
    $catid = $_GET['catid'];

    $query = "DELETE FROM CATEGORY WHERE CATEGORYID = :catid";
    $stid = oci_parse($dbconn, $query);
    oci_bind_by_name($stid, ":catid", $catid);

    oci_execute($stid);
    oci_free_statement($stid);
}

oci_close($dbconn);
header("Location: category.php");
exit;
?>