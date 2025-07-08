<?php
session_start();
require '../dbconn.php';

if (!isset($_SESSION['empid'])) {
  header('Location: ../login.php');
  exit;
}

$EmployeeName = '';
$EmployeeID = $_SESSION['empid'];

$sql = "SELECT EMPLOYEEFIRSTNAME || ' ' || EMPLOYEELASTNAME AS FULLNAME FROM EMPLOYEE WHERE EMPID = :empid";
$stid = oci_parse($dbconn, $sql);
oci_bind_by_name($stid, ":empid", $EmployeeID);
oci_execute($stid);
if ($row = oci_fetch_assoc($stid)) {
  $EmployeeName = $row['FULLNAME'];
}
oci_free_statement($stid);

$labels = [];
$data = [];

$TotalRevenue = 0;
$sqlRevenue = "SELECT SUM(InvoiceTotalAmount) AS TOTAL FROM INVOICE";
$stid = oci_parse($dbconn, $sqlRevenue);
oci_execute($stid);
if ($row = oci_fetch_assoc($stid)) {
  $TotalRevenue = $row['TOTAL'] ?? 0;
}
oci_free_statement($stid);

$monthLabels = [];
$monthlySales = [];
$sql = "SELECT TO_CHAR(INVOICEDATE, 'YYYY-MM') AS SALE_MONTH, SUM(INVOICETOTALAMOUNT) AS TOTAL_SALES FROM INVOICE GROUP BY TO_CHAR(INVOICEDATE, 'YYYY-MM') ORDER BY SALE_MONTH";
$stid = oci_parse($dbconn, $sql);
oci_execute($stid);
while ($row = oci_fetch_assoc($stid)) {
  $monthLabels[] = $row['SALE_MONTH'];
  $monthlySales[] = round($row['TOTAL_SALES'], 2);
}
oci_free_statement($stid);

$empLabels = [];
$empSales = [];

$sql = "SELECT 
            E.EMPLOYEEFIRSTNAME || ' ' || E.EMPLOYEELASTNAME AS EMPLOYEENAME,
            SUM(I.INVOICETOTALAMOUNT) AS TOTAL_SALES
        FROM EMPLOYEE E
        JOIN INVOICE I ON E.EMPID = I.EMPID
        GROUP BY E.EMPLOYEEFIRSTNAME, E.EMPLOYEELASTNAME
        ORDER BY TOTAL_SALES DESC";
$stid = oci_parse($dbconn, $sql);
oci_execute($stid);
while ($row = oci_fetch_assoc($stid)) {
  $empLabels[] = $row['EMPLOYEENAME'];
  $empSales[] = round($row['TOTAL_SALES'], 2);
}
oci_free_statement($stid);

$categoryLabels = [];
$categorySales = [];

$sql = "SELECT 
            C.CATEGORYNAME,
            SUM(OP.AMOUNT) AS TOTAL_CATEGORY_SALES
        FROM ORDERPRODUCT OP
        JOIN PRODUCT P ON OP.PRODUCTID = P.PRODID
        JOIN CATEGORY C ON P.CATEGORYID = C.CATEGORYID
        GROUP BY C.CATEGORYNAME
        ORDER BY TOTAL_CATEGORY_SALES DESC";

$stid = oci_parse($dbconn, $sql);
oci_execute($stid);

while ($row = oci_fetch_assoc($stid)) {
  $categoryLabels[] = $row['CATEGORYNAME'];
  $categorySales[] = round($row['TOTAL_CATEGORY_SALES'], 2);
}
oci_free_statement($stid);

$dailyLabels = [];
$dailySales = [];

$sql = "SELECT TO_CHAR(INVOICEDATE, 'YYYY-MM-DD') AS SALE_DATE, SUM(INVOICETOTALAMOUNT) AS TOTAL_SALES
        FROM INVOICE
        GROUP BY TO_CHAR(INVOICEDATE, 'YYYY-MM-DD')
        ORDER BY SALE_DATE";
$stid = oci_parse($dbconn, $sql);
oci_execute($stid);
while ($row = oci_fetch_assoc($stid)) {
  $dailyLabels[] = $row['SALE_DATE'];
  $dailySales[] = round($row['TOTAL_SALES'], 2);
}
oci_free_statement($stid);

oci_close($dbconn);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Kedai Ibu | Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
      background-color: #f4f6f9;
    }

    .sidebar {
      background-color: #2d3e50;
      color: white;
      width: 250px;
      flex-shrink: 0;
    }

    .sidebar .nav-link {
      color: #cfd8dc;
    }

    .sidebar .nav-link.active {
      background-color: #1a252f;
      color: #fff;
    }

    .header {
      background-color: #e74c3c;
      padding: 10px 20px;
      color: white;
    }

    .card-summary {
      border-radius: 10px;
      padding: 20px;
    }

    .card-summary.green {
      background-color: #2ecc71;
      color: white;
    }

    .categorySalesChart {
      margain: left 100px;
    }
  </style>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
  <div class="d-flex min-vh-100">
    <div class="d-flex min-vh-100">
      <!-- Sidebar -->
      <div class="sidebar p-3">
        <h4 class="mb-4 fs-5 d-flex align-items-center">
          <img src="../Logo Kedai Ibu.png" alt="Logo" style="height: 60px; width: auto; margin-right: 10px;">
          <span>Kedai Ibu</span>
        </h4>

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
            <a class="nav-link small" href="../master_detail_forms/order_product/order_product.php"><i
                class="fa fa-shopping-cart me-2"></i> Order Master Form</a>
            <a class="nav-link small" href="../master_detail_forms/category_product/category_product.php"><i
                class="fa fa-box-open me-2"></i> Product Master Form</a>
          </div>

          <!-- Forms with accordion -->
          <a class="nav-link mb-2" data-bs-toggle="collapse" href="#formsMenu" role="button" aria-expanded="false"
            aria-controls="formsMenu">
            <i class="fa fa-file-alt me-2"></i> Forms <i class="fa fa-caret-down float-end"></i>
          </a>
          <div class="collapse ps-3 mb-2" id="formsMenu">
            <a class="nav-link small" href="../forms/employee.php"><i class="fa fa-user me-2"></i> Employee Form</a>
            <a class="nav-link small" href="../forms/fulltime.php"><i class="fa fa-user-tie me-2"></i> Full-Time
              Employee
              Form</a>
            <a class="nav-link small" href="../forms/parttime.php"><i class="fa fa-user-clock me-2"></i> Part-Time
              Employee Form</a>
            <!-- <a class="nav-link small" href="../forms/category.php"><i class="fa fa-tags me-2"></i> Category Form</a>
          <a class="nav-link small" href="../forms/product.php"><i class="fa fa-box-open me-2"></i> Product Form</a>
          <a class="nav-link small" href="../forms/order.php"><i class="fa fa-shopping-cart me-2"></i> Order Form</a> -->
            <a class="nav-link small" href="../forms/invoice.php"><i class="fa fa-file-invoice-dollar me-2"></i> Invoice
              Form</a>
          </div>

          <!-- Query Reports -->
          <a class="nav-link mb-2" data-bs-toggle="collapse" href="#queryReportsMenu" role="button"
            aria-expanded="false" aria-controls="queryReportsMenu">
            <i class="fa fa-search me-2"></i> Query Reports <i class="fa fa-caret-down float-end ms-2"></i>
          </a>
          <div class="collapse ps-3 mb-2" id="queryReportsMenu">
            <a class="nav-link small" href="../query_reports/dailysales_sql.php">
              <i class="fa fa-calendar-day me-2"></i> Daily Sales
            </a>
            <a class="nav-link small" href="../query_reports/montlysales_sql.php">
              <i class="fa fa-calendar-alt me-2"></i> Monthly Sales
            </a>
            <a class="nav-link small" href="../query_reports/fulltimesalary_sql.php">
              <i class="fa fa-money-bill me-2"></i> Fulltime Employee Salary
            </a>
            <a class="nav-link small" href="../query_reports/parttimeearning_sql.php">
              <i class="fa fa-coins me-2"></i> Part-time Employee Earning
            </a>
            <a class="nav-link small" href="../query_reports/empperformence_sql.php">
              <i class="fa fa-chart-line me-2"></i> Employee Performance
            </a>
            <a class="nav-link small" href="../query_reports/paymentmethod_sql.php">
              <i class="fa fa-credit-card me-2"></i> Payment Method
            </a>
            <a class="nav-link small" href="../query_reports/productcategory_sql.php">
              <i class="fa fa-boxes me-2"></i> Product and its Category
            </a>
            <a class="nav-link small" href="../query_reports/productrevenue_sql.php">
              <i class="fa fa-dollar-sign me-2"></i> Product Revenue
            </a>
            <a class="nav-link small" href="../query_reports/showinvoice_sql.php">
              <i class="fa fa-file-invoice me-2"></i> Show Invoice
            </a>
            <a class="nav-link small" href="../query_reports/supervisorsupervisee_sql.php">
              <i class="fa fa-user-friends me-2"></i> Employee and Supervisor
            </a>
          </div>

          <!-- Reports -->
          <a class="nav-link mb-2" data-bs-toggle="collapse" href="#reportsMenu" role="button" aria-expanded="false"
            aria-controls="reportsMenu">
            <i class="fa fa-chart-line me-2"></i> Reports <i class="fa fa-caret-down float-end"></i>
          </a>
          <div class="collapse ps-3 mb-2" id="reportsMenu">
            <a class="nav-link small" href="../reports/employee_report.php"><i
                class="fa fa-user-check me-2"></i>Employee
              Report</a>
            <a class="nav-link small" href="../reports/fulltime_report.php"><i class="fa fa-user-tie me-2"></i>
              Full Time
              Report</a>
            <a class="nav-link small" href="../reports/parttime_report.php"><i class="fa fa-user-clock me-2"></i> Part
              Time Report</a>
            <a class="nav-link small" href="../reports/category_report.php"><i class="fa fa-tags me-2"></i> Category
              Report</a>
            <a class="nav-link small" href="../reports/product_report.php"><i class="fa fa-box-open me-2"></i> Product
              Report</a>
            <a class="nav-link small" href="../reports/orders_report.php"><i class="fa fa-boxes-stacked me-2"></i>
              Orders
              Report</a>
            <a class="nav-link small" href="../reports/orderproduct_report.php"><i class="fa fa-cart-plus me-2"></i>
              OrderProduct Report</a>
            <a class="nav-link small" href="../reports/invoice_report.php"><i
                class="fa fa-file-invoice-dollar me-2"></i>
              Invoice Report</a>
          </div>
        </nav>
      </div>

      <!-- Main Content -->
      <div class="flex-fill">
        <div class="header d-flex justify-content-between align-items-center">
          <h4 class="mb-0">Dashboard</h4>
          <div class="dropdown">
            <div class="d-flex align-items-center dropdown-toggle" role="button" data-bs-toggle="dropdown"
              aria-expanded="false" style="cursor: pointer;">
              <i class="fa fa-user me-2"></i><?= htmlspecialchars($EmployeeName) ?>
            </div>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="../forms/employee_update2.php?empid=<?= $EmployeeID ?>">Profile</a></li>
              <li><a class="dropdown-item" href="../backend/logout.php">Logout</a></li>
            </ul>
          </div>
        </div>
        <div class="row justify-content-center gy-4">
          <!-- Monthly Sales Chart -->
          <div class="col-md-6">
            <div class="card text-center">
              <div class="card-body">
                <h6 class="mb-3">Monthly Sales (Line Chart)</h6>
                <div style="height: 300px;">
                  <canvas id="barMonthlySalesChart"></canvas>
                </div>
              </div>
            </div>
          </div>

          <!-- Employee Performance Chart -->
          <div class="col-md-6">
            <div class="card text-center">
              <div class="card-body">
                <h6 class="mb-3">Employee Performance</h6>
                <div style="height: 300px;">
                  <canvas id="employeePerformanceChart"></canvas>
                </div>
              </div>
            </div>
          </div>

          <!-- Category Sales Chart -->
          <div class="col-md-6">
            <div class="card text-center">
              <div class="card-body">
                <h6 class="mb-3">Sales by Product Category</h6>
                <canvas id="categorySalesChart" class="mx-auto" style="max-width: 400px;"></canvas>
              </div>
            </div>
          </div>

          <!-- Daily Sales Chart -->
          <div class="col-md-6">
            <div class="card text-center">
              <div class="card-body">
                <h6 class="mb-3">Daily Sales (Line Chart)</h6>
                <div style="height: 300px;">
                  <canvas id="dailySalesChart"></canvas>
                </div>
              </div>
            </div>
          </div>
        </div>

        <script>
          const labels = <?= json_encode($labels) ?>;
          const data = <?= json_encode($data) ?>;
          const monthLabels = <?= json_encode($monthLabels) ?>;
          const monthlySales = <?= json_encode($monthlySales) ?>;

          new Chart(document.getElementById('barMonthlySalesChart'), {
            type: 'line', // tukar dari 'bar' ke 'line'
            data: {
              labels: monthLabels,
              datasets: [{
                label: 'Monthly Sales (RM)',
                data: monthlySales,
                borderColor: '#3498db',
                backgroundColor: 'rgba(52, 152, 219, 0.2)', // gunakan transparansi untuk line
                tension: 0.4,
                fill: true
              }]
            },
            options: {
              responsive: true,
              plugins: {
                legend: { position: 'top' },
                title: { display: true, text: 'Monthly Sales (Line Chart)' }
              },
              scales: {
                y: { beginAtZero: true, title: { display: true, text: 'RM' } },
                x: { title: { display: true, text: 'Month' } }
              }
            }
          });

          const empLabels = <?= json_encode($empLabels) ?>;
          const empSales = <?= json_encode($empSales) ?>;

          function dynamicColors(index, border = false) {
            const colors = [
              '#3498db', '#e74c3c', '#2ecc71', '#f1c40f', '#9b59b6',
              '#1abc9c', '#e67e22', '#34495e', '#fd79a8', '#00b894',
              '#6c5ce7', '#d63031', '#fdcb6e', '#0984e3'
            ];
            const color = colors[index % colors.length];
            return border ? color : color + '99';
          }

          const employeePerformanceConfig = {
            type: 'bar',
            data: {
              labels: empLabels,
              datasets: [{
                label: 'Total Sales (RM)',
                data: empSales,
                backgroundColor: empLabels.map((_, i) => dynamicColors(i)),
                borderColor: empLabels.map((_, i) => dynamicColors(i, true)),
                borderWidth: 1
              }]
            },
            options: {
              responsive: true,
              plugins: {
                legend: { position: 'top' },
                title: { display: true, text: 'Employee Performance - Total Sales' }
              },
              scales: {
                y: {
                  beginAtZero: true,
                  title: { display: true, text: 'RM' }
                },
                x: {
                  title: { display: true, text: 'Employee' }
                }
              }
            }
          };

          new Chart(document.getElementById('employeePerformanceChart'), employeePerformanceConfig);

          const categoryLabels = <?= json_encode($categoryLabels) ?>;
          const categorySales = <?= json_encode($categorySales) ?>;

          new Chart(document.getElementById('categorySalesChart'), {
            type: 'pie',
            data: {
              labels: categoryLabels,
              datasets: [{
                label: 'Category Sales (RM)',
                data: categorySales,
                backgroundColor: categoryLabels.map((_, i) => dynamicColors(i)),
                borderColor: categoryLabels.map((_, i) => dynamicColors(i, true)),
                borderWidth: 1
              }]
            },
            options: {
              responsive: true,
              plugins: {
                legend: {
                  position: 'top',
                },
                title: {
                  display: true,
                  text: 'Sales by Product Category (Pie Chart)'
                }
              }
            }
          });

          const dailyLabels = <?= json_encode($dailyLabels) ?>;
          const dailySales = <?= json_encode($dailySales) ?>;

          new Chart(document.getElementById('dailySalesChart'), {
            type: 'line',
            data: {
              labels: dailyLabels,
              datasets: [{
                label: 'Daily Sales (RM)',
                data: dailySales,
                borderColor: '#8e44ad',
                backgroundColor: 'rgba(142, 68, 173, 0.2)',
                tension: 0.4,
                fill: true
              }]
            },
            options: {
              responsive: true,
              plugins: {
                legend: { position: 'top' },
                title: { display: true, text: 'Daily Sales (Line Chart)' }
              },
              scales: {
                y: { beginAtZero: true, title: { display: true, text: 'RM' } },
                x: { title: { display: true, text: 'Date' } }
              }
            }
          });
        </script>
</body>

</html>