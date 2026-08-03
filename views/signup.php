<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up - GroceryGo.lk</title>
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
      background-color: var(--light);
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      padding: 20px;
    }
    
    .signup-card {
      background-color: white;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      padding: 40px;
      width: 100%;
      max-width: 450px;
      margin: 0 auto;
    }
    
    .signup-logo {
      font-family: 'Fredoka One', cursive;
      font-size: 2rem;
      color: var(--primary);
      text-align: center;
      margin-bottom: 20px;
    }
    
    .signup-title {
      font-size: 1.5rem;
      font-weight: 600;
      text-align: center;
      margin-bottom: 30px;
      color: var(--dark);
    }
    
    .form-label {
      color: var(--dark);
      font-weight: 500;
    }
    
    .form-control {
      padding: 10px 15px;
      border-radius: 10px;
      border: 1px solid #e2e8f0;
    }
    
    .form-control:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 0.25rem rgba(47, 133, 90, 0.25);
    }
    
    .btn-primary {
      background-color: var(--primary);
      border-color: var(--primary);
      border-radius: 10px;
      font-weight: 500;
      padding: 10px;
    }
    
    .btn-primary:hover {
      background-color: var(--secondary);
      border-color: var(--secondary);
    }
    
    .signup-link {
      color: var(--primary);
      text-decoration: none;
      font-weight: 500;
    }
    
    .signup-link:hover {
      color: var(--secondary);
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="signup-card">
       <a href="../index.php" style="text-decoration: none;"> <div class="signup-logo">GroceryGo.lk <i class="fa-solid fa-shopping-basket"></i></div> </a>
    <div class="signup-title">Create Your Account</div>
    <form method="post" action="../controller/sign_up_process.php">
      <div class="mb-3">
        <label for="signupName" class="form-label">Full Name</label>
        <input type="text" class="form-control" id="signupName" name="name" required autofocus autocomplete="off">
      </div>
      <div class="mb-3">
        <label for="signupEmail" class="form-label">Email address</label>
        <input type="email" class="form-control" id="signupEmail" name="email" required autocomplete="off">
      </div>
      <div class="mb-3">
        <label for="signupPassword" class="form-label">Password</label>
        <input type="password" class="form-control" id="signupPassword" name="password" required autocomplete="off">
      </div>
      <div class="mb-3">
        <label for="signupConfirmPassword" class="form-label">Confirm Password</label>
        <input type="password" class="form-control" id="signupConfirmPassword" name="confirmPassword" required autocomplete="off">
      </div>
      <button class="btn btn-primary w-100 py-2 mt-2" type="submit">Sign Up</button>
    </form>
    <div class="text-center mt-3">
      <span class="text-muted">Already have an account?</span>
      <a href="./signin.php" class="signup-link ms-1">Sign in</a>
    </div>
  </div>
</body>
</html>
