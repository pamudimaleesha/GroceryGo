<?php
    session_start();
    include '../config/db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About Us - GroceryGo.lk</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Fredoka+One&display=swap" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- SweetAlert2 -->
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.5/dist/sweetalert2.min.css" rel="stylesheet">
  <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">
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
      background-color: #f8fcf9;
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
      .username {
      font-weight: 600;
      color: var(--primary) !important;
    }
    
    /* About page specific styles */
    .about-hero {
      background-color: var(--light);
      padding: 5rem 0;
    }
    
    .about-image {
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }
    
    .about-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.5s ease;
    }
    
    .about-image:hover img {
      transform: scale(1.03);
    }
    
   .feature-card{

border-radius:20px;

transition:.4s;

overflow:hidden;

}

.feature-card:hover{

transform:translateY(-12px);

box-shadow:0 20px 45px rgba(0,0,0,.15);

}
.feature-card i{

background:#F0FFF4;

padding:25px;

border-radius:50%;

transition:.4s;

}

.feature-card:hover i{

background:#38A169;

color:white;

}
    
    .values-section {
      background-color: var(--light);
      padding: 5rem 0;
    }
    
    .value-card {
      background-color: white;
      border-radius: 10px;
      padding: 2rem;
      text-align: center;
      height: 100%;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
      transition: transform 0.3s ease;
    }
    
    .value-card:hover {
      transform: translateY(-5px);
    }
    
    .value-icon {
      width: 70px;
      height: 70px;
      background-color: var(--light);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1.5rem;
      color: var(--primary);
      font-size: 1.8rem;
    }
    
    .feature-list li {
      margin-bottom: 1rem;
      display: flex;
      align-items: center;
    }
    
    .feature-list li i {
      color: var(--primary);
      margin-right: 1rem;
      font-size: 1.2rem;
    }
    
    .about-hero{
background:linear-gradient(135deg,#F0FFF4,#ffffff);
padding:90px 0;
position:relative;
overflow:hidden;
}

.about-hero::before{
content:'';
position:absolute;
width:350px;
height:350px;
background:#38A16920;
border-radius:50%;
top:-100px;
right:-120px;
}

.about-hero::after{
content:'';
position:absolute;
width:250px;
height:250px;
background:#F6E05E25;
border-radius:50%;
bottom:-100px;
left:-100px;
}
    .about-hero p {
      font-size: 1.1rem;
      line-height: 1.8;
      color: #4A5568;
      margin-bottom: 2rem;
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
    
    .team-section {
      padding: 5rem 0;
      background-color: white;
    }
    
    .team-card {
      text-align: center;
      padding: 1.5rem;
      border-radius: 10px;
      background-color: white;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
      transition: transform 0.3s ease;
    }
    
 

.team-card:hover{

transform:translateY(-10px);

box-shadow:0 20px 40px rgba(0,0,0,.15);

}
    
    .team-img-container {
      width: 150px;
      height: 150px;
      border-radius: 50%;
      overflow: hidden;
      margin: 0 auto 1.5rem;
      border: 5px solid var(--light);
    }
    
    .team-img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    
    .btn-outline-primary {
      color: var(--primary);
      border-color: var(--primary);
    }
    
    .btn-outline-primary:hover {
      background-color: var(--primary);
      color: white;
    }
    
    @media (max-width: 767px) {
      .about-hero {
        padding: 3rem 0;
      }
      
      .about-image {
        margin-top: 2rem;
      }
      
      .values-section {
        padding: 3rem 0;
      }
    }
  </style>
</head>
<body>
<!-- Navbar -->
<?php include_once('../includes/nav.php'); ?>

<!-- About Hero Section -->
<section class="about-hero py-5">
  <div class="container">
    <div class="row align-items-center g-4">
      <div class="col-lg-6 mb-4 mb-lg-0">
        <h2 class="text-primary display-4 fw-bold">About GroceryGo.lk</h2>
        <p class="mb-4">
          Welcome to GroceryGo.lk, your premier online supermarket in Sri Lanka. Founded with a commitment to making grocery shopping convenient, affordable, and enjoyable, GroceryGo.lk has quickly become a trusted name for online grocery delivery across Colombo and its suburbs.
          <br><br>
          Our journey began with a simple mission: to provide busy families and professionals with a reliable way to stock their kitchens without the hassle of traditional grocery shopping. Today, we pride ourselves on offering a wide selection of fresh produce, pantry staples, household essentials, and more—all delivered directly to your doorstep.
          <br><br>
          At GroceryGo.lk, we work directly with local farmers and trusted suppliers to bring you the freshest products at competitive prices. Our dedicated team carefully selects, packs, and delivers your groceries with the utmost care, ensuring everything arrives in perfect condition.
        </p>
        <ul class="feature-list list-unstyled mb-4">
          <li><i class="fa-solid fa-truck-fast"></i>Fast delivery within Colombo and suburbs</li>
          <li><i class="fa-solid fa-leaf"></i>Fresh produce sourced from local farmers</li>
          <li><i class="fa-solid fa-apple-whole"></i>Quality products and competitive pricing</li>
          <li><i class="fa-solid fa-medal"></i>100% satisfaction guarantee on all products</li>
          <li><i class="fa-solid fa-recycle"></i>Eco-friendly packaging initiatives</li>
        </ul>
        <a href="./shop.php" class="btn btn-primary btn-lg px-4">Shop Now</a>
      </div>
      <div class="col-lg-6">
        <div class="about-image">
          <img src="../public/img/about/about_cover.jpg" alt="GroceryGo.lk" class="img-fluid">
        </div>
      </div>
    </div>
  </div>
</section>
<section class="py-5 bg-white">
<div class="container">

<div class="row text-center">

<div class="col-md-3">
<h2 class="text-success fw-bold">5000+</h2>
<p>Happy Customers</p>
</div>

<div class="col-md-3">
<h2 class="text-success fw-bold">120+</h2>
<p>Fresh Products</p>
</div>

<div class="col-md-3">
<h2 class="text-success fw-bold">24/7</h2>
<p>Customer Support</p>
</div>

<div class="col-md-3">
<h2 class="text-success fw-bold">100%</h2>
<p>Quality Guaranteed</p>
</div>

</div>
</div>
</section>
<!-- Features Section -->
<section class="py-5 bg-white">
  <div class="container">
    <h2 class="text-center text-primary mb-5 fw-bold">Why Choose GroceryGo.lk?</h2>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="feature-card">
          <div class="text-center mb-4">
            <i class="fas fa-bolt text-primary fa-3x"></i>
          </div>
          <h4 class="text-center mb-3">Fast Delivery</h4>
          <p class="text-muted">We deliver your groceries in as little as 2 hours within Colombo city and offer next-day delivery to suburbs, ensuring you never run out of essentials.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="feature-card">
          <div class="text-center mb-4">
            <i class="fas fa-apple-alt text-primary fa-3x"></i>
          </div>
          <h4 class="text-center mb-3">Fresh Products</h4>
          <p class="text-muted">We handpick the freshest produce from local farms and reliable suppliers. Our quality control ensures you receive only the best products.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="feature-card">
          <div class="text-center mb-4">
            <i class="fas fa-tag text-primary fa-3x"></i>
          </div>
          <h4 class="text-center mb-3">Best Prices</h4>
          <p class="text-muted">By cutting out middlemen and optimizing our operations, we offer competitive prices and regular promotions to help you save on your grocery bills.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Values Section -->
<section class="values-section">
  <div class="container">
    <h2 class="text-center text-primary mb-5 fw-bold">Our Core Values</h2>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="value-card">
          <div class="value-icon">
            <i class="fas fa-leaf"></i>
          </div>
          <h4 class="mb-3">Freshness</h4>
          <p class="text-muted">We are committed to providing the freshest groceries. Our products are carefully selected and delivered promptly to ensure quality and freshness.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="value-card">
          <div class="value-icon">
            <i class="fas fa-users"></i>
          </div>
          <h4 class="mb-3">Community</h4>
          <p class="text-muted">We support local farmers and businesses, contributing to the growth of our community while providing you with locally sourced products.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="value-card">
          <div class="value-icon">
            <i class="fas fa-shield-alt"></i>
          </div>
          <h4 class="mb-3">Trust</h4>
          <p class="text-muted">We value your trust and strive to maintain transparency in our operations. Your satisfaction and safety are our top priorities.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Team Section -->
<section class="team-section">
  <div class="container">
    <h2 class="text-center text-primary mb-5 fw-bold">Meet Our Team</h2>
    <div class="row g-4">
      <div class="col-lg-3 col-md-6">
        <div class="team-card">
          <div class="team-img-container">
            <img src="../public/img/about/team-1.jpg" class="team-img" alt="Team Member">
          </div>
          <h4>Suchintha Pathum</h4>
          <p class="text-muted">Founder & CEO</p>
          <p class="small">With over 15 years in retail management, Suchintha leads our vision to revolutionize grocery shopping in Sri Lanka.</p>
        </div>
      </div>
      <div class="col-lg-3 col-md-6">
        <div class="team-card">
          <div class="team-img-container">
            <img src="../public/img/about/team-2.jpg" class="team-img" alt="Team Member">
          </div>
          <h4>Kaveesha Ekanayaka</h4>
          <p class="text-muted">Operations Manager</p>
          <p class="small">Kaveesha ensures our operations run smoothly, from product sourcing to delivery logistics.</p>
        </div>
      </div>
      <div class="col-lg-3 col-md-6">
        <div class="team-card">
          <div class="team-img-container">
            <img src="../public/img/about/team-3.jpg" class="team-img" alt="Team Member">
          </div>
          <h4>Amal Silva</h4>
          <p class="text-muted">Quality Control</p>
          <p class="small">Amal's expertise in food safety and quality ensures every product meets our strict standards.</p>
        </div>
      </div>
      <div class="col-lg-3 col-md-6">
        <div class="team-card">
          <div class="team-img-container">
            <img src="../public/img/about/team-4.jpg" class="team-img" alt="Team Member">
          </div>
          <h4>Naduni Deshani</h4>
          <p class="text-muted">Technology Lead</p>
          <p class="small">Naduni develops and maintains our website and mobile app to provide a seamless shopping experience.</p>
        </div>
      </div>
    </div>
  </div>
</section>
<section class="py-5 text-center bg-success text-white">

<div class="container">

<h2>Ready to Shop Fresh Groceries?</h2>

<p>Order today and get fast delivery at your doorstep.</p>

<a href="shop.php" class="btn btn-warning btn-lg">
Start Shopping
</a>

</div>

</section>
<!-- Footer -->
<?php include_once('../includes/footer.php'); ?>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.5/dist/sweetalert2.all.min.js"></script>
</body>
</html>
