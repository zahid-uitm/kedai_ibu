<?php
session_start();
require '../dbconn.php'; 

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $empID = $_POST['empid'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT Password FROM EMPLOYEE WHERE EmpID = ?");
    $stmt->execute([$empID]);

    if ($stmt->rowCount() === 1) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (password_verify($password, $row['Password'])) {
            $_SESSION['empid'] = $empID;
            header("Location: dashboard.php");
            exit;
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "EmpID not found. <a href='signup.php'>Sign up here</a>";
    }
}
?>


