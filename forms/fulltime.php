<?php
session_start();
require '../dbconn.php';

if (!isset($_SESSION['empid'])) {
    header('Location: ../login.php');
    exit;
}

// Fetch fulltime employees
$fulltime = [];
$query = "SELECT * FROM FULLTIME";
$stid = oci_parse($dbconn, $query);
oci_execute($stid);

while ($row = oci_fetch_assoc($stid)) {
    $fulltime[] = $row;
}

oci_free_statement($stid);
oci_close($dbconn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Kedai Ibu | Full-Time Employee Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-4">
        <h1 class="mb-5 text-center">Full-Time Employee Form</h1>

        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Employee ID</th>
                    <th>Salary</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($fulltime as $emp): ?>
                    <tr>
                        <td><?= htmlspecialchars($emp['EMPID']) ?></td>
                        <td>RM <?= number_format($emp['SALARY'], 2) ?></td>
                        <td>
                            <a href="fulltime_update.php?empid=<?= $emp['EMPID'] ?>" class="btn btn-sm btn-primary">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>