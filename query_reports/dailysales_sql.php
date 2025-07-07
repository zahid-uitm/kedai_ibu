<?php
session_start();
require '../dbconn.php';

if (!isset($_SESSION['empid'])) {
    header('Location: ../login.php');
    exit;
}

$sales = [];
$dates = [];
$selected_date = $_POST['selected_date'] ?? '';

// Step 1: Get all distinct sale dates
$date_sql = "SELECT DISTINCT TO_CHAR(TRUNC(INVOICEDATE), 'YYYY-MM-DD') AS SALE_DATE FROM INVOICE ORDER BY SALE_DATE DESC";
$date_stid = oci_parse($dbconn, $date_sql);
oci_execute($date_stid);
while (($row = oci_fetch_array($date_stid, OCI_ASSOC)) != false) {
    $dates[] = $row['SALE_DATE'];
}

// Step 2: Build the main query
$sql = "SELECT 
            TO_CHAR(TRUNC(INVOICEDATE), 'YYYY-MM-DD') AS SALE_DATE,
            SUM(INVOICETOTALAMOUNT) AS TOTAL_SALES
        FROM INVOICE";

if ($selected_date) {
    $sql .= " WHERE TO_CHAR(TRUNC(INVOICEDATE), 'YYYY-MM-DD') = :selected_date";
}

$sql .= " GROUP BY TO_CHAR(TRUNC(INVOICEDATE), 'YYYY-MM-DD') ORDER BY SALE_DATE DESC";

$stid = oci_parse($dbconn, $sql);

if ($selected_date) {
    oci_bind_by_name($stid, ":selected_date", $selected_date);
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
    <title>Daily Sales Report</title>
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
    <h1 class="mb-4 text-center">Daily Sales Report</h1>

    <!-- Filter Form -->
    <form method="POST" class="row g-3 align-items-center mb-4">
        <div class="col-auto">
            <label for="selected_date" class="col-form-label fw-bold">Select Date:</label>
        </div>
        <div class="col-auto">
            <select name="selected_date" id="selected_date" class="form-select">
                <option value="">-- All Dates --</option>
                <?php foreach ($dates as $date): ?>
                    <option value="<?= $date ?>" <?= ($date == $selected_date) ? 'selected' : '' ?>>
                        <?= $date ?>
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
                        <th>SALE DATE</th>
                        <th>TOTAL SALES (RM)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sales as $row): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($row['SALE_DATE']) ?></strong></td>
                            <td class="text-success fw-bold">RM <?= number_format($row['TOTAL_SALES'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">No sales data found for the selected date.</div>
    <?php endif; ?>

    <a href="../views/dashboard.php" class="btn btn-danger mt-4">&larr; Back to Dashboard</a>
</div>
</body>
</html>
