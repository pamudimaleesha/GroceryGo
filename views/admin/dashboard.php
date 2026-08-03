<?php 
session_start();
include '../../config/db.php';
  $users = [];
  $stmt = $conn->query('SELECT id, name, email, role FROM users ORDER BY id ASC');
  if ($stmt) {
      $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } 
  $products = [];
  $sql = 'SELECT p.id, p.name, c.category_name, p.price, p.qty, p.image, p.created_at FROM products p LEFT JOIN category c ON p.category_id = c.id ORDER BY p.id DESC';
  $stmt = $conn->query($sql);
  if ($stmt) {
      $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  $categories = [];
  $stmt = $conn->query('SELECT id, category_name FROM category ORDER BY id ASC');
  if ($stmt) {
      $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  $orders = [];
  $stmt = $conn->query('SELECT order_id, user_id, total_amount, status, order_date FROM orders ORDER BY order_id DESC');
  if ($stmt) {
      $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - GroceryGo</title>
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
        
        /* Dashboard Cards */
        .stat-card {
            border: none;
            border-radius: 15px;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
            overflow: hidden;
            height: 100%;
        }
        
        .stat-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 30px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card .card-body {
            padding: 1.5rem;
            position: relative;
            z-index: 1;
        }
        
        .stat-card::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 100%;
            height: 5px;
            background-color: var(--primary);
            transition: height 0.3s ease;
        }
        
        .stat-card:hover::after {
            height: 8px;
        }
        
        .stat-card.products::after { background-color: var(--primary); }
        .stat-card.categories::after { background-color: #38B2AC; } /* Teal */
        .stat-card.orders::after { background-color: var(--accent); }
        .stat-card.users::after { background-color: #ED8936; } /* Orange */
        
        .stat-icon-wrapper {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover .stat-icon-wrapper {
            transform: scale(1.1) rotate(10deg);
        }
        
        .stat-card.products .stat-icon-wrapper {
            background-color: rgba(47, 133, 90, 0.1);
            color: var(--primary);
        }
        
        .stat-card.categories .stat-icon-wrapper {
            background-color: rgba(56, 178, 172, 0.1);
            color: #38B2AC;
        }
        
        .stat-card.orders .stat-icon-wrapper {
            background-color: rgba(246, 224, 94, 0.2);
            color: #975A16;
        }
        
        .stat-card.users .stat-icon-wrapper {
            background-color: rgba(237, 137, 54, 0.1);
            color: #ED8936;
        }
        
        .stat-count {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0.5rem 0;
            line-height: 1;
        }
        
        .stat-card.products .stat-count { color: var(--primary); }
        .stat-card.categories .stat-count { color: #38B2AC; }
        .stat-card.orders .stat-count { color: #975A16; }
        .stat-card.users .stat-count { color: #ED8936; }
        
        .stat-title {
            margin: 0;
            font-weight: 600;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.9rem;
        }
        
        .recent-activity {
            margin-top: 2.5rem;
        }
        
        .section-title {
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
            border-left: 4px solid var(--primary);
            padding-left: 12px;
        }
        
        .quick-links-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            margin-bottom: 2rem;
        }
        
        .quick-links-header {
            background-color: var(--light);
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .quick-links-header h5 {
            margin-bottom: 0;
            font-weight: 600;
            color: var(--primary);
        }
        
        .quick-links {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            padding: 1.5rem;
        }
        
        .quick-link {
            padding: 1rem;
            border-radius: 8px;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            color: inherit;
            text-decoration: none;
            border: 1px solid var(--border-color);
        }
        
        .quick-link:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            border-color: var(--primary);
        }
        
        .quick-link-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background-color: var(--light);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1.2rem;
            transition: all 0.3s ease;
        }
        
        .quick-link:hover .quick-link-icon {
            background-color: var(--primary);
            color: white;
        }
        
        .quick-link-text {
            font-weight: 500;
        }
          @media (max-width: 767px) {
            .welcome-text {
                max-width: 100%;
            }
            
            .welcome-icon {
                display: none;
            }
            
            .quick-links {
                grid-template-columns: 1fr;
            }
            
            .main-content {
                padding: 1rem;
                padding-top: 5rem;
            }
            
            .page-header {
                margin-top: 1rem;
            }
        }
    </style>
</head>
<body>    <div class="container-fluid px-0 admin-wrapper">
        <div class="row g-0">
            <!-- Sidebar -->
            <?php include_once('./includes/admin_nav.php'); ?>
            
            <!-- Main Content -->
            <main class="col-12 main-content">
                <div class="page-header">
                    <h1 class="welcome-title">Welcome to GroceryGo Admin Dashboard</h1>
                    <p class="welcome-text">Manage your products, categories, orders, and users from this central dashboard.</p>
                    <i class="fas fa-tachometer-alt welcome-icon"></i>
                </div>
                
                <div class="row g-4 mb-5">
                    <div class="col-sm-6 col-xl-3">
                        <div class="card stat-card products">
                            <div class="card-body text-center">
                                <div class="stat-icon-wrapper">
                                    <i class="fas fa-box-open fa-2x"></i>
                                </div>
                                <div class="stat-count"><?php echo count($products); ?></div>
                                <h6 class="stat-title">Products</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="card stat-card categories">
                            <div class="card-body text-center">
                                <div class="stat-icon-wrapper">
                                    <i class="fas fa-tags fa-2x"></i>
                                </div>
                                <div class="stat-count"><?php echo count($categories); ?></div>
                                <h6 class="stat-title">Categories</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="card stat-card orders">
                            <div class="card-body text-center">
                                <div class="stat-icon-wrapper">
                                    <i class="fas fa-shopping-cart fa-2x"></i>
                                </div>
                                <div class="stat-count"><?php echo count($orders); ?></div>
                                <h6 class="stat-title">Orders</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="card stat-card users">
                            <div class="card-body text-center">
                                <div class="stat-icon-wrapper">
                                    <i class="fas fa-users fa-2x"></i>
                                </div>
                                <div class="stat-count"><?php echo count($users); ?></div>
                                <h6 class="stat-title">Users</h6>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Links Section -->
                <div class="recent-activity">
                    <h2 class="section-title">Quick Actions</h2>
                    <div class="card quick-links-card">
                        <div class="quick-links-header">
                            <h5><i class="fas fa-bolt me-2"></i> Quick Links</h5>
                        </div>
                        <div class="quick-links">
                            <a href="./product_add.php" class="quick-link">
                                <div class="quick-link-icon">
                                    <i class="fas fa-plus"></i>
                                </div>
                                <span class="quick-link-text">Add New Product</span>
                            </a>
                            <a href="./category_add.php" class="quick-link">
                                <div class="quick-link-icon">
                                    <i class="fas fa-folder-plus"></i>
                                </div>
                                <span class="quick-link-text">Add New Category</span>
                            </a>
                            <a href="./orders.php" class="quick-link">
                                <div class="quick-link-icon">
                                    <i class="fas fa-clipboard-list"></i>
                                </div>
                                <span class="quick-link-text">View Recent Orders</span>
                            </a>
                            <a href="./feedbacks.php" class="quick-link">
                                <div class="quick-link-icon">
                                    <i class="fas fa-comments"></i>
                                </div>
                                <span class="quick-link-text">Check Feedbacks</span>
                            </a>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.5/dist/sweetalert2.all.min.js"></script>
</body>
</html>
