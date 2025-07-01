<?php
session_start();
if (!isset($_SESSION['empid'])) {
    header('Location: ../login.php');
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
                <div class="modal-footer">
                    <strong>Total: <span id="cartTotal">RM 0.00</span></strong>
                    <button class="btn btn-success" onclick="checkout()">Checkout</button>
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
            cart[index].qty = parseInt(value) || 1;
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
            console.log('cart item:', cart);
            if (cart.length === 0) {
                Toast.fire({
                    icon: 'warning',
                    title: 'Your cart is empty!'
                });
                return;
            } else {
                Swal.fire({ title: 'Order Placed!', icon: 'success' })
                    .then(() => {
                        fetch
                    })
                    .then(() => {
                        cart = [];
                    });
            }

            updateCartModal();
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