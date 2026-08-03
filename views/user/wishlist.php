<?php
session_start();
require '../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: signin.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$wishlist_items = [];

try {
    $stmt = $conn->prepare('
        SELECT w.wishlist_id, p.id AS product_id, p.name, p.price, p.qty AS stock, p.image, cat.category_name 
        FROM wishlist w 
        JOIN products p ON w.product_id = p.id 
        LEFT JOIN category cat ON p.category_id = cat.id 
        WHERE w.user_id = ? 
        ORDER BY w.added_at DESC
    ');
    $stmt->execute([$user_id]);
    $wishlist_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist - GroceryGo.lk</title>
    <!-- Bootstrap CSS -->
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
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover, .btn-primary:focus {
            background-color: var(--secondary);
            border-color: var(--secondary);
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .btn-outline-primary {
            color: var(--primary);
            border-color: var(--primary);
            transition: all 0.3s ease;
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary);
            border-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .btn-outline-danger {
            color: #E53E3E;
            border-color: #E53E3E;
            transition: all 0.3s ease;
        }
        
        .btn-outline-danger:hover {
            background-color: #E53E3E;
            border-color: #E53E3E;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(229, 62, 62, 0.2);
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
        
        /* Wishlist Card Styling */
        .card {
            height: 100%;
            border: none;
            border-radius: 10px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
            background-color: #fff;
            position: relative;
            box-shadow: var(--card-shadow);
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        
        .card .position-relative {
            height: 220px;
            overflow: hidden;
        }
        
        .card-img-top {
            height: 100%;
            width: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .card:hover .card-img-top {
            transform: scale(1.05);
        }
        
        .card-body {
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 0.75rem;
        }
        
        .card-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.25rem;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
            height: 2.5rem;
        }
        
        .card-text {
            font-size: 0.9rem;
            color: #64748b;
            margin-bottom: 0.5rem;
        }
        
        .badge {
            font-weight: 500;
            padding: 0.5em 0.8em;
            border-radius: 6px;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .badge:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        /* Stock indicator */
        .stock-status {
            font-size: 0.85rem;
            padding: 0.35rem 0.6rem;
            border-radius: 4px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
        }
        
        .stock-status i {
            margin-right: 4px;
            font-size: 0.8rem;
        }
        
        .in-stock {
            background-color: rgba(56, 161, 105, 0.1);
            color: #2F855A;
        }
        
        .low-stock {
            background-color: rgba(246, 224, 94, 0.2);
            color: #975A16;
        }
        
        .out-of-stock {
            background-color: rgba(229, 62, 62, 0.1);
            color: #C53030;
        }
        
        /* Action buttons */
        .action-buttons {
            display: flex;
            justify-content: space-between;
            gap: 0.5rem;
            margin-top: auto;
        }
        
        .btn-circle {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .btn-circle:hover {
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }
        
        /* Empty state styling */
        .empty-wishlist {
            text-align: center;
            padding: 4rem 1rem;
            background-color: var(--light);
            border-radius: 12px;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }
        
        .empty-wishlist:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        
        .empty-wishlist i {
            font-size: 4rem;
            color: var(--primary);
            margin-bottom: 1.5rem;
            opacity: 0.7;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));
        }
        
        .empty-wishlist h4 {
            color: var(--dark);
            margin-bottom: 1rem;
            font-weight: 600;
        }
        
        .empty-wishlist p {
            color: #64748b;
            margin-bottom: 1.5rem;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.6;
        }
        
        /* Price tag */
        .price-tag {
            font-weight: 600;
            background-color: var(--light);
            color: var(--primary);
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            transition: all 0.2s ease;
            font-size: 1.1rem;
        }
        
        .price-tag:hover {
            transform: scale(1.05);
            background-color: var(--primary);
            color: white;
        }

    </style>

<body>
    <div class="page-container">
        <div class="container-fluid">
            <div class="row content-wrapper">
                <!-- Sidebar -->
                <?php include_once('./includes/user_nav.php'); ?>

                <!-- Main Content -->
                <div class="col-md-9 col-lg-9 content">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary rounded-circle p-3 me-3 text-white shadow-sm">
                                <i class="fas fa-heart fa-2x"></i>
                            </div>
                            <h2 class="section-title mb-0">My Wishlist</h2>
                        </div>
                        <div class="wishlist-stats">
                            <div class="badge badge-rounded-pill bg-light text-primary px-3 py-2 shadow-sm">
                                <i class="fas fa-heart"></i>
                                <span class="ms-1">Total Items: <?= count($wishlist_items) ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row g-4">
                        <!-- Empty state -->
                        <?php if (empty($wishlist_items)) { ?>
                            <div class="col-12">
                                <div class="empty-wishlist shadow-sm">
                                    <div class="d-inline-block mb-4 p-3 rounded-circle bg-white shadow">
                                        <i class="fas fa-heart-broken"></i>
                                    </div>
                                    <h4>Your Wishlist is Empty</h4>
                                    <p>Start saving your favorite items to keep track of products you love. Add items to your wishlist while browsing our shop!</p>
                                    <a href="../shop.php" class="btn btn-primary btn-lg px-4 py-2 shadow">
                                        <i class="text-white fas fa-shopping-basket me-2"></i> Browse Products
                                    </a>
                                </div>
                            </div>
                        <?php } ?>
                        <?php foreach ($wishlist_items as $item): ?>
                            <div class="col-md-6 col-lg-4 mb-4 wishlist-item" data-wishlist-id="<?= $item['wishlist_id'] ?>">
                                <div class="card h-100">
                                    <div class="position-relative">
                                        <img src="../../uploads/products/<?= htmlspecialchars($item['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($item['name']) ?>">
                                        <?php 
                                        // Include helpers file if it's not already included
                                        if (!function_exists('generateCategoryColor')) {
                                            include_once('../../config/helpers.php');
                                        }
                                        
                                        $categoryName = $item['category_name'] ?? 'Uncategorized';
                                        if (function_exists('generateCategoryColor')) {
                                            list($bgColor, $textColor) = generateCategoryColor($categoryName);
                                        } else {
                                            $bgColor = '#2F855A';
                                            $textColor = '#fff';
                                        }
                                        ?>
                                        <span class="badge position-absolute" 
                                              style="top: 10px; right: 10px; background-color: <?php echo $bgColor; ?>; color: <?php echo $textColor; ?>;">
                                            <i class="fas fa-tag me-1"></i>
                                            <?= htmlspecialchars($categoryName) ?>
                                        </span>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($item['name']) ?></h5>
                                        
                                        <!-- Stock indicator -->
                                        <?php
                                        $stockClass = '';
                                        $stockText = '';
                                        $stockIcon = '';
                                        
                                        if ($item['stock'] <= 0) {
                                            $stockClass = 'out-of-stock';
                                            $stockText = 'Out of Stock';
                                            $stockIcon = 'times-circle';
                                        } elseif ($item['stock'] <= 5) {
                                            $stockClass = 'low-stock';
                                            $stockText = 'Low Stock: ' . $item['stock'];
                                            $stockIcon = 'exclamation-circle';
                                        } else {
                                            $stockClass = 'in-stock';
                                            $stockText = 'In Stock';
                                            $stockIcon = 'check-circle';
                                        }
                                        ?>
                                        <div class="stock-status <?= $stockClass ?>">
                                            <i class="fas fa-<?= $stockIcon ?>"></i> <?= $stockText ?>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center mt-3">
                                            <div class="price-tag">Rs. <?= number_format($item['price'], 2) ?></div>
                                            <a href="../details.php?id=<?= $item['product_id'] ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye me-1"></i> View Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.5/dist/sweetalert2.all.min.js"></script>
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <script src="../../public/js/wishlist.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add hover effects to cards
            document.querySelectorAll('.card').forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.querySelector('.btn-primary')?.classList.add('animate__animated', 'animate__pulse');
                });
                
                card.addEventListener('mouseleave', function() {
                    this.querySelector('.btn-primary')?.classList.remove('animate__animated', 'animate__pulse');
                });
            });
        });
    </script>
</body>
</html>