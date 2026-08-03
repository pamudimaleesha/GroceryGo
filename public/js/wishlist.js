// Helper function to get the appropriate path based on current location
function getControllerPath(file) {
    const isIndex = window.location.pathname.endsWith('index.php') || window.location.pathname.endsWith('LaFlora/');
    return isIndex ? `controller/${file}` : `../controller/${file}`;
}

// Update wishlist count in the navbar
function updateWishlistCount(count = null) {
    // If count is provided, update directly
    if (count !== null) {
        updateWishlistBadge(count);
        return;
    }

    // Otherwise fetch the count
    fetch(getControllerPath('get_wishlist_count.php'))
        .then(response => response.json())
        .then(data => {
            updateWishlistBadge(data.count);
        })
        .catch(error => console.error('Error:', error));
}

// Helper function to update the wishlist badge
function updateWishlistBadge(count) {
    const wishlistCount = document.querySelector('.wishlist-count');
    if (wishlistCount) {
        wishlistCount.textContent = count;
        wishlistCount.style.display = count > 0 ? '' : 'none';
    }
}

// Remove product from wishlist page UI
function removeProductFromUI(button) {
    const productCard = button.closest('.col-sm-6, .col-md-4, .col-lg-3');
    if (productCard) {
        productCard.style.transition = 'opacity 0.3s ease-out';
        productCard.style.opacity = '0';
        setTimeout(() => {
            productCard.remove();
            
            // Check if there are any items left
            const remainingItems = document.querySelectorAll('.product-card');
            if (remainingItems.length === 0) {
                location.reload(); // Reload to show empty state
            }
        }, 300);
    }
}

// Toggle wishlist item
function toggleWishlist(productId, button, showConfirm = false) {
    // If showing confirmation and it's a remove action (from wishlist page)
    if (showConfirm) {
        Swal.fire({
            title: 'Remove from Wishlist?',
            text: 'Are you sure you want to remove this item from your wishlist?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e75480',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, remove it!'
        }).then((result) => {
            if (result.isConfirmed) {
                performWishlistAction(productId, button, true); // Pass true to indicate removal
            }
        });
    } else {
        performWishlistAction(productId, button);
    }
}

function performWishlistAction(productId, button, isRemoval = false) {
    // Check if the button already has the heart filled (added to wishlist)
    const isInWishlist = button.querySelector('i.fas') !== null;
    
    const formData = new FormData();
    formData.append('product_id', productId);
    
    // If already in wishlist or explicit removal requested, remove it
    if (isInWishlist || isRemoval) {
        formData.append('action', 'remove');
    }

    // Disable the button and show loading state
    button.disabled = true;
    if (button.classList.contains('btn-wishlist')) {
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    }

    fetch(getControllerPath('add_to_wishlist.php'), {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'error' && data.message === 'Please login to add items to wishlist') {
            Swal.fire({
                title: 'Login Required',
                text: data.message,
                icon: 'warning',
                confirmButtonText: 'Got it',
                confirmButtonColor: '#e75480'
            });
            return;
        }

        if (data.status === 'success') {
            // Handle removal UI update
            if (isRemoval && button.classList.contains('wishlist-remove')) {
                // Show success message for removal
                Swal.fire({
                    title: 'Removed!',
                    text: 'Item has been removed from your wishlist',
                    icon: 'success',
                    showConfirmButton: false,
                    timer: 1500
                });

                // Remove the item with animation
                removeProductFromUI(button);
            } else {
                // Update button state for toggle buttons                // Re-enable the button
                button.disabled = false;
                
                if (data.action === 'added') {
                    button.classList.add('active');
                    
                    // Update the heart icon
                    if (button.classList.contains('btn-wishlist')) {
                        button.innerHTML = '<i class="fas fa-heart"></i>';
                    } else {
                        button.innerHTML = '<i class="fa fa-heart me-1 text-danger"></i> Remove from Wishlist';
                    }

                    // Show success message for add action
                    Swal.fire({
                        title: 'Added!',
                        text: data.message,
                        icon: 'success',
                        showConfirmButton: false,
                        timer: 1500
                    });
                } else {
                    button.classList.remove('active');
                    
                    // Update the heart icon
                    if (button.classList.contains('btn-wishlist')) {
                        button.innerHTML = '<i class="far fa-heart"></i>';
                    } else {
                        button.innerHTML = '<i class="fa fa-heart me-1"></i> Add to Wishlist';
                    }
                }
            }
            
            // Update count from response or fetch new count
            if (typeof data.wishlistCount !== 'undefined') {
                updateWishlistCount(data.wishlistCount);
                
                // If wishlist becomes empty, reload the page to show empty state
                if (data.wishlistCount === 0 && isRemoval) {
                    setTimeout(() => {
                        location.reload();
                    }, 1600); // Increased delay to show the success message
                }
            } else {
                updateWishlistCount();
            }
        }
    })    .catch(error => {
        console.error('Error:', error);
        
        // Re-enable the button and restore original state
        button.disabled = false;
        const isCurrentlyInWishlist = button.querySelector('i.fas') !== null;
        
        if (button.classList.contains('btn-wishlist')) {
            button.innerHTML = `<i class="${isCurrentlyInWishlist ? 'fas' : 'far'} fa-heart"></i>`;
        } else {
            // For button with text
            if (isCurrentlyInWishlist) {
                button.classList.add('active');
                button.innerHTML = '<i class="fa fa-heart me-1 text-danger"></i> Remove from Wishlist';
            } else {
                button.classList.remove('active');
                button.innerHTML = '<i class="fa fa-heart me-1"></i> Add to Wishlist';
            }
        }
        
        // Show error only if not removing
        if (!isRemoval) {
            Swal.fire({
                title: 'Error!',
                text: 'Failed to update wishlist. Please try again.',
                icon: 'error',
                showConfirmButton: false,
                timer: 1500
            });
        }
    });
}

// Clear all items from wishlist
function clearWishlist() {
    Swal.fire({
        title: 'Clear Wishlist?',
        text: "This will remove all items from your wishlist!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e75480',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, clear it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading state
            Swal.fire({
                title: 'Clearing...',
                text: 'Please wait while we clear your wishlist.',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch(getControllerPath('add_to_wishlist.php'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'clear=1'
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        title: 'Cleared!',
                        text: 'Your wishlist has been cleared.',
                        icon: 'success',
                        showConfirmButton: false,
                        timer: 1500
                    });

                    // Update count
                    updateWishlistCount(0);
                    
                    // Reload page to show empty state
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    throw new Error(data.message || 'Failed to clear wishlist');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: error.message || 'Failed to clear wishlist. Please try again.',
                    icon: 'error'
                });
            });
        }
    });
}

// Initialize wishlist functionality
document.addEventListener('DOMContentLoaded', function() {
    // Update initial wishlist count
    updateWishlistCount();    // Handle wishlist toggle buttons
    document.addEventListener('click', function(e) {
        // Get the closest button with either class
        const btn = e.target.closest('.add-to-wishlist-btn, .btn-wishlist');
        
        if (btn) {
            e.preventDefault();
            // Only proceed if it's a button with a product ID
            if (btn.tagName === 'BUTTON' && btn.dataset.productId) {
                const productId = btn.dataset.productId;
                toggleWishlist(productId, btn);
            }
        }
    });// Handle remove from wishlist buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('.wishlist-remove')) {
            e.preventDefault();
            const btn = e.target.closest('.wishlist-remove');
            const productId = btn.closest('.card').querySelector('.add-to-cart-btn').dataset.productId;
            toggleWishlist(productId, btn, true); // Pass true to show confirmation
        }
    });

    // Handle clear all button
    const clearWishlistBtn = document.getElementById('clearWishlist');
    if (clearWishlistBtn) {
        clearWishlistBtn.addEventListener('click', clearWishlist);
    }
});
