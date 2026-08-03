<?php
// Include database connection
session_start();
require_once '../../config/db.php';
require_once '../../config/helpers.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['category_name'])) {
    $category_name = trim($_POST['category_name']);
    if (!empty($category_name)) {
        try {
            $stmt = $conn->prepare('INSERT INTO category (category_name) VALUES (:name)');
            $stmt->bindParam(':name', $category_name, PDO::PARAM_STR);
            if ($stmt->execute()) {
                $success = 'Category added successfully!';
            } else {
                $error = 'Error adding category.';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    } else {
        $error = 'Category name cannot be empty.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Category - GroceryGo</title>
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
        }
        
        .content-card-header h5 {
            margin-bottom: 0;
            font-weight: 600;
            color: var(--primary);
            font-size: 1.25rem;
        }
        
        /* Form Styles */
        .form-control {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 0.75rem 1rem;
            background-color: var(--input-bg);
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(47, 133, 90, 0.2);
            border-color: var(--primary);
        }
        
        .form-label {
            color: var(--dark);
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        
        /* Button styles */
        .btn-back {
            background-color: var(--light);
            color: var(--primary);
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
            text-decoration: none;
        }
        
        .btn-back:hover {
            background-color: #E6FFF4;
            transform: translateY(-2px);
            color: var(--primary);
        }
        
        .btn-save {
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
        }
        
        .btn-save:hover {
            background: var(--secondary);
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(47, 133, 90, 0.3);
            color: white;
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
                    <h1 class="welcome-title">Add New Category</h1>
                    <p class="welcome-text">Create a new category for your products</p>
                </div>
                <i class="fas fa-folder-plus welcome-icon"></i>
            </div>
            
            <!-- Content -->
            <div class="content-card">
                <div class="content-card-header d-flex justify-content-between align-items-center">
                    <h5>Category Information</h5>
                    <a href="categories.php" class="btn-back">
                        <i class="fas fa-arrow-left me-2"></i> Back to Categories
                    </a>
                </div>
                <div class="card-body p-4">
                    <form method="post" action="">
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                            </div>
                        <?php elseif (!empty($error)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mb-4">
                            <label for="categoryName" class="form-label">Category Name</label>
                            <input type="text" class="form-control" id="categoryName" name="category_name" placeholder="Enter category name" required autocomplete="off">
                            <div class="form-text text-muted">Choose a clear and concise name for your category</div>
                        </div>
                        
                        <div class="mt-4 text-end">
                            <button type="submit" class="btn-save">
                                <i class="fas fa-save me-2"></i>Save Category
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>