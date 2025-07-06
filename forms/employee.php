<?php
session_start();
require '../dbconn.php';

if (!isset($_SESSION['empid'])) {
    header('Location: ../login.php');
    exit;
}

// Fetch employees
$employees = [];
$query = "SELECT * FROM EMPLOYEE";
$stid = oci_parse($dbconn, $query);
oci_execute($stid);

while ($row = oci_fetch_assoc($stid)) {
    $employees[] = $row;
}

oci_free_statement($stid);
oci_close($dbconn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Kedai Ibu | Employee Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <style>
        .sidebar {
            background-color: #2d3e50;
            color: white;
            width: 250px;
        }

        .sidebar .nav-link {
            color: #cfd8dc;
        }

        .sidebar .nav-link.active {
            background-color: #1a252f;
            color: #fff;
        }
    </style>
</head>

<body>
    <div class="d-flex min-vh-100">
        <div class="sidebar p-3">
            <h4 class="mb-4 fs-5"><i class="fa fa-shop"></i> Kedai Ibu</h4>
            <nav class="nav flex-column">
                <!-- Home -->
                <a class="nav-link mb-2" href="../views/dashboard.php">
                    <i class="fa fa-home me-2"></i> Home
                </a>

                <!-- Master Form and Subform -->
                <a class="nav-link mb-2" data-bs-toggle="collapse" href="#masterDetailFormsMenu" role="button"
                    aria-expanded="false" aria-controls="masterDetailFormsMenu">
                    <i class="fa fa-file-alt me-2"></i> Master Detail Forms <i class="fa fa-caret-down float-end"></i>
                </a>
                <div class="collapse ps-3 mb-2" id="masterDetailFormsMenu">
                    <a class="nav-link small" href="#"><i class="fa fa-shopping-cart me-2"></i> Order Master Form</a>
                    <a class="nav-link small" href="#"><i class="fa fa-box-open me-2"></i> Product Master Form</a>
                </div>

                <!-- Forms with accordion -->
                <a class="nav-link mb-2" data-bs-toggle="collapse" href="#formsMenu" role="button" aria-expanded="false"
                    aria-controls="formsMenu">
                    <i class="fa fa-file-alt me-2"></i> Forms <i class="fa fa-caret-down float-end"></i>
                </a>
                <div class="collapse ps-3 mb-2" id="formsMenu">
                    <a class="nav-link small" href="../forms/employee.php"><i class="fa fa-user me-2"></i> Employee
                        Form</a>
                    <a class="nav-link small" href="../forms/fulltime.php"><i class="fa fa-user-tie me-2"></i> Full-Time
                        Employee
                        Form</a>
                    <a class="nav-link small" href="../forms/parttime.php"><i class="fa fa-user-clock me-2"></i>
                        Part-Time
                        Employee Form</a>
                    <a class="nav-link small" href="../forms/category.php"><i class="fa fa-tags me-2"></i> Category
                        Form</a>
                    <a class="nav-link small" href="../forms/product.php"><i class="fa fa-box-open me-2"></i> Product
                        Form</a>
                    <a class="nav-link small" href="../forms/order.php"><i class="fa fa-shopping-cart me-2"></i> Order
                        Form</a>
                    <a class="nav-link small" href="../forms/invoice.php"><i class="fa fa-file-invoice-dollar me-2"></i>
                        Invoice
                        Form</a>
                </div>

                <!-- Query Reports -->
                <a class="nav-link mb-2" data-bs-toggle="collapse" href="#queryReportsMenu" role="button"
                    aria-expanded="false" aria-controls="queryReportsMenu">
                    <i class="fa fa-search me-2"></i> Query Reports <i class="fa fa-caret-down float-end ms-2"></i>
                </a>
                <div class="collapse ps-3 mb-2" id="queryReportsMenu">
                    <a class="nav-link small" href="#"><i class="fa fa-chart-bar me-2"></i> List of Employees and their
                        Supervisors</a>
                    <a class="nav-link small" href="#"><i class="fa fa-boxes-stacked me-2"></i> Query Report 2</a>
                </div>

                <!-- Reports -->
                <a class="nav-link mb-2" data-bs-toggle="collapse" href="#reportsMenu" role="button"
                    aria-expanded="false" aria-controls="reportsMenu">
                    <i class="fa fa-chart-line me-2"></i> Reports <i class="fa fa-caret-down float-end"></i>
                </a>
                <div class="collapse ps-3 mb-2" id="reportsMenu">
                    <a class="nav-link small" href="../reports/employee_report.php"><i
                            class="fa fa-user-check me-2"></i>Employee
                        Report</a>
                    <a class="nav-link small" href="../reports/fulltime_report.php"><i class="fa fa-user-tie me-2"></i>
                        Full
                        Time
                        Report</a>
                    <a class="nav-link small" href="#"><i class="fa fa-user-clock me-2"></i> Part Time Report</a>
                    <a class="nav-link small" href="#"><i class="fa fa-tags me-2"></i> Category Report</a>
                    <a class="nav-link small" href="#"><i class="fa fa-box-open me-2"></i> Product Report</a>
                    <a class="nav-link small" href="#"><i class="fa fa-boxes-stacked me-2"></i> Orders Report</a>
                    <a class="nav-link small" href="#"><i class="fa fa-cart-plus me-2"></i> OrderProduct Report</a>
                    <a class="nav-link small" href="#"><i class="fa fa-file-invoice-dollar me-2"></i> Invoice Report</a>
                </div>
            </nav>
        </div>
        <div class="container mt-4">
            <h1 class="mb-4 text-center">Employee Form</h1>
            <div class="d-flex align-items-end mb-3">
                <a href="employee_create2.php" class="btn btn-success">+ Create Employee</a>
            </div>

            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Employee ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Phone Number</th>
                        <th>Hire Date</th>
                        <th>Type</th>
                        <th>Manager ID</th>
                        <th>Password</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employees as $emp): ?>
                        <tr>
                            <td><?= htmlspecialchars($emp['EMPID']) ?></td>
                            <td><?= htmlspecialchars($emp['EMPLOYEEFIRSTNAME']) ?></td>
                            <td><?= htmlspecialchars($emp['EMPLOYEELASTNAME']) ?></td>
                            <td><?= htmlspecialchars($emp['EMPPHONENUMBER']) ?></td>
                            <td><?= htmlspecialchars($emp['EMPHIREDATE']) ?></td>
                            <td><?= htmlspecialchars($emp['EMPTYPE']) ?></td>
                            <td><?= htmlspecialchars($emp['MANAGERID']) ?: '-' ?></td>
                            <td><?= htmlspecialchars($emp['PASSWORD']) ?: '-' ?></td>
                            <td>
                                <a href="employee_update2.php?empid=<?= $emp['EMPID'] ?>"
                                    class="btn btn-sm btn-primary">Edit</a>
                                <a href="employee_delete.php?empid=<?= $emp['EMPID'] ?>" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Are you sure to delete this employee?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>