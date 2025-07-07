<?php
session_start();
require '../dbconn.php';

if (isset($_GET['prodid'])) {
    $prodid = $_GET['prodid'];
    $categoryID = $_GET['categoryID'];

    // $query = "DELETE FROM PRODUCT WHERE PRODID = :prodid";
    // $stid = oci_parse($dbconn, $query);
    // oci_bind_by_name($stid, ":prodid", $prodid);

    $query = "UPDATE PRODUCT SET IS_ACTIVE = 'N' WHERE PRODID = :prodid";
    $stid = oci_parse($dbconn, $query);
    oci_bind_by_name($stid, ":prodid", $prodid);


    oci_execute($stid);
    oci_free_statement($stid);
}

oci_close($dbconn);
header("Location: ../master_detail_forms/category_product/category_detail.php?categoryID=$categoryID");
exit;
?>