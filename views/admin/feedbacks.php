<?php
session_start();
require_once '../../config/db.php';
require_once '../../config/helpers.php';

$success = '';
$error = '';

// Handle feedback status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['feedback_id'], $_POST['feedback_status'])) {
    $feedback_id = intval($_POST['feedback_id']);
    $feedback_status = in_array($_POST['feedback_status'], ['inactive', 'active']) ? $_POST['feedback_status'] : 'inactive';
    $stmt = $conn->prepare('UPDATE feedback SET status = :status WHERE f_id = :id');
    $stmt->bindParam(':status', $feedback_status);
    $stmt->bindParam(':id', $feedback_id, PDO::PARAM_INT);
    if ($stmt->execute()) {
        $success = 'Feedback status updated successfully!';
    } else {
        $error = 'Failed to update feedback status.';
    }
}

// Handle feedback delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_feedback_id'])) {
    $feedback_id = intval($_POST['delete_feedback_id']);
    $stmt = $conn->prepare('DELETE FROM feedback WHERE f_id = :id');
    $stmt->bindParam(':id', $feedback_id, PDO::PARAM_INT);
    if ($stmt->execute()) {
        $success = 'Feedback deleted successfully!';
    } else {
        $error = 'Failed to delete feedback.';
    }
}

// Fetch all feedback with user details
$feedbacks = [];
$stmt = $conn->query('
    SELECT f.f_id, f.message, f.status, u.name as user_name, u.email as user_email 
    FROM feedback f 
    LEFT JOIN users u ON f.user_id = u.id 
    ORDER BY f.f_id DESC
');
if ($stmt) {
    $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Management - GroceryGo</title>
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
            padding: 1rem 1.25rem;
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
        
        /* Status Badges */
        .status-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 50rem;
            font-weight: 500;
            font-size: 0.75rem;
            display: inline-block;
            text-align: center;
        }
        
        .status-active {
            background-color: #D1FAE5; /* green-100 */
            color: var(--primary);
        }
        
        .status-inactive {
            background-color: #FEE2E2; /* red-100 */
            color: #B91C1C; /* red-800 */
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
        
        /* Form elements */
        .form-select {
            border-radius: 8px;
            border: 1px solid var(--border-color);
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            background-color: var(--input-bg);
            transition: all 0.3s ease;
        }
        
        .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(47, 133, 90, 0.25);
            outline: 0;
        }
        
        /* Action buttons */
        .btn-action {
            width: 36px;
            height: 36px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        
        .btn-outline-danger {
            color: #e53e3e;
            border-color: #e53e3e;
        }
        
        .btn-outline-danger:hover {
            background-color: #e53e3e;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(229, 62, 62, 0.2);
        }
        
        /* Feedback message styles */        .feedback-message {
            position: relative;
            padding: 1rem;
            background-color: var(--input-bg);
            border-radius: 0.5rem;
            border-left: 3px solid var(--primary);
            margin-bottom: 0;
        }
        
        .feedback-message-container {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
          .message-preview {
            position: relative;
            padding: 0.75rem;
            background-color: var(--input-bg);
            border-radius: 0.5rem;
            border-left: 3px solid var(--primary);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
            font-size: 0.95rem;
            color: #4A5568;
        }
        
        .read-more-btn {
            align-self: flex-start;
            background-color: var(--primary);
            border-color: var(--primary);
            font-size: 0.8rem;
            font-weight: 500;
            padding: 0.35rem 0.75rem;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
        }
        
        .read-more-btn:hover {
            background-color: var(--secondary);
            border-color: var(--secondary);
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
          .message-truncate {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .feedback-user {
            display: flex;
            align-items: center;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--light);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 0.75rem;
            color: var(--primary);
        }
        
        .user-name {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.125rem;
            line-height: 1.2;
        }
        
        .user-email {
            font-size: 0.75rem;
            color: #718096; /* gray-500 */
        }
          /* Modal styles */
        .modal {
            z-index: 1050;
        }
        
        .modal-backdrop {
            z-index: 1040;
        }
        
        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            position: relative;
        }
        
        .modal-header {
            background-color: var(--light);
            border-bottom: 1px solid var(--border-color);
            padding: 1.25rem 1.5rem;
        }
        
        .modal-header .modal-title {
            color: var(--primary);
            font-weight: 600;
        }
        
        .modal-body {
            padding: 1.5rem;
        }
        
        .modal-footer {
            border-top: 1px solid var(--border-color);
            padding: 1rem 1.5rem;
        }
        
        .btn-secondary {
            background-color: #718096;
            border-color: #718096;
        }
        
        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 3rem 0;
            background-color: white;
            border-radius: 15px;
            box-shadow: var(--card-shadow);
        }
        
        .empty-state-icon {
            font-size: 4rem;
            color: #CBD5E0;
            margin-bottom: 1.5rem;
        }
        
        .empty-state-title {
            font-weight: 600;
            color: #4A5568;
            margin-bottom: 0.75rem;
        }
        
        .empty-state-text {
            color: #718096;
            max-width: 400px;
            margin: 0 auto;
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
                <div>                    <h1 class="welcome-title">Feedback Management</h1>
                    <p class="welcome-text">View and manage customer feedback and inquiries</p>
                </div>
                <i class="fas fa-comments welcome-icon"></i>
            </div>
            
            <!-- Alerts -->
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($success) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <!-- Feedback Content Card -->
            <div class="content-card">
                <div class="content-card-header">
                    <h5><i class="fas fa-comments me-2"></i> Customer Feedback List</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive overflow-x-auto">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>                                <th width="50">ID</th>
                                    <th width="150">User Name</th>
                                    <th width="180">User Email</th>
                                    <th>Message</th>
                                    <th width="140">Status</th>
                                    <th class="text-center" width="80">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($feedbacks as $feedback): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($feedback['f_id']) ?></td>
                                        <td>
                                            <div class="feedback-user">
                                                <div class="user-avatar">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                                <div>
                                                    <div class="user-name"><?= htmlspecialchars($feedback['user_name'] ?? 'Unknown User') ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($feedback['user_email'] ?? 'No Email') ?></td>                                        <td>
                                            <div class="feedback-message-container">
                                                <div class="message-preview">
                                                    <?= htmlspecialchars(substr($feedback['message'], 0, 100)) ?>
                                                    <?php if (strlen($feedback['message']) > 100): ?>
                                                        <span class="text-muted">...</span>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if (strlen($feedback['message']) > 100): ?>                                                    <button type="button" class="btn btn-sm btn-primary read-more-btn" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#feedbackModal<?= $feedback['f_id'] ?>"
                                                            data-bs-toggle="tooltip"
                                                            title="View full message">
                                                        <i class="fas fa-eye me-1"></i> Read More
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="feedback_id" value="<?= htmlspecialchars($feedback['f_id']) ?>">
                                                <select name="feedback_status" class="form-select form-select-sm" onchange="this.form.submit()">
                                                    <option value="inactive" <?= $feedback['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                                    <option value="active" <?= $feedback['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                                </select>
                                            </form>
                                        </td>
                                        <td class="text-center">
                                            <form method="POST" class="d-inline delete-feedback-form">
                                                <input type="hidden" name="delete_feedback_id" value="<?= htmlspecialchars($feedback['f_id']) ?>">
                                                <button type="button" class="btn btn-action btn-outline-danger delete-feedback-btn" title="Delete">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    
                                    <!-- Modal for full feedback text -->
                                    <?php if (strlen($feedback['message']) > 100): ?>                                        <div class="modal fade" id="feedbackModal<?= $feedback['f_id'] ?>" tabindex="-1" aria-labelledby="feedbackModalLabel<?= $feedback['f_id'] ?>" aria-hidden="true" data-bs-backdrop="static">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="feedbackModalLabel<?= $feedback['f_id'] ?>">
                                                            <i class="fas fa-comment-alt me-2"></i> Message from <?= htmlspecialchars($feedback['user_name'] ?? 'Unknown User') ?>
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3 d-flex align-items-center">
                                                            <div class="user-avatar me-3">
                                                                <i class="fas fa-user"></i>
                                                            </div>
                                                            <div>
                                                                <h6 class="mb-0"><?= htmlspecialchars($feedback['user_name'] ?? 'Unknown User') ?></h6>
                                                                <p class="text-muted mb-0"><?= htmlspecialchars($feedback['user_email'] ?? 'No Email') ?></p>
                                                            </div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <strong>Status:</strong>                                                <span class="status-badge <?= $feedback['status'] === 'active' ? 'status-active' : 'status-inactive' ?>">
                                                                <i class="fas <?= $feedback['status'] === 'active' ? 'fa-check-circle' : 'fa-times-circle' ?> me-1"></i> <?= ucfirst($feedback['status']) ?>
                                                            </span>
                                                        </div>
                                                        <div class="feedback-message p-3 bg-light rounded">
                                                            <?= nl2br(htmlspecialchars($feedback['message'])) ?>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary modal-close-btn" data-bs-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                      <?php if (empty($feedbacks)): ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="fas fa-comments"></i>
                            </div>
                            <h4 class="empty-state-title">No Feedback Found</h4>
                            <p class="empty-state-text">There are no customer feedback entries in the system yet.</p>
                            <div class="mt-4">
                                <a href="../../index.php" class="btn btn-outline-primary">
                                    <i class="fas fa-home me-1"></i> Back to Home
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.5/dist/sweetalert2.all.min.js"></script>    <script>    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltips.forEach(t => new bootstrap.Tooltip(t));
        
        // Handle delete confirmation
        document.querySelectorAll('.delete-feedback-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const form = btn.closest('form');
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Do you really want to delete this feedback?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#2F855A',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
        
        // Fix modal backdrop issues
        const modals = document.querySelectorAll('.modal');
        
        // Initialize all modals properly
        modals.forEach(modalEl => {
            const modal = new bootstrap.Modal(modalEl);
            
            // Clear modal state on hidden
            modalEl.addEventListener('hidden.bs.modal', function(e) {
                // Remove any remaining backdrop
                document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            });
            
            // Additional handling for the close button
            const closeBtn = modalEl.querySelector('.modal-close-btn');
            if (closeBtn) {
                closeBtn.addEventListener('click', function() {
                    modal.hide();
                });
            }
        });
        
        // Add event listener to "Read More" buttons
        document.querySelectorAll('[data-bs-toggle="modal"]').forEach(button => {
            button.addEventListener('click', function(e) {
                e.stopPropagation(); // Prevent event bubbling
                const targetModal = document.querySelector(this.getAttribute('data-bs-target'));
                if (targetModal) {
                    // Ensure any existing backdrops are removed first
                    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                    document.body.classList.remove('modal-open');
                }
            });
        });
    });
    </script>
</body>
</html>