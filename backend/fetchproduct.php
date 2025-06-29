<?php
session_start();
require '../dbconn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['empid'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

// Fetch categories
$categories = [];
$sqlCat = "SELECT * FROM CATEGORY";
$stmtCat = oci_parse($dbconn, $sqlCat);
oci_execute($stmtCat);
while ($row = oci_fetch_assoc($stmtCat)) {
    $categories[$row['CATEGORYID']] = [
        'id' => $row['CATEGORYID'],
        'name' => $row['CATEGORYNAME'],
        'products' => []
    ];
}

// Fetch products
$sqlProd = "SELECT * FROM PRODUCT";
$stmtProd = oci_parse($dbconn, $sqlProd);
oci_execute($stmtProd);
while ($row = oci_fetch_assoc($stmtProd)) {
    $catId = $row['CATEGORYID'];
    if (isset($categories[$catId])) {
        $categories[$catId]['products'][] = [
            'id' => $row['PRODID'],
            'name' => $row['PRODNAME'],
            'price' => $row['PRODPRICE']
        ];
    }
}

echo json_encode(array_values($categories));
?>