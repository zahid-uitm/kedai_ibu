<?php
session_start();
require '../dbconn.php';
if (!isset($_SESSION['empid'])) {
    header('Location: ../forms/login.php');
    exit;
}

if($_SERVER["REQUEST_METHOD"] === "POST") {
    $empID = $_SESSION['empid'];
    $orderItems = json_decode($_POST['orderItems'], true);
    $totalAmount = $_POST['totalAmount'];

    // Insert order into database
    $sql = "INSERT INTO ORDERS (EMPID, TOTAL_AMOUNT) VALUES (:empid, :total_amount)";
    $stmt = oci_parse($dbconn, $sql);
    oci_bind_by_name($stmt, ":empid", $empID);
    oci_bind_by_name($stmt, ":total_amount", $totalAmount);
    
    if (oci_execute($stmt)) {
        $orderId = oci_insert_id($stmt);

        // Insert each item into ORDER_ITEMS
        foreach ($orderItems as $item) {
            $sqlItem = "INSERT INTO ORDER_ITEMS (ORDER_ID, PRODUCT_ID, QUANTITY, PRICE) VALUES (:order_id, :product_id, :quantity, :price)";
            $stmtItem = oci_parse($dbconn, $sqlItem);
            oci_bind_by_name($stmtItem, ":order_id", $orderId);
            oci_bind_by_name($stmtItem, ":product_id", $item['id']);
            oci_bind_by_name($stmtItem, ":quantity", $item['qty']);
            oci_bind_by_name($stmtItem, ":price", $item['price']);

            oci_execute($stmtItem);
            oci_free_statement($stmtItem);
        }

        echo json_encode(['status' => 'success', 'message' => 'Order placed successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to place order.']);
    }

    oci_free_statement($stmt);
    oci_close($dbconn);
}
?>