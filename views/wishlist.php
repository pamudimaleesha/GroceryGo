<?php
    session_start();
    include '../config/db.php';

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header('Location: signin.php');
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $wishlist_items = [];

    // Fetch wishlist items with product details
    $stmt = $conn->prepare('
        SELECT w.wishlist_id, p.id as product_id, p.name, p.price, p.qty as stock, p.image, cat.category_name 
        FROM wishlist w 
        JOIN products p ON w.product_id = p.id 
        LEFT JOIN category cat ON p.category_id = cat.id 
        WHERE w.user_id = ? 
        ORDER BY w.added_at DESC
    ');
    $stmt->execute([$user_id]);
    $wishlist_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get wishlist count
    $wishlist_count = count($wishlist_items);
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist - GroceryGo.lk</title>
    <!-- Bootstrap 5 CSS -->
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
        
        /* Wishlist specific styles */
        .wishlist-hero-section {
            background-color: var(--light);
            padding: 3rem 0;
        }
        
        .wishlist-hero-section .product-card {
            min-height: 370px;
            max-height: 400px;
            height: 100%;
            display: flex;
            flex-direction: column;
            border-radius: 10px;
            transition: transform 0.3s, box-shadow 0.3s;
            overflow: hidden;
            background: #fff;
            border: none;
        }
        
        .wishlist-hero-section .product-card:hover {
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            transform: translateY(-5px);
        }
        
        .wishlist-hero-section .product-card .card-img-top {
            height: 180px;
            object-fit: cover;
            width: 100%;
        }
        
        .wishlist-hero-section .product-card .card-body {
            flex: 1 1 auto;
            display: flex;
            flex-direction: column;
            padding: 1rem;
        }
        
        .wishlist-hero-section .product-card .card-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            height: 2.4rem;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        
        .btn-outline-primary {
            color: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary);
            color: white;
        }
        
        .text-success {
            color: var(--primary) !important;
        }
        
        @media (max-width: 991.98px) {
            .wishlist-hero-section .product-card {
                min-height: 340px;
                max-height: 370px;
            }
            
            .wishlist-hero-section .product-card .card-img-top {
                height: 150px;
            }
        }
        
        @media (max-width: 575.98px) {
            .wishlist-hero-section .product-card {
                min-height: 300px;
                max-height: 340px;
            }
            
            .wishlist-hero-section .product-card .card-img-top {
                height: 120px;
            }
        }
    </style>
<body>
<!-- Navbar -->
<?php include_once('../includes/nav.php'); ?>

<section class="wishlist-hero-section py-5">
    <div class="container">        <h1 class="mb-4 text-center text-primary fw-bold">My Wishlist</h1>
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div class="fs-5 text-secondary">
                <i class="fa fa-heart text-primary me-2"></i>
                <?php echo $wishlist_count; ?> item<?php echo $wishlist_count !== 1 ? 's' : ''; ?> in your wishlist
            </div>
            <?php if (!empty($wishlist_items)): ?>
                <button class="btn btn-outline-danger btn-sm px-4" id="clearWishlist">
                    <i class="fa fa-trash me-1"></i> Remove All
                </button>
            <?php endif; ?>
        </div>
        <div class="row g-4" id="wishlistGrid">
            <?php if (empty($wishlist_items)): ?>
                <div class="col-12">                <div class="text-center py-5">
                        <i class="fas fa-heart fa-3x text-muted mb-3"></i>
                        <h4 class="text-primary">Your wishlist is empty</h4>
                        <a href="shop.php" class="btn btn-primary mt-3">
                            <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($wishlist_items as $item): ?>
                    <div class="col-sm-6 col-md-4 col-lg-3">
                        <div class="card product-card h-100 shadow-sm">
                            <a href="./details.php?id=<?php echo $item['product_id']; ?>">
                                <img src="../uploads/products/<?php echo htmlspecialchars($item['image']); ?>" 
                                     class="card-img-top" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            </a>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title text-primary"><?php echo htmlspecialchars($item['name']); ?></h5>
                                <div class="mb-2 text-muted small"><?php echo htmlspecialchars($item['category_name']); ?></div>                                <div class="mb-3 fw-bold text-primary">Rs. <?php echo number_format($item['price'], 2); ?></div>
                                <div class="mt-auto d-flex gap-2">
                                    <button type="button" class="btn btn-primary btn-sm w-100 add-to-cart-btn" 
                                            data-product-id="<?php echo $item['product_id']; ?>">
                                        <i class="fa fa-cart-plus me-1"></i> Add to Cart
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm wishlist-remove" 
                                            data-wishlist-id="<?php echo $item['wishlist_id']; ?>">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Footer -->
<?php include_once('../includes/footer.php'); ?>

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