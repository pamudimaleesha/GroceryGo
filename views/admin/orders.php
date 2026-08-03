<?php
session_start();
include '../../config/db.php';
require_once '../../config/helpers.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../signin.php');
    exit;
}

// Process status updates if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['update_status'];
    
    // Only allow processing and shipped status updates
    if (in_array($new_status, ['processing', 'shipped'])) {
        $stmt = $conn->prepare('UPDATE orders SET status = ? WHERE order_id = ? AND status NOT IN ("complete", "canceled")');
        if ($stmt->execute([$new_status, $order_id])) {
            $success_message = "Order status updated successfully!";
        }
    }
}

// Delete order if requested
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_order'])) {
    $order_id = $_POST['order_id'];
    
    // Start transaction
    $conn->beginTransaction();
    
    try {
        // Delete order items first
        $stmt = $conn->prepare('DELETE FROM order_items WHERE order_id = ?');
        $stmt->execute([$order_id]);
        
        // Then delete the order
        $stmt = $conn->prepare('DELETE FROM orders WHERE order_id = ?');
        $stmt->execute([$order_id]);
        
        // Commit transaction
        $conn->commit();
        $success_message = "Order deleted successfully!";
    } catch (Exception $e) {
        $conn->rollBack();
        $error_message = "Failed to delete order. Please try again.";
    }
}

// Fetch all orders with user details
$stmt = $conn->prepare('
    SELECT o.*, u.name as customer_name, u.email,
           GROUP_CONCAT(CONCAT(p.name, " (", oi.quantity, ")") SEPARATOR ", ") as product_names
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    LEFT JOIN order_items oi ON o.order_id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.id
    GROUP BY o.order_id
    ORDER BY o.order_date DESC
');
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - GroceryGo</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Fredoka+One&display=swap" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.5/dist/sweetalert2.min.css" rel="stylesheet">
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
            color: var(--dark);
        }
        
        .admin-wrapper {
            min-height: 100vh;
            background-color: #f8fafc;
            position: relative;
            overflow-x: hidden;
        }
        
        .main-content {
            transition: all 0.3s ease;
            padding: 2rem;
            position: relative;
            z-index: 1;
        }
        
        @media (min-width: 768px) {
            .main-content {
                margin-left: 25%;
                width: 75%;
            }
        }
        
        @media (min-width: 992px) {
            .main-content {
                margin-left: 16.666667%;
                width: 83.333333%;
            }
        }
        
        .page-header {
            position: relative;
            margin-bottom: 2.5rem;
            padding: 1.5rem 2rem;
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            color: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(47, 133, 90, 0.2);
            overflow: hidden;
        }
        
        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 100%;
            background-image: url("data:image/svg+xml,%3Csvg width='52' height='26' viewBox='0 0 52 26' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Cpath d='M10 10c0-2.21-1.79-4-4-4-3.314 0-6-2.686-6-6h2c0 2.21 1.79 4 4 4 3.314 0 6 2.686 6 6 0 2.21 1.79 4 4 4 3.314 0 6 2.686 6 6 0 2.21 1.79 4 4 4v2c-3.314 0-6-2.686-6-6 0-2.21-1.79-4-4-4-3.314 0-6-2.686-6-6zm25.464-1.95l8.486 8.486-1.414 1.414-8.486-8.486 1.414-1.414z' /%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            opacity: 0.2;
        }
        
        .welcome-title {
            font-weight: 700;
            margin-bottom: 0.5rem;
            font-size: 1.8rem;
            position: relative;
            z-index: 1;
        }
        
        .welcome-text {
            opacity: 0.9;
            font-weight: 300;
            max-width: 80%;
            position: relative;
            z-index: 1;
        }
        
        .welcome-icon {
            position: absolute;
            right: 2rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 4rem;
            opacity: 0.3;
        }
        
        /* Content Cards */
        .content-card {
            border: none;
            border-radius: 15px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            margin-bottom: 2rem;
            background-color: white;
        }
        
        .content-card-header {
            background-color: var(--light);
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .content-card-header h5 {
            margin-bottom: 0;
            font-weight: 600;
            color: var(--primary);
            font-size: 1.25rem;
        }
        
        /* Table Styles */
        .table-wrapper {
            background-color: white;
            border-radius: 15px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
        }
        
        .table {
            margin-bottom: 0;
            vertical-align: middle;
        }
        
        .table > :not(caption) > * > * {
            padding: 1rem 1.25rem;
        }
        
        .table thead th {
            background-color: var(--light);
            color: var(--primary);
            font-weight: 600;
            border-top: none;
            border-bottom: 1px solid var(--border-color);
            white-space: nowrap;
            font-size: 0.9rem;
        }
        
        .table tbody tr:hover {
            background-color: rgba(240, 255, 244, 0.5);
        }
        
        /* Order Status Badges */
        .badge-status {
            padding: 0.5rem 0.75rem;
            border-radius: 50rem;
            font-weight: 600;
            font-size: 0.775rem;
            display: inline-block;
            text-align: center;
        }
        
        .badge-pending {
            background-color: #FEF3C7; /* yellow-100 */
            color: #92400E; /* yellow-800 */
        }
        
        .badge-processing {
            background-color: #DBEAFE; /* blue-100 */
            color: #1E40AF; /* blue-800 */
        }
        
        .badge-shipped {
            background-color: #C7D2FE; /* indigo-100 */
            color: #3730A3; /* indigo-800 */
        }
        
        .badge-complete, .badge-delivered {
            background-color: #D1FAE5; /* green-100 */
            color: var(--primary);
        }
        
        .badge-canceled, .badge-cancelled {
            background-color: #FEE2E2; /* red-100 */
            color: #B91C1C; /* red-800 */
        }
        
        /* Alert styles */
        .alert {
            border-radius: 10px;
            border: none;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
        }
        
        .alert-success {
            background-color: rgba(56, 161, 105, 0.1);
            color: var(--primary);
            border-left: 4px solid var(--primary);
        }
        
        .alert-danger {
            background-color: rgba(229, 62, 62, 0.1);
            color: #e53e3e;
            border-left: 4px solid #e53e3e;
        }
        
        /* Form elements */
        .form-select {
            border-radius: 8px;
            border: 1px solid var(--border-color);
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            background-color: var(--input-bg);
            transition: all 0.3s ease;
        }
        
        .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(47, 133, 90, 0.25);
            outline: 0;
        }
        
        /* Action buttons */
        .btn-action {
            width: 36px;
            height: 36px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        
        .btn-outline-danger {
            color: #e53e3e;
            border-color: #e53e3e;
        }
        
        .btn-outline-danger:hover {
            background-color: #e53e3e;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(229, 62, 62, 0.2);
        }
        
        /* Customer info */
        .customer-info {
            display: flex;
            align-items: center;
        }
        
        .customer-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: var(--light);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 0.75rem;
        }
        
        .customer-name {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.125rem;
            line-height: 1.2;
        }
        
        .customer-email {
            font-size: 0.75rem;
            color: #718096; /* gray-500 */
        }
        
        /* Order items styling */
        .order-items {
            max-width: 280px;
        }
        
        .order-item {
            margin-bottom: 0.35rem;
            padding-bottom: 0.35rem;
            border-bottom: 1px dashed #E2E8F0;
            font-size: 0.875rem;
            color: var(--dark);
        }
        
        .order-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .order-amount {
            font-weight: 700;
            color: var(--primary);
        }
    </style>
</head>

<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <?php include_once('./includes/admin_nav.php'); ?>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1 class="welcome-title">Order Management</h1>
                    <p class="welcome-text">Track and manage customer orders</p>
                </div>
                <i class="fas fa-shopping-cart welcome-icon"></i>
            </div>
            
            <!-- Alerts -->
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> <?php echo htmlspecialchars($success_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> <?php echo htmlspecialchars($error_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>            <!-- Orders Table Card -->
            <div class="content-card">
                <div class="content-card-header">
                    <h5>
                        <i class="fas fa-clipboard-list me-2"></i> Customer Orders
                    </h5>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-light text-dark border">
                            <i class="fas fa-shopping-bag me-1"></i> Total: <?= count($orders) ?>
                        </span>
                    </div>
                </div>
                
                <div class="p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">                            <thead>
                                <tr>
                                    <th>ORDER #</th>
                                    <th>CUSTOMER</th>
                                    <th>DATE</th>
                                    <th>ITEMS</th>
                                    <th>TOTAL</th>
                                    <th>STATUS</th>
                                    <th>UPDATE STATUS</th>
                                    <th class="text-center">ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($orders)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <i class="fas fa-shopping-cart fa-3x mb-3 text-muted"></i>
                                            <p class="mb-0">No orders found</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td class="fw-bold">#<?= $order['order_id'] ?></td>
                                            <td>
                                                <div class="customer-info">
                                                    <div class="customer-avatar">
                                                        <i class="fas fa-user text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <div class="customer-name"><?= htmlspecialchars($order['customer_name']) ?></div>
                                                        <div class="customer-email"><?= htmlspecialchars($order['email']) ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="far fa-calendar-alt text-muted me-2"></i>
                                                    <?= date('M j, Y', strtotime($order['order_date'])) ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="order-items">
                                                    <?php 
                                                    $products = explode(', ', $order['product_names']);
                                                    foreach ($products as $index => $product): 
                                                        if ($index >= 3 && count($products) > 4): // Show only first 3 items if more than 4
                                                            ?>
                                                            <div class="text-muted small">
                                                                +<?= count($products) - 3 ?> more items...
                                                            </div>
                                                            <?php
                                                            break;
                                                        endif;
                                                    ?>
                                                        <div class="order-item">
                                                            <i class="fas fa-box-open me-1 text-muted"></i>
                                                            <?= htmlspecialchars($product) ?>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="order-amount">Rs. <?= number_format($order['total_amount'], 2) ?></div>
                                            </td>
                                            <td>
                                                <?php
                                                    $status_class = match($order['status']) {
                                                        'pending' => 'badge-pending',
                                                        'processing' => 'badge-processing',
                                                        'shipped' => 'badge-shipped',
                                                        'complete', 'delivered' => 'badge-complete',
                                                        'canceled', 'cancelled' => 'badge-canceled',
                                                        default => 'badge-secondary'
                                                    };
                                                    $status_icon = match($order['status']) {
                                                        'pending' => '<i class="fas fa-clock me-1"></i>',
                                                        'processing' => '<i class="fas fa-spinner me-1"></i>',
                                                        'shipped' => '<i class="fas fa-truck me-1"></i>',
                                                        'complete', 'delivered' => '<i class="fas fa-check me-1"></i>',
                                                        'canceled', 'cancelled' => '<i class="fas fa-ban me-1"></i>',
                                                        default => '<i class="fas fa-question-circle me-1"></i>'
                                                    };
                                                    $status_text = ucfirst($order['status']);
                                                ?>
                                                <span class="badge-status <?= $status_class ?>">
                                                    <?= $status_icon ?><?= $status_text ?>
                                                </span>
                                            </td>
                                            <!-- Separate column for status update -->
                                            <td>
                                                <?php if (!in_array($order['status'], ['complete', 'canceled', 'cancelled'])): ?>
                                                    <form method="POST" class="status-update-form" data-order-id="<?= $order['order_id'] ?>">
                                                        <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                                        <div class="input-group input-group-sm">
                                                            <span class="input-group-text bg-light"><i class="fas fa-exchange-alt"></i></span>
                                                            <select name="update_status" class="form-select py-0" style="font-size: 0.8rem">
                                                                <option value="">Update...</option>
                                                                <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                                                                <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                                            </select>
                                                        </div>
                                                    </form>                                                <?php else: ?>
                                                    <span class="text-muted small">
                                                        <i class="fas fa-lock me-1"></i> No changes allowed
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <!-- Separate column for delete action -->
                                            <td class="text-center">
                                                <button type="button" class="btn btn-outline-danger btn-action delete-order-btn" 
                                                        data-order-id="<?= $order['order_id'] ?>" 
                                                        title="Delete Order">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.5/dist/sweetalert2.all.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle status updates
        document.querySelectorAll('.status-update-form select').forEach(select => {
            const originalValue = select.value;
            
            select.addEventListener('change', function() {
                if (this.value && this.value !== originalValue) {
                    // Apply visual feedback
                    this.classList.add('border-primary');
                    const form = this.closest('form');
                    
                    // Submit form after brief delay for visual feedback
                    setTimeout(() => {
                        form.submit();
                    }, 300);
                }
            });
        });

        // Handle delete buttons
        document.querySelectorAll('.delete-order-btn').forEach(button => {
            button.addEventListener('click', function() {
                const orderId = this.dataset.orderId;
                
                Swal.fire({
                    title: 'Delete Order?',
                    text: 'This action cannot be undone. All order information will be permanently removed.',
                    icon: 'warning',
                    iconColor: '#e53e3e',
                    showCancelButton: true,
                    confirmButtonColor: '#e53e3e',
                    cancelButtonColor: '#718096',
                    confirmButtonText: 'Yes, delete order',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        confirmButton: 'btn btn-danger',
                        cancelButton: 'btn btn-outline-secondary'
                    },
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Create and submit form
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.style.display = 'none';

                        const orderIdInput = document.createElement('input');
                        orderIdInput.type = 'hidden';
                        orderIdInput.name = 'order_id';
                        orderIdInput.value = orderId;

                        const deleteInput = document.createElement('input');
                        deleteInput.type = 'hidden';
                        deleteInput.name = 'delete_order';
                        deleteInput.value = '1';

                        form.appendChild(orderIdInput);
                        form.appendChild(deleteInput);
                        document.body.appendChild(form);

                        form.submit();
                    }
                });
            });
        });
    });
    </script>
</body>
</html>
