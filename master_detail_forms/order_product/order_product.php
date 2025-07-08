<?php
// Oracle connection
$conn = oci_connect('danish', 'danish123', 'localhost/FREEPDB1');

if (!$conn) {
  $e = oci_error();
  die("Connection failed: " . $e['message']);
}

//get the employee name to be display
$empid = $_SESSION['empid'] ?? null;

$query = "SELECT EMPLOYEEFIRSTNAME || ' ' || EMPLOYEELASTNAME AS FULLNAME FROM EMPLOYEE WHERE EMPID = '$empid'";
$result = oci_parse($conn, $query);
oci_execute($result);
$row = oci_fetch_assoc($result);
$fullname = $row['FULLNAME'] ?? 'Guest';

// Handle add new order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'addOrder') {
  $newDateTime = $_POST['newDateTime'];

  $insert = oci_parse($conn, "INSERT INTO ORDERS (orderID, orderDateTime) VALUES ('O' || LPAD(ORDER_SEQ.NEXTVAL, 3, '0'), TO_DATE(:dateTime, 'YYYY-MM-DD'))");
  oci_bind_by_name($insert, ":dateTime", $newDateTime);
  oci_execute($insert);

  header("Location: order_product.php");
  exit;
}

// Handle delete order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'deleteOrder') {
  $deleteID = $_POST['deleteOrderID'];
  $deleteQuery = "DELETE FROM ORDERS WHERE orderID = :id";
  $deleteStid = oci_parse($conn, $deleteQuery);
  oci_bind_by_name($deleteStid, ":id", $deleteID);
  oci_execute($deleteStid);

  header("Location: order_product.php");
  exit;
}

// Handle search input
$search = isset($_GET['search']) ? $_GET['search'] : '';
$query = "SELECT ORDERID, TO_CHAR(ORDERDATETIME, 'YYYY-MM-DD HH24:MI:SS') AS ORDERDATETIME FROM ORDERS";
if (!empty($search)) {
  $query .= " WHERE TO_CHAR(ORDERDATETIME, 'YYYY-MM-DD') = :search";
}
$query .= " ORDER BY ORDERID";

$stid = oci_parse($conn, $query);
if (!empty($search)) {
  oci_bind_by_name($stid, ":search", $search);
}
oci_execute($stid);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Kedai Ibu | Category-Product</title>
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
        <img src="../../Logo Kedai Ibu.png" alt="Logo" style="height: 60px; width: auto; margin-right: 10px;">
        <span>Kedai Ibu</span>
      </h4>

      <nav class="nav flex-column">
        <!-- Home -->
        <a class="nav-link mb-2" href="../../views/dashboard.php">
          <i class="fa fa-home me-2"></i> Home
        </a>

        <!-- Master Form and Subform -->
        <a class="nav-link mb-2" data-bs-toggle="collapse" href="#masterDetailFormsMenu" role="button"
          aria-expanded="false" aria-controls="masterDetailFormsMenu">
          <i class="fa fa-file-alt me-2"></i> Master Detail Forms <i class="fa fa-caret-down float-end"></i>
        </a>
        <div class="collapse ps-3 mb-2" id="masterDetailFormsMenu">
          <a class="nav-link small" href="../order_product/order_product.php"><i class="fa fa-shopping-cart me-2"></i>
            Order Master Form</a>
          <a class="nav-link small" href="../category_product/category_product.php"><i class="fa fa-box-open me-2"></i>
            Product Master Form</a>
        </div>

        <!-- Forms with accordion -->
        <a class="nav-link mb-2" data-bs-toggle="collapse" href="#formsMenu" role="button" aria-expanded="false"
          aria-controls="formsMenu">
          <i class="fa fa-file-alt me-2"></i> Forms <i class="fa fa-caret-down float-end"></i>
        </a>
        <div class="collapse ps-3 mb-2" id="formsMenu">
          <a class="nav-link small" href="../../forms/employee.php"><i class="fa fa-user me-2"></i> Employee Form</a>
          <a class="nav-link small" href="../../forms/fulltime.php"><i class="fa fa-user-tie me-2"></i> Full-Time
            Employee
            Form</a>
          <a class="nav-link small" href="../../forms/parttime.php"><i class="fa fa-user-clock me-2"></i> Part-Time
            Employee Form</a>
          <!-- <a class="nav-link small" href="../forms/category.php"><i class="fa fa-tags me-2"></i> Category Form</a>
          <a class="nav-link small" href="../forms/product.php"><i class="fa fa-box-open me-2"></i> Product Form</a>
          <a class="nav-link small" href="../forms/order.php"><i class="fa fa-shopping-cart me-2"></i> Order Form</a> -->
          <a class="nav-link small" href="../../forms/invoice.php"><i class="fa fa-file-invoice-dollar me-2"></i>
            Invoice
            Form</a>
        </div>

        <!-- Query Reports -->
        <a class="nav-link mb-2" data-bs-toggle="collapse" href="#queryReportsMenu" role="button" aria-expanded="false"
          aria-controls="queryReportsMenu">
          <i class="fa fa-search me-2"></i> Query Reports <i class="fa fa-caret-down float-end ms-2"></i>
        </a>
        <div class="collapse ps-3 mb-2" id="queryReportsMenu">
          <a class="nav-link small" href="../../query_reports/dailysales_sql.php">
            <i class="fa fa-calendar-day me-2"></i> Daily Sales
          </a>
          <a class="nav-link small" href="../../query_reports/montlysales_sql.php">
            <i class="fa fa-calendar-alt me-2"></i> Monthly Sales
          </a>
          <a class="nav-link small" href="../../query_reports/fulltimesalary_sql.php">
            <i class="fa fa-money-bill me-2"></i> Fulltime Employee Salary
          </a>
          <a class="nav-link small" href="../../query_reports/parttimeearning_sql.php">
            <i class="fa fa-coins me-2"></i> Part-time Employee Earning
          </a>
          <a class="nav-link small" href="../../query_reports/empperformence_sql.php">
            <i class="fa fa-chart-line me-2"></i> Employee Performance
          </a>
          <a class="nav-link small" href="../../query_reports/paymentmethod_sql.php">
            <i class="fa fa-credit-card me-2"></i> Payment Method
          </a>
          <a class="nav-link small" href="../../query_reports/productcategory_sql.php">
            <i class="fa fa-boxes me-2"></i> Product and its Category
          </a>
          <a class="nav-link small" href="../../query_reports/productrevenue_sql.php">
            <i class="fa fa-dollar-sign me-2"></i> Product Revenue
          </a>
          <a class="nav-link small" href="../../query_reports/showinvoice_sql.php">
            <i class="fa fa-file-invoice me-2"></i> Show Invoice
          </a>
          <a class="nav-link small" href="../../query_reports/supervisorsupervisee_sql.php">
            <i class="fa fa-user-friends me-2"></i> Employee and Supervisor
          </a>
        </div>

        <!-- Reports -->
        <a class="nav-link mb-2" data-bs-toggle="collapse" href="#reportsMenu" role="button" aria-expanded="false"
          aria-controls="reportsMenu">
          <i class="fa fa-chart-line me-2"></i> Reports <i class="fa fa-caret-down float-end"></i>
        </a>
        <div class="collapse ps-3 mb-2" id="reportsMenu">
          <a class="nav-link small" href="../../reports/employee_report.php"><i
              class="fa fa-user-check me-2"></i>Employee
            Report</a>
          <a class="nav-link small" href="../../reports/fulltime_report.php"><i class="fa fa-user-tie me-2"></i>
            Full Time
            Report</a>
          <a class="nav-link small" href="../../reports/parttime_report.php"><i class="fa fa-user-clock me-2"></i> Part
            Time Report</a>
          <a class="nav-link small" href="../../reports/category_report.php"><i class="fa fa-tags me-2"></i> Category
            Report</a>
          <a class="nav-link small" href="../../reports/product_report.php"><i class="fa fa-box-open me-2"></i> Product
            Report</a>
          <a class="nav-link small" href="../../reports/orders_report.php"><i class="fa fa-boxes-stacked me-2"></i>
            Orders
            Report</a>
          <a class="nav-link small" href="../../reports/orderproduct_report.php"><i class="fa fa-cart-plus me-2"></i>
            OrderProduct Report</a>
          <a class="nav-link small" href="../../reports/invoice_report.php"><i
              class="fa fa-file-invoice-dollar me-2"></i>
            Invoice Report</a>
        </div>
      </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-fill">
      <div class="header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Order-Product Master Detail</h4>
        <div class="dropdown">
          <div class="d-flex align-items-center dropdown-toggle" role="button" data-bs-toggle="dropdown"
            aria-expanded="false" style="cursor: pointer;">
            <i class="fa fa-user me-2"></i><?= htmlspecialchars($fullname) ?>
          </div>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="../../forms/employee_update2.php?empid=<?= $empid ?>">Profile</a></li>
            <li><a class="dropdown-item" href="../../backend/logout.php">Logout</a></li>
          </ul>
        </div>
      </div>

      <div class="container mt-4">

        <form method="GET" class="row mb-3">
          <div class="col-md-3">
            <label for="start">Search Order Date</label>
            <input type="date" class="form-control" id="start" name="search"
              value="<?php echo htmlspecialchars($search); ?>" />
          </div>

          <div class="col-md-3 d-flex align-items-end">
            <button class="btn btn-primary w-50">Search</button>
          </div>
        </form>


        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5 class="mb-0">Order List</h5>
              <!-- <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addOrderModal">
                <i class="fa fa-plus"></i> Add Order
              </button> -->
              <button class="btn btn-success btn-sm">
                <a href="../../forms/order_product2.php" class="btn btn-success"><i class="fa fa-plus"></i> Add
                  Order</a>
              </button>
            </div>
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>Order ID</th>
                  <th>Order Date and Time</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($row = oci_fetch_assoc($stid)): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($row['ORDERID']); ?></td>
                    <td><?php echo htmlspecialchars($row['ORDERDATETIME']); ?></td>
                    <td>
                      <a href="order_detail.php?orderID=<?php echo $row['ORDERID']; ?>"
                        class="btn btn-sm btn-info text-white">
                        View Details
                      </a>
                      <form method="post" action="" class="d-inline"
                        onsubmit="return confirm('Are you sure you want to delete this order?');">
                        <input type="hidden" name="action" value="deleteOrder">
                        <input type="hidden" name="deleteOrderID" value="<?php echo $row['ORDERID']; ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                      </form>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>


      </div>
    </div>

  </div>

  <!-- Add Order Modal -->
  <div class="modal fade" id="addOrderModal" tabindex="-1" aria-labelledby="addOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <form method="post" class="modal-content">
        <input type="hidden" name="action" value="addOrder">
        <div class="modal-header">
          <h5 class="modal-title" id="addOrderModalLabel">Add New Order</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Order Date and Time</label>
            <input type="date" class="form-control" name="newDateTime" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Add Order</button>
        </div>
      </form>
    </div>
  </div>


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>