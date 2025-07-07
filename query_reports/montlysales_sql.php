<?php
session_start();
require '../dbconn.php';

if (!isset($_SESSION['empid'])) {
    header('Location: ../login.php');
    exit;
}

$sales = [];
$months = [];
$selected_month = $_POST['selected_month'] ?? '';

// Step 1: Get all available months
$month_sql = "SELECT DISTINCT TO_CHAR(INVOICEDATE, 'YYYY-MM') AS SALE_MONTH FROM INVOICE ORDER BY SALE_MONTH DESC";
$month_stid = oci_parse($dbconn, $month_sql);
oci_execute($month_stid);
while (($row = oci_fetch_array($month_stid, OCI_ASSOC)) != false) {
    $months[] = $row['SALE_MONTH'];
}

// Step 2: Prepare the main query
$sql = "SELECT 
            TO_CHAR(INVOICEDATE, 'YYYY-MM') AS SALE_MONTH,
            SUM(INVOICETOTALAMOUNT) AS TOTAL_SALES
        FROM INVOICE";

if ($selected_month) {
    $sql .= " WHERE TO_CHAR(INVOICEDATE, 'YYYY-MM') = :selected_month";
}

$sql .= " GROUP BY TO_CHAR(INVOICEDATE, 'YYYY-MM') ORDER BY SALE_MONTH DESC";

$stid = oci_parse($dbconn, $sql);

if ($selected_month) {
    oci_bind_by_name($stid, ":selected_month", $selected_month);
}

oci_execute($stid);

while (($row = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) != false) {
    $sales[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Monthly Sales Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        h1 {
            font-weight: bold;
            color:rgb(0, 0, 0);
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <h1 class="mb-4 text-center">Monthly Sales Report</h1>

    <!-- Filter Form -->
    <form method="POST" class="row g-3 align-items-center mb-4">
        <div class="col-auto">
            <label for="selected_month" class="col-form-label fw-bold">Select Month:</label>
        </div>
        <div class="col-auto">
            <select name="selected_month" id="selected_month" class="form-select">
                <option value="">-- All Months --</option>
                <?php foreach ($months as $month): ?>
                    <option value="<?= $month ?>" <?= ($month == $selected_month) ? 'selected' : '' ?>>
                        <?= $month ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">Filter</button>
        </div>
    </form>

    <!-- Sales Table -->
    <?php if (count($sales) > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle text-center">
                <thead class="table-dark">
                    <tr>
                        <th>SALE MONTH</th>
                        <th>TOTAL SALES (RM)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sales as $row): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($row['SALE_MONTH']) ?></strong></td>
                            <td class="text-success fw-bold">RM <?= number_format($row['TOTAL_SALES'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">No sales data found for the selected month.</div>
    <?php endif; ?>

    <a href="../views/dashboard.php" class="btn btn-danger mt-4">&larr; Back to Dashboard</a>
</div>
</body>
</html>
