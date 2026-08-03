<?php
session_start();
require_once '../../config/db.php';
require_once '../../config/helpers.php';

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);
    $stmt = $conn->prepare('DELETE FROM category WHERE id = :id');
    $stmt->bindParam(':id', $delete_id, PDO::PARAM_INT);
    $stmt->execute();
}

// Fetch all categories
$categories = [];
$stmt = $conn->query('SELECT id, category_name FROM category ORDER BY id ASC');
if ($stmt) {
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Count products per category
$categoryProductCounts = [];
$stmt = $conn->query('SELECT category_id, COUNT(*) as product_count FROM products GROUP BY category_id');
if ($stmt) {
    $counts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($counts as $count) {
        $categoryProductCounts[$count['category_id']] = $count['product_count'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories Management - GroceryGo</title>
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
        .category-table {
            margin-bottom: 0;
        }

        .category-table th {
            background-color: var(--light);
            color: var(--primary);
            font-weight: 600;
            border-color: var(--border-color);
            padding: 1rem;
        }

        .category-table td {
            padding: 1rem;
            border-color: var(--border-color);
            vertical-align: middle;
        }

        .category-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 500;
            font-size: 0.875rem;
        }

        .category-count {
            background-color: var(--light);
            color: var(--primary);
            border-radius: 50px;
            padding: 0.2rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
            margin-left: 0.5rem;
        }

        /* Button styles */
        .btn-add-category {
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

        .btn-add-category:hover {
            background: var(--secondary);
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(47, 133, 90, 0.3);
            color: white;
        }

        .btn-add-category i {
            margin-right: 0.5rem;
        }

        .action-btn {
            width: 38px;
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: all 0.3s ease;
            margin: 0 0.2rem;
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
                    <h1 class="welcome-title">Categories Management</h1>
                    <p class="welcome-text">Organize your products with categories</p>
                </div>
                <i class="fas fa-layer-group welcome-icon"></i>
            </div>

            <!-- Content -->
            <div class="content-card">
                <div class="content-card-header">
                    <h5>All Categories</h5>
                    <a href="./category_add.php" class="btn-add-category">
                        <i class="fas fa-plus"></i> Add New Category
                    </a>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($categories)): ?>
                    <div class="table-responsive">
                        <table class="table category-table">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">ID</th>
                                    <th>Category Name</th>
                                    <th style="width: 150px;">Products</th>
                                    <th style="width: 100px;" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $cat): 
                                    $categoryColor = generateCategoryColor($cat['category_name']);
                                    $productCount = isset($categoryProductCounts[$cat['id']]) ? $categoryProductCounts[$cat['id']] : 0;
                                ?>
                                    <tr>
                                        <td class="text-center"><?php echo htmlspecialchars($cat['id']); ?></td>
                                        <td>
                                            <span class="category-badge" style="background-color: <?php echo $categoryColor[0]; ?>; color: <?php echo $categoryColor[1]; ?>">
                                                <?php echo htmlspecialchars($cat['category_name']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="category-count"><?php echo $productCount; ?> products</span>
                                        </td>
                                        <td class="text-center">
                                            <form method="post" action="" style="display:inline;">
                                                <input type="hidden" name="delete_id" value="<?php echo $cat['id']; ?>">
                                                <button type="submit" class="action-btn btn-delete" title="Delete"><i class="fas fa-trash-alt"></i></button>
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
                            <i class="fas fa-layer-group"></i>
                        </div>
                        <h4>No Categories Found</h4>
                        <p class="empty-state-text">You haven't created any categories yet.</p>
                        <a href="./category_add.php" class="btn-add-category">
                            <i class="fas fa-plus"></i> Add First Category
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
            document.querySelectorAll('form').forEach(function(form) {
                if (form.querySelector('input[name="delete_id"]')) {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        Swal.fire({
                            title: 'Are you sure?',
                            text: 'You won\'t be able to revert this!',
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
                                    text: 'Category has been deleted.',
                                    icon: 'success',
                                    confirmButtonColor: '#2F855A'
                                });
                            }
                        });
                    });
                }
            });
        });
    </script>
</body>

</html>