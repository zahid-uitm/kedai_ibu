<?php
session_start();
require '../dbconn.php';

if (!isset($_GET['empid'])) {
    header("Location: employee.php");
    exit;
}

$empid = $_GET['empid'];

// Fetch employee
$query = "SELECT * FROM EMPLOYEE WHERE EMPID = :empid";
$stid = oci_parse($dbconn, $query);
oci_bind_by_name($stid, ":empid", $empid);
oci_execute($stid);
$employee = oci_fetch_assoc($stid);
oci_free_statement($stid);

// Fetch subclass data
$fullTimeData = null;
$partTimeData = null;

if ($employee['EMPTYPE'] === 'FullTime') {
    $ftQuery = "SELECT * FROM FULLTIME WHERE EMPID = :empid";
    $ftStid = oci_parse($dbconn, $ftQuery);
    oci_bind_by_name($ftStid, ":empid", $empid);
    oci_execute($ftStid);
    $fullTimeData = oci_fetch_assoc($ftStid);
    oci_free_statement($ftStid);
} elseif ($employee['EMPTYPE'] === 'PartTime') {
    $ptQuery = "SELECT * FROM PARTTIME WHERE EMPID = :empid";
    $ptStid = oci_parse($dbconn, $ptQuery);
    oci_bind_by_name($ptStid, ":empid", $empid);
    oci_execute($ptStid);
    $partTimeData = oci_fetch_assoc($ptStid);
    oci_free_statement($ptStid);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $phone = $_POST['phone'];
    $hireDate = $_POST['hire_date'];
    $empType = $_POST['emp_type'];
    $managerId = $_POST['manager_id'] ?: null;
    $password = $_POST['password'];

    // Update EMPLOYEE table
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
    oci_execute($stid);
    oci_free_statement($stid);

    // Detect type change
    $prevType = $employee['EMPTYPE'];
    if ($prevType !== $empType) {
        $delQuery = $prevType === 'FullTime' ? "DELETE FROM FULLTIME WHERE EMPID = :id" : "DELETE FROM PARTTIME WHERE EMPID = :id";
        $delStid = oci_parse($dbconn, $delQuery);
        oci_bind_by_name($delStid, ":id", $empid);
        oci_execute($delStid);
        oci_free_statement($delStid);
    }

    // Handle subclass data
    if ($empType === 'FullTime') {
        $salary = $_POST['salary'];
        $checkQuery = "SELECT COUNT(*) AS CNT FROM FULLTIME WHERE EMPID = :id";
        $checkStid = oci_parse($dbconn, $checkQuery);
        oci_bind_by_name($checkStid, ":id", $empid);
        oci_execute($checkStid);
        $exists = oci_fetch_assoc($checkStid)['CNT'] > 0;
        oci_free_statement($checkStid);

        if ($exists) {
            $stmt = oci_parse($dbconn, "UPDATE FULLTIME SET SALARY = :salary WHERE EMPID = :id");
        } else {
            $stmt = oci_parse($dbconn, "INSERT INTO FULLTIME (EMPID, SALARY) VALUES (:id, :salary)");
        }
        oci_bind_by_name($stmt, ":id", $empid);
        oci_bind_by_name($stmt, ":salary", $salary);
        oci_execute($stmt);
        oci_free_statement($stmt);

    } elseif ($empType === 'PartTime') {
        $hourlyRate = $_POST['hourly_rate'];
        $hoursWorked = $_POST['hours_worked'];
        $checkQuery = "SELECT COUNT(*) AS CNT FROM PARTTIME WHERE EMPID = :id";
        $checkStid = oci_parse($dbconn, $checkQuery);
        oci_bind_by_name($checkStid, ":id", $empid);
        oci_execute($checkStid);
        $exists = oci_fetch_assoc($checkStid)['CNT'] > 0;
        oci_free_statement($checkStid);

        if ($exists) {
            $stmt = oci_parse($dbconn, "UPDATE PARTTIME SET HOURLYRATE = :rate, HOURSWORK = :hours WHERE EMPID = :id");
        } else {
            $stmt = oci_parse($dbconn, "INSERT INTO PARTTIME (EMPID, HOURLYRATE, HOURSWORK) VALUES (:id, :rate, :hours)");
        }
        oci_bind_by_name($stmt, ":id", $empid);
        oci_bind_by_name($stmt, ":rate", $hourlyRate);
        oci_bind_by_name($stmt, ":hours", $hoursWorked);
        oci_execute($stmt);
        oci_free_statement($stmt);
    }

    oci_close($dbconn);
    header("Location: employee.php");
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Kedai Ibu | Update Employee</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h2>Update Employee Information</h2>
        <form method="POST">
            <div class="mb-3">
                <label>First Name</label>
                <input type="text" name="first_name" class="form-control" value="<?= $employee['EMPLOYEEFIRSTNAME'] ?>"
                    required>
            </div>
            <div class="mb-3">
                <label>Last Name</label>
                <input type="text" name="last_name" class="form-control" value="<?= $employee['EMPLOYEELASTNAME'] ?>"
                    required>
            </div>
            <div class="mb-3">
                <label>Phone</label>
                <input type="text" name="phone" class="form-control" value="<?= $employee['EMPPHONENUMBER'] ?>"
                    required>
            </div>
            <div class="mb-3">
                <label>Hire Date</label>
                <input type="date" name="hire_date" class="form-control"
                    value="<?= date('Y-m-d', strtotime($employee['EMPHIREDATE'])) ?>" required>
            </div>

            <div class="mb-3"><label>Employment Type</label><br>
                <div class="form-check form-check-inline mt-2">
                    <input class="form-check-input" type="radio" name="emp_type" value="FullTime" id="fullTimeRadio"
                        <?= $employee['EMPTYPE'] === 'FullTime' ? 'checked' : '' ?> required>
                    <label class="form-check-label" for="fullTimeRadio">Full Time</label>
                </div>
                <div class="form-check form-check-inline mt-2">
                    <input class="form-check-input" type="radio" name="emp_type" value="PartTime" id="partTimeRadio"
                        <?= $employee['EMPTYPE'] === 'PartTime' ? 'checked' : '' ?> required>
                    <label class="form-check-label" for="partTimeRadio">Part Time</label>
                </div>
            </div>

            <!-- FullTime Fields -->
            <div class="mb-3" id="fullTimeFields" style="display:none;">
                <label>Salary (RM) (min RM 1500.00)</label>
                <input type="number" step="0.01" name="salary" class="form-control"
                    value="<?= $fullTimeData['SALARY'] ?? '' ?>">
            </div>

            <!-- PartTime Fields -->
            <div id="partTimeFields" style="display:none;">
                <div class="mb-3">
                    <label>Hourly Rate (RM) (min RM 9.00)</label>
                    <input type="number" step="0.01" name="hourly_rate" class="form-control"
                        value="<?= $partTimeData['HOURLYRATE'] ?? '' ?>">
                </div>
                <div class="mb-3">
                    <label>Hours Worked</label>
                    <input type="number" name="hours_worked" class="form-control"
                        value="<?= $partTimeData['HOURSWORK'] ?? '' ?>">
                </div>
            </div>

            <div class="mb-3">
                <label>Manager ID</label>
                <input type="text" name="manager_id" class="form-control" value="<?= $employee['MANAGERID'] ?>">
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input type="text" name="password" class="form-control" value="<?= $employee['PASSWORD'] ?>">
            </div>

            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>

    <!-- Toggle JS -->
    <script>
        function toggleFields() {
            const fullTime = document.getElementById("fullTimeRadio").checked;
            document.getElementById("fullTimeFields").style.display = fullTime ? "block" : "none";
            document.getElementById("partTimeFields").style.display = !fullTime ? "block" : "none";
        }

        document.addEventListener("DOMContentLoaded", function () {
            toggleFields();
            document.getElementById("fullTimeRadio").addEventListener("change", toggleFields);
            document.getElementById("partTimeRadio").addEventListener("change", toggleFields);
        });
    </script>
</body>

</html>