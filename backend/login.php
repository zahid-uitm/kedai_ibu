<?php
session_start();
require '../dbconn.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $empID = trim($_POST['empid']);
    $inputPassword = $_POST['password'];

    $sql = "SELECT PASSWORD FROM EMPLOYEE WHERE EMPID = :empid";
    $stmt = oci_parse($dbconn, $sql);
    oci_bind_by_name($stmt, ":empid", $empID);
    oci_execute($stmt);

    $row = oci_fetch_assoc($stmt);

    if ($row) {
        $storedPassword = $row['PASSWORD'];

        if ($inputPassword === $storedPassword) {
            $_SESSION['empid'] = $empID;
            echo "success login";
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "Employee ID not found";
    }

    oci_free_statement($stmt);
    oci_close($dbconn);
}
?>