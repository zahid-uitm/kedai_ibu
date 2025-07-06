<?php
session_start();
require '../dbconn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $phone = $_POST['phone'];
    $hireDate = $_POST['hire_date'];
    $empType = $_POST['emp_type'];
    $managerId = $_POST['manager_id'] ?: null;
    $password = $_POST['password'];

    if (empty($hireDate)) {
        $query = "INSERT INTO EMPLOYEE (EMPID, EMPLOYEEFIRSTNAME, EMPLOYEELASTNAME, EMPPHONENUMBER, EMPHIREDATE, EMPTYPE, MANAGERID, PASSWORD)
                  VALUES (TO_CHAR(EMPLOYEE_SEQ.NEXTVAL), :first, :last, :phone, SYSDATE, :type, :manager, :pwd)";
    } else {
        $query = "INSERT INTO EMPLOYEE (EMPID, EMPLOYEEFIRSTNAME, EMPLOYEELASTNAME, EMPPHONENUMBER, EMPHIREDATE, EMPTYPE, MANAGERID, PASSWORD)
                  VALUES (TO_CHAR(EMPLOYEE_SEQ.NEXTVAL), :first, :last, :phone, TO_DATE(:hireDate, 'YYYY-MM-DD'), :type, :manager, :pwd)";
    }

    $stid = oci_parse($dbconn, $query);
    oci_bind_by_name($stid, ":first", $firstName);
    oci_bind_by_name($stid, ":last", $lastName);
    oci_bind_by_name($stid, ":phone", $phone);
    if (!empty($hireDate)) {
        oci_bind_by_name($stid, ":hireDate", $hireDate);
    }
    oci_bind_by_name($stid, ":type", $empType);
    oci_bind_by_name($stid, ":manager", $managerId);
    oci_bind_by_name($stid, ":pwd", $password);

    if (oci_execute($stid)) {
        header('Location: employee.php');
        exit;
    }

    oci_free_statement($stid);
    oci_close($dbconn);
}
?>



<!DOCTYPE html>
<html>

<head>
    <title>Kedai Ibu | Create Employee</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h2>Create New Employee</h2>
        <form method="POST">
            <div class="mb-3"><label>First Name</label><input type="text" name="first_name" class="form-control"
                    required></div>
            <div class="mb-3"><label>Last Name</label><input type="text" name="last_name" class="form-control" required>
            </div>
            <div class="mb-3"><label>Phone Number</label><input type="text" name="phone" class="form-control" required>
            </div>
            <div class="mb-3"><label>Hire Date</label><input type="date" name="hire_date" class="form-control"
                    value="<?= date('Y-m-d') ?>">
            </div>
            <div class="mb-3"><label>Type</label><select name="emp_type" class="form-control">
                    <option>FullTime</option>
                    <option>PartTime</option>
                </select></div>
            <div class="mb-3"><label>Manager ID</label><input type="text" name="manager_id" class="form-control"></div>
            <div class="mb-3"><label>Password</label><input type="text" name="password" class="form-control"></div>
            <button type="submit" class="btn btn-success">Create</button>
        </form>
    </div>
</body>

</html>