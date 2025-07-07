<?php
session_start();
require '../dbconn.php';
//get the employee name to be display
$empid = $_SESSION['empid'] ?? null;

$query = "SELECT EMPLOYEEFIRSTNAME || ' ' || EMPLOYEELASTNAME AS FULLNAME FROM EMPLOYEE WHERE EMPID = '$empid'";
$result = oci_parse($dbconn, $query);
oci_execute($result);
$row = oci_fetch_assoc($result);
$fullname = $row['FULLNAME'] ?? 'Guest';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Kedai Ibu | Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
  <style>
    body {
      background-color: #f4f6f9;
    }

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

    .card-summary.purple {
      background-color: #8e44ad;
      color: white;
    }
  </style>
</head>

<body>
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
          <a class="nav-link small" href="../master_detail_forms/order_product/order_product.php"><i class="fa fa-shopping-cart me-2"></i> Order Master Form</a>
          <a class="nav-link small" href="../master_detail_forms/category_product/category_product.php"><i class="fa fa-box-open me-2"></i> Product Master Form</a>
        </div>
        
        <!-- Forms with accordion -->
        <a class="nav-link mb-2" data-bs-toggle="collapse" href="#formsMenu" role="button" aria-expanded="false"
          aria-controls="formsMenu">
          <i class="fa fa-file-alt me-2"></i> Forms <i class="fa fa-caret-down float-end"></i>
        </a>
        <div class="collapse ps-3 mb-2" id="formsMenu">
          <a class="nav-link small" href="../forms/employee.php"><i class="fa fa-user me-2"></i> Employee Form</a>
          <a class="nav-link small" href="../forms/fulltime.php"><i class="fa fa-user-tie me-2"></i> Full-Time Employee
            Form</a>
          <a class="nav-link small" href="../forms/parttime.php"><i class="fa fa-user-clock me-2"></i> Part-Time
            Employee Form</a>
          <a class="nav-link small" href="../forms/category.php"><i class="fa fa-tags me-2"></i> Category Form</a>
          <a class="nav-link small" href="../forms/product.php"><i class="fa fa-box-open me-2"></i> Product Form</a>
          <a class="nav-link small" href="../forms/order.php"><i class="fa fa-shopping-cart me-2"></i> Order Form</a>
          <a class="nav-link small" href="../forms/invoice.php"><i class="fa fa-file-invoice-dollar me-2"></i> Invoice
            Form</a>
        </div>

        <!-- Query Reports -->
        <a class="nav-link mb-2" data-bs-toggle="collapse" href="#queryReportsMenu" role="button" aria-expanded="false"
          aria-controls="queryReportsMenu">
          <i class="fa fa-search me-2"></i> Query Reports <i class="fa fa-caret-down float-end ms-2"></i>
        </a>
        <div class="collapse ps-3 mb-2" id="queryReportsMenu">
          <a class="nav-link small" href="../query_reports/dailysales_sql.php"><i class="fa fa-chart-bar me-2"></i>Daily
            Sales</a>
          <a class="nav-link small" href="../query_reports/empperformence_sql.php"><i
              class="fa fa-boxes-stacked me-2"></i>Employee Performance</a>
          <a class="nav-link small" href="../query_reports/fulltimesalary_sql.php"><i
              class="fa fa-boxes-stacked me-2"></i>Fulltime Employee Salary</a>
          <a class="nav-link small" href="../query_reports/montlysales_sql.php"><i
              class="fa fa-boxes-stacked me-2"></i>Monthly Sales</a>
          <a class="nav-link small" href="../query_reports/parttimeearning_sql.php"><i
              class="fa fa-boxes-stacked me-2"></i>Part-time Employee Earning</a>
          <a class="nav-link small" href="../query_reports/paymentmethod_sql.php"><i
              class="fa fa-boxes-stacked me-2"></i>Payment Method</a>
          <a class="nav-link small" href="../query_reports/productcategory_sql.php"><i
              class="fa fa-boxes-stacked me-2"></i>Product and its Category</a>
          <a class="nav-link small" href="../query_reports/productrevenue_sql.php"><i
              class="fa fa-boxes-stacked me-2"></i>Product Revenue</a>
          <a class="nav-link small" href="../query_reports/showinvoice_sql.php"><i
              class="fa fa-boxes-stacked me-2"></i>Show Invoice</a>
          <a class="nav-link small" href="../query_reports/supervisorsupervisee_sql.php"><i
              class="fa fa-boxes-stacked me-2"></i>Employee and Supervisor</a>
        </div>

        <!-- Reports -->
        <a class="nav-link mb-2" data-bs-toggle="collapse" href="#reportsMenu" role="button" aria-expanded="false"
          aria-controls="reportsMenu">
          <i class="fa fa-chart-line me-2"></i> Reports <i class="fa fa-caret-down float-end"></i>
        </a>
        <div class="collapse ps-3 mb-2" id="reportsMenu">
          <a class="nav-link small" href="../reports/employee_report.php"><i class="fa fa-user-check me-2"></i>Employee
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
          <a class="nav-link small" href="../reports/orders_report.php"><i class="fa fa-boxes-stacked me-2"></i> Orders
            Report</a>
          <a class="nav-link small" href="../reports/orderproduct_report.php"><i class="fa fa-cart-plus me-2"></i>
            OrderProduct Report</a>
          <a class="nav-link small" href="../reports/invoice_report.php"><i class="fa fa-file-invoice-dollar me-2"></i>
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
            <i class="fa fa-user me-2"></i><?= htmlspecialchars($fullname) ?>
          </div>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="../forms/employee_update2.php?empid=<?= $empid ?>">Profile</a></li>
            <li><a class="dropdown-item" href="../backend/logout.php">Logout</a></li>
          </ul>
        </div>
      </div>

      <div class="container mt-4">
        <div class="row mb-3">
          <div class="col-md-3">
            <label for="start">Start Time</label>
            <input type="time" class="form-control" id="start" />
          </div>
          <div class="col-md-3">
            <label for="end">End Time</label>
            <input type="time" class="form-control" id="end" />
          </div>
          <div class="col-md-3 d-flex align-items-end">
            <button class="btn btn-primary w-100">Update</button>
          </div>
        </div>

        <div class="row mb-4">
          <div class="col-md-6">
            <div class="card-summary green">
              <h5>Total Sales Revenue</h5>
              <h2>0.00</h2>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card-summary purple">
              <h5>Total Customers</h5>
              <h2>0</h2>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-body">
            <canvas id="revenueChart" height="100"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    const ctx = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Kont'],
        datasets: [{
          label: 'Revenue',
          data: [287.35, 260.00, 250.00, 0, 0],
          borderColor: '#3498db',
          tension: 0.3,
          fill: true
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: true }
        }
      }
    });
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>