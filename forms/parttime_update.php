<?php
session_start();
require '../dbconn.php';

if (!isset($_GET['empid'])) {
    header("Location: employee.php");
    exit;
}

$empid = $_GET['empid'];

// Fetch existing part time employee
$query = "SELECT * FROM PARTTIME WHERE EMPID = :empid";
$stid = oci_parse($dbconn, $query);
oci_bind_by_name($stid, ":empid", $empid);
oci_execute($stid);
$parttime = oci_fetch_assoc($stid);
oci_free_statement($stid);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hourlyRate = $_POST['HOURLYRATE'];
    $hoursWorked = $_POST['HOURSWORK'];

    $query = "UPDATE PARTTIME
              SET HOURLYRATE = :hourlyRate, HOURSWORK = :hoursWorked
              WHERE EMPID = :id";
    $stid = oci_parse($dbconn, $query);
    oci_bind_by_name($stid, ":hourlyRate", $hourlyRate);
    oci_bind_by_name($stid, ":hoursWorked", $hoursWorked);
    oci_bind_by_name($stid, ":id", $empid);

    if (oci_execute($stid)) {
        header("Location: parttime.php");
        exit;
    }

    oci_free_statement($stid);
}

oci_close($dbconn);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Kedai Ibu | Update Part Time Employee</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="container mt-5">
        <h2>Update Part Time Employee Information</h2>
        <form method="POST">
            <div class="mb-3"><label>Hourly Rate (min RM 9.00)</label><input type="text" name="HOURLYRATE"
                    class="form-control" value="<?= $parttime['HOURLYRATE'] ?>" required></div>
            <div class="mb-3"><label>Hours Worked</label><input type="text" name="HOURSWORK" class="form-control"
                    value="<?= $parttime['HOURSWORK'] ?>" required></div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
</body>

</html>