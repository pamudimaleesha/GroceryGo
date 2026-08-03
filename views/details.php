<?php
session_start();
require_once '../config/db.php';
require_once '../config/helpers.php';

// Get product ID from query string
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$product = null;

if ($product_id > 0) {
    $user_id = $_SESSION['user_id'] ?? 0;

    $stmt = $conn->prepare('
        SELECT p.*, c.category_name AS category_name,
        CASE WHEN w.wishlist_id IS NOT NULL THEN 1 ELSE 0 END as in_wishlist
        FROM products p 
        LEFT JOIN category c ON p.category_id = c.id 
        LEFT JOIN wishlist w ON w.product_id = p.id AND w.user_id = :user_id
        WHERE p.id = :id
    ');
    $stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details - GroceryGo.lk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Fredoka+One&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.5/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #2F855A; /* Tailwind green-700 */
            --secondary: #38A169; /* Tailwind green-600 */
            --light: #F0FFF4; /* Tailwind green-50 */
            --accent: #F6E05E; /* Tailwind yellow-300 */
            --dark: #1A202C; /* Tailwind gray-800 */
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8fafc;
        }
        
        .navbar-brand {
            font-family: 'Fredoka One', cursive;
            font-size: 1.8rem;
            color: var(--primary);
        }
        
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary);
            border-color: var(--secondary);
        }
        
        .btn-outline-primary {
            color: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary);
            color: white;
        }
        
        .bg-primary {
            background-color: var(--primary) !important;
        }
        
        .text-primary {
            color: var(--primary) !important;
        }
        
        /* Navigation styles */
        .navbar-nav .nav-link {
            color: var(--dark);
            font-weight: 500;
            padding: 0.5rem 1rem;
        }
        
        .navbar-nav .nav-link:hover {
            color: var(--primary);
        }
        
        .nav-link.active {
            font-weight: 600;
            color: var(--primary) !important;
        }
        
        /* Username styles */
        .username {
            font-weight: 600;
            color: var(--primary) !important;
        }
        
        /* Footer styles */
        .footer {
            background-color: var(--dark);
            color: white;
            padding: 60px 0 20px;
        }
        
        .footer-logo {
            font-family: 'Fredoka One', cursive;
            font-size: 1.8rem;
            color: white;
        }
        
        .footer-link {
            color: #a0aec0;
            text-decoration: none;
            display: block;
            margin-bottom: 8px;
        }
        
        .footer-link:hover {
            color: white;
        }
        
        .footer-social {
            color: white;
            margin: 0 10px;
            font-size: 1.2rem;
        }
        
        .footer-social:hover {
            color: var(--accent);
        }
        
        /* Product details specific styles */
        .product-details-wrapper {
            padding: 3rem 0;
            background-color: var(--light);
        }
        
        .product-details-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            padding: 2.5rem !important;
            margin: 1rem 0;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .product-details-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        
        .product-image-container {
            position: relative;
            background: #fff;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }
        
        .product-image {
            max-height: 400px;
            width: auto;
            object-fit: contain;
            transition: transform 0.3s ease;
        }
        
        .product-image:hover {
            transform: scale(1.05);
        }
        
        .product-title {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 2rem;
            margin-bottom: 1rem;
            color: var(--primary);
        }
        
        .product-price {
            font-size: 1.3rem;
            font-weight: 400;
            color: var(--primary);
            margin-bottom: 1.5rem;
        }
        
        .category-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            background-color: var(--light);
            border-radius: 25px;
            color: var(--primary);
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
        }
        
        .product-description {
            font-size: 1rem;
            line-height: 1.8;
            color: #4A5568;
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #eee;
        }
        
        .btn i {
            margin-right: 8px;
        }
          /* Active wishlist button styles */
        .btn-outline-primary.active {
            color: #fff;
            background-color: var(--primary);
        }
        
        .add-to-wishlist-btn .fa-heart.text-danger {
            color: var(--primary) !important;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .product-details-wrapper {
                padding: 2rem 0;
            }
            
            .product-details-card {
                padding: 1.5rem !important;
            }
            
            .product-title {
                font-size: 1.8rem;
            }
            
            .product-price {
                font-size: 1.2rem;
            }
            
            .product-image {
                max-height: 300px;
            }
        }
    </style>
</head>
<body>    <?php include_once '../includes/nav.php'; ?>
    <div class="product-details-wrapper">
        <div class="container">
            <?php if ($product): ?>
                <div class="row g-4 align-items-center product-details-card">
                    <div class="col-md-6">
                        <div class="product-image-container">
                            <img src="../uploads/products/<?= htmlspecialchars($product['image'] ?? 'default.png') ?>" 
                                 alt="<?= htmlspecialchars($product['name']) ?>" 
                                 class="img-fluid rounded product-image">
                        </div>
                    </div>
                    <div class="col-md-6">                        
                        <h2 class="mb-3 product-title"><?= htmlspecialchars($product['name']) ?></h2>
                        <h4 class="mb-3 product-price">Rs <?= number_format($product['price'], 2) ?></h4>
                        <?php
                            $categoryName = $product['category_name'] ?? 'Uncategorized';
                            list($bgColor, $textColor) = generateCategoryColor($categoryName);
                        ?>
                        <p class="mb-2 text-muted"><strong>Category:</strong> 
                            <span class="badge rounded-pill" style="background-color: <?php echo $bgColor; ?>; color: <?php echo $textColor; ?>;">
                                <?= htmlspecialchars($categoryName) ?>
                            </span>
                        </p>
                        <p class="mb-4 product-description"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                        
                        <div class="d-flex gap-2">
                            <?php if (isset($_SESSION['user_id'])): ?>                                <button type="button" class="btn btn-primary add-to-cart-btn" 
                                        data-product-id="<?= $product['id'] ?>">
                                    <i class="fas fa-shopping-cart me-1"></i> Add to Cart
                                </button>                                <button type="button" class="btn btn-outline-primary add-to-wishlist-btn<?php echo $product['in_wishlist'] ? ' active' : ''; ?>"
                                    data-product-id="<?= $product['id'] ?>">
                                    <i class="fa fa-heart me-1<?php echo $product['in_wishlist'] ? ' text-white' : ''; ?>"></i> 
                                    <?php echo $product['in_wishlist'] ? 'Remove from Wishlist' : 'Add to Wishlist'; ?>
                                </button>
                            <?php else: ?>                               
                                 <a href="signin.php" class="btn btn-primary">
                                    <i class="fas fa-shopping-cart me-1"></i> Add to Cart
                                </a>                                
                                <a href="signin.php" class="btn btn-outline-primary">
                                    <i class="fa fa-heart me-1"></i> Add to Wishlist
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-warning text-center">Product not found.</div>
            <?php endif; ?>            </div>
        </div>
    </div>
    <?php include_once '../includes/footer.php'; ?>
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.5/dist/sweetalert2.all.min.js"></script>
    <!-- Custom JS -->
    <script src="../public/js/main.js"></script>
    <script src="../public/js/cart.js"></script>
    <script src="../public/js/wishlist.js"></script>
</body>
</html>