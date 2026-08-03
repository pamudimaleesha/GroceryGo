<?php
    session_start();
    include '../../config/db.php';

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../signin.php');
        exit;
    }

    $user_id = $_SESSION['user_id'];

    // Process status updates if form is submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
        $order_id = $_POST['order_id'];
        $new_status = $_POST['update_status'];
        
        // Only allow delivered or cancelled status updates
        if (in_array($new_status, ['complete', 'canceled'])) {
            $stmt = $conn->prepare('UPDATE orders SET status = ? WHERE order_id = ? AND user_id = ? AND status NOT IN ("delivered", "cancelled")');
            if ($stmt->execute([$new_status, $order_id, $user_id])) {
                $success_message = "Order status updated successfully!";
            }
        }
    }

    // Fetch user's orders with item details and quantities
    $stmt = $conn->prepare('
        SELECT o.*, 
               GROUP_CONCAT(CONCAT(p.name, " (", oi.quantity, ")") SEPARATOR ", ") as product_names
        FROM orders o
        LEFT JOIN order_items oi ON o.order_id = oi.order_id
        LEFT JOIN products p ON oi.product_id = p.id
        WHERE o.user_id = ?
        GROUP BY o.order_id
        ORDER BY o.order_date DESC
    ');
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - GroceryGo.lk</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Fredoka+One&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.5/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary: #2F855A; /* Tailwind green-700 */
            --secondary: #38A169; /* Tailwind green-600 */
            --light: #F0FFF4; /* Tailwind green-50 */
            --accent: #F6E05E; /* Tailwind yellow-300 */
            --dark: #1A202C; /* Tailwind gray-800 */
            --border-color: #E2E8F0; /* Tailwind gray-200 */
            --input-bg: #F9FAFB; /* Tailwind gray-50 */
            --card-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.03);
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8fafc;
            min-height: 100vh;
            color: var(--dark);
        }
        
        .content-wrapper {
            background-color: #fff;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            min-height: 100vh;
        }
        
        .content {
            padding: 2rem;
        }
        
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary);
            border-color: var(--secondary);
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .bg-primary {
            background-color: var(--primary) !important;
        }
        
        .text-primary {
            color: var(--primary) !important;
        }
        
        .section-title {
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
            border-left: 4px solid var(--primary);
            padding-left: 12px;
        }
        
        /* Card Styling */
        .order-card {
            border: none;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
            overflow: hidden;
        }
        
        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        
        .order-card .card-header {
            background-color: var(--light);
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 1.25rem;
        }
        
        .order-card .card-header h5 {
            margin-bottom: 0;
            font-weight: 600;
            color: var(--primary);
            font-size: 1.1rem;
        }
        
        .order-card .card-body {
            padding: 1.5rem;
        }
        
        /* Table Styling */
        .table {
            border-color: var(--border-color);
            margin-bottom: 0;
        }
        
        .orders-table {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
        }
        
        .table > :not(caption) > * > * {
            padding: 1rem 0.75rem;
            vertical-align: middle;
        }
        
        .table-hover tbody tr {
            transition: all 0.2s ease-in-out;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(47, 133, 90, 0.04);
            transform: translateX(3px);
        }
        
        .table thead th {
            background-color: var(--light);
            color: var(--primary);
            font-weight: 600;
            border-bottom: 2px solid var(--border-color);
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }
        
        /* Status Badge Styling */
        .badge {
            font-weight: 500;
            padding: 0.5em 0.8em;
            border-radius: 6px;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }
        
        .badge:hover {
            transform: translateY(-2px);
        }
        
        .badge-rounded-pill {
            border-radius: 50rem;
            padding-right: 0.8em;
            padding-left: 0.8em;
        }
        
        /* Status Select Styling */
        .status-select {
            border: 2px solid var(--border-color);
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            background-color: var(--input-bg);
            transition: all 0.3s ease;
            width: auto;
            min-width: 160px;
            font-weight: 500;
        }
        
        .status-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(47, 133, 90, 0.15);
            outline: none;
            background-color: #fff;
        }
        
        .status-select:hover {
            border-color: var(--secondary);
        }

        /* Action Buttons */
        .view-order-btn {
            color: var(--primary) !important;
            background-color: var(--light);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            padding: 0;
            margin: 0;
            font-size: 1rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.08);
            border: 2px solid transparent;
        }

        .view-order-btn:hover {
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
            background-color: var(--primary);
            color: white !important;
            border-color: var(--light);
        }
        
        /* Order Items Display */
        .item-list {
            max-height: 120px;
            overflow-y: auto;
            padding-right: 5px;
            border-radius: 8px;
            background-color: var(--light);
            padding: 10px;
        }
        
        .item-list::-webkit-scrollbar {
            width: 5px;
        }
        
        .item-list::-webkit-scrollbar-track {
            background: var(--light);
            border-radius: 10px;
        }
        
        .item-list::-webkit-scrollbar-thumb {
            background-color: var(--primary);
            border-radius: 10px;
        }
        
        .item-list .item {
            padding: 8px 10px;
            border-radius: 6px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            font-size: 0.9rem;
            transition: all 0.2s ease;
            background-color: white;
            margin-bottom: 5px;
        }
        
        .item-list .item:hover {
            transform: translateX(3px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            border-left: 3px solid var(--primary);
        }
        
        .item-list .item:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        
        .item-quantity {
            color: #64748b;
            font-size: 0.85em;
            font-weight: 600;
            background-color: rgba(0,0,0,0.05);
            padding: 2px 6px;
            border-radius: 4px;
            margin-left: 5px;
        }
        
        /* Form Elements */
        .status-update-form {
            margin: 0;
        }
        
        /* Order ID formatting */
        .order-id {
            font-family: 'Courier New', monospace;
            font-weight: 600;
            background: var(--light);
            padding: 6px 10px;
            border-radius: 6px;
            color: var(--primary);
            font-size: 0.9rem;
            transition: all 0.2s ease;
            border-bottom: 2px solid var(--primary);
        }
        
        .order-id:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 10px rgba(47, 133, 90, 0.15);
        }
        
        /* Price formatting */
        .price {
            font-weight: 600;
            color: var(--primary);
            background: var(--light);
            padding: 5px 10px;
            border-radius: 6px;
            transition: all 0.2s ease;
        }
        
        .price:hover {
            transform: scale(1.05);
        }
        
        /* Empty state styling */
        .empty-state {
            text-align: center;
            padding: 4rem 1rem;
            background-color: var(--light);
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        
        .empty-state:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        
        .empty-state i {
            font-size: 4rem;
            color: var(--primary);
            margin-bottom: 1.5rem;
            opacity: 0.7;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));
        }
        
        .empty-state h4 {
            color: var(--dark);
            margin-bottom: 1rem;
            font-weight: 600;
        }
        
        .empty-state p {
            color: #64748b;
            margin-bottom: 1.5rem;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.6;
        }
        
        /* Order stats badges */
        .order-stats .badge {
            display: flex;
            align-items: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            font-weight: 500;
        }
        
        .order-stats .badge i {
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="container-fluid">
            <div class="row content-wrapper">
                <!-- Sidebar -->
            <?php include('./includes/user_nav.php'); ?>

                <!-- Main Content -->
                <div class="col-md-9 col-lg-9 content">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary rounded-circle p-3 me-3 text-white shadow-sm">
                                <i class="fas fa-clipboard-list fa-2x"></i>
                            </div>
                            <h2 class="section-title mb-0">My Orders</h2>
                        </div>
                        <div class="order-stats d-flex gap-3">
                            <div class="badge badge-rounded-pill bg-light text-primary px-3 py-2 shadow-sm">
                                <i class="fas fa-shopping-bag"></i>
                                <span class="ms-1">Total Orders: <?= count($orders) ?></span>
                            </div>
                            <?php if(!empty($orders)): ?>
                            <div class="badge badge-rounded-pill bg-light text-success px-3 py-2 shadow-sm">
                                <i class="fas fa-calendar-alt"></i>
                                <span class="ms-1">Last Order: <?= date('M d, Y', strtotime($orders[0]['order_date'])) ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success alert-dismissible fade show shadow-sm border-start border-4 animate__animated animate__fadeIn" role="alert">
                            <div class="d-flex align-items-center">
                                <div class="me-3 bg-success bg-opacity-25 p-2 rounded-circle">
                                    <i class="fas fa-check-circle fs-4 text-success"></i>
                                </div>
                                <div>
                                    <h5 class="alert-heading mb-1">Success!</h5>
                                    <p class="mb-0"><?php echo htmlspecialchars($success_message); ?></p>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Orders Card -->
                    <div class="card order-card">
                        <div class="card-header">
                            <div class="d-flex align-items-center justify-content-between">
                                <h5 class="mb-0 d-flex align-items-center">
                                    <span class="bg-primary text-white p-2 rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        <i class="fas fa-shopping-bag"></i>
                                    </span>
                                    Order History
                                </h5>
                                <div class="badge bg-primary text-white px-3 py-2 shadow-sm">
                                    <i class="fas fa-receipt me-1"></i>
                                    <span class="ms-1"><?php echo count($orders); ?> Orders</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($orders)): ?>
                                <div class="empty-state">
                                    <div class="d-inline-block mb-4 p-3 rounded-circle bg-white shadow">
                                        <i class="fas fa-shopping-cart"></i>
                                    </div>
                                    <h4>No Orders Yet</h4>
                                    <p>You haven't placed any orders yet. Start shopping to see your order history here.</p>
                                    <a href="../shop.php" class="btn btn-primary btn-lg px-4 py-2 shadow">
                                        <i class="fas fa-shopping-basket me-2"></i> Browse Products
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table align-middle table-hover orders-table">
                                        <thead>
                                            <tr>
                                                <th><i class="fas fa-hashtag me-1"></i> Order #</th>
                                                <th><i class="fas fa-calendar me-1"></i> Date</th>
                                                <th><i class="fas fa-box me-1"></i> Items</th>
                                                <th><i class="fas fa-coins me-1"></i> Total</th>
                                                <th><i class="fas fa-flag me-1"></i> Status</th>
                                                <th><i class="fas fa-tools me-1"></i> Action</th>
                                                <th><i class="fas fa-search me-1"></i> View</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($orders as $order): ?>
                                                <tr>
                                                    <td><span class="order-id">#<?php echo $order['order_id']; ?></span></td>
                                                    <td>
                                                        <div class="d-flex flex-column">
                                                            <span class="fw-medium"><?php echo date('M d, Y', strtotime($order['order_date'])); ?></span>
                                                            <small class="text-muted"><?php echo date('h:i A', strtotime($order['order_date'])); ?></small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="item-list">
                                                            <?php
                                                            $products = explode(', ', $order['product_names']);
                                                            foreach ($products as $product) {
                                                                // Extract product name and quantity
                                                                if (preg_match('/(.+) \((\d+)\)/', $product, $matches)) {
                                                                    $productName = $matches[1];
                                                                    $quantity = $matches[2];
                                                                    echo '<div class="item">' . 
                                                                         '<i class="fas fa-shopping-basket me-2 text-primary"></i>' .
                                                                         htmlspecialchars($productName) . 
                                                                         '<span class="item-quantity">x' . htmlspecialchars($quantity) . '</span>' . 
                                                                         '</div>';
                                                                } else {
                                                                    echo '<div class="item">' . htmlspecialchars($product) . '</div>';
                                                                }
                                                            }
                                                            ?>
                                                        </div>
                                                    </td>
                                                    <td><span class="price">Rs. <?php echo number_format($order['total_amount'], 2); ?></span></td>
                                                    <td>
                                                        <?php
                                                            $status_class = match($order['status']) {
                                                                'pending' => 'bg-warning text-dark',
                                                                'processing' => 'bg-info text-dark',
                                                                'shipped' => 'bg-primary',
                                                                'complete', 'delivered' => 'bg-success',
                                                                'canceled', 'cancelled' => 'bg-danger',
                                                                default => 'bg-secondary'
                                                            };
                                                            $status_text = ucfirst($order['status']);
                                                            $status_icon = match($order['status']) {
                                                                'pending' => 'clock',
                                                                'processing' => 'spinner',
                                                                'shipped' => 'truck',
                                                                'complete', 'delivered' => 'check-circle',
                                                                'canceled', 'cancelled' => 'times-circle',
                                                                default => 'info-circle'
                                                            };
                                                        ?>
                                                        <span class="badge badge-rounded-pill <?php echo $status_class; ?>">
                                                            <i class="fas fa-<?php echo $status_icon; ?> me-1"></i>
                                                            <span class="ms-1"><?php echo $status_text; ?></span>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if ($order['status'] !== 'complete' && $order['status'] !== 'canceled' && $order['status'] !== 'delivered' && $order['status'] !== 'cancelled'): ?>
                                                        <form method="POST" class="status-update-form" data-order-id="<?php echo $order['order_id']; ?>">
                                                            <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                                            <div class="input-group">
                                                                <span class="input-group-text bg-light border-end-0">
                                                                    <i class="fas fa-edit text-primary"></i>
                                                                </span>
                                                                <select name="update_status" class="form-select form-select-sm status-select border-start-0">
                                                                    <option value="">Update status...</option>
                                                                    <option value="complete">✓ Mark as Received</option>
                                                                    <option value="canceled">✗ Cancel Order</option>
                                                                </select>
                                                            </div>
                                                        </form>
                                                        <?php else: ?>
                                                            <span class="badge bg-light text-secondary px-3 py-2">
                                                                <i class="fas fa-lock me-1"></i>
                                                                <span class="ms-1">No actions available</span>
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <a href="../order_confirmation.php?order_id=<?php echo $order['order_id']; ?>" 
                                                           class="view-order-btn shadow-sm" 
                                                           title="View Order Details">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.5/dist/sweetalert2.all.min.js"></script>
</body>
</html>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit the form when selection changes
    document.querySelectorAll('.status-update-form select').forEach(select => {
        select.addEventListener('change', function() {
            if (this.value) {
                this.closest('form').submit();
            }
        });
    });

    document.querySelectorAll('.status-update-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(form);
            const orderId = form.dataset.orderId;
            const newStatus = formData.get('update_status');
            
            if (!newStatus) return; // Don't submit if no status selected
            
            // Show loading spinner
            const submitCell = form.closest('td');
            const originalContent = submitCell.innerHTML;
            submitCell.innerHTML = '<div class="d-flex justify-content-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
            
            fetch(form.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.ok)
            .then(success => {
                if (success) {
                    // Update the status badge
                    const row = form.closest('tr');
                    const statusBadge = row.querySelector('.badge');
                    let newClass = '';
                    let statusText = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
                    let iconName = '';
                    
                    switch(newStatus) {
                        case 'pending':
                            newClass = 'bg-warning text-dark';
                            iconName = 'clock';
                            break;
                        case 'processing':
                            newClass = 'bg-info text-dark';
                            iconName = 'spinner';
                            break;
                        case 'shipped':
                            newClass = 'bg-primary';
                            iconName = 'truck';
                            break;
                        case 'complete':
                        case 'delivered':
                            newClass = 'bg-success';
                            iconName = 'check-circle';
                            break;
                        case 'canceled':
                        case 'cancelled':
                            newClass = 'bg-danger';
                            iconName = 'times-circle';
                            break;
                        default:
                            newClass = 'bg-secondary';
                            iconName = 'info-circle';
                    }
                    
                    // Update with animation
                    statusBadge.style.transform = 'scale(1.2)';
                    statusBadge.style.opacity = '0.7';
                    
                    setTimeout(() => {
                        statusBadge.className = 'badge badge-rounded-pill ' + newClass;
                        statusBadge.innerHTML = `<i class="fas fa-${iconName} me-1"></i><span class="ms-1">${statusText}</span>`;
                        statusBadge.style.transform = 'scale(1)';
                        statusBadge.style.opacity = '1';
                    }, 300);
                    
                    // If order is complete or canceled, replace form with "No actions available"
                    if (newStatus === 'complete' || newStatus === 'canceled') {
                        setTimeout(() => {
                            submitCell.innerHTML = `<span class="badge bg-light text-secondary px-3 py-2">
                                <i class="fas fa-lock me-1"></i>
                                <span class="ms-1">No actions available</span>
                            </span>`;
                        }, 500);
                    } else {
                        // Restore form
                        setTimeout(() => {
                            submitCell.innerHTML = originalContent;
                            // Reattach event listener to the new select element
                            const newSelect = submitCell.querySelector('select');
                            if (newSelect) {
                                newSelect.addEventListener('change', function() {
                                    if (this.value) {
                                        this.closest('form').submit();
                                    }
                                });
                            }
                        }, 500);
                    }
                    
                    // Show success message with SweetAlert2
                    Swal.fire({
                        icon: 'success',
                        title: 'Order Updated',
                        text: `Order #${orderId} status changed to ${statusText}`,
                        showConfirmButton: false,
                        timer: 2000,
                        toast: true,
                        position: 'top-end'
                    });
                }
            })
            .catch(error => {
                console.error('Error updating status:', error);
                // Restore form
                submitCell.innerHTML = originalContent;
                // Show error message
                Swal.fire({
                    icon: 'error',
                    title: 'Update Failed',
                    text: 'Failed to update order status. Please try again.',
                });
            });
        });
    });

    // Add hover effects to table rows
    document.querySelectorAll('.orders-table tbody tr').forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.querySelector('.view-order-btn')?.classList.add('animate__animated', 'animate__pulse');
        });
        
        row.addEventListener('mouseleave', function() {
            this.querySelector('.view-order-btn')?.classList.remove('animate__animated', 'animate__pulse');
        });
    });
});
</script>
