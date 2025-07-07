<?php
$conn = oci_connect("danish", "danish123", "localhost/FREEPDB1");

if (!$conn) {
  $e = oci_error();
  die("Connection failed: " . $e['message']);
}

if (!isset($_GET['categoryID'])) {
  die("No category selected!");
}

$categoryID = $_GET['categoryID'];

// Handle update category name
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editCategoryName'])) {
  $categoryID = $_POST['categoryID'];
  $newName = $_POST['categoryName'];

  $updateCategory = oci_parse($conn, "UPDATE CATEGORY SET categoryName = :catName WHERE categoryID = :id");
  oci_bind_by_name($updateCategory, ":catName", $newName);
  oci_bind_by_name($updateCategory, ":id", $categoryID);
  oci_execute($updateCategory);
}

// Get category info
$catQuery = "SELECT * FROM CATEGORY WHERE categoryID = :id";
$catStid = oci_parse($conn, $catQuery);
oci_bind_by_name($catStid, ":id", $categoryID);
oci_execute($catStid);
$category = oci_fetch_assoc($catStid);

// Get product info
$productQuery = "SELECT * FROM PRODUCT WHERE categoryID = :id";
$productStid = oci_parse($conn, $productQuery);
oci_bind_by_name($productStid, ":id", $categoryID);
oci_execute($productStid);

// Handle adding new product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'addProduct') {
  $prodName = $_POST['prodName'];
  $prodPrice = $_POST['prodPrice'];

  // Insert the product
  $insertProd = oci_parse($conn, "INSERT INTO PRODUCT (prodID, prodName, prodPrice, categoryID) VALUES ('P' || LPAD(PRODUCT_SEQ.NEXTVAL, 3, '0'), :name, :price, :catID)");
  oci_bind_by_name($insertProd, ":name", $prodName);
  oci_bind_by_name($insertProd, ":price", $prodPrice);
  oci_bind_by_name($insertProd, ":catID", $categoryID);
  oci_execute($insertProd);

  // Refresh product list after insert
  $productQuery = "SELECT * FROM PRODUCT WHERE categoryID = :id";
  $productStid = oci_parse($conn, $productQuery);
  oci_bind_by_name($productStid, ":id", $categoryID);
  oci_execute($productStid);

  header("Location: category_detail.php?categoryID=$categoryID");
  exit;
}

// Handle Edit Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editProduct'])) {
  $prodID = $_POST['prodID'];
  $prodName = $_POST['prodName'];
  $prodPrice = $_POST['prodPrice'];

  $updateQuery = "UPDATE PRODUCT SET prodName = :name, prodPrice = :price WHERE prodID = :id";
  $updateStid = oci_parse($conn, $updateQuery);
  oci_bind_by_name($updateStid, ":name", $prodName);
  oci_bind_by_name($updateStid, ":price", $prodPrice);
  oci_bind_by_name($updateStid, ":id", $prodID);
  oci_execute($updateStid);

  header("Location: category_detail.php?categoryID=$categoryID");
  exit;
}

// Handle Delete Product
// if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'deleteProductID') {
//   $deleteID = $_POST['deleteProductID'];

//   $deleteQuery = "DELETE FROM PRODUCT WHERE prodID = :id";
//   $deleteStid = oci_parse($conn, $deleteQuery);
//   oci_bind_by_name($deleteStid, ":id", $deleteID);
//   oci_execute($deleteStid);

//   header("Location: category_detail.php?categoryID=$categoryID");
//   exit;
// }
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
          <a class="nav-link small" href="../order_product/order_product.php"><i class="fa fa-shopping-cart me-2"></i>
            Order-Product Master Detail</a>
          <a class="nav-link small" href="../category_product/category_product.php"><i class="fa fa-box-open me-2"></i>
            Category-Product Master Detail</a>
        </div>

        <!-- Forms with accordion -->
        <a class="nav-link mb-2" data-bs-toggle="collapse" href="#formsMenu" role="button" aria-expanded="false"
          aria-controls="formsMenu">
          <i class="fa fa-file-alt me-2"></i> Forms <i class="fa fa-caret-down float-end"></i>
        </a>
        <div class="collapse ps-3 mb-2" id="formsMenu">
          <a class="nav-link small" href="../views/employee_form.php"><i class="fa fa-user me-2"></i> Employee Form</a>
          <a class="nav-link small" href="../views/category_form.php"><i class="fa fa-tags me-2"></i> Category Form</a>
          <a class="nav-link small" href="../views/product_form.php"><i class="fa fa-box-open me-2"></i> Product
            Form</a>
          <a class="nav-link small" href="../forms/order.php"><i class="fa fa-shopping-cart me-2"></i> Order Form</a>
        </div>

        <!-- Query Reports -->
        <a class="nav-link mb-2" data-bs-toggle="collapse" href="#queryReportsMenu" role="button" aria-expanded="false"
          aria-controls="queryReportsMenu">
          <i class="fa fa-search me-2"></i> Query Reports <i class="fa fa-caret-down float-end ms-2"></i>
        </a>
        <div class="collapse ps-3 mb-2" id="queryReportsMenu">
          <a class="nav-link small" href="#"><i class="fa fa-chart-bar me-2"></i> Query Report 1</a>
          <a class="nav-link small" href="#"><i class="fa fa-boxes-stacked me-2"></i> Query Report 2</a>
        </div>

        <!-- Reports -->
        <a class="nav-link mb-2" data-bs-toggle="collapse" href="#reportsMenu" role="button" aria-expanded="false"
          aria-controls="reportsMenu">
          <i class="fa fa-chart-line me-2"></i> Reports <i class="fa fa-caret-down float-end"></i>
        </a>
        <div class="collapse ps-3 mb-2" id="reportsMenu">
          <a class="nav-link small" href="../reports/employee_query.php"><i class="fa fa-user-check me-2"></i>Employee
            Report</a>
          <a class="nav-link small" href="#"><i class="fa fa-user-tie me-2"></i> Full Time Report</a>
          <a class="nav-link small" href="#"><i class="fa fa-user-clock me-2"></i> Part Time Report</a>
          <a class="nav-link small" href="#"><i class="fa fa-tags me-2"></i> Category Report</a>
          <a class="nav-link small" href="#"><i class="fa fa-box-open me-2"></i> Product Report</a>
          <a class="nav-link small" href="#"><i class="fa fa-boxes-stacked me-2"></i> Orders Report</a>
          <a class="nav-link small" href="#"><i class="fa fa-cart-plus me-2"></i> OrderProduct Report</a>
          <a class="nav-link small" href="#"><i class="fa fa-file-invoice-dollar me-2"></i> Invoice Report</a>
        </div>
      </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-fill">
      <div class="header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Category-Product Master Detail</h4>
        <div><i class="fa fa-user"></i> Ahmad Zulkifli</div>
      </div>

      <div class="container mt-4">

        <h3>Category ID: <?php echo htmlspecialchars($category['CATEGORYID']); ?></h3>
        <h5 class="mb-4">Category Name: <?php echo htmlspecialchars($category['CATEGORYNAME']); ?></h5>

        <!-- Category Edit Form -->
        <form method="post" class="mb-4">
          <input type="hidden" name="editCategoryName" value="1">
          <input type="hidden" name="categoryID" value="<?php echo $categoryID; ?>">
          <div class="mb-3">
            <label class="form-label">Category Name</label>
            <input type="text" name="categoryName" class="form-control"
              value="<?php echo htmlspecialchars($category['CATEGORYNAME']); ?>" required>
          </div>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>

        <!-- Product Table -->
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="mb-0">Products in this Category:</h5>
          <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addProductModal">
            <i class="fa fa-plus"></i> Add Product
          </button>
        </div>

        <table class="table table-bordered bg-white">

          <thead>
            <tr>
              <th>Product ID</th>
              <th>Name</th>
              <th>Price (RM)</th>
              <th>Action</th>
            </tr>
          </thead>

          <tbody>
            <?php while ($product = oci_fetch_assoc($productStid)): ?>
              <tr>
                <td><?php echo htmlspecialchars($product['PRODID']); ?></td>
                <td><?php echo htmlspecialchars($product['PRODNAME']); ?></td>
                <td><?php echo number_format($product['PRODPRICE'], 2); ?></td>
                <td>
                  <!-- Edit Button -->
                  <button class="btn btn-sm btn-warning me-1" data-bs-toggle="modal"
                    data-bs-target="#editProductModal<?php echo $product['PRODID']; ?>">
                    <i class="fa fa-edit"></i>
                  </button>

                  <!-- Product Status -->
                  <?php if ($product['IS_ACTIVE'] === 'Y'): ?>

                    <a href="../../forms/product_deactivate.php?prodid=<?= $product['PRODID'] ?>&categoryID=<?= $categoryID ?>" class="btn btn-sm btn-danger"
                      onclick="return confirm('Are you sure to deactivate this product?');">Deactivate</a>
                    <span class="badge bg-success ms-3">Active</span>
                  <?php else: ?>
                    <a href="../../forms/product_activate.php?prodid=<?= $product['PRODID'] ?>&categoryID=<?= $categoryID ?>"
                      class="btn btn-sm btn-success">Activate</a>
                    <span class="badge bg-secondary ms-3">Inactive</span>
                  <?php endif; ?>

                  <!-- Delete Button
                  <form method="post" action="" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this product?');">
                    <input type="hidden" name="action" value="deleteProductID">
                    <input type="hidden" name="deleteProductID" value="<?php echo $product['PRODID']; ?>">
                    <button type="submit" class="btn btn-sm btn-danger">
                      <i class="fa fa-trash"></i>
                    </button>
                  </form> -->
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
                          <h5 class="modal-title" id="editModalLabel<?php echo $product['PRODID']; ?>">Edit Product</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body pt-2">
                          <input type="hidden" name="editProduct" value="1">
                          <input type="hidden" name="prodID" value="<?php echo $product['PRODID']; ?>">
                          <input type="hidden" name="categoryID" value="<?php echo $categoryID; ?>">

                          <div class="mb-3">
                            <label class="form-label">Product Name</label>
                            <input type="text" name="prodName" class="form-control"
                              value="<?php echo htmlspecialchars($product['PRODNAME']); ?>" required>
                          </div>

                          <div class="mb-3">
                            <label class="form-label">Price (RM)</label>
                            <input type="number" step="0.01" name="prodPrice" class="form-control"
                              value="<?php echo $product['PRODPRICE']; ?>" required>
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

        <a href="category_product.php" class="btn btn-secondary">← Back to Categories</a>


      </div>
    </div>

  </div>

  <!-- Add Product Modal -->
  <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <form method="post" class="modal-content">
        <input type="hidden" name="action" value="addProduct">
        <div class="modal-header">
          <h5 class="modal-title" id="addProductModalLabel">Add New Product</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="addProduct" value="1">
          <input type="hidden" name="categoryID" value="<?php echo $categoryID; ?>">

          <div class="mb-3">
            <label class="form-label">Product Name</label>
            <input type="text" name="prodName" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Price (RM)</label>
            <input type="number" step="0.01" name="prodPrice" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Add Product</button>
        </div>
      </form>
    </div>
  </div>


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>