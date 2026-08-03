document.addEventListener('DOMContentLoaded', function() {
    // Helper function to get the appropriate path based on current location
    function getControllerPath(file) {
        const isIndex = window.location.pathname.endsWith('index.php') || window.location.pathname.endsWith('LaFlora/');
        return isIndex ? `controller/${file}` : `../controller/${file}`;
    }

    // Helper function to get the appropriate views path based on current location
    function getViewsPath(file) {
        const isIndex = window.location.pathname.endsWith('index.php') || window.location.pathname.endsWith('LaFlora/');
        return isIndex ? `views/${file}` : file;
    }

    // Helper function to format currency
    function formatCurrency(amount) {
        return 'Rs. ' + amount.toFixed(2);
    }

    // Helper function to update cart totals in UI
    function updateCartTotals(total) {
        const subtotalElement = document.getElementById('cartSubtotal');
        const totalElement = document.getElementById('cartTotal');
        if (subtotalElement) subtotalElement.textContent = formatCurrency(total);
        if (totalElement) totalElement.textContent = formatCurrency(total);
    }

    // Helper function to update all cart badges in the DOM
    function updateCartBadges(count) {
        document.querySelectorAll('.cart-count').forEach(badge => {
            badge.textContent = count;
            badge.style.display = (parseInt(count) > 0) ? '' : 'none';
        });
    }

    // Handle quantity buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('.cart-qty-btn')) {
            e.preventDefault();
            const btn = e.target.closest('.cart-qty-btn');
            const form = btn.closest('form');
            const input = form.querySelector('.cart-qty-input');
            const cartId = btn.value;
            let quantity = parseInt(input.value);

            if (btn.name === 'increase_qty') {
                quantity++;
            } else if (btn.name === 'decrease_qty' && quantity > 1) {
                quantity--;
            }

            updateCartQuantity(cartId, quantity, input);
        }
    });

    // Handle manual quantity input
    document.querySelectorAll('.cart-qty-input').forEach(input => {
        input.addEventListener('change', function() {
            const cartId = this.closest('form').querySelector('.cart-qty-btn').value;
            const quantity = parseInt(this.value);
            updateCartQuantity(cartId, quantity, this);
        });
    });

    function updateCartQuantity(cartId, quantity, input) {
        if (quantity < 1) quantity = 1;
        if (isNaN(quantity)) quantity = 1;

        // Show loading state
        const form = input.closest('form');
        form.querySelectorAll('button').forEach(btn => btn.disabled = true);
        
        fetch(getControllerPath('cart_process.php'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=update&cart_id=${cartId}&quantity=${quantity}`
        })
        .then(response => response.json())
        .then(data => {                if (data.status === 'success') {
                input.value = quantity;
                if (data.cart_total) {
                    updateCartTotals(parseFloat(data.cart_total));
                }
                // Don't update cart count for quantity changes
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message
                });
                // If there's an available stock limit, set the input to that value
                if (data.available) {
                    input.value = data.available;
                    updateCartQuantity(cartId, data.available, input);
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to update quantity. Please try again.'
            });
        })
        .finally(() => {
            // Reset loading state
            form.querySelectorAll('button').forEach(btn => btn.disabled = false);
        });
    }    // Handle add to cart button clicks
    document.addEventListener('click', function(e) {
        if (e.target.closest('.add-to-cart-btn')) {
            e.preventDefault();
            const btn = e.target.closest('.add-to-cart-btn');
            const productId = btn.dataset.productId;
              // Show loading state
            btn.disabled = true;
            // Check if it's a circular button or a normal button
            if(btn.classList.contains('rounded-circle')) {
                btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
            } else {
                btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding...';
            }
            
            fetch(getControllerPath('cart_process.php'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=add&product_id=${productId}&quantity=1`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Update cart count before showing success message
                    if (typeof data.cartCount !== 'undefined') {
                        updateCartBadges(data.cartCount);
                    }
                    // If on wishlist page, also remove from wishlist
                    if (window.location.pathname.includes('wishlist.php')) {
                        fetch(getControllerPath('add_to_wishlist.php'), {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `product_id=${productId}&action=remove`
                        })
                        .then(response => response.json())
                        .then(wishlistData => {
                            if (wishlistData.status === 'success') {
                                if (typeof removeProductFromUI === 'function') {
                                    removeProductFromUI(btn);
                                }
                                if (typeof updateWishlistCount === 'function') {
                                    updateWishlistCount(wishlistData.wishlistCount);
                                }
                            }
                        });
                    }
                    Swal.fire({
                        icon: 'success',
                        title: 'Added to Cart!',
                        text: data.message,
                        showConfirmButton: false,
                        timer: 1500
                    });
                } else if (data.status === 'exists') {
                    // Update cart count for existing item
                    if (typeof data.cartCount !== 'undefined') {
                        updateCartBadges(data.cartCount);
                    }
                    Swal.fire({
                        icon: 'info',
                        title: 'Already in Cart',
                        text: `This item is already in your cart (Quantity: ${data.current_quantity})`,
                        showCancelButton: true,
                        confirmButtonText: 'View Cart',
                        cancelButtonText: 'Continue Shopping'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = getViewsPath('cart.php');
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: data.message
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: error.message || 'Failed to add item to cart. Please try again.'
                });
            })            .finally(() => {
                // Reset button state
                btn.disabled = false;
                // Check if it's a circular button or a normal button
                if(btn.classList.contains('rounded-circle')) {
                    btn.innerHTML = '<i class="fas fa-plus"></i>';
                } else {
                    btn.innerHTML = '<i class="fa fa-cart-plus me-1"></i> Add to Cart';
                }
            });
        }
    });

    // Handle remove from cart
    document.addEventListener('click', function(e) {
        if (e.target.closest('.cart-remove')) {
            e.preventDefault();
            const btn = e.target.closest('.cart-remove');
            const cartId = btn.value;
            Swal.fire({
                title: 'Remove Item?',
                text: "Are you sure you want to remove this item from your cart?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, remove it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading state
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
                    fetch(getControllerPath('cart_process.php'), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=remove&cart_id=${cartId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // Remove the item from DOM
                            const cartItem = btn.closest('.cart-card');
                            cartItem.remove();
                            if (data.cart_total) {
                                updateCartTotals(parseFloat(data.cart_total));
                            }
                            updateCartCount();
                            Swal.fire({
                                icon: 'success',
                                title: 'Removed!',
                                text: 'Item has been removed from your cart.',
                                showConfirmButton: false,
                                timer: 1500
                            });
                            // If cart is empty, show empty state and hide summary
                            const cartList = document.getElementById('cartList');
                            if (cartList && !cartList.querySelector('.cart-card')) {
                                cartList.innerHTML = `
                                    <div class="text-center py-5">
                                        <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                        <h4 class="text-muted">Your cart is empty</h4>
                                        <a href="${getViewsPath('shop.php')}" class="btn btn-laflora mt-3">
                                            <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
                                        </a>
                                    </div>
                                `;
                                // Hide the order summary section
                                const summary = document.querySelector('.cart-summary');
                                if (summary) summary.style.display = 'none';
                            }
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to remove item. Please try again.'
                        });
                    })
                    .finally(() => {
                        // Reset button state if item wasn't removed
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa fa-trash"></i>';
                    });
                }
            });
        }
    });

    // Function to update cart count in navbar
    function updateCartCount() {
        fetch(getControllerPath('get_cart_count.php'))
            .then(response => response.json())
            .then(data => {
                const cartCountElement = document.querySelector('.cart-count');
                if (cartCountElement) {
                    if (data.count > 0) {
                        cartCountElement.textContent = data.count;
                        cartCountElement.classList.remove('d-none');
                    } else {
                        cartCountElement.classList.add('d-none');
                    }
                }
            })
            .catch(error => console.error('Error updating cart count:', error));
    }

    // Handle clear cart
    const clearCartBtn = document.getElementById('clearCart');
    if (clearCartBtn) {
        clearCartBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            Swal.fire({
                title: 'Clear Cart?',
                text: "Are you sure you want to remove all items from your cart?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, clear it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(getControllerPath('cart_process.php'), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'action=clear'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // Update cart badges in the DOM
                            if (typeof data.cartCount !== 'undefined') {
                                updateCartBadges(data.cartCount);
                            }
                            location.reload();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to clear cart. Please try again.'
                        });
                    });
                }
            });
        });
    }
});
