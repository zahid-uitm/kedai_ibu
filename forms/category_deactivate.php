<?php
session_start();
require '../dbconn.php';

if (isset($_GET['catid'])) {
    $catid = $_GET['catid'];

    // Deactivate the category
    $query1 = "UPDATE CATEGORY SET IS_ACTIVE = 'N' WHERE CATEGORYID = :catid";
    $stid1 = oci_parse($dbconn, $query1);
    oci_bind_by_name($stid1, ":catid", $catid);
    $success1 = oci_execute($stid1);
    oci_free_statement($stid1);

    // If successful, deactivate all products in that category
    if ($success1) {
        $query2 = "UPDATE PRODUCT SET IS_ACTIVE = 'N' WHERE CATEGORYID = :catid";
        $stid2 = oci_parse($dbconn, $query2);
        oci_bind_by_name($stid2, ":catid", $catid);
        oci_execute($stid2);
        oci_free_statement($stid2);
    }
}

oci_close($dbconn);
header("Location: ../master_detail_forms/category_product/category_product.php");
exit;
?>