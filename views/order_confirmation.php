<?php
    session_start();
    include '../config/db.php';

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header('Location: signin.php');
        exit;
    }

    // Check if order_id is provided
    if (!isset($_GET['order_id'])) {
        header('Location: cart.php');
        exit;
    }

    $order_id = $_GET['order_id'];
    $user_id = $_SESSION['user_id'];

    // Fetch order details
    $stmt = $conn->prepare('
        SELECT o.*, u.name as customer_name
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.order_id = ? AND o.user_id = ?
    ');
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    // Redirect if order not found or doesn't belong to user
    if (!$order) {
        header('Location: cart.php');
        exit;
    }

    // Fetch order items
    $stmt = $conn->prepare('
        SELECT oi.*, p.name as product_name
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ');
    $stmt->execute([$order_id]);
    $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Order Confirmation - GroceryGo.lk</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Fredoka+One&display=swap" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
    
    .text-success {
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
    
    /* Order confirmation specific styles */
    .order-confirm-section {
      background-color: var(--light);
      padding: 3rem 0;
    }
    
    .order-complete-icon {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      background-color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 10px 15px rgba(47, 133, 90, 0.15);
    }
    
    .order-complete-icon i {
      color: var(--primary);
    }
    
    .receipt-card {
      border-radius: 10px;
      transition: transform 0.3s, box-shadow 0.3s;
    }
    
    .receipt-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }
    
    @media (max-width: 767px) {
      .order-confirm-section {
        padding: 2rem 0;
      }
    }
  </style>
</head>
<body>
<!-- Navbar (same as other pages) -->
 <?php include_once('../includes/nav.php'); ?>

<section class="order-confirm-section py-5">
  <div class="container">    <div class="d-flex flex-column align-items-center mb-4">
      <div class="order-complete-icon mb-3">
        <i class="fa fa-circle-check fa-4x"></i>
      </div>
      <h1 class="text-center mb-2 text-primary fw-bold">Order Confirmed!</h1>
      <div class="fs-5 text-secondary text-center mb-4">Thank you for choosing GroceryGo.lk! Your order has been placed successfully.</div>
    </div>
    <div class="row justify-content-center">
      <div class="col-lg-7 col-xl-6">
        <div class="card receipt-card shadow-sm border-0 p-4 mb-4">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="fw-bold">Order #</span>
            <span><?php echo htmlspecialchars($order_id); ?></span>
          </div>
          <div class="mb-2">
            <?php foreach ($order_items as $item): ?>
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span><?php echo htmlspecialchars($item['product_name']); ?></span>
                <span>Rs. <?php echo number_format($item['price'], 2); ?> x <?php echo $item['quantity']; ?></span>
              </div>
            <?php endforeach; ?>
          </div>
          <hr>
          <div class="d-flex justify-content-between mb-2">
            <span>Subtotal</span>
            <span>Rs. <?php echo number_format($order['total_amount'], 2); ?></span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <span>Delivery</span>
            <span>Free</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <span>Payment Method</span>
            <span><?php echo ucwords(str_replace('_', ' ', $order['payment_method'])); ?></span>
          </div>
          <hr>          <div class="d-flex justify-content-between mb-3">
            <span class="fs-5 fw-bold">Total</span>
            <span class="fs-5 fw-bold text-primary">Rs. <?php echo number_format($order['total_amount'], 2); ?></span>
          </div><div class="mt-3">
            <h6 class="mb-2">Shipping Details:</h6>
            <p class="mb-2">
              <strong>Customer Name:</strong> 
              <?php echo htmlspecialchars($order['customer_name']); ?>
            </p>
            <p class="mb-1">
              <strong>Phone:</strong> 
              <?php echo htmlspecialchars($order['phone']); ?>
            </p>
            <?php 
                $address_parts = explode("'", $order['shipping_address']);
                $labels = ['Street', 'City', 'Province', 'Postal Code', 'Country'];
                foreach ($address_parts as $index => $part):
            ?>
                <p class="mb-1">
                    <strong><?php echo $labels[$index]; ?>:</strong> 
                    <?php echo htmlspecialchars(trim($part)); ?>
                </p>
            <?php endforeach; ?>
          </div>
        </div>        <div class="text-center">
          <a href="../index.php" class="btn btn-primary me-2"><i class="fa fa-home me-2"></i>Home</a>
          <a href="user/orders.php" class="btn btn-outline-primary"><i class="fa fa-box me-2"></i>My Orders</a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Footer (same as other pages) -->
 <?php include_once('../includes/footer.php'); ?>
<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="../public/js/main.js"></script>
</body>
</html>