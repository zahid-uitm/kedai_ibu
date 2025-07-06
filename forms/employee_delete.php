<?php
session_start();
require '../dbconn.php';

if (isset($_GET['empid'])) {
    $empid = $_GET['empid'];

    $query = "DELETE FROM EMPLOYEE WHERE EMPID = :empid";
    $stid = oci_parse($dbconn, $query);
    oci_bind_by_name($stid, ":empid", $empid);

    oci_execute($stid);
    oci_free_statement($stid);
}

oci_close($dbconn);
header("Location: employee.php");
exit;
?>
