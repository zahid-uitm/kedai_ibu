<?php
session_start();
require '../dbconn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryName = $_POST['category_name'];


    $query = "INSERT INTO CATEGORY (CATEGORYID, CATEGORYNAME)
                  VALUES ('C' || LPAD(CATEGORY_SEQ.NEXTVAL, 3, '0'), :categoryName)";

    $stid = oci_parse($dbconn, $query);
    oci_bind_by_name($stid, ":categoryName", $categoryName);

    if (oci_execute($stid)) {
        header('Location: category.php');
        exit;
    }

    oci_free_statement($stid);
    oci_close($dbconn);
}
?>



<!DOCTYPE html>
<html>

<head>
    <title>Kedai Ibu | Create Category</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h2>Create New Category</h2>
        <form method="POST">
            <div class="mb-3"><label>Category Name</label><input type="text" name="category_name" class="form-control"
                    required></div>
            <button type="submit" class="btn btn-success">Create</button>
        </form>
    </div>
</body>

</html>