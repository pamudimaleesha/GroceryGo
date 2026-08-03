<?php
    session_start();
    include '../config/db.php';

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header('Location: signin.php');
        exit;
    }

    // Fetch cart items with product details
    $user_id = $_SESSION['user_id'];
    $cart_items = [];
    $cart_total = 0;

    $stmt = $conn->prepare('
        SELECT c.cart_id, c.quantity, p.id as product_id, p.name, p.price, p.qty as stock
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ? 
        ORDER BY c.added_at DESC
    ');
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate cart total
    foreach ($cart_items as $item) {
        $cart_total += $item['price'] * $item['quantity'];
    }

    // Process checkout form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $phone = $_POST['phone'];
            $street = $_POST['street'];
            $city = $_POST['city'];
            $province = $_POST['province'];
            $postal_code = $_POST['postal_code'];
            $country = $_POST['country'];
            $payment_method = $_POST['payment_method'];            // Format the complete address with ' separator
            $address = implode("'", array_filter([
                trim($street),
                trim($city),
                trim($province),
                trim($postal_code),
                trim($country)
            ]));
            
            // Start transaction
            $conn->beginTransaction();
            
            // Insert into orders table
            $stmt = $conn->prepare('
                INSERT INTO orders (user_id, order_date, total_amount, status, payment_method, phone, shipping_address)
                VALUES (?, NOW(), ?, ?, ?, ?, ?)
            ');
            $initial_status = 'pending';
            $stmt->execute([$user_id, $cart_total, $initial_status, $payment_method, $phone, $address]);
            
            $order_id = $conn->lastInsertId();
            
            // Insert order items
            $stmt = $conn->prepare('
                INSERT INTO order_items (order_id, product_id, quantity, price)
                VALUES (?, ?, ?, ?)
            ');
            
            foreach ($cart_items as $item) {
                $stmt->execute([
                    $order_id,
                    $item['product_id'],
                    $item['quantity'],
                    $item['price']
                ]);
                
                // Update product stock
                $update_stock = $conn->prepare('
                    UPDATE products 
                    SET qty = qty - ? 
                    WHERE id = ?
                ');
                $update_stock->execute([$item['quantity'], $item['product_id']]);
            }
            
            // Clear user's cart
            $stmt = $conn->prepare('DELETE FROM cart WHERE user_id = ?');
            $stmt->execute([$user_id]);
            
            // Commit transaction
            $conn->commit();
            
            // Redirect to order confirmation
            header("Location: order_confirmation.php?order_id=" . $order_id);
            exit;
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollBack();
            $error_message = "An error occurred while processing your order. Please try again.";
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkout - GroceryGo.lk</title>
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
    
    /* Checkout page specific styles */
    .checkout-hero-section {
      background-color: var(--light);
      padding: 3rem 0;
    }
    
    .checkout-form-card {
      border-radius: 10px;
      overflow: hidden;
      transition: transform 0.3s, box-shadow 0.3s;
    }
    
    .checkout-form-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }
    
    .form-control:focus,
    .form-select:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 0.25rem rgba(47, 133, 90, 0.25);
    }
    
    .cart-summary {
      background-color: white;
      border-radius: 10px;
      transition: transform 0.3s, box-shadow 0.3s;
    }
    
    .cart-summary:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }
    
    .text-success {
      color: var(--primary) !important;
    }
    
    @media (max-width: 767px) {
      .checkout-hero-section {
        padding: 2rem 0;
      }
    }
  </style>
</head>
<body>
<!-- Navbar (same as other pages) -->
 <?php include_once('../includes/nav.php'); ?>

<section class="checkout-hero-section py-5">
  <div class="container">
    <h1 class="mb-4 text-center text-primary fw-bold">Checkout</h1>
    <div class="row g-4 align-items-start">
      <!-- Checkout Form -->
      <div class="col-lg-7">
        <div class="card shadow-sm border-0 p-4 checkout-form-card">          <h4 class="mb-3 text-primary">Shipping Details</h4>
          <?php if (isset($error_message)): ?>
            <div class="alert alert-danger" role="alert">
              <?php echo htmlspecialchars($error_message); ?>
            </div>
          <?php endif; ?>
          
          <form method="post" action="">            
            <div class="mb-3">
              <label for="checkoutPhone" class="form-label">Phone Number</label>
              <input type="tel" class="form-control" id="checkoutPhone" name="phone" pattern="[0-9]{10}" title="Please enter a valid 10-digit phone number" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" required autocomplete="off">
            </div>
            <div class="row">
              <div class="col-12 mb-3">
                <label for="street" class="form-label">Street Address</label>
                <input type="text" class="form-control" id="street" name="street" value="<?php echo isset($_POST['street']) ? htmlspecialchars($_POST['street']) : ''; ?>" required autocomplete="off">
              </div>
              <div class="col-md-6 mb-3">
                <label for="city" class="form-label">City</label>
                <input type="text" class="form-control" id="city" name="city" value="<?php echo isset($_POST['city']) ? htmlspecialchars($_POST['city']) : ''; ?>" required autocomplete="off">
              </div>
              <div class="col-md-6 mb-3">
                <label for="province" class="form-label">Province</label>
                <select class="form-select" id="province" name="province" required>
                  <option value="" disabled <?php echo !isset($_POST['province']) ? 'selected' : ''; ?>>Select Province</option>
                  <option value="Central" <?php echo (isset($_POST['province']) && $_POST['province'] === 'Central') ? 'selected' : ''; ?>>Central Province</option>
                  <option value="Eastern" <?php echo (isset($_POST['province']) && $_POST['province'] === 'Eastern') ? 'selected' : ''; ?>>Eastern Province</option>
                  <option value="North Central" <?php echo (isset($_POST['province']) && $_POST['province'] === 'North Central') ? 'selected' : ''; ?>>North Central Province</option>
                  <option value="Northern" <?php echo (isset($_POST['province']) && $_POST['province'] === 'Northern') ? 'selected' : ''; ?>>Northern Province</option>
                  <option value="North Western" <?php echo (isset($_POST['province']) && $_POST['province'] === 'North Western') ? 'selected' : ''; ?>>North Western Province</option>
                  <option value="Sabaragamuwa" <?php echo (isset($_POST['province']) && $_POST['province'] === 'Sabaragamuwa') ? 'selected' : ''; ?>>Sabaragamuwa Province</option>
                  <option value="Southern" <?php echo (isset($_POST['province']) && $_POST['province'] === 'Southern') ? 'selected' : ''; ?>>Southern Province</option>
                  <option value="Uva" <?php echo (isset($_POST['province']) && $_POST['province'] === 'Uva') ? 'selected' : ''; ?>>Uva Province</option>
                  <option value="Western" <?php echo (isset($_POST['province']) && $_POST['province'] === 'Western') ? 'selected' : ''; ?>>Western Province</option>
                </select>
              </div>
              <div class="col-md-6 mb-3">
                <label for="postalCode" class="form-label">Postal Code</label>
                <input type="text" class="form-control" id="postalCode" name="postal_code" pattern="[0-9]{5}" title="Please enter a valid 5-digit postal code" value="<?php echo isset($_POST['postal_code']) ? htmlspecialchars($_POST['postal_code']) : ''; ?>" required autocomplete="off">
              </div>
              <div class="col-md-6 mb-3">
                <label for="country" class="form-label">Country</label>
                <input type="text" class="form-control" id="country" name="country" value="Sri Lanka" readonly>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label" for="paymentMethod">Payment Method</label>
              <select class="form-select" id="paymentMethod" name="payment_method" required>
                <option value="cod" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] === 'cod') ? 'selected' : ''; ?>>Cash on Delivery</option>
                <option value="pickup" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] === 'pickup') ? 'selected' : ''; ?>>Pickup from Shop</option>
              </select>
            </div>            <button class="btn btn-primary w-100 py-2 fs-5 mt-2" type="submit" <?php echo empty($cart_items) ? 'disabled' : ''; ?>>
              <i class="fa fa-check-circle me-2"></i>Place Order
            </button>
            <?php if (empty($cart_items)): ?>
              <div class="text-center text-muted mt-2">
                <small>Please add items to your cart before checking out</small>
              </div>
            <?php endif; ?>
          </form>
        </div>
      </div>      <!-- Order Summary -->
      <div class="col-lg-5">
        <div class="cart-summary card shadow-sm border-0 p-4 sticky-top" style="top: 100px;">
          <h4 class="mb-3 text-primary">Order Summary</h4>
          <?php if (empty($cart_items)): ?>            <div class="text-center py-4">
              <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
              <h5 class="text-primary">Your cart is empty</h5><a href="shop.php" class="btn btn-primary mt-3">
                <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
              </a>
            </div>
          <?php else: ?>
            <div class="mb-3">
              <?php foreach ($cart_items as $item): ?>
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <span class="fw-bold"><?php echo htmlspecialchars($item['name']); ?></span>
                  <span>Rs. <?php echo number_format($item['price'], 2); ?> x <?php echo $item['quantity']; ?></span>
                </div>
              <?php endforeach; ?>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <span>Subtotal</span>
              <span class="fw-bold">Rs. <?php echo number_format($cart_total, 2); ?></span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <span>Delivery</span>
              <span>Free</span>
            </div>
            <hr>
            <div class="d-flex justify-content-between mb-3">
              <span class="fs-5 fw-bold">Total</span>
              <span class="fs-5 fw-bold text-success">Rs. <?php echo number_format($cart_total, 2); ?></span>
            </div>
          <?php endif; ?>
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