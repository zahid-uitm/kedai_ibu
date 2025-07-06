<?php
session_start();
require '../dbconn.php';

if (!isset($_GET['empid'])) {
    header("Location: employee.php");
    exit;
}

$empid = $_GET['empid'];

// Fetch existing fulltime employee
$query = "SELECT * FROM FULLTIME WHERE EMPID = :empid";
$stid = oci_parse($dbconn, $query);
oci_bind_by_name($stid, ":empid", $empid);
oci_execute($stid);
$fulltime = oci_fetch_assoc($stid);
oci_free_statement($stid);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $salary = $_POST['SALARY'];

    $query = "UPDATE FULLTIME
              SET SALARY = :salary
              WHERE EMPID = :id";
    $stid = oci_parse($dbconn, $query);
    oci_bind_by_name($stid, ":salary", $salary);
    oci_bind_by_name($stid, ":id", $empid);

    if (oci_execute($stid)) {
        header("Location: fulltime.php");
        exit;
    }

    oci_free_statement($stid);
}

oci_close($dbconn);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Kedai Ibu | Update Fulltime Employee</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="container mt-5">
        <h2>Update Fulltime Employee Information</h2>
        <form method="POST">
            <div class="mb-3"><label>Salary (min RM 1500.00)</label><input type="text" name="SALARY" class="form-control"
                    value="<?= $fulltime['SALARY'] ?>" required></div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
</body>

</html>