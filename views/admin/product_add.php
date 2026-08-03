<?php
session_start();
require_once '../../config/db.php';
require_once '../../config/helpers.php';

    $success = '';
    $error = '';
    $edit_mode = false;
    $edit_product = null;

    // Fetch categories for the select dropdown
    $categories = [];
    $stmt = $conn->query('SELECT id, category_name FROM category ORDER BY category_name ASC');
    if ($stmt) {
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Check if editing an existing product
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $edit_mode = true;
        $product_id = intval($_GET['id']);
        $stmt = $conn->prepare('SELECT * FROM products WHERE id = :id');
        $stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        $edit_product = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$edit_product) {
            $error = 'Product not found.';
            $edit_mode = false;
        }
    }

    // Handle product add/update form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name'] ?? '');
        $category_id = intval($_POST['category'] ?? 0);
        $price = floatval($_POST['price'] ?? 0);
        $stock = intval($_POST['stock'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $image_path = $edit_product['image'] ?? '';

        // Handle image upload if a new image is provided
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imgTmp = $_FILES['image']['tmp_name'];
            $imgName = basename($_FILES['image']['name']);
            $imgExt = strtolower(pathinfo($imgName, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array($imgExt, $allowed)) {
                $newName = uniqid('prod_', true) . '.' . $imgExt;
                $uploadDir = '../../uploads/products/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $dest = $uploadDir . $newName;
                if (move_uploaded_file($imgTmp, $dest)) {
                    $image_path = $newName;
                } else {
                    $error = 'Failed to upload image.';
                }
            } else {
                $error = 'Invalid image file type.';
            }
        } else if (!$edit_mode && empty($image_path)) {
            $error = 'Product image is required.';
        }

        // Insert or update product if no error
        if (empty($error)) {
            try {
                if ($edit_mode) {
                    $stmt = $conn->prepare('UPDATE products SET name = :name, category_id = :category_id, price = :price, qty = :stock, image = :image, description = :description WHERE id = :id');
                    $stmt->bindParam(':id', $product_id);
                } else {
                    $stmt = $conn->prepare('INSERT INTO products (name, category_id, price, qty, image, description) VALUES (:name, :category_id, :price, :stock, :image, :description)');
                }
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':category_id', $category_id);
                $stmt->bindParam(':price', $price);
                $stmt->bindParam(':stock', $stock);
                $stmt->bindParam(':image', $image_path);
                $stmt->bindParam(':description', $description);
                if ($stmt->execute()) {
                    $success = $edit_mode ? 'Product updated successfully!' : 'Product added successfully!';
                    if ($edit_mode) {
                        // Refresh product data after update
                        $stmt = $conn->prepare('SELECT * FROM products WHERE id = :id');
                        $stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
                        $stmt->execute();
                        $edit_product = $stmt->fetch(PDO::FETCH_ASSOC);
                    }
                } else {
                    $error = $edit_mode ? 'Failed to update product.' : 'Failed to add product.';
                }
            } catch (PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $edit_mode ? 'Edit' : 'Add'; ?> Product - GroceryGo</title>
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
        
        /* Form Styles */
        .form-control, .form-select {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 0.75rem 1rem;
            background-color: var(--input-bg);
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 3px rgba(47, 133, 90, 0.2);
            border-color: var(--primary);
        }
        
        .form-label {
            color: var(--dark);
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
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
        
        /* Image preview */
        .image-preview {
            margin-top: 1rem;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid var(--border-color);
            display: inline-block;
        }
        
        .image-preview img {
            width: 100px;
            height: 100px;
            object-fit: cover;
        }
        
        .preview-label {
            display: block;
            font-size: 0.85rem;
            color: #718096;
            margin-top: 0.5rem;
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
                    <h1 class="welcome-title"><?php echo $edit_mode ? 'Edit Product' : 'Add New Product'; ?></h1>
                    <p class="welcome-text"><?php echo $edit_mode ? 'Update product details' : 'Create a new product for your store'; ?></p>
                </div>
                <i class="fas fa-<?php echo $edit_mode ? 'edit' : 'box-open'; ?> welcome-icon"></i>
            </div>
            
            <!-- Content -->
            <div class="content-card">
                <div class="content-card-header d-flex justify-content-between align-items-center">
                    <h5><?php echo $edit_mode ? 'Product Details' : 'Product Information'; ?></h5>
                    <a href="products.php" class="btn-back">
                        <i class="fas fa-arrow-left me-2"></i> Back to Products
                    </a>
                </div>                <div class="card-body p-4">
                    <form method="post" action="<?php echo isset($product_id) ? '?id=' . $product_id : ''; ?>" enctype="multipart/form-data">
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php elseif (!empty($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        
                        <div class="row g-4">
                            <!-- Product Name -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="productName" class="form-label">Product Name</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-tag"></i></span>
                                        <input type="text" class="form-control" id="productName" name="name" required 
                                            value="<?php echo htmlspecialchars($edit_product['name'] ?? ''); ?>" 
                                            placeholder="Enter product name" autocomplete="off">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Category -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="productCategory" class="form-label">Category</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-folder"></i></span>
                                        <select class="form-select" id="productCategory" name="category" required>
                                            <option value="">Select Category</option>
                                            <?php foreach ($categories as $cat): ?>
                                                <option value="<?php echo $cat['id']; ?>" 
                                                    <?php if (($edit_product['category_id'] ?? '') == $cat['id']) echo 'selected'; ?>>
                                                    <?php echo htmlspecialchars($cat['category_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Price -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="productPrice" class="form-label">Price (Rs.)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-rupee-sign"></i></span>
                                        <input type="number" class="form-control" id="productPrice" name="price" 
                                            min="0" step="0.01" required 
                                            value="<?php echo htmlspecialchars($edit_product['price'] ?? ''); ?>"
                                            placeholder="0.00" autocomplete="off">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Stock Quantity -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="productStock" class="form-label">Stock</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-cubes"></i></span>
                                        <input type="number" class="form-control" id="productStock" name="stock" 
                                            min="0" required 
                                            value="<?php echo htmlspecialchars($edit_product['qty'] ?? ''); ?>"
                                            placeholder="0" autocomplete="off">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Product Image -->
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="productImage" class="form-label">Product Image</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-image"></i></span>
                                        <input class="form-control" type="file" id="productImage" name="image" 
                                            accept="image/*" <?php if (!$edit_mode) echo 'required'; ?>>
                                    </div>
                                    <small class="text-muted">Recommended size: 600×600 pixels. Max size: 2MB.</small>
                                    
                                    <?php if ($edit_mode && !empty($edit_product['image'])): ?>
                                        <div class="image-preview mt-3">
                                            <p class="mb-2"><strong>Current Image:</strong></p>
                                            <div class="d-flex align-items-center">
                                                <img src="<?php echo '../../uploads/products/' . htmlspecialchars($edit_product['image']); ?>" 
                                                    alt="Current Image" class="rounded shadow-sm border"
                                                    style="width:100px; height:100px; object-fit:cover;">
                                                <span class="text-muted ms-3 small">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    Upload a new image to replace this one
                                                </span>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Description -->
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="productDescription" class="form-label">Description</label>
                                    <textarea class="form-control" id="productDescription" name="description" 
                                        rows="5" required
                                        placeholder="Enter product details, features, etc." autocomplete="off"><?php echo htmlspecialchars($edit_product['description'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Form Actions -->
                        <div class="d-flex justify-content-end gap-3 mt-4">
                            <a href="products.php" class="btn btn-back">
                                <i class="fas fa-times me-2"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-save">
                                <i class="fas fa-save me-2"></i>
                                <?php echo $edit_mode ? 'Update Product' : 'Save Product'; ?>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.5/dist/sweetalert2.all.min.js"></script>
    
    <script>
        // Preview image on file selection
        document.getElementById('productImage').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            const reader = new FileReader();
            reader.onload = function(e) {
                const imagePreview = document.createElement('div');
                imagePreview.classList.add('image-preview', 'mt-3');
                
                imagePreview.innerHTML = `
                    <p class="mb-2"><strong>New Image Preview:</strong></p>
                    <div class="d-flex align-items-center">
                        <img src="${e.target.result}" alt="New Image Preview" class="rounded shadow-sm border"
                            style="width:100px; height:100px; object-fit:cover;">
                        <span class="text-muted ms-3 small">
                            <i class="fas fa-check-circle me-1 text-success"></i>
                            Image ready for upload
                        </span>
                    </div>
                `;
                
                // Remove existing preview if any
                const existingPreview = document.querySelector('.new-image-preview');
                if (existingPreview) {
                    existingPreview.remove();
                }
                
                imagePreview.classList.add('new-image-preview');
                document.getElementById('productImage').parentNode.parentNode.appendChild(imagePreview);
            };
            
            reader.readAsDataURL(file);
        });
    </script>
</body>
</html>