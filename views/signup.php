<?php
session_start();
require '../dbconn.php';

$empid = $_POST['empid'] ?? '';
$password = $_POST['password'] ?? '';

if (!$empid || !$password) {
    echo "Missing EmpID or password.";
    exit;
}

// Check if EmpID exists
$sql = "SELECT Password FROM EMPLOYEE WHERE EmpID = :empid";
$stid = oci_parse($dbconn, $sql);
oci_bind_by_name($stid, ":empid", $empid);
oci_execute($stid);

if (!($row = oci_fetch_assoc($stid))) {
    echo "EmpID does not exist.";
    exit;
}

// Check if password is already set
if (!empty($row['PASSWORD'])) {
    echo "Account already signed up.";
    exit;
}

// Hash the password
$hashed = password_hash($password, PASSWORD_DEFAULT);

// Update the EMPLOYEE table
$update = "UPDATE EMPLOYEE SET Password = :password WHERE EmpID = :empid";
$stid2 = oci_parse($dbconn, $update);
oci_bind_by_name($stid2, ":password", $hashed);
oci_bind_by_name($stid2, ":empid", $empid);

if (oci_execute($stid2)) {
    echo "success";
} else {
    echo "Error saving password.";
}
?>
