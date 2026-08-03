<?php
session_start();
require '../../config/db.php';

if (isset($_POST['update_password'])) {
    $currentPassword = $_POST['current-password'];
    $newPassword     = $_POST['new-password'];
    $confirmPassword = $_POST['confirm-password'];

    $userId = $_SESSION['user_id'];

    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $_SESSION['alert'] = ['type' => 'error', 'message' => 'All fields are required.'];
        header('Location: profile.php'); // redirect back
        exit;
    }

    if ($newPassword !== $confirmPassword) {
        $_SESSION['alert'] = ['type' => 'warning', 'message' => 'New passwords do not match.'];
        header('Location: profile.php');
        exit;
    }

    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($currentPassword, $user['password'])) {
        $_SESSION['alert'] = ['type' => 'error', 'message' => 'Current password is incorrect.'];
        header('Location: profile.php');
        exit;
    }

    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $updated = $updateStmt->execute([$hashedPassword, $userId]);

    if ($updated) {
        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Password updated successfully.'];
    } else {
        $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error updating password.'];
    }

    header('Location: profile.php');
    exit;
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - GroceryGo.lk</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Fredoka+One&display=swap" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.5/dist/sweetalert2.min.css" rel="stylesheet">    <style>
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
        
        .section-title {
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
            border-left: 4px solid var(--primary);
            padding-left: 12px;
        }
        
        /* Card Styling */
        .profile-card {
            border: none;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
            margin-bottom: 2rem;
        }
        
        .profile-card .card-header {
            background-color: var(--light);
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 1.25rem;
        }
        
        .profile-card .card-header h5 {
            margin-bottom: 0;
            font-weight: 600;
            color: var(--primary);
            font-size: 1.1rem;
        }
        
        .profile-card .card-body {
            padding: 1.5rem;
        }
        
        /* Input Fields */
        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            border: 2px solid var(--border-color);
            font-size: 0.95rem;
            background-color: var(--input-bg);
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(47, 133, 90, 0.15);
            background-color: #fff;
        }
        
        .form-control[readonly] {
            background-color: var(--input-bg);
            border: 2px solid var(--border-color);
            opacity: 0.8;
        }
        
        .form-label {
            font-weight: 500;
            color: var(--dark);
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }
        
        /* Button Styling */
        .btn {
            padding: 0.6rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-primary:hover, .btn-primary:focus {
            background-color: var(--secondary);
            border-color: var(--secondary);
        }
        
        /* Section separation */
        .password-section {
            margin-top: 2rem;
        }
        
        /* Input Icon Groups */
        .input-group-text {
            background-color: var(--input-bg);
            border: 2px solid var(--border-color);
            border-right: none;
            color: var(--dark);
        }
        
        .input-group .form-control {
            border-left: none;
        }
        
        /* Password field show/hide */
        .password-toggle {
            cursor: pointer;
            padding: 0.75rem 1rem;
            border-radius: 0 8px 8px 0;
            border: 2px solid var(--border-color);
            border-left: none;
            color: var(--dark);
            background-color: var(--input-bg);
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="container-fluid">
            <div class="row content-wrapper">
                <!-- Sidebar -->
                <?php include_once('./includes/user_nav.php'); ?>                <!-- Main Content -->
                <div class="col-md-9 col-lg-9 content">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h2 class="section-title mb-0">My Profile</h2>
                        <span class="badge bg-light text-primary px-3 py-2 rounded-pill">
                            <i class="fas fa-user-circle me-1"></i> Member since <?php echo date('M Y', strtotime($_SESSION['created_at'] ?? 'now')); ?>
                        </span>
                    </div>
                    
                    <div class="row">
                        <div class="col-lg-8">
                            <!-- Personal Info Card -->
                            <div class="card profile-card">
                                <div class="card-header">
                                    <h5><i class="fas fa-user-circle me-2"></i> Personal Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-4">
                                        <div class="col-md-6 mb-3 mb-md-0">
                                            <label for="fullname" class="form-label">Full Name</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                                <input type="text" class="form-control" id="fullname" value="<?php echo $_SESSION['name']; ?>" readonly autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="email" class="form-label">Email Address</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                                <input type="email" class="form-control" id="email" value="<?php echo $_SESSION['email']; ?>" readonly autocomplete="off">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="text-muted small">
                                        <i class="fas fa-info-circle me-1"></i> Contact our support team if you need to update your personal information.
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Change Password Card -->
                            <div class="card profile-card mt-4">
                                <div class="card-header">
                                    <h5><i class="fas fa-lock me-2"></i> Change Password</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="" id="password-form">
                                        <div class="mb-3">
                                            <label for="current-password" class="form-label">Current Password</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-key"></i></span>
                                                <input type="password" class="form-control" id="current-password" name="current-password" required>
                                                <span class="password-toggle" onclick="togglePassword('current-password')">
                                                    <i class="far fa-eye"></i>
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="new-password" class="form-label">New Password</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                                <input type="password" class="form-control" id="new-password" name="new-password" required>
                                                <span class="password-toggle" onclick="togglePassword('new-password')">
                                                    <i class="far fa-eye"></i>
                                                </span>
                                            </div>
                                            <div class="form-text text-muted">Password must be at least 8 characters long</div>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <label for="confirm-password" class="form-label">Confirm New Password</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-lock-open"></i></span>
                                                <input type="password" class="form-control" id="confirm-password" name="confirm-password" required>
                                                <span class="password-toggle" onclick="togglePassword('confirm-password')">
                                                    <i class="far fa-eye"></i>
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex justify-content-end">
                                            <button type="submit" class="btn btn-primary" name="update_password">
                                                <i class="fas fa-key me-2"></i> Update Password
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right column for activity summary or additional info -->
                        <div class="col-lg-4 d-none d-lg-block">
                            <div class="card profile-card">
                                <div class="card-header">
                                    <h5><i class="fas fa-shield-alt me-2"></i> Account Security</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="me-3 text-center">
                                            <i class="fas fa-lock text-success fs-4"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1 fw-bold">Password</h6>
                                            <p class="mb-0 small text-muted">Last updated: <?php echo isset($_SESSION['password_updated_at']) ? date('M d, Y', strtotime($_SESSION['password_updated_at'])) : 'Not available'; ?></p>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="me-3 text-center">
                                            <i class="fas fa-envelope text-primary fs-4"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1 fw-bold">Email Verification</h6>
                                            <p class="mb-0 small text-muted">Status: <span class="text-success">Verified</span></p>
                                        </div>
                                    </div>
                                    
                                    <hr>
                                    
                                    <div class="alert alert-light border-left border-primary mb-0" role="alert">
                                        <div class="d-flex">
                                            <div class="me-3">
                                                <i class="fas fa-info-circle text-primary fs-4"></i>
                                            </div>
                                            <div>
                                                <h6 class="alert-heading mb-1">Security Tips</h6>
                                                <p class="mb-0 small">Always use a strong password and never share your login credentials with anyone.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.5/dist/sweetalert2.all.min.js"></script>    <?php if (isset($_SESSION['alert'])): ?>
    <script>
        Swal.fire({
            icon: '<?= $_SESSION['alert']['type'] ?>',
            title: '<?= $_SESSION['alert']['type'] === 'success' ? "Success" : "Oops!" ?>',
            text: '<?= $_SESSION['alert']['message'] ?>',
            confirmButtonColor: '#2F855A'
        });
    </script>
    <?php unset($_SESSION['alert']); endif; ?>
    
    <script>
        // Function to toggle password visibility
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.parentNode.querySelector('.password-toggle i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        // Validate password match
        document.getElementById('password-form')?.addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new-password').value;
            const confirmPassword = document.getElementById('confirm-password').value;
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Password Mismatch',
                    text: 'New password and confirm password do not match',
                    confirmButtonColor: '#2F855A'
                });
            }
        });
    </script>
</body>
</html>