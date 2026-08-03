 <?php
session_start();
include './config/db.php';
include './config/helpers.php';

$produtcts = [];
$stmt = $conn->query('SELECT id, name, price, image FROM products ORDER BY id DESC LIMIT 4');
if ($stmt) {
  $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
$feedbacks = [];
$stmt = $conn->prepare('
    SELECT f.*, u.name AS user_name
    FROM feedbacks f
    LEFT JOIN users u ON f.user_id = u.id
    WHERE f.status = "active"
    ORDER BY f.f_id DESC
    LIMIT 12
');
$stmt->execute();
$feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>GroceryGo.lk - Online Supermarket</title>
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
    
    .hero-modern{

background:#f0fdf4;
padding:80px 0;

}


.hero-modern h1{

font-size:50px;
font-weight:700;
color:#166534;

}


.hero-modern h1 span{

color:#f59e0b;

}


.hero-modern p{

font-size:18px;
color:#555;
margin:25px 0;

}


.hero-img{

border-radius:30px;
box-shadow:0 20px 40px rgba(0,0,0,.15);

}
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
    /* ===== Dark Theme ===== */

body{
    background:#0f172a;
    color:white;
}


/* Navbar */

.navbar{
    background:#111827 !important;
}


.navbar-brand{
    color:#22c55e !important;
}


.navbar-brand span{
    color:#facc15;
}


.nav-link{
    color:#e5e7eb !important;
}


.nav-link:hover{
    color:#22c55e !important;
}



/* Search */

.search-box input{
    background:#1f2937;
    border:none;
    color:white;
}


.search-box input::placeholder{
    color:#9ca3af;
}



/* Hero */

.hero-modern{

    background:
    linear-gradient(
    135deg,
    #020617,
    #064e3b
    );

    padding:100px 0;

}



.hero-modern h1{

    color:white;
    font-size:55px;

}


.hero-modern h1 span{

    color:#22c55e;

}


.hero-modern p{

    color:#cbd5e1;
}



/* Hero Image Effect */


.hero-img{

    border-radius:30px;

    transition:0.5s;

    box-shadow:
    0 0 30px rgba(34,197,94,0.3);

}



.hero-img:hover{

    transform:scale(1.08)
    rotate(2deg);

    box-shadow:
    0 0 50px rgba(34,197,94,0.7);

}



/* Buttons */


.btn-success{

    background:#22c55e;
    border:none;

    transition:0.3s;

}



.btn-success:hover{

    background:#16a34a;

    transform:
    translateY(-5px);

    box-shadow:
    0 10px 25px rgba(34,197,94,.5);

}
/* Category Cards */

.card{

    background:#111827 !important;
    color:white;

}


.category-card{

    background:
    rgba(31,41,55,0.8);

    border-radius:20px;

    transition:0.4s;

    overflow:hidden;

}


.category-card:hover{

    transform:translateY(-10px);

    box-shadow:
    0 15px 35px rgba(34,197,94,.3);

}


.category-card i{

    color:#22c55e;

    transition:.4s;

}


.category-card:hover i{

    transform:scale(1.2)
    rotate(10deg);

}


.category-card h5{

    color:white;

}

    @media (max-width: 767px) {
      .product-card {
        height: 360px;
      }
      
      .product-card .position-relative {
        height: 200px;
      }
    }
    
    .product-card .position-relative {
      height: 220px;
      overflow: hidden;
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
      .testimonial-section {
      background-color: var(--light);
      position: relative;
      overflow: hidden;
    }
      .testimonial-carousel {
      position: relative;
      margin: 0 auto;
      max-width: 1200px;
      overflow: hidden;
    }
      .testimonial-track {
      display: flex;
      transition: transform 0.5s ease-in-out;
      gap: 20px;
      width: 100%;
      cursor: grab;
      will-change: transform;
    }
      .testimonial-slide {
      min-width: calc(100% / 3);
      width: calc(100% / 3);
      padding: 10px;
      box-sizing: border-box;
      flex-shrink: 0;
    }
    
    @media (max-width: 991px) {
      .testimonial-slide {
        min-width: calc(100% / 2);
        width: calc(100% / 2);
      }
    }
    
    @media (max-width: 767px) {
      .testimonial-slide {
        min-width: 100%;
        width: 100%;
      }
    }
    
    .testimonial-card {
      background: white;
      border-radius: 15px;
      padding: 30px;
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
      height: 100%;
      display: flex;
      flex-direction: column;
      transition: transform 0.3s, box-shadow 0.3s;
    }
    
    .testimonial-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
    }
    
    .testimonial-stars {
      color: var(--accent);
      font-size: 1.1rem;
    }
    
    .testimonial-message {
      font-style: italic;
      margin-bottom: 20px;
      flex-grow: 1;
      line-height: 1.6;
    }
    
    .testimonial-avatar {
      width: 50px;
      height: 50px;
      flex-shrink: 0;
    }
    
    .slider-controls {
      position: absolute;
      width: 100%;
      top: 50%;
      transform: translateY(-50%);
      pointer-events: none;
      z-index: 2;
    }
      .slider-arrow {
      position: absolute;
      width: 44px;
      height: 44px;
      border-radius: 50%;
      background: white;
      color: var(--primary);
      border: 2px solid var(--primary);
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      pointer-events: auto;
      transition: all 0.3s ease;
      box-shadow: 0 3px 10px rgba(0,0,0,0.1);
      padding: 0;
      z-index: 5;
    }
    
    .slider-arrow:hover {
      background: var(--primary);
      color: white;
      transform: scale(1.1);
    }
    
    .slider-arrow:disabled {
      opacity: 0.5;
      cursor: not-allowed;
      transform: none;
    }
    
    .slider-arrow:focus {
      outline: none;
      box-shadow: 0 0 0 3px rgba(47, 133, 90, 0.3);
    }
    
    .slider-prev {
      left: 10px;
    }
    
    .slider-next {
      right: 10px;
    }
      .slider-dots {
      display: flex;
      gap: 10px;
      justify-content: center;
      margin-top: 20px;
    }
    
    .slider-dot {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      background-color: #ccc;
      cursor: pointer;
      transition: all 0.3s ease;
      border: none;
      padding: 0;
      margin: 0 4px;
      opacity: 0.6;
    }
    
    .slider-dot:hover {
      opacity: 0.9;
    }
    
    .slider-dot.active {
      background-color: var(--primary);
      transform: scale(1.3);
      opacity: 1;
    }
    
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
    
    .badge-discount {
      position: absolute;
      top: 10px;
      right: 10px;
      background-color: var(--accent);
      color: var(--dark);
      font-weight: bold;
      z-index: 2;
      padding: 0.35rem 0.65rem;
    }

    .navbar-nav .nav-link {
      color: var(--dark);
      font-weight: 500;
      padding: 0.5rem 1rem;
    }
    
    .username {
      font-weight: 600;
      color: var(--primary) !important;
    }
    
    .nav-link.active {
      font-weight: 600;
      color: var(--primary) !important;
    }
    
    /* Additional responsive testimonial styles */
    @media (max-width: 1199px) {
      .slider-prev {
        left: 0px;
      }
      
      .slider-next {
        right: 0px;
      }
    }
    
    @media (max-width: 767px) {
      .testimonial-section {
        padding: 60px 0;
      }
      
      .testimonial-card {
        padding: 20px;
      }
      
      .testimonial-message {
        font-size: 0.95rem;
      }
      
      .slider-prev, .slider-next {
        width: 35px;
        height: 35px;
        font-size: 0.8rem;
      }
      
      .slider-dot {
        width: 10px;
        height: 10px;
      }
      
      .testimonial-stars {
        font-size: 0.9rem;
      }
    }
    
    /* Animation for slide transitions */
    .testimonial-slide {
      transition: opacity 0.3s ease, transform 0.3s ease;
    }
    
    /* Additional product card styles for consistency */
    .badge-discount {
      position: absolute;
      top: 10px;
      right: 10px;
      background-color: var(--accent);
      color: var(--dark);
      font-weight: bold;
      z-index: 2;
      padding: 0.35rem 0.65rem;
    }
    
    /* Ensure equal height columns */
    .col-12.col-sm-6.col-md-4.col-lg-3.d-flex {
      display: flex;
    }
    
    /* Apply consistent price styling */
    .product-card .fw-bold.text-primary {
      white-space: nowrap;
      font-size: 1rem !important;
    }
    
    .product-card .text-decoration-line-through {
      white-space: nowrap;
      font-size: 0.8rem;
    }
    
    /* Ensure consistent button size */
    .product-card .btn.btn-sm {
      width: 32px;
      height: 32px;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 0;
    }
    
    /* Wishlist button styles */    .btn-wishlist {
      position: absolute;
      top: 10px;
      left: 10px;
      background-color: white;
      color: var(--primary);
      width: 36px;
      height: 36px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      border: none;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
      z-index: 2;
      transition: all 0.3s ease;
      cursor: pointer;
      opacity: 0.9;
      text-decoration: none;
      user-select: none;
      -webkit-user-select: none;
      -moz-user-select: none;
    }
    
    .btn-wishlist:hover {
      transform: scale(1.1);
      color: #e74c3c;
      opacity: 1;
    }
    
    .btn-wishlist.active {
      color: #e74c3c;
    }
    
    .btn-wishlist.active i {
      font-weight: 900;
    }
    /* Product Cards */


.product-card{

    background:#111827 !important;

    border-radius:20px;

    overflow:hidden;

    transition:.4s;

}



.product-card:hover{

    transform:
    translateY(-12px);

    box-shadow:

    0 20px 40px rgba(34,197,94,.25);

}



/* Product Image */


.product-card img{

    transition:.5s;

}



.product-card:hover img{

    transform:scale(1.12);

}



/* Product Name */

.product-card .card-title{

    color:white;

}



/* Price */

.product-card .text-primary{

    color:#22c55e !important;

}


/* Wishlist button */

.btn-wishlist{

    background:#111827;

    color:#22c55e;

}


.btn-wishlist:hover{

    background:#22c55e;

    color:white;

    transform:scale(1.15);

}
.bg-light{

    background:#020617 !important;

}


.text-muted{

    color:#94a3b8 !important;

}
.product-image{

    height:220px;
    object-fit:cover;

}

    /* Animation for wishlist button */
    @keyframes heartBeat {
      0% {
        transform: scale(1);
      }
      14% {
        transform: scale(1.3);
      }
      28% {
        transform: scale(1);
      }
      42% {
        transform: scale(1.3);
      }
      70% {
        transform: scale(1);
      }
    }
    
    .wishlist-animation {
      animation: heartBeat 1s;
    }
    
    /* SweetAlert2 toast styles */
    .swal2-toast-popup {
      padding: 10px 15px;
      max-width: 280px;
      font-size: 0.9rem;
    }
    
    .swal2-toast-popup .swal2-title,
    .swal2-toast-popup .swal2-html-container {
      font-size: 0.9rem;
      margin: 5px 0;
    }
  </style>
</head>

<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm py-3 sticky-top">
    <div class="container">
      <a class="navbar-brand" href="#">GroceryGo.lk <i class="fa-solid fa-basket-shopping ms-1"></i></a>     
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="#home">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="./views/about.php">About</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="./views/shop.php">Shop</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="./views/contact.php">Contact</a>
          </li>
        </ul>
        <ul class="navbar-nav ms-3">
          <li class="nav-item">
            <a class="nav-link <?php if ($current == 'wishlist.php') echo 'active'; ?> position-relative" href="./views/wishlist.php" title="Wishlist">
              <i class="fa-regular fa-heart"></i>
              <?php
              if (isset($_SESSION['user_id'])) {
                $stmt = $conn->prepare('SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?');
                $stmt->execute([$_SESSION['user_id']]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $count = (int)($result['count'] ?? 0);
                if ($count > 0) {
                  echo '<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger wishlist-count">'
                    . $count . '</span>';
                } else {
                  echo '<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger wishlist-count" style="display: none;">0</span>';
                }
              }
              ?>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?php if ($current == 'cart.php') echo 'active'; ?> position-relative" href="./views/cart.php" title="Cart">
              <i class="fa-solid fa-cart-shopping"></i>
              <?php
              if (isset($_SESSION['user_id'])) {
                $stmt = $conn->prepare('SELECT COUNT(*) as count FROM cart WHERE user_id = ?');
                $stmt->execute([$_SESSION['user_id']]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $count = (int)($result['count'] ?? 0);
                echo '<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-count"'
                  . ($count > 0 ? '' : ' style="display:none;"') . '>' . $count . '</span>';
              } else {
                echo '<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-count" style="display:none;">0</span>';
              }
              ?>
            </a>
          </li>
          <?php
          if (isset($_SESSION['name'])) {
            if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
              echo '<li class="nav-item">
                            <a class="nav-link" href="./views/admin/dashboard.php" title="Admin Dashboard"><i class="fa-solid fa-user-shield"></i></a>                            
                          </li>';
            } else
              echo '<li class="nav-item">
                        <a class="nav-link" href="./views/user/profile.php" title="Profile"><i class="fa-regular fa-user"></i></a>
                      </li>';
          } else {
            echo '<li class="nav-item">
                        <a class="nav-link" href="views/signin.php" title="Sign In"><i class="fa-regular fa-user"></i></a>
                      </li>';
          }
          ?>
           <?php if (isset($_SESSION['name'])) { ?>
           <li class="nav-link username"><?php echo htmlspecialchars($_SESSION['name']); ?></li>
         <?php } ?> 
        </ul>
      </div>
    </div>
  </nav>

  <!-- Hero Section -->
  <section class="hero-modern">

<div class="container">

<div class="row align-items-center">


<div class="col-md-6">

<h1>
Fresh Groceries 
<span>Delivered</span>
<br>
To Your Door 🚚
</h1>


<p>
Shop fresh fruits, vegetables, beverages 
and daily essentials from GroceryGo.lk.
</p>


<a href="./views/shop.php" 
class="btn btn-success btn-lg rounded-pill px-5">

Shop Now
<i class="fa fa-arrow-right ms-2"></i>

</a>


</div>



<div class="col-md-6 text-center">

<img src="./public/img/home/grocery-banner.jpg"
class="img-fluid hero-img">

</div>


</div>

</div>

</section>

  <!-- Categories Section -->
  <section class="py-5 bg-white">
    <div class="container">
      <h2 class="text-center mb-5 text-primary">Shop by Category</h2>
      <div class="row g-4">
        <div class="col-6 col-md-3">
          <div class="card category-card border-0 h-100 text-center py-4">
            <div class="card-body">
              <i class="fas fa-apple-alt fa-3x mb-3 text-primary"></i>
              <h5 class="card-title">Fresh Produce</h5>
              
            </div>
          </div>
        </div>
        <div class="col-6 col-md-3">
         <div class="card category-card border-0 h-100 text-center py-4">
            <div class="card-body">
              <i class="fas fa-wine-bottle fa-3x mb-3 text-primary"></i>
              <h5 class="card-title">Beverages</h5>
             
            </div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card category-card border-0 h-100 text-center py-4">
            <div class="card-body">
              <i class="fas fa-bread-slice fa-3x mb-3 text-primary"></i>
              <h5 class="card-title">Bakery</h5>
              
            </div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card category-card border-0 h-100 text-center py-4">
            <div class="card-body">
              <i class="fas fa-broom fa-3x mb-3 text-primary"></i>
              <h5 class="card-title">Household</h5>
             
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Featured Products Section -->
  <section class="py-5 bg-light">
    <div class="container">
      <div class="d-flex justify-content-between align-items-center mb-5">
        <h2 class="text-primary mb-0">Featured Products</h2>
        <a href="./views/shop.php" class="btn btn-outline-primary">View All <i class="ms-1 fas fa-arrow-right"></i></a>
      </div>      <div class="row g-4">
        <?php foreach ($products as $product): ?>
          <div class="col-12 col-sm-6 col-md-4 col-lg-3 d-flex">
            <div class="card product-card border-0">              <div class="position-relative">
               <img 
src="./uploads/products/<?php echo $product['image']; ?>" 
class="card-img-top product-image"
alt="<?php echo htmlspecialchars($product['name']); ?>">
                <span class="badge badge-discount rounded-pill px-2 py-1">Featured</span>                <?php if (isset($_SESSION['user_id'])): ?>
                  <button type="button" class="btn-wishlist add-to-wishlist-btn" data-product-id="<?php echo $product['id']; ?>" aria-label="Add to Wishlist">
                    <i class="far fa-heart"></i>
                  </button>
                <?php else: ?>
                  <a href="views/signin.php" class="btn-wishlist" aria-label="Add to Wishlist">
                    <i class="far fa-heart"></i>
                  </a>
                <?php endif; ?>
              </div>
              <div class="card-body">
                <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <span class="fw-bold text-primary fs-5">Rs. <?php echo number_format($product['price']); ?></span>
                  </div>
                  <?php if (isset($_SESSION['user_id'])): ?>
                    <button type="button" class="btn btn-sm btn-primary rounded-circle add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>" title="Add to Cart">
                      <i class="fas fa-plus"></i>
                    </button>
                  <?php else: ?>
                    <a href="views/signin.php" class="btn btn-sm btn-primary rounded-circle" title="Add to Cart">
                      <i class="fas fa-plus"></i>
                    </a>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- About Section -->
  <section id="about" class="py-5 bg-white">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-md-6 mb-4 mb-md-0">
          <h2 class="mb-3 text-primary">Why Choose GroceryGo?</h2>
          <p class="lead">We bring the supermarket to your doorstep with the freshest products and best prices in Colombo.</p>
          <div class="d-flex align-items-start mb-3">
            <i class="fas fa-bolt text-primary mt-1 me-3"></i>
            <div>
              <h5 class="mb-1">Fast Delivery</h5>
              <p class="mb-0 text-muted">Get your groceries delivered in as little as 2 hours</p>
            </div>
          </div>
          <div class="d-flex align-items-start mb-3">
            <i class="fas fa-leaf text-primary mt-1 me-3"></i>
            <div>
              <h5 class="mb-1">Fresh Products</h5>
              <p class="mb-0 text-muted">Direct from farms and trusted suppliers</p>
            </div>
          </div>
          <div class="d-flex align-items-start">
            <i class="fas fa-percentage text-primary mt-1 me-3"></i>
            <div>
              <h5 class="mb-1">Best Prices</h5>
              <p class="mb-0 text-muted">Competitive prices with regular discounts</p>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <img src="./public/img/home/grocery-banner02.jpg" class="img-fluid rounded-3 shadow" alt="Grocery Delivery">
        </div>
      </div>
    </div>
  </section>
  <!-- Testimonials Section -->
  <section id="testimonials" class="testimonial-section py-5">
    <div class="container">
      <h2 class="text-center mb-5 text-primary fw-bold">What Our Customers Say</h2>
      
      <div class="testimonial-carousel position-relative">
        <?php if (!$feedbacks) {
          echo '<p class="text-center">No testimonials available at the moment.</p>';
        } else { ?>
          <div class="testimonial-track" id="testimonialTrack">
            <?php foreach ($feedbacks as $feedback): ?>
              <div class="testimonial-slide">
                <div class="testimonial-card h-100">
                  <div class="mb-3 testimonial-stars">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                  </div>
                  <p class="testimonial-message">
                    "<?php echo htmlspecialchars($feedback['message']); ?>"
                  </p>
                  <div class="d-flex align-items-center mt-auto">
                    <div class="testimonial-avatar bg-primary rounded-circle d-flex align-items-center justify-content-center">
                      <i class="fas fa-user text-white"></i>
                    </div>
                    <div class="ms-3">
                      <h5 class="testimonial-author mb-0"><?php echo htmlspecialchars($feedback['user_name']); ?></h5>
                      <small class="text-muted">Verified Customer</small>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          
          <div class="slider-controls">
            <button class="slider-arrow slider-prev" id="prevBtn" aria-label="Previous testimonial">
              <i class="fas fa-chevron-left"></i>
            </button>
            <button class="slider-arrow slider-next" id="nextBtn" aria-label="Next testimonial">
              <i class="fas fa-chevron-right"></i>
            </button>
          </div>
          
          <div class="slider-dots text-center mt-4" id="sliderDots">
            <!-- Dots will be generated by JavaScript -->
          </div>
        <?php } ?>
      </div>
    </div>
  </section>

  <!-- Delivery Info Section -->
  <section class="py-5 bg-white">
    <div class="container">
      <div class="row g-4">
        <div class="col-md-4">
          <div class="d-flex align-items-center">
            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 60px; height: 60px;">
              <i class="fas fa-truck fa-lg"></i>
            </div>
            <div class="ms-4">
              <h5 class="mb-1">Free Delivery</h5>
              <p class="mb-0 text-muted">On orders over Rs. 3000</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="d-flex align-items-center">
            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 60px; height: 60px;">
              <i class="fas fa-undo fa-lg"></i>
            </div>
            <div class="ms-4">
              <h5 class="mb-1">Easy Returns</h5>
              <p class="mb-0 text-muted">Within 7 days</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="d-flex align-items-center">
            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 60px; height: 60px;">
              <i class="fas fa-headset fa-lg"></i>
            </div>
            <div class="ms-4">
              <h5 class="mb-1">24/7 Support</h5>
              <p class="mb-0 text-muted">Dedicated support</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="footer">
    <div class="container">
      <div class="row gy-4">
        <div class="col-lg-4">
          <div class="mb-4">
            <span class="footer-logo">GroceryGo.lk <i class="fas fa-basket-shopping"></i></span>
          </div>
          <p>Your one-stop online supermarket in Sri Lanka. Fresh groceries delivered to your doorstep.</p>
          <div class="mt-4">
            <a href="#" class="footer-social me-3"><i class="fab fa-facebook-f"></i></a>
            <a href="#" class="footer-social me-3"><i class="fab fa-instagram"></i></a>
            <a href="#" class="footer-social me-3"><i class="fab fa-twitter"></i></a>
          </div>
        </div>
        <div class="col-lg-2 col-md-4">
          <h5 class="text-white mb-4">Quick Links</h5>
          <a href="#" class="footer-link">Home</a>
          <a href="./views/about.php" class="footer-link">About</a>
          <a href="./views/shop.php" class="footer-link">Shop</a>
          <a href="./views/contact.php" class="footer-link">Contact</a>
        </div>
        <div class="col-lg-2 col-md-4">
          <h5 class="text-white mb-4">Categories</h5>
          <a href="#" class="footer-link">Fresh Produce</a>
          <a href="#" class="footer-link">Beverages</a>
          <a href="#" class="footer-link">Bakery</a>
          <a href="#" class="footer-link">Household</a>
        </div>
        <div class="col-lg-4 col-md-4">
          <h5 class="text-white mb-4">Contact Us</h5>
          <div class="mb-2"><i class="fas fa-map-marker-alt me-2"></i> 123 Supermarket St, Colombo 05</div>
          <div class="mb-2"><i class="fas fa-phone me-2"></i> <a href="tel:0715343747" class="footer-link">071 534 3747</a></div>
          <div class="mb-2"><i class="fas fa-envelope me-2"></i> <a href="mailto:info@grocerygo.lk" class="footer-link">info@grocerygo.lk</a></div>
        </div>
      </div>
      <div class="footer-bottom mt-5 pt-4 border-top border-secondary text-center text-white">
        &copy; 2023 GroceryGo.lk. All rights reserved.
      </div>
    </div>
    
  </footer>

  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.5/dist/sweetalert2.all.min.js"></script>
  <!-- Custom JS -->
  <script src="public/js/main.js"></script>
  <script src="public/js/cart.js"></script>
  <script src="public/js/wishlist.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Testimonial Slider Setup
      const track = document.getElementById('testimonialTrack');
      if (!track) return; // Exit if no testimonials are present
      
      const slides = document.querySelectorAll('.testimonial-slide');
      const totalSlides = slides.length;
      const prevBtn = document.getElementById('prevBtn');
      const nextBtn = document.getElementById('nextBtn');
      const sliderDots = document.getElementById('sliderDots');
      
      // Create dots
      for (let i = 0; i < totalSlides; i++) {
        const dot = document.createElement('button');
        dot.className = 'slider-dot';
        dot.setAttribute('aria-label', `Go to slide ${i + 1}`);
        dot.addEventListener('click', () => goToSlide(i));
        sliderDots.appendChild(dot);
      }
      
      const dots = document.querySelectorAll('.slider-dot');
      
      // Set initial state
      let currentSlide = 0;
      let slidesPerView = getSlidesPerView();
      let maxSlide = Math.max(0, totalSlides - slidesPerView);
      let autoplayInterval;
      
      function getSlidesPerView() {
        if (window.innerWidth >= 992) return 3;
        if (window.innerWidth >= 768) return 2;
        return 1;
      }
          function updateSlider() {
        // Ensure the track has a proper width based on the number of slides
        const trackWidth = totalSlides * (100 / slidesPerView);
        track.style.width = `${trackWidth}%`;
        
        // Update the transform to show the current slide
        const slideWidth = 100 / slidesPerView;
        const offset = currentSlide * (100 / totalSlides) * slidesPerView;
        track.style.transform = `translateX(-${offset}%)`;
        
        // Update controls visibility
        if (prevBtn) {
          prevBtn.disabled = currentSlide === 0;
          prevBtn.style.opacity = currentSlide === 0 ? "0.5" : "1";
        }
        if (nextBtn) {
          nextBtn.disabled = currentSlide >= maxSlide;
          nextBtn.style.opacity = currentSlide >= maxSlide ? "0.5" : "1";
        }
        
        // Update dots
        dots.forEach((dot, index) => {
          // Only show dots for navigable positions
          if (index <= maxSlide) {
            dot.style.display = 'inline-block';
            dot.classList.toggle('active', index === currentSlide);
          } else {
            dot.style.display = 'none';
          }
        });
        
        // Add animation class to current slides for a subtle effect
        slides.forEach((slide, index) => {
          if (index >= currentSlide && index < currentSlide + slidesPerView) {
            slide.style.opacity = '1';
            slide.style.transform = 'scale(1)';
          } else {
            slide.style.opacity = '0.5';
            slide.style.transform = 'scale(0.95)';
          }
        });
      }
      
      function goToSlide(slideIndex) {
        currentSlide = slideIndex;
        if (currentSlide < 0) currentSlide = 0;
        if (currentSlide > maxSlide) currentSlide = maxSlide;
        updateSlider();
        restartAutoplay();
      }
      
      function nextSlide() {
        goToSlide(currentSlide + 1);
      }
      
      function prevSlide() {
        goToSlide(currentSlide - 1);
      }
      
      function startAutoplay() {
        stopAutoplay();
        autoplayInterval = setInterval(() => {
          if (currentSlide >= maxSlide) {
            goToSlide(0); // Loop back to the start
          } else {
            nextSlide();
          }
        }, 5000); // Change slide every 5 seconds
      }
      
      function stopAutoplay() {
        if (autoplayInterval) clearInterval(autoplayInterval);
      }
      
      function restartAutoplay() {
        stopAutoplay();
        startAutoplay();
      }
      
      // Handle window resize
      window.addEventListener('resize', () => {
        slidesPerView = getSlidesPerView();
        maxSlide = Math.max(0, totalSlides - slidesPerView);
        if (currentSlide > maxSlide) currentSlide = maxSlide;
        updateSlider();
      });
      
      // Touch and mouse events for swiping
      let startX = 0;
      let isDragging = false;
      let startTime = 0;
        // Touch and Mouse events for more reliable slide interactions
      
      // Mouse events
      track.addEventListener('mousedown', (e) => {
        startX = e.clientX;
        isDragging = true;
        startTime = new Date().getTime();
        stopAutoplay();
        // Prevent default text selection while dragging
        track.style.cursor = 'grabbing';
        e.preventDefault();
      });
      
      window.addEventListener('mousemove', (e) => {
        if (!isDragging) return;
        const currentX = e.clientX;
        const diff = startX - currentX;
        
        // Don't apply transform during drag if there's no more slides in that direction
        if ((currentSlide === 0 && diff < 0) || (currentSlide >= maxSlide && diff > 0)) {
          return;
        }
        
        // Calculate movement based on cursor position
        const slideWidth = track.clientWidth / slidesPerView;
        const translateX = -currentSlide * (100 / totalSlides) * slidesPerView - (diff / slideWidth * 100) * 0.5;
        track.style.transform = `translateX(${translateX}%)`;
      });
      
      window.addEventListener('mouseup', (e) => {
        if (!isDragging) return;
        
        track.style.cursor = 'grab';
        const endX = e.clientX;
        const diff = startX - endX;
        const duration = new Date().getTime() - startTime;
        
        // Register as a swipe if movement is significant and fast enough
        if (Math.abs(diff) > 50 || (Math.abs(diff) > 20 && duration < 300)) {
          diff > 0 ? nextSlide() : prevSlide();
        } else {
          // Return to current position
          updateSlider();
        }
        
        isDragging = false;
        restartAutoplay();
      });
      
      // Touch events - using passive for better performance
      track.addEventListener('touchstart', (e) => {
        startX = e.touches[0].clientX;
        isDragging = true;
        startTime = new Date().getTime();
        stopAutoplay();
      }, { passive: true });
      
      track.addEventListener('touchmove', (e) => {
        if (!isDragging) return;
        const currentX = e.touches[0].clientX;
        const diff = startX - currentX;
        
        // Don't apply transform during drag if there's no more slides in that direction
        if ((currentSlide === 0 && diff < 0) || (currentSlide >= maxSlide && diff > 0)) {
          return;
        }
        
        const slideWidth = track.clientWidth / slidesPerView;
        // Calculate movement with resistance
        const offset = currentSlide * (100 / totalSlides) * slidesPerView;
        const moveX = (diff / slideWidth * 100) * 0.5;
        track.style.transform = `translateX(-${offset + moveX}%)`;
      }, { passive: true });
      
      track.addEventListener('touchend', (e) => {
        if (!isDragging) return;
        
        const endX = e.changedTouches[0].clientX;
        const diff = startX - endX;
        const duration = new Date().getTime() - startTime;
        
        // Swipe if the movement is significant and fast enough
        if (Math.abs(diff) > 50 || (Math.abs(diff) > 20 && duration < 300)) {
          diff > 0 ? nextSlide() : prevSlide();
        } else {
          // Snap back to current position
          updateSlider();
        }
        
        isDragging = false;
        restartAutoplay();
      }, { passive: true });
      
      // Button handlers
      if (prevBtn) prevBtn.addEventListener('click', () => {
        prevSlide();
        stopAutoplay();
      });
      
      if (nextBtn) nextBtn.addEventListener('click', () => {
        nextSlide();
        stopAutoplay();
      });
        // Initialize slider with a slight delay to ensure all elements are ready
      setTimeout(() => {
        // Force recalculation of slides per view
        slidesPerView = getSlidesPerView();
        maxSlide = Math.max(0, totalSlides - slidesPerView);
        
        // Initialize slider layout
        slides.forEach((slide) => {
          slide.style.width = `${100 / totalSlides}%`;
        });
        
        // Initial slider setup
        updateSlider();
        startAutoplay();
      }, 200);
      
      // Focus and blur events to stop/start autoplay
      track.addEventListener('mouseenter', stopAutoplay);
      track.addEventListener('mouseleave', startAutoplay);
    });
    
    // URL Parameter handling for alerts
    const urlParams = new URLSearchParams(window.location.search);

    if (urlParams.get('login') === 'success') {
        Swal.fire({
            icon: 'success',
            title: 'Welcome!',
            text: 'You have successfully logged in.',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        }).then(() => {
            const url = new URL(window.location);
            url.searchParams.delete('login');
            window.history.replaceState({}, document.title, url.pathname + url.search);
        });
    }

    if (urlParams.get('logout') === 'success') {
        Swal.fire({
            icon: 'success',
            title: 'Goodbye!',
            text: 'You have been successfully logged out.',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        }).then(() => {
            const url = new URL(window.location);
            url.searchParams.delete('logout');
            window.history.replaceState({}, document.title, url.pathname + url.search);
        });
    }
  </script>
  <script>
    // Wishlist button functionality for featured products
    document.addEventListener('DOMContentLoaded', function() {
      // Add to wishlist functionality
      const wishlistButtons = document.querySelectorAll('.add-to-wishlist-btn');
      
      wishlistButtons.forEach(button => {
        // Check if product is already in wishlist and update button appearance
        checkWishlistStatus(button);
          button.addEventListener('click', function(e) {
          e.preventDefault();
          e.stopPropagation();
          const productId = this.getAttribute('data-product-id');
          // Remove any tooltip or title that might be showing
          this.removeAttribute('title');
          fetch('controller/add_to_wishlist.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `product_id=${productId}`
          })
          .then(response => response.json())
          .then(data => {
            if (data.status === 'success') {
              // Visual feedback with heart animation
              this.classList.add('wishlist-animation');
              setTimeout(() => this.classList.remove('wishlist-animation'), 1000);
              
              // Toggle heart appearance
              const heartIcon = this.querySelector('i');
              if (data.action === 'added') {
                heartIcon.classList.remove('far');
                heartIcon.classList.add('fas');
                this.classList.add('active');
              } else if (data.action === 'removed') {
                heartIcon.classList.remove('fas');
                heartIcon.classList.add('far');
                this.classList.remove('active');
              }
              
              // Update wishlist count
              updateWishlistCount(data.count);
                // Show toast notification
              Swal.fire({
                toast: true,
                position: 'bottom-end',
                icon: data.icon,
                text: data.message,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                customClass: {
                  popup: 'swal2-toast-popup'
                }
              });
            } else {
              Swal.fire({
                toast: true,
                position: 'bottom-end',
                icon: data.icon,
                text: data.message,
                showConfirmButton: false,
                timer: 3000,
                customClass: {
                  popup: 'swal2-toast-popup'
                }
              });
            }
          })
          .catch(error => console.error('Error:', error));
        });
      });
      
      // Function to check if product is in wishlist and update button appearance
      function checkWishlistStatus(button) {
        const productId = button.getAttribute('data-product-id');
        
        fetch(`controller/check_wishlist.php?product_id=${productId}`)
          .then(response => response.json())
          .then(data => {
            if (data.inWishlist) {
              const heartIcon = button.querySelector('i');
              heartIcon.classList.remove('far');
              heartIcon.classList.add('fas');
              button.classList.add('active');
            }
          })
          .catch(error => console.error('Error:', error));
      }
    });
  </script>
  
</body>
</html>