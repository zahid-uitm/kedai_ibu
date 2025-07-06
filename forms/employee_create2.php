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

    $salary = $_POST['salary'] ?? null;
    $hourlyRate = $_POST['hourly_rate'] ?? null;
    $hoursWorked = $_POST['hours_worked'] ?? null;

    // Step 1: Get new EMPID
    $empIdQuery = "SELECT TO_CHAR(EMPLOYEE_SEQ.NEXTVAL) AS NEW_ID FROM DUAL";
    $stmt = oci_parse($dbconn, $empIdQuery);
    oci_execute($stmt);
    $row = oci_fetch_assoc($stmt);
    $newEmpId = $row['NEW_ID'];
    oci_free_statement($stmt);

    // Step 2: Insert into EMPLOYEE
    $query = "INSERT INTO EMPLOYEE (
                EMPID, EMPLOYEEFIRSTNAME, EMPLOYEELASTNAME, EMPPHONENUMBER,
                EMPHIREDATE, EMPTYPE, MANAGERID, PASSWORD
              ) VALUES (
                :empid, :first, :last, :phone,
                " . (empty($hireDate) ? "SYSDATE" : "TO_DATE(:hireDate, 'YYYY-MM-DD')") . ",
                :type, :manager, :pwd
              )";
    $stid = oci_parse($dbconn, $query);
    oci_bind_by_name($stid, ":empid", $newEmpId);
    oci_bind_by_name($stid, ":first", $firstName);
    oci_bind_by_name($stid, ":last", $lastName);
    oci_bind_by_name($stid, ":phone", $phone);
    if (!empty($hireDate)) {
        oci_bind_by_name($stid, ":hireDate", $hireDate);
    }
    oci_bind_by_name($stid, ":type", $empType);
    oci_bind_by_name($stid, ":manager", $managerId);
    oci_bind_by_name($stid, ":pwd", $password);

    if (!oci_execute($stid)) {
        $e = oci_error($stid);
        echo "Failed to insert EMPLOYEE: " . $e['message'];
        exit;
    }
    oci_free_statement($stid);

    // Step 3: Insert into subclass table
    if ($empType === 'FullTime') {
        $queryFT = "INSERT INTO FULLTIME (EMPID, SALARY) VALUES (:empid, :salary)";
        $stidFT = oci_parse($dbconn, $queryFT);
        oci_bind_by_name($stidFT, ":empid", $newEmpId);
        oci_bind_by_name($stidFT, ":salary", $salary);
        if (!oci_execute($stidFT)) {
            $e = oci_error($stidFT);
            echo "Failed to insert FULLTIME: " . $e['message'];
            exit;
        }
        oci_free_statement($stidFT);
    } elseif ($empType === 'PartTime') {
        $queryPT = "INSERT INTO PARTTIME (EMPID, HOURLYRATE, HOURSWORK) VALUES (:empid, :rate, :hours)";
        $stidPT = oci_parse($dbconn, $queryPT);
        oci_bind_by_name($stidPT, ":empid", $newEmpId);
        oci_bind_by_name($stidPT, ":rate", $hourlyRate);
        oci_bind_by_name($stidPT, ":hours", $hoursWorked);
        if (!oci_execute($stidPT)) {
            $e = oci_error($stidPT);
            echo "Failed to insert PARTTIME: " . $e['message'];
            exit;
        }
        oci_free_statement($stidPT);
    }

    oci_close($dbconn);
    header('Location: employee.php');
    exit;
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
            <div class="mb-3"><label>First Name</label>
                <input type="text" name="first_name" class="form-control" required>
            </div>

            <div class="mb-3"><label>Last Name</label>
                <input type="text" name="last_name" class="form-control" required>
            </div>

            <div class="mb-3"><label>Phone Number</label>
                <input type="text" name="phone" class="form-control" required>
            </div>

            <div class="mb-3"><label>Hire Date</label>
                <input type="date" name="hire_date" class="form-control" value="<?= date('Y-m-d') ?>">
            </div>

            <div class="mb-3"><label>Employment Type</label><br>
                <div class="form-check form-check-inline mt-2">
                    <input class="form-check-input" type="radio" name="emp_type" value="FullTime" id="fullTimeRadio"
                        required>
                    <label class="form-check-label" for="fullTimeRadio">Full Time</label>
                </div>
                <div class="form-check form-check-inline mt-2">
                    <input class="form-check-input" type="radio" name="emp_type" value="PartTime" id="partTimeRadio"
                        required>
                    <label class="form-check-label" for="partTimeRadio">Part Time</label>
                </div>
            </div>

            <!-- FullTime Fields -->
            <div class="mb-3" id="fullTimeFields" style="display:none;">
                <label>Salary (RM) (min RM 1500)</label>
                <input type="number" step="0.01" name="salary" class="form-control">
            </div>

            <!-- PartTime Fields -->
            <div id="partTimeFields" style="display:none;">
                <div class="mb-3">
                    <label>Hourly Rate (RM) (min RM 9.00)</label>
                    <input type="number" step="0.01" name="hourly_rate" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Hours Worked</label>
                    <input type="number" name="hours_worked" class="form-control">
                </div>
            </div>

            <div class="mb-3"><label>Manager ID</label>
                <input type="text" name="manager_id" class="form-control">
            </div>

            <div class="mb-3"><label>Password</label>
                <input type="text" name="password" class="form-control">
            </div>

            <button type="submit" class="btn btn-success">Create</button>
        </form>
    </div>

    <!-- JS for toggling form fields -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const fullTimeRadio = document.getElementById('fullTimeRadio');
            const partTimeRadio = document.getElementById('partTimeRadio');
            const fullTimeFields = document.getElementById('fullTimeFields');
            const partTimeFields = document.getElementById('partTimeFields');

            function toggleFields() {
                fullTimeFields.style.display = fullTimeRadio.checked ? 'block' : 'none';
                partTimeFields.style.display = partTimeRadio.checked ? 'block' : 'none';
            }

            fullTimeRadio.addEventListener('change', toggleFields);
            partTimeRadio.addEventListener('change', toggleFields);
        });
    </script>
</body>

</html>