<?php
session_start();
require '../dbconn.php';

if (!isset($_GET['empid'])) {
    header("Location: employee.php");
    exit;
}

$empid = $_GET['empid'];

// Fetch existing employee
$query = "SELECT * FROM EMPLOYEE WHERE EMPID = :empid";
$stid = oci_parse($dbconn, $query);
oci_bind_by_name($stid, ":empid", $empid);
oci_execute($stid);
$employee = oci_fetch_assoc($stid);
oci_free_statement($stid);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $phone = $_POST['phone'];
    $hireDate = $_POST['hire_date'];
    $empType = $_POST['emp_type'];
    $managerId = $_POST['manager_id'] ?: null;
    $password = $_POST['password'];

    $query = "UPDATE EMPLOYEE
              SET EMPLOYEEFIRSTNAME = :first, EMPLOYEELASTNAME = :last,
                  EMPPHONENUMBER = :phone, EMPHIREDATE = TO_DATE(:hireDate, 'YYYY-MM-DD'),
                  EMPTYPE = :type, MANAGERID = :manager, PASSWORD = :pwd
              WHERE EMPID = :id";
    $stid = oci_parse($dbconn, $query);
    oci_bind_by_name($stid, ":first", $firstName);
    oci_bind_by_name($stid, ":last", $lastName);
    oci_bind_by_name($stid, ":phone", $phone);
    oci_bind_by_name($stid, ":hireDate", $hireDate);
    oci_bind_by_name($stid, ":type", $empType);
    oci_bind_by_name($stid, ":manager", $managerId);
    oci_bind_by_name($stid, ":pwd", $password);
    oci_bind_by_name($stid, ":id", $empid);

    if (oci_execute($stid)) {
        header("Location: employee.php");
        exit;
    }

    oci_free_statement($stid);
}

oci_close($dbconn);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Kedai Ibu | Update Employee</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="container mt-5">
        <h2>Update Employee Information</h2>
        <form method="POST">
            <div class="mb-3"><label>First Name</label><input type="text" name="first_name" class="form-control"
                    value="<?= $employee['EMPLOYEEFIRSTNAME'] ?>" required></div>
            <div class="mb-3"><label>Last Name</label><input type="text" name="last_name" class="form-control"
                    value="<?= $employee['EMPLOYEELASTNAME'] ?>" required></div>
            <div class="mb-3"><label>Phone</label><input type="text" name="phone" class="form-control"
                    value="<?= $employee['EMPPHONENUMBER'] ?>" required></div>
            <div class="mb-3"><label>Hire Date</label><input type="date" name="hire_date" class="form-control"
                    value="<?= date('Y-m-d', strtotime($employee['EMPHIREDATE'])) ?>" required></div>
            <div class="mb-3"><label>Type</label>
                <select name="emp_type" class="form-control">
                    <option <?= $employee['EMPTYPE'] == 'FullTime' ? 'selected' : '' ?>>FullTime</option>
                    <option <?= $employee['EMPTYPE'] == 'PartTime' ? 'selected' : '' ?>>PartTime</option>
                </select>
            </div>
            <div class="mb-3"><label>Manager ID</label><input type="text" name="manager_id" class="form-control"
                    value="<?= $employee['MANAGERID'] ?>"></div>
            <div class="mb-3"><label>Password</label><input type="text" name="password" class="form-control"
                    value="<?= $employee['PASSWORD'] ?>"></div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
</body>

</html>