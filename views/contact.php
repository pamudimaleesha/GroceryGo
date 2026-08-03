<?php
    session_start();
    include '../config/db.php';

    $users_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    if (!$users_id) {
        header('Location: ./signin.php');
        exit;
    }

    $users = $conn->prepare('SELECT * FROM users WHERE id = ?');
    $users->execute([$users_id]);
    $user = $users->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - GroceryGo.lk</title>
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
        
        /* Contact page specific styles */
        .contact-hero-section {
            background-color: var(--light);
            padding: 3rem 0;
        }
        
        .contact-title {
            color: var(--primary);
            font-weight: 700;
            font-size: 2.5rem;
        }
        
        .contact-desc {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #4A5568;
        }
        
        .contact-details li i {
            color: var(--primary);
            width: 20px;
            text-align: center;
        }
        
        .contact-link {
            color: #4A5568;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .contact-link:hover {
            color: var(--primary);
        }
        
        .contact-form-card {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .contact-form-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }
        
        .contact-input {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        
        .contact-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(47, 133, 90, 0.1);
        }
        
        .contact-map-section {
            padding: 3rem 0;
        }
        
        .map-embed-wrapper {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .map-embed-wrapper:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }
        
        @media (max-width: 767px) {
            .contact-hero-section,
            .contact-map-section {
                padding: 2rem 0;
            }
            
            .contact-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
<!-- Navbar (copied from index.html, do not change) -->
 <?php include_once('../includes/nav.php'); ?>

<section class="contact-hero-section py-5">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-6 mb-4 mb-lg-0">        <h1 class="contact-title mb-3">Contact Us</h1>
        <p class="contact-desc mb-4">We'd love to hear from you! Whether you have a question about our products, need help with an order, or just want to share your experience, our team is here to help. Reach out to us using the form, or connect with us directly using the details below.</p>
        <ul class="contact-details list-unstyled mb-4">
          <li class="mb-2"><i class="fa-solid fa-phone me-2"></i> <a href="tel:0715343747" class="contact-link">0715343747</a></li>
          <li class="mb-2"><i class="fa-solid fa-envelope me-2"></i> <a href="mailto:info@grocerygo.lk" class="contact-link">info@grocerygo.lk</a></li>
          <li class="mb-2"><i class="fa-solid fa-location-dot me-2"></i> 123 Supermarket St, Colombo 05, Sri Lanka</li>
        </ul>
        <div class="contact-social mt-3">
          <a href="#" class="footer-social me-2" title="Facebook"><i class="fab fa-facebook-f"></i></a>
          <a href="#" class="footer-social me-2" title="Instagram"><i class="fab fa-instagram"></i></a>
          <a href="#" class="footer-social" title="Twitter"><i class="fab fa-twitter"></i></a>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="contact-form-card p-4 shadow-lg rounded-4">          <form id="contactForm" novalidate>
            <h3 class="text-center mb-3" style="color: var(--primary); font-size: 2rem; font-weight: 400">Show some Love <i class="fas fa-heart" style="font-size: 1.8rem;"></i></h3>
            <div class="row mb-3">
              <div class="col-md-6 mb-3 mb-md-0">
                <input type="text" class="form-control contact-input" id="name" value="<?php echo htmlspecialchars($user['name']); ?>" required readonly>
              </div>
              <div class="col-md-6">
                <input type="email" class="form-control contact-input" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" required readonly>
              </div>
            </div>
            <div class="mb-3">
              <textarea class="form-control contact-input" id="message" name="message" rows="5" placeholder="Your Message" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2">Submit Feedback</button>
          </form>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.5/dist/sweetalert2.all.min.js"></script>
        <script>
          document.getElementById('contactForm').addEventListener('submit', function(e) {
              e.preventDefault();
              
              const message = document.getElementById('message').value.trim();
              if (!message) {
                  Swal.fire({
                      icon: 'warning',
                      title: 'Message Required',
                      text: 'Please enter your message before submitting.',
                      showConfirmButton: false,
                      timer: 1800
                  });
                  return;
              }
              
              fetch('../controller/feedback_process.php', {
                  method: 'POST',
                  headers: {
                      'Content-Type': 'application/x-www-form-urlencoded',
                  },
                  body: 'message=' + encodeURIComponent(message)
              })
              .then(response => response.json())
              .then(data => {
                  if (data.status === 'success') {
                      Swal.fire({
                          icon: 'success',
                          title: 'Thank you!',
                          text: data.message,
                          showConfirmButton: false,
                          timer: 1800
                      }).then(() => {
                          // Clear the form after successful submission
                          document.getElementById('message').value = '';
                        });
                  } else {
                      Swal.fire({
                          icon: 'error',
                          title: 'Error',
                          text: data.message,
                          showConfirmButton: false,
                          timer: 1800
                      });
                  }
              })
              .catch(error => {
                  console.error('Error:', error);
                  Swal.fire({
                      icon: 'error',
                      title: 'Error',
                      text: 'An error occurred. Please try again.',
                      showConfirmButton: false,
                      timer: 1800
                  });
              });
          });
        </script>
      </div>
    </div>
  </div>
</section>

<!-- Map Section -->
<section class="contact-map-section py-4">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-10">        <div class="map-embed-wrapper rounded-4 overflow-hidden shadow">
          <iframe class="contact-map-iframe" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d63316.29376355144!2d79.8250176!3d6.9270786!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3ae2595b6b6b6b6b%3A0x7b7b7b7b7b7b7b7b!2sColombo%2C%20Sri%20Lanka!5e0!3m2!1sen!2slk!4v1680000000000!5m2!1sen!2slk" width="100%" height="340" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="GroceryGo.lk Location"></iframe>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Footer (copied from index.html, do not change) -->
 <?php include_once('../includes/footer.php'); ?>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="../public/js/main.js"></script>
</body>
</html>
