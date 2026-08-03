<?php
    session_start();
    include '../config/db.php';
    include '../config/helpers.php';// Function to generate consistent colors for category badges
    function getCategoryColor($categoryName) {
        // Hash the category name to generate a consistent value
        $hash = crc32($categoryName);
        
        // Define an array of visually distinct colors (RGBA values)
        $colors = [
            ['#FF6B6B', '#333'], // Red, dark text
            ['#4CAF50', '#fff'], // Green, white text
            ['#42A5F5', '#fff'], // Light Blue, white text
            ['#FF7043', '#fff'], // Orange, white text
            ['#8D6E63', '#fff'], // Brown, white text
            ['#26A69A', '#fff'], // Teal, white text
            ['#FFD54F', '#333'], // Yellow, dark text
            ['#7986CB', '#fff'], // Indigo, white text
            ['#EC407A', '#fff'], // Pink, white text
            ['#64B5F6', '#fff'], // Sky Blue, white text
            ['#9CCC65', '#fff'], // Light Green, white text
            ['#FFB74D', '#333'], // Amber, dark text
            ['#BA68C8', '#fff'], // Purple, white text
            ['#4DB6AC', '#fff'], // Teal, white text
            ['#81C784', '#333'], // Light green, dark text
        ];
        
        // Use the hash to select a color
        $index = abs($hash) % count($colors);
        
        return $colors[$index];
    }
    
    // Static function that can be called from anywhere without requiring the file
    if (!function_exists('generateCategoryColor')) {
        function generateCategoryColor($categoryName) {
            // Hash the category name to generate a consistent value
            $hash = crc32($categoryName);
            
            // Define an array of visually distinct colors (RGBA values)
            $colors = [
                ['#FF6B6B', '#333'], // Red, dark text
                ['#4CAF50', '#fff'], // Green, white text
                ['#42A5F5', '#fff'], // Light Blue, white text
                ['#FF7043', '#fff'], // Orange, white text
                ['#8D6E63', '#fff'], // Brown, white text
                ['#26A69A', '#fff'], // Teal, white text
                ['#FFD54F', '#333'], // Yellow, dark text
                ['#7986CB', '#fff'], // Indigo, white text
                ['#EC407A', '#fff'], // Pink, white text
                ['#64B5F6', '#fff'], // Sky Blue, white text
                ['#9CCC65', '#fff'], // Light Green, white text
                ['#FFB74D', '#333'], // Amber, dark text
                ['#BA68C8', '#fff'], // Purple, white text
                ['#4DB6AC', '#fff'], // Teal, white text
                ['#81C784', '#333'], // Light green, dark text
            ];
            
            // Use the hash to select a color
            $index = abs($hash) % count($colors);
            
            return $colors[$index];
        }
    }

    // Get categories for the filter
    $categories = [];
    $stmt = $conn->query('SELECT id, category_name FROM category ORDER BY id ASC');
    if ($stmt) {
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - GroceryGo.lk</title>
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
        
        /* Shop page specific styles */
        .shop-hero-section {
            padding: 3rem 0;
            background-color: var(--light);
            margin-bottom: 2rem;
            border-radius: 10px;
        }
        
        .shop-title {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 2rem;
        }
        
        .shop-search-input, .shop-filter-select {
            border-radius: 10px;
            padding: 10px 15px;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        
        .shop-search-input:focus, .shop-filter-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(47, 133, 90, 0.1);
        }
          /* Product cards styling to match index.php */
        .product-card {
            transition: transform 0.3s, box-shadow 0.3s;
            border-radius: 10px;
            overflow: hidden;
            border: none;
            height: 380px;
            width: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .product-card .position-relative {
            height: 220px;
            overflow: hidden;
            position: relative;
        }
        
        .product-card .card-img-top {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }
        
        .product-card .card-body {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 1rem;
        }
        
        .product-card .card-title {
            font-size: 1rem;
            margin-bottom: 0.75rem;
            font-weight: 600;
            height: 2.4rem;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        
        .product-card .d-flex {
            margin-top: auto;
        }
        
        .product-card .fw-bold.text-primary {
            font-size: 1.1rem;
        }
        
        .product-card .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        
        .btn-wishlist {
            position: absolute;
            top: 10px;
            left: 10px;
            background: white;
            border: none;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            z-index: 2;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-wishlist:hover {
            transform: scale(1.1);
            box-shadow: 0 3px 8px rgba(0,0,0,0.15);
        }
        
        .btn-wishlist i {
            color: var(--primary);
            font-size: 1.1rem;
        }
        
        .btn-wishlist i.fas {
            color: var(--primary);
        }          .badge-discount {
            position: absolute;
            top: 10px;
            right: 10px;
            font-weight: bold;
            z-index: 2;
            padding: 0.35rem 0.65rem !important;
            border-radius: 50rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            font-size: 0.85rem;
            display: inline-block;
            white-space: nowrap;
            text-align: center;
        }
        
        @media (max-width: 767px) {
            .product-card {
                height: 360px;
            }
            
            .product-card .position-relative {
                height: 200px;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <?php include_once('../includes/nav.php'); ?>    <section class="shop-hero-section py-5">
        <div class="container">
            <h1 class="shop-title mb-4 text-center">Shop Groceries</h1>
            <div id="shopFilterForm">
                <div class="row mb-4 align-items-center g-3">
                    <div class="col-md-4">                        <input type="text" class="form-control shop-search-input" id="shopSearch" 
                               placeholder="Search for groceries...">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select shop-filter-select" id="categoryFilter">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat['id']); ?>">
                                    <?php echo htmlspecialchars($cat['category_name']); ?>
                                </option>
                            <?php endforeach; ?>         
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select shop-filter-select" id="priceFilter">
                            <option value="">All Prices</option>
                            <option value="0-1000">Below Rs. 100</option>
                            <option value="1000-1500">Rs. 100 - Rs. 150</option>
                            <option value="1500-2000">Rs. 150 - Rs. 200</option>
                            <option value="2000-99999">Above Rs. 200</option>
                        </select>
                    </div>
                    <div class="col-md-2 text-end">
                        <button type="button" class="btn btn-primary w-100" id="clearFilters">Clear Filters</button>
                    </div>
                </div>
            </div>
            
            <div class="row g-4" id="shopProductGrid">
                <!-- Products will be loaded here by AJAX -->
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
    <script src="../public/js/search.js"></script>
    <script src="../public/js/cart.js"></script>
    <script src="../public/js/wishlist.js"></script>
</body>
</html>
