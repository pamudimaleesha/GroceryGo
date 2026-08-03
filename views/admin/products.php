<?php
session_start();
require_once '../../config/db.php';
require_once '../../config/helpers.php';

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);
    $stmt = $conn->prepare('DELETE FROM products WHERE id = :id');
    $stmt->bindParam(':id', $delete_id, PDO::PARAM_INT);
    $stmt->execute();
}

// Fetch all products with category name
$products = [];
$sql = 'SELECT p.id, p.name, c.category_name, p.price, p.qty, p.image, p.created_at FROM products p LEFT JOIN category c ON p.category_id = c.id ORDER BY p.id DESC';
$stmt = $conn->query($sql);
if ($stmt) {
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management - GroceryGo</title>
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
        .product-table {
            margin-bottom: 0;
        }
        
        .product-table th {
            background-color: var(--light);
            color: var(--primary);
            font-weight: 600;
            border-color: var(--border-color);
            padding: 1rem;
            white-space: nowrap;
        }
        
        .product-table td {
            padding: 1rem;
            border-color: var(--border-color);
            vertical-align: middle;
        }
        
        .product-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }
        
        .category-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.35rem 0.75rem;
            border-radius: 50px;
            font-weight: 500;
            font-size: 0.75rem;
        }
        
        .product-name {
            font-weight: 500;
            color: var(--dark);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            max-width: 200px;
        }
        
        /* Button styles */
        .btn-add-product {
            background: var(--primary);
            color: white;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 4px 6px rgba(47, 133, 90, 0.2);
            text-decoration: none;
        }
        
        .btn-add-product:hover {
            background: var(--secondary);
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(47, 133, 90, 0.3);
            color: white;
        }
        
        .btn-add-product i {
            margin-right: 0.5rem;
        }
        
        .action-btn {
            width: 35px;
            height: 35px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: all 0.3s ease;
            margin: 0 0.2rem;
        }
        
        .btn-edit {
            color: #3182ce;
            background-color: rgba(49, 130, 206, 0.1);
            border: none;
        }
        
        .btn-edit:hover {
            background-color: #3182ce;
            color: white;
        }
        
        .btn-delete {
            color: #e53e3e;
            background-color: rgba(229, 62, 62, 0.1);
            border: none;
        }
        
        .btn-delete:hover {
            background-color: #e53e3e;
            color: white;
        }
        
        /* Empty state */
        .empty-state {
            padding: 3rem;
            text-align: center;
        }
        
        .empty-state-icon {
            font-size: 3rem;
            color: var(--border-color);
            margin-bottom: 1rem;
        }
        
        .empty-state-text {
            color: #718096;
            font-weight: 500;
            margin-bottom: 1.5rem;
        }
        
        /* Stock status */
        .stock-badge {
            padding: 0.35rem 0.75rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .stock-available {
            background-color: rgba(47, 133, 90, 0.1);
            color: var(--primary);
        }
        
        .stock-low {
            background-color: rgba(246, 224, 94, 0.2);
            color: #975a16;
        }
        
        .stock-out {
            background-color: rgba(229, 62, 62, 0.1);
            color: #e53e3e;
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
                    <h1 class="welcome-title">Product Management</h1>
                    <p class="welcome-text">Manage your store's product inventory</p>
                </div>
                <i class="fas fa-boxes welcome-icon"></i>
            </div>
            
            <!-- Content -->
            <div class="content-card">
                <div class="content-card-header">
                    <h5>All Products</h5>
                    <a href="./product_add.php" class="btn-add-product">
                        <i class="fas fa-plus"></i> Add New Product
                    </a>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($products)): ?>
                    <div class="table-responsive">
                        <table class="table product-table">
                            <thead>
                                <tr>
                                    <th style="width: 60px;">ID</th>
                                    <th style="width: 80px;">Image</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Created</th>
                                    <th style="width: 100px;" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $prod):
                                    $categoryColor = generateCategoryColor($prod['category_name']);
                                    $stockStatus = '';
                                    $stockClass = '';
                                    
                                    if($prod['qty'] <= 0) {
                                        $stockStatus = 'Out of Stock';
                                        $stockClass = 'stock-out';
                                    } elseif($prod['qty'] < 10) {
                                        $stockStatus = 'Low Stock';
                                        $stockClass = 'stock-low';
                                    } else {
                                        $stockStatus = 'Available';
                                        $stockClass = 'stock-available';
                                    }
                                ?>
                                    <tr>
                                        <td class="text-center"><?php echo htmlspecialchars($prod['id']); ?></td>
                                        <td>
                                            <?php if (!empty($prod['image'])): ?>
                                                <img src="<?php echo '../../uploads/products/' . htmlspecialchars($prod['image']); ?>" alt="Product Image" class="product-img">
                                            <?php else: ?>
                                                <div class="product-img d-flex align-items-center justify-content-center bg-light">
                                                    <i class="fas fa-image text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="product-name"><?php echo htmlspecialchars($prod['name']); ?></div>
                                        </td>
                                        <td>
                                            <span class="category-badge" style="background-color: <?php echo $categoryColor[0]; ?>; color: <?php echo $categoryColor[1]; ?>">
                                                <?php echo htmlspecialchars($prod['category_name']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong>Rs. <?php echo number_format($prod['price'], 2); ?></strong>
                                        </td>
                                        <td>
                                            <span class="stock-badge <?php echo $stockClass; ?>">
                                                <?php echo $stockStatus; ?>
                                                <span class="ms-1">(<?php echo $prod['qty']; ?>)</span>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $date = new DateTime($prod['created_at']);
                                            echo $date->format('M d, Y');
                                            ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="product_add.php?id=<?php echo $prod['id']; ?>" class="action-btn btn-edit" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="post" action="" style="display:inline;" class="delete-product-form">
                                                <input type="hidden" name="delete_id" value="<?php echo $prod['id']; ?>">
                                                <button type="submit" class="action-btn btn-delete delete-product-btn" title="Delete">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-box-open"></i>
                        </div>
                        <h4>No Products Found</h4>
                        <p class="empty-state-text">You haven't added any products yet.</p>
                        <a href="./product_add.php" class="btn-add-product">
                            <i class="fas fa-plus"></i> Add First Product
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Attach event listeners to all delete forms
            document.querySelectorAll('.delete-product-form').forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const productName = this.closest('tr').querySelector('.product-name').textContent;
                    
                    Swal.fire({
                        title: 'Delete this product?',
                        text: `Are you sure you want to delete "${productName}"?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#2F855A',
                        cancelButtonColor: '#e53e3e',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                            Swal.fire({
                                title: 'Deleted!',
                                text: 'Product has been deleted.',
                                icon: 'success',
                                confirmButtonColor: '#2F855A'
                            });
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>