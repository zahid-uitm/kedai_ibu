<?php
session_start();
require '../dbconn.php';

//retrieve category details to be associated with the product
$catQuery = "SELECT * FROM CATEGORY";
$stid = oci_parse($dbconn, $catQuery);
oci_execute($stid);

$categories = [];
while ($row = oci_fetch_assoc($stid)) {
    $categories[] = $row;
}
oci_free_statement($stid);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productName = $_POST['product_name'];
    $productPrice = $_POST['product_price'];
    $categoryId = $_POST['category_id'];

    $query = "INSERT INTO PRODUCT (PRODID, PRODNAME, PRODPRICE, CATEGORYID)
              VALUES ('P' || LPAD(PRODUCT_SEQ.NEXTVAL, 3, '0'), :productName, :productPrice, :categoryId)";

    $stid = oci_parse($dbconn, $query);
    oci_bind_by_name($stid, ":productName", $productName);
    oci_bind_by_name($stid, ":productPrice", $productPrice);
    oci_bind_by_name($stid, ":categoryId", $categoryId);

    if (oci_execute($stid)) {
        header('Location: product.php');
        exit;
    }

    oci_free_statement($stid);
    oci_close($dbconn);
}
?>



<!DOCTYPE html>
<html>

<head>
    <title>Kedai Ibu | Create Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h2>Create New Product</h2>
        <form method="POST">
            <div class="mb-3"><label>Product Name</label><input type="text" name="product_name" class="form-control"
                    required></div>
            <div class="mb-3"><label>Product Price</label><input type="text" name="product_price" class="form-control"
                    required></div>
            <div class="mb-3"><label>Category</label>
                <select name="category_id" class="form-control" required>
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['CATEGORYID'] ?>"><?= $category['CATEGORYNAME'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-success">Create</button>
        </form>
    </div>
</body>

</html>