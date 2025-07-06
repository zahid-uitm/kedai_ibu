<?php
session_start();
require '../dbconn.php';

if (!isset($_GET['catid'])) {
    header("Location: category.php");
    exit;
}

$catid = $_GET['catid'];

// Fetch existing category
$query = "SELECT * FROM CATEGORY WHERE CATEGORYID = :catid";
$stid = oci_parse($dbconn, $query);
oci_bind_by_name($stid, ":catid", $catid);
oci_execute($stid);
$category = oci_fetch_assoc($stid);
oci_free_statement($stid);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryName = $_POST['category_name'];
    $query = "UPDATE CATEGORY
              SET CATEGORYNAME = :categoryName
              WHERE CATEGORYID = :catid";

    $stid = oci_parse($dbconn, $query);
    oci_bind_by_name($stid, ":categoryName", $categoryName);
    oci_bind_by_name($stid, ":catid", $catid);

    if (oci_execute($stid)) {
        header("Location: category.php");
        exit;
    }

    oci_free_statement($stid);
}

oci_close($dbconn);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Kedai Ibu | Update Product Category</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="container mt-5">
        <h2>Update Product Category Information</h2>
        <form method="POST">
            <div class="mb-3"><label>Category Name</label><input type="text" name="category_name" class="form-control"
                    value="<?= $category['CATEGORYNAME'] ?>" required></div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
</body>

</html>