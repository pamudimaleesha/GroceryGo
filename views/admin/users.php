<?php
session_start();
require_once '../../config/db.php';
require_once '../../config/helpers.php';

$success = '';
$error = '';

// Handle user type update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['user_type'])) {
    $user_id = intval($_POST['user_id']);
    $user_type = $_POST['user_type'] === 'admin' ? 'admin' : 'user';
    $stmt = $conn->prepare('UPDATE users SET role = :user_type WHERE id = :id');
    $stmt->bindParam(':user_type', $user_type);
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
    if ($stmt->execute()) {
        $success = 'User type updated successfully!';
    } else {
        $error = 'Failed to update user type.';
    }
}

// Handle user delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_id'])) {
    $user_id = intval($_POST['delete_user_id']);
    $stmt = $conn->prepare('DELETE FROM users WHERE id = :id');
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
    if ($stmt->execute()) {
        $success = 'User deleted successfully!';
    } else {
        $error = 'Failed to delete user.';
    }
}

// Fetch all users
$users = [];
$stmt = $conn->query('SELECT id, name, email, role FROM users ORDER BY id ASC');
if ($stmt) {
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - GroceryGo</title>
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
        
        /* Content Cards */
        .content-card {
            border: none;
            border-radius: 15px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            margin-bottom: 2rem;
            background-color: white;
        }
        
        .content-card-header {
            background-color: var(--light);
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .content-card-header h5 {
            margin-bottom: 0;
            font-weight: 600;
            color: var(--primary);
            font-size: 1.25rem;
        }
        
        /* Table Styles */
        .table-wrapper {
            background-color: white;
            border-radius: 15px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
        }
        
        .table {
            margin-bottom: 0;
            vertical-align: middle;
        }
        
        .table > :not(caption) > * > * {
            padding: 1rem 1.5rem;
        }
        
        .table thead th {
            background-color: var(--light);
            color: var(--primary);
            font-weight: 600;
            border-top: none;
            border-bottom: 1px solid var(--border-color);
            white-space: nowrap;
        }
        
        .table tbody tr:hover {
            background-color: rgba(240, 255, 244, 0.5);
        }
        
        /* Role Badge */
        .role-badge {
            padding: 0.35rem 0.65rem;
            border-radius: 50rem;
            font-size: 0.8125rem;
            font-weight: 600;
            display: inline-block;
            text-align: center;
            white-space: nowrap;
        }
        
        .role-badge-admin {
            background-color: var(--primary);
            color: white;
        }
        
        .role-badge-user {
            background-color: var(--accent);
            color: var(--dark);
        }
        
        /* Alert styles */
        .alert {
            border-radius: 10px;
            border: none;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
        }
        
        .alert-success {
            background-color: rgba(56, 161, 105, 0.1);
            color: var(--primary);
            border-left: 4px solid var(--primary);
        }
        
        .alert-danger {
            background-color: rgba(229, 62, 62, 0.1);
            color: #e53e3e;
            border-left: 4px solid #e53e3e;
        }
        
        /* Custom Select */
        .form-select {
            border-radius: 8px;
            border-color: var(--border-color);
            cursor: pointer;
        }
        
        .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(47, 133, 90, 0.25);
            border-color: var(--primary);
        }
        
        /* Action buttons */
        .btn-action {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            margin-left: 0.5rem;
            transition: all 0.2s ease;
        }
        
        .btn-outline-danger {
            color: #e53e3e;
            border-color: #e53e3e;
        }
        
        .btn-outline-danger:hover {
            background-color: #e53e3e;
            color: white;
            border-color: #e53e3e;
            transform: translateY(-2px);
        }
    </style>
</head>

<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <?php include_once('./includes/admin_nav.php'); ?>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1 class="welcome-title">User Management</h1>
                    <p class="welcome-text">Manage your system users and their permissions</p>
                </div>
                <i class="fas fa-users welcome-icon"></i>
            </div>
            
            <!-- Alert Messages -->
            <?php if (!empty($success)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($success) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>            <!-- Users Table Card -->
            <div class="content-card">
                <div class="content-card-header">
                    <h5>
                        <i class="fas fa-user-circle me-2"></i> System Users
                    </h5>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-light text-dark border">
                            <i class="fas fa-users me-1"></i> Total: <?= count($users) ?>
                        </span>
                    </div>
                </div>
                
                <div class="p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 60px">ID</th>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th style="width: 180px">Role</th>
                                    <th style="width: 100px" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($users)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <i class="fas fa-users fa-3x mb-3 text-muted"></i>
                                            <p class="mb-0">No users found</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td class="fw-bold">#<?= htmlspecialchars($user['id']) ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar bg-light rounded-circle text-center p-2 me-3" style="width:40px; height:40px;">
                                                        <i class="fas fa-user text-<?= $user['role'] === 'admin' ? 'primary' : 'secondary' ?>"></i>
                                                    </div>
                                                    <div>
                                                        <p class="fw-medium mb-0"><?= htmlspecialchars($user['name']) ?></p>
                                                        <span class="role-badge role-badge-<?= $user['role'] ?>">
                                                            <?= $user['role'] === 'admin' ? '<i class="fas fa-shield-alt me-1"></i>' : '<i class="fas fa-user me-1"></i>' ?>
                                                            <?= ucfirst(htmlspecialchars($user['role'])) ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="mailto:<?= htmlspecialchars($user['email']) ?>" class="text-decoration-none">
                                                    <i class="fas fa-envelope me-1 text-muted"></i>
                                                    <?= htmlspecialchars($user['email']) ?>
                                                </a>
                                            </td>
                                            <td>
                                                <form method="POST" class="d-inline role-update-form">
                                                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-light"><i class="fas fa-user-tag"></i></span>
                                                        <select name="user_type" class="form-select user-type-select" onchange="this.form.submit()">
                                                            <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                                            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                                        </select>
                                                    </div>
                                                </form>
                                            </td>
                                            <td class="text-center">
                                                <form method="POST" class="d-inline delete-user-form">
                                                    <input type="hidden" name="delete_user_id" value="<?= htmlspecialchars($user['id']) ?>">
                                                    <button type="button" class="btn btn-outline-danger btn-action delete-user-btn" title="Delete User">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.5/dist/sweetalert2.all.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Delete User Confirmation
        document.querySelectorAll('.delete-user-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const form = btn.closest('form');
                
                Swal.fire({
                    title: 'Delete User?',
                    text: 'This action cannot be undone. All user data will be permanently removed.',
                    icon: 'warning',
                    iconColor: '#e53e3e',
                    showCancelButton: true,
                    confirmButtonColor: '#e53e3e',
                    cancelButtonColor: '#718096',
                    confirmButtonText: 'Yes, delete user',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        confirmButton: 'btn btn-danger',
                        cancelButton: 'btn btn-outline-secondary'
                    },
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
        
        // Highlight Role Change
        document.querySelectorAll('.user-type-select').forEach(select => {
            const originalValue = select.value;
            
            select.addEventListener('change', function() {
                const newValue = this.value;
                if (newValue !== originalValue) {
                    this.closest('.input-group').classList.add('border', 'border-primary');
                    
                    // Submit the form after a small delay to show the highlight effect
                    setTimeout(() => {
                        this.form.submit();
                    }, 300);
                }
            });
        });
    });
    </script>
</body>
</html>
