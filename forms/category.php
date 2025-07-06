<?php
session_start();
require '../dbconn.php';

if (!isset($_SESSION['empid'])) {
    header('Location: ../login.php');
    exit;
}

// Fetch categories
$categories = [];
$query = "SELECT * FROM CATEGORY";
$stid = oci_parse($dbconn, $query);
oci_execute($stid);

while ($row = oci_fetch_assoc($stid)) {
    $categories[] = $row;
}

oci_free_statement($stid);
oci_close($dbconn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Kedai Ibu | Product Category Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-4">
        <h1 class="mb-4 text-center"> Product Category Form</h1>
        <div class="d-flex align-items-end mb-3">
            <a href="category_create.php" class="btn btn-success">+ Create Category</a>
        </div>

        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Category ID</th>
                    <th>Category Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td><?= htmlspecialchars($cat['CATEGORYID']) ?></td>
                        <td><?= htmlspecialchars($cat['CATEGORYNAME']) ?></td>
                        <td>
                            <a href="category_update.php?catid=<?= $cat['CATEGORYID'] ?>"
                                class="btn btn-sm btn-primary">Edit</a>
                            <a href="category_delete.php?catid=<?= $cat['CATEGORYID'] ?>" class="btn btn-sm btn-danger"
                                onclick="return confirm('Are you sure to delete this category?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>