<?php
session_start();
require '../dbconn.php';

if (!isset($_SESSION['empid'])) {
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $empID = $_SESSION['empid'];
    $data = json_decode(file_get_contents("php://input"), true);
    $orderItems = $data['orderItems'];
    $totalAmount = $data['totalAmount'];
    $paymentMethod = $data['paymentMethod'];

    // Generate ORDERID using sequence
    $fetchOrderIdSQL = "SELECT 'O' || LPAD(ORDER_SEQ.NEXTVAL, 3, '0') AS NEW_ORDERID FROM DUAL";
    $fetchStmt = oci_parse($dbconn, $fetchOrderIdSQL);
    oci_execute($fetchStmt);
    $row = oci_fetch_assoc($fetchStmt);
    $orderId = $row['NEW_ORDERID'];
    oci_free_statement($fetchStmt);

    // Insert into ORDERS table
    $insertOrderSQL = "INSERT INTO ORDERS (ORDERID, ORDERDATETIME) VALUES (:order_id, SYSDATE)";
    $stmtOrder = oci_parse($dbconn, $insertOrderSQL);
    oci_bind_by_name($stmtOrder, ":order_id", $orderId);

    if (!oci_execute($stmtOrder)) {
        oci_free_statement($stmtOrder);
        oci_close($dbconn);
        echo json_encode(['status' => 'error', 'message' => 'Failed to insert order.']);
        exit;
    }
    oci_free_statement($stmtOrder);

    // Insert each item into ORDERPRODUCT
    foreach ($orderItems as $item) {
        $productId = $item['id'];
        $quantity = $item['qty'];
        $amount = $item['price'] * $item['qty'];

        $insertProductSQL = "INSERT INTO ORDERPRODUCT (ORDERID, PRODUCTID, QUANTITY, AMOUNT) 
                             VALUES (:order_id, :product_id, :quantity, :amount)";
        $stmtProduct = oci_parse($dbconn, $insertProductSQL);
        oci_bind_by_name($stmtProduct, ":order_id", $orderId);
        oci_bind_by_name($stmtProduct, ":product_id", $productId);
        oci_bind_by_name($stmtProduct, ":quantity", $quantity);
        oci_bind_by_name($stmtProduct, ":amount", $amount);

        if (!oci_execute($stmtProduct)) {
            oci_free_statement($stmtProduct);
            oci_close($dbconn);
            echo json_encode(['status' => 'error', 'message' => 'Failed to insert order item.']);
            exit;
        }

        oci_free_statement($stmtProduct);
    }

    // Insert into INVOICE table
    $insertInvoiceSQL = "INSERT INTO INVOICE (INVOICEID, PAYMENTMETHOD, INVOICETOTALAMOUNT, INVOICEDATE, ORDERID, EMPID) 
                         VALUES ('INV' || LPAD(INVOICE_SEQ.NEXTVAL, 3, '0'), :payment_method, :total_amount, SYSDATE, :order_id, :emp_id)";
    $stmtInvoice = oci_parse($dbconn, $insertInvoiceSQL);
    oci_bind_by_name($stmtInvoice, ":payment_method", $paymentMethod);
    oci_bind_by_name($stmtInvoice, ":total_amount", $totalAmount);
    oci_bind_by_name($stmtInvoice, ":order_id", $orderId);
    oci_bind_by_name($stmtInvoice, ":emp_id", $empID);

    if (!oci_execute($stmtInvoice)) {
        oci_free_statement($stmtInvoice);
        oci_close($dbconn);
        echo json_encode(['status' => 'error', 'message' => 'Failed to insert invoice.']);
        exit;
    }

    oci_free_statement($stmtInvoice);
    oci_close($dbconn);
    echo json_encode(['status' => 'success', 'message' => 'Order placed successfully!', 'orderId' => $orderId]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Kedai Ibu | Order</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background-color: #f4f6f9;
        }

        .product-card {
            min-height: 200px;
        }

        .floating-cart-icon {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #007bff;
            color: white;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            cursor: pointer;
            z-index: 999;
        }

        .category-title {
            font-size: 1.4rem;
            font-weight: bold;
            margin: 30px 0 15px;
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 5px;
        }

        .modal-body input {
            width: 60px;
        }

        .colored-toast.swal2-icon-success {
            background-color: #a5dc86 !important;
        }

        .colored-toast.swal2-icon-error {
            background-color: #f27474 !important;
        }

        .colored-toast.swal2-icon-warning {
            background-color: #f8bb86 !important;
        }

        .colored-toast.swal2-icon-info {
            background-color: #3fc3ee !important;
        }

        .colored-toast.swal2-icon-question {
            background-color: #87adbd !important;
        }

        .colored-toast .swal2-title {
            color: white;
        }

        .colored-toast .swal2-close {
            color: white;
        }

        .colored-toast .swal2-html-container {
            color: white;
        }
    </style>
</head>

<body>

    <div id="productContainer" class="container py-4">
        <h2 class="text-center mb-4">Order</h2>
    </div>

    <!-- Floating Cart Icon -->
    <div class="floating-cart-icon" data-bs-toggle="modal" data-bs-target="#cartModal">
        <i class="fa fa-shopping-cart"></i>
    </div>

    <!-- Cart Modal -->
    <div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Your Cart</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="cartItems">
                    <!-- Cart items rendered here -->
                </div>
                <div class="modal-footer d-flex flex-column align-items-start gap-2">
                    <div class="w-100 d-flex justify-content-between align-items-center">
                        <label for="paymentMethod" class="form-label mb-0 me-2">Payment Method:</label>
                        <select id="paymentMethod" class="form-select form-select-sm w-auto">
                            <option value="Cash">Cash</option>
                            <option value="Online Transfer">Online Transfer</option>
                        </select>
                    </div>
                    <div class="w-100 d-flex justify-content-between align-items-center">
                        <strong>Total: <span id="cartTotal">RM 0.00</span></strong>
                        <button class="btn btn-success" onclick="checkout()">Checkout</button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        let cart = [];
        const Toast = Swal.mixin({
            toast: true,
            iconColor: 'white',
            position: 'bottom',
            customClass: {
                popup: 'colored-toast',
            },
            showConfirmButton: false,
            timer: 1000,
            timerProgressBar: true,
        })

        function addToCart(id, name, price, qtyInputId) {
            console.log(id, name, price, qtyInputId);
            const qtyField = document.getElementById(qtyInputId);
            const qty = parseInt(qtyField?.value) || 1;

            const existing = cart.find(item => item.id === id);
            if (existing) {
                existing.qty += qty;
            } else {
                cart.push({ id, name, price, qty });
            }

            Toast.fire({
                icon: 'success',
                title: `${name} added to cart!`
            });

            updateCartModal();
        }

        function updateCartModal() {
            const cartItems = document.getElementById("cartItems");
            const cartTotal = document.getElementById("cartTotal");
            cartItems.innerHTML = "";
            let total = 0;

            if (cart.length === 0) {
                cartItems.innerHTML = "<p>Your cart is empty.</p>";
                cartTotal.textContent = "RM 0.00";
                return;
            }

            cart.forEach((item, index) => {
                const subtotal = item.price * item.qty;
                total += subtotal;

                cartItems.innerHTML += `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <strong>${item.name}</strong><br>
                        <small>RM ${item.price.toFixed(2)} each</small>
                    </div>
                    <div class="d-flex align-items-center">
                        <input type="number" class="form-control form-control-sm me-2" value="${item.qty}" min="1" onchange="changeQty(${index}, this.value)">
                        <span class="me-2">RM ${subtotal.toFixed(2)}</span>
                        <button class="btn btn-sm btn-danger" onclick="removeItem(${index})">&times;</button>
                    </div>
                </div>
                `;
            });

            cartTotal.textContent = `RM ${total.toFixed(2)}`;
        }

        function changeQty(index, value) {
            const qty = parseInt(value);
            cart[index].qty = (qty >= 1) ? qty : 1;
            updateCartModal();
        }

        function removeItem(index) {
            cart.splice(index, 1);
            Toast.fire({
                icon: 'info',
                title: 'Item removed from cart!'
            });
            updateCartModal();
        }

        function checkout() {
            if (cart.length === 0) {
                Toast.fire({ icon: 'warning', title: 'Your cart is empty!' });
                return;
            }

            const paymentMethod = document.getElementById('paymentMethod').value;
            const totalAmount = cart.reduce((sum, item) => sum + (item.qty * item.price), 0);

            fetch('order_product2.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ orderItems: cart, totalAmount: totalAmount, paymentMethod: paymentMethod })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({ title: 'Success', text: data.message, icon: 'success' }).then(() => {
                            window.open(`invoice_generate.php?orderid=${data.orderId}`, '_blank');
                        });
                        cart = [];
                        updateCartModal();
                    } else {
                        Swal.fire({ title: 'Error', text: data.message, icon: 'error' });
                    }
                })
                .catch(err => {
                    console.error('Checkout failed:', err);
                    Swal.fire({ title: 'Error', text: 'Something went wrong.', icon: 'error' });
                });

            const modal = bootstrap.Modal.getInstance(document.getElementById('cartModal'));
            modal.hide();
        }

        // Fetch categories and products
        fetch('../backend/fetchproduct.php')
            .then(res => res.json())
            .then(data => {
                const container = document.getElementById("productContainer");

                data.forEach(category => {
                    const categoryTitle = document.createElement("div");
                    categoryTitle.className = "category-title";
                    categoryTitle.textContent = category.name;
                    container.appendChild(categoryTitle);

                    const row = document.createElement("div");
                    row.className = "row";

                    category.products.forEach(product => {
                        const col = document.createElement("div");
                        col.className = "col-md-3 mb-3";

                        col.innerHTML = `
                            <div class="card product-card p-2">
                                <div class="card-body d-flex flex-column align-items-center">
                                    <h5 class="card-title">${product.name}</h5>
                                    <p>RM ${parseFloat(product.price).toFixed(2)}</p>
                                    <div class="mt-auto d-flex align-items-center">
                                        <input type="number" class="form-control form-control-sm me-2" id="qty-${product.id}" value="1" min="1" style="width: 60px;">
                                        <button class="btn btn-sm btn-warning text-white" onclick="addToCart('${product.id}', '${product.name.replace(/'/g, "\\'")}', ${product.price}, 'qty-${product.id}')">Add</button>
                                    </div>
                                </div>
                            </div>
                             `;
                        row.appendChild(col);
                    });

                    container.appendChild(row);
                });
            })
            .catch(err => {
                console.error('Failed to load products:', err);
                document.getElementById("productContainer").innerHTML += `<div class="text-danger">Failed to load product list.</div>`;
            });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>