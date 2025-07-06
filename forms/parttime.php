<?php
session_start();
require '../dbconn.php';

if (!isset($_SESSION['empid'])) {
    header('Location: ../login.php');
    exit;
}

// Fetch part time employees
$parttime = [];
$query = "SELECT * FROM PARTTIME";
$stid = oci_parse($dbconn, $query);
oci_execute($stid);

while ($row = oci_fetch_assoc($stid)) {
    $parttime[] = $row;
}

oci_free_statement($stid);
oci_close($dbconn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Kedai Ibu | Part-Time Employee Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-4">
        <h1 class="mb-5 text-center">Part-Time Employee Form</h1>

        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Employee ID</th>
                    <th>Hourly Rate</th>
                    <th>Hours Worked</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($parttime as $emp): ?>
                    <tr>
                        <td><?= htmlspecialchars($emp['EMPID']) ?></td>
                        <td>RM <?= number_format($emp['HOURLYRATE'], 2) ?></td>
                        <td><?= htmlspecialchars($emp['HOURSWORK']) ?> Hours</td>
                        <td>
                            <a href="parttime_update.php?empid=<?= $emp['EMPID'] ?>" class="btn btn-sm btn-primary">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>