<?php
$conn = oci_connect("danish", "danish123", "localhost/FREEPDB1");
if (!$conn) {
  $e = oci_error();
  die("Connection failed: " . $e['message']);
}

if (!isset($_GET['orderID'])) {
  die("No order selected!");
}

//get the employee name to be display
$empid = $_SESSION['empid'] ?? null;

$query = "SELECT EMPLOYEEFIRSTNAME || ' ' || EMPLOYEELASTNAME AS FULLNAME FROM EMPLOYEE WHERE EMPID = '$empid'";
$result = oci_parse($conn, $query);
oci_execute($result);
$row = oci_fetch_assoc($result);
$fullname = $row['FULLNAME'] ?? 'Guest';

$orderID = $_GET['orderID'];

// Handle update order date
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editOrderDate'])) {
  $orderID = $_POST['orderID'];
  $newDate = $_POST['orderDateTime'];

  $formattedDateTime = date('Y-m-d H:i:s', strtotime($newDate));

  $updateOrder = oci_parse($conn, "UPDATE ORDERS SET orderDateTime = TO_DATE(:dateTime, 'YYYY-MM-DD HH24:MI:SS') WHERE orderID = :id");
  oci_bind_by_name($updateOrder, ":dateTime", $formattedDateTime);
  oci_bind_by_name($updateOrder, ":id", $orderID);
  oci_execute($updateOrder);
}

// Get order info
$orderQuery = "SELECT ORDERID, TO_CHAR(ORDERDATETIME, 'YYYY-MM-DD HH24:MI:SS') AS ORDERDATETIME FROM ORDERS WHERE orderID = :id";
$orderStid = oci_parse($conn, $orderQuery);
oci_bind_by_name($orderStid, ":id", $orderID);
oci_execute($orderStid);
$order = oci_fetch_assoc($orderStid);

// Get products in the order using the bridge table ORDERPRODUCT
$productQuery = "
  SELECT P.PRODID, P.PRODNAME, P.PRODPRICE, OP.QUANTITY, OP.AMOUNT
  FROM PRODUCT P
  JOIN ORDERPRODUCT OP ON P.PRODID = OP.PRODUCTID
  WHERE OP.ORDERID = :id
";
$productStid = oci_parse($conn, $productQuery);
oci_bind_by_name($productStid, ":id", $orderID);
oci_execute($productStid);

// Handle Add Product to Order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'addOrderProduct') {
  $prodID = $_POST['prodID'];
  $quantity = $_POST['quantity'];

  // Get product price
  $priceStid = oci_parse($conn, "SELECT ProdPrice FROM PRODUCT WHERE ProdID = :id");
  oci_bind_by_name($priceStid, ":id", $prodID);
  oci_execute($priceStid);
  $priceRow = oci_fetch_assoc($priceStid);
  $price = $priceRow['PRODPRICE'];
  $amount = $price * $quantity;

  // Insert into ORDERPRODUCT
  $insertStid = oci_parse($conn, "INSERT INTO ORDERPRODUCT (OrderID, ProductID, Quantity, Amount) VALUES (:orderID, :prodID, :qty, :amt)");
  oci_bind_by_name($insertStid, ":orderID", $orderID);
  oci_bind_by_name($insertStid, ":prodID", $prodID);
  oci_bind_by_name($insertStid, ":qty", $quantity);
  oci_bind_by_name($insertStid, ":amt", $amount);
  oci_execute($insertStid);

  header("Location: order_detail.php?orderID=$orderID");
  exit;
}

// Handle Delete Product from Order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'deleteOrderProduct') {
  $prodID = $_POST['prodID'];
  $deleteStid = oci_parse($conn, "DELETE FROM ORDERPRODUCT WHERE OrderID = :orderID AND ProductID = :prodID");
  oci_bind_by_name($deleteStid, ":orderID", $orderID);
  oci_bind_by_name($deleteStid, ":prodID", $prodID);
  oci_execute($deleteStid);

  // Recalculate total amount for the order
  $sumQuery = "SELECT SUM(AMOUNT) AS TOTAL FROM ORDERPRODUCT WHERE ORDERID = :orderid";
  $sumStmt = oci_parse($conn, $sumQuery);
  oci_bind_by_name($sumStmt, ":orderid", $orderID);
  oci_execute($sumStmt);
  $row = oci_fetch_assoc($sumStmt);
  $newTotal = $row['TOTAL'];
  oci_free_statement($sumStmt);

  // Update INVOICE table
  $updateInvoice = "UPDATE INVOICE SET INVOICETOTALAMOUNT = :newtotal WHERE ORDERID = :orderid";
  $invStmt = oci_parse($conn, $updateInvoice);
  oci_bind_by_name($invStmt, ":newtotal", $newTotal);
  oci_bind_by_name($invStmt, ":orderid", $orderID);
  oci_execute($invStmt);
  oci_free_statement($invStmt);

  header("Location: order_detail.php?orderID=$orderID");
  exit;
}

// Handle Edit Product in Order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editOrderProduct'])) {
  $orderID = $_POST['orderID'];
  $prodID = $_POST['prodID'];
  $quantity = $_POST['quantity'];

  // Get the product price from the PRODUCT table
  $priceQuery = "SELECT prodPrice FROM PRODUCT WHERE prodID = :pid";
  $priceStid = oci_parse($conn, $priceQuery);
  oci_bind_by_name($priceStid, ":pid", $prodID);
  oci_execute($priceStid);

  $row = oci_fetch_assoc($priceStid);
  $price = $row['PRODPRICE'];

  // Recalculate amount
  $amount = $quantity * $price;

  // Update ORDERPRODUCT table
  $updateQuery = "UPDATE ORDERPRODUCT SET quantity = :qty, amount = :amt WHERE orderID = :oid AND productID = :pid";
  $stid = oci_parse($conn, $updateQuery);

  oci_bind_by_name($stid, ":qty", $quantity);
  oci_bind_by_name($stid, ":amt", $amount);
  oci_bind_by_name($stid, ":oid", $orderID);
  oci_bind_by_name($stid, ":pid", $prodID);

  oci_execute($stid);

  oci_free_statement($stid);
  oci_free_statement($priceStid);

  // Recalculate total amount for the order
  $sumQuery = "SELECT SUM(AMOUNT) AS TOTAL FROM ORDERPRODUCT WHERE ORDERID = :orderid";
  $sumStmt = oci_parse($conn, $sumQuery);
  oci_bind_by_name($sumStmt, ":orderid", $orderID);
  oci_execute($sumStmt);
  $row = oci_fetch_assoc($sumStmt);
  $newTotal = $row['TOTAL'];
  oci_free_statement($sumStmt);

  // Update INVOICE table
  $updateInvoice = "UPDATE INVOICE SET INVOICETOTALAMOUNT = :newtotal WHERE ORDERID = :orderid";
  $invStmt = oci_parse($conn, $updateInvoice);
  oci_bind_by_name($invStmt, ":newtotal", $newTotal);
  oci_bind_by_name($invStmt, ":orderid", $orderID);
  oci_execute($invStmt);
  oci_free_statement($invStmt);

  // Redirect to refresh the data
  header("Location: order_detail.php?orderID=" . $orderID);
  exit();
}

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

    .modal-content {
      background-color: #fff !important;
      color: #000;
    }
  </style>

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

        <h3>Order ID: <?php echo htmlspecialchars($order['ORDERID']); ?></h3>
        <h5 class="mb-4">Order Date and Time: <?php echo htmlspecialchars($order['ORDERDATETIME']); ?></h5>

        <!-- Order Edit Form -->
        <form method="post" class="mb-4">
          <input type="hidden" name="editOrderDate" value="1">
          <input type="hidden" name="orderID" value="<?php echo $orderID; ?>">
          <div class="mb-3">
            <label class="form-label">Order Date</label>
            <input type="datetime-local" name="orderDateTime" class="form-control"
              value="<?php echo date('Y-m-d H:i:s', strtotime($order['ORDERDATETIME'])); ?>" required>
          </div>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>

        <!-- Product Table -->
        <!-- <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="mb-0">Products in this Order:</h5>
          <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addProductModal">
            <i class="fa fa-plus"></i> Add Product to Order
          </button>
        </div> -->

        <table class="table table-bordered bg-white">

          <thead>
            <tr>
              <th>Product ID</th>
              <th>Name</th>
              <th>Price</th>
              <th>Quantity</th>
              <th>Amount</th>
              <th>Action</th>
            </tr>
          </thead>

          <tbody>
            <?php while ($product = oci_fetch_assoc($productStid)): ?>
              <tr>
                <td><?php echo htmlspecialchars($product['PRODID']); ?></td>
                <td><?php echo htmlspecialchars($product['PRODNAME']); ?></td>
                <td>RM<?php echo number_format($product['PRODPRICE'], 2); ?></td>
                <td><?php echo $product['QUANTITY']; ?></td>
                <td>RM<?php echo number_format($product['AMOUNT'], 2); ?></td>
                <td>
                  <!-- Edit Button -->
                  <button class="btn btn-sm btn-warning me-1" data-bs-toggle="modal"
                    data-bs-target="#editProductModal<?php echo $product['PRODID']; ?>">
                    <i class="fa fa-edit"></i>
                  </button>

                  <!-- Delete Button -->
                  <form method="post" class="d-inline" onsubmit="return confirm('Delete this product from the order?');">
                    <input type="hidden" name="action" value="deleteOrderProduct">
                    <input type="hidden" name="prodID" value="<?php echo $product['PRODID']; ?>">
                    <button class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                  </form>
                </td>

              </tr>

              <!-- Edit Product Modal -->
              <div class="modal fade" id="editProductModal<?php echo $product['PRODID']; ?>" tabindex="-1"
                aria-labelledby="editModalLabel<?php echo $product['PRODID']; ?>" aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content border-0 bg-transparent">
                    <div class="bg-white rounded shadow-sm p-4">
                      <form method="post">
                        <div class="modal-header border-0 pb-0">
                          <h5 class="modal-title" id="editModalLabel<?php echo $product['PRODID']; ?>">Edit Product in
                            Order</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body pt-2">
                          <input type="hidden" name="editOrderProduct" value="1">
                          <input type="hidden" name="orderID" value="<?php echo $orderID; ?>">
                          <input type="hidden" name="prodID" value="<?php echo $product['PRODID']; ?>">

                          <!-- Read-only Product Name -->
                          <div class="mb-3">
                            <label class="form-label">Product Name</label>
                            <input type="text" class="form-control"
                              value="<?php echo htmlspecialchars($product['PRODNAME']); ?>" readonly
                              style="background-color: #e9ecef; cursor: not-allowed;">
                          </div>

                          <!-- Editable Quantity -->
                          <div class="mb-3">
                            <label class="form-label">Quantity</label>
                            <input type="number" name="quantity" class="form-control"
                              value="<?php echo $product['QUANTITY']; ?>" required>
                          </div>
                        </div>

                        <div class="modal-footer border-0 pt-0">
                          <button type="submit" class="btn btn-success">Save Changes</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              </div>

            <?php endwhile; ?>
          </tbody>


        </table>

        <a href="order_product.php" class="btn btn-secondary">&larr; Back to Orders</a>


      </div>
    </div>

  </div>

  <!-- Add Product Modal -->
  <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <form method="post" class="modal-content">
        <input type="hidden" name="action" value="addOrderProduct">
        <div class="modal-header">
          <h5 class="modal-title">Add Product to Order</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Select Product</label>
            <select name="prodID" class="form-select" required>
              <?php
              $allProducts = oci_parse($conn, "SELECT ProdID, ProdName FROM PRODUCT");
              oci_execute($allProducts);
              while ($prod = oci_fetch_assoc($allProducts)) {
                echo '<option value="' . $prod['PRODID'] . '">' . $prod['PRODNAME'] . '</option>';
              }
              ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Quantity</label>
            <input type="number" name="quantity" class="form-control" required min="1">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Add to Order</button>
        </div>
      </form>
    </div>
  </div>


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>