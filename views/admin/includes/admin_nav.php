<?php
if(session_status() == PHP_SESSION_NONE){
    session_start();
}

$current = basename($_SERVER['PHP_SELF']);

// Get admin info safely
$admin_name = isset($_SESSION['name']) ? $_SESSION['name'] : 'Admin';
$admin_email = isset($_SESSION['email']) ? $_SESSION['email'] : '';
$admin_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '';

$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : '';

?>

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
    }      .admin-sidebar {
        background-color: var(--dark);
        color: white;
        min-height: 100vh;
        box-shadow: 5px 0 15px rgba(0, 0, 0, 0.1);
        padding: 0;
        transition: all 0.3s ease;
        position: fixed;
        z-index: 10;
        width: 25%;
        max-width: 280px;
        overflow-y: auto; /* Enable vertical scrolling */
        height: 100vh; /* Full viewport height */
        display: flex;
        flex-direction: column; /* Stack children vertically */
    }
      .admin-sidebar {
    background: linear-gradient(
        180deg,
        #111827,
        #1f2937,
        #064e3b
    );
    color: white;
    min-height:100vh;
    box-shadow: 8px 0 25px rgba(0,0,0,0.35);
}
    .admin-info {
        text-align: center;
        padding: 1.5rem 1rem;
        margin-bottom: 1rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .admin-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background-color: var(--secondary);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        font-size: 2rem;
        color: white;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        border: 3px solid var(--accent);
    }
    
    .admin-name {
        font-weight: 600;
        font-size: 1.1rem;
        margin-bottom: 0.25rem;
        color: white;
    }
    
    .admin-email {
        font-size: 0.9rem;
        color: rgba(255, 255, 255, 0.7);
        margin-bottom: 0;
        word-break: break-all;
    }
    
    .admin-role {
        display: inline-block;
        background-color: var(--primary);
        color: white;
        font-size: 0.75rem;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        margin-top: 0.5rem;
        font-weight: 500;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }
    
    .nav-item {
        padding: 0 1rem;
    }
    
    .admin-nav-link {
        color: rgba(255, 255, 255, 0.85) !important;
        border-radius: 8px;
        padding: 0.85rem 1.25rem;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        margin-bottom: 0.5rem;
        font-weight: 500;
        display: flex;
        align-items: center;
    }
    
    .admin-nav-link:before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 4px;
        background-color: var(--accent);
        transform: scaleY(0);
        transition: transform 0.3s ease;
        border-radius: 0 2px 2px 0;
    }
    
    .admin-nav-link:hover {
        background-color: rgba(255, 255, 255, 0.1);
        color: white !important;
    }
    
    .admin-nav-link.active {
        background-color: var(--primary);
        color: white !important;
        font-weight: 600;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    .admin-nav-link.active:before {
        transform: scaleY(1);
    }
    
    .admin-nav-link i {
        font-size: 1.1rem;
        width: 24px;
        margin-right: 10px;
        color: var(--accent);
        transition: transform 0.3s ease;
    }
    
    .admin-nav-link:hover i {
        transform: translateX(3px);
    }
    
    .admin-nav-link.active i {
        color: white;
    }
      .admin-logout {
        margin-top: 0.5rem;
        padding: 0.85rem 1.25rem;
        background-color: #e53e3e; /* Solid red for visibility */
        color: white !important;
        border: 2px solid rgba(255, 255, 255, 0.5);
        text-align: center;
        width: 100%;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
    
    .admin-logout i {
        font-size: 1.1rem;
        width: 24px;
        margin-right: 10px;
        color: white;
    }
    
    .admin-logout:hover {
        background-color: #c53030;
        color: white !important;
        border-color: white;
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
    }
      .admin-footer {
        margin-top: auto;
        padding: 1.5rem 1rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        text-align: center;
        font-size: 0.8rem;
        color: rgba(255, 255, 255, 0.5);
        width: 100%;
        background-color: var(--dark); /* Match sidebar */
    }
    
    .dashboard-stats {
        margin-top: 1rem;
        padding: 0 1rem 1rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .stat-item {
        display: flex;
        align-items: center;
        color: rgba(255, 255, 255, 0.85);
        padding: 0.5rem 0;
        font-size: 0.9rem;
    }
    
    .stat-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background-color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 0.75rem;
        color: white;
    }
    
    .stat-value {
        font-weight: 600;
        font-size: 1.1rem;
        color: white;
        margin-left: auto;
    }
      @media (max-width: 992px) {
        .admin-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 280px;
            transform: translateX(-100%);
            z-index: 1040;
            max-width: 80%;
        }
        
        .admin-sidebar.show {
            transform: translateX(0);
        }
        
        .admin-toggle-btn {
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 1050;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
    }
    
    @media (max-width: 767px) {
        .main-content {
            margin-left: 0 !important;
            width: 100% !important;
            padding-top: 1rem;
        }
    }
</style>

<nav class="col-md-3 col-lg-2 d-md-block admin-sidebar sidebar">    <div class="d-flex flex-column" style="min-height: 100%;">
        <a class="navbar-brand" href="../../index.php">
            GroceryGo <i class="fa-solid fa-basket-shopping ms-2"></i>
        </a>
        
        <!-- Admin Info -->
        <div class="admin-info">
            <div class="admin-avatar">
                <i class="fas fa-user-shield"></i>
            </div>
            <h5 class="admin-name"><?php echo htmlspecialchars($admin_name); ?></h5>
            <?php if (!empty($admin_email)): ?>
            <p class="admin-email"><?php echo htmlspecialchars($admin_email); ?></p>
            <?php endif; ?>
         <span class="admin-role">
    <?php echo htmlspecialchars(ucfirst($user_role)); ?>
</span>
        </div>
          <ul class="nav flex-column w-100 mb-4">
            <li class="nav-item">
                <a class="nav-link admin-nav-link <?php if($current=='dashboard.php') echo 'active'; ?>" href="./dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link admin-nav-link <?php if($current=='products.php') echo 'active'; ?>" href="./products.php">
                    <i class="fas fa-box-open"></i> Products
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link admin-nav-link <?php if($current=='categories.php') echo 'active'; ?>" href="./categories.php">
                    <i class="fas fa-tags"></i> Categories
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link admin-nav-link <?php if($current=='orders.php') echo 'active'; ?>" href="./orders.php">
                    <i class="fas fa-shopping-cart"></i> Orders
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link admin-nav-link <?php if($current=='feedbacks.php') echo 'active'; ?>" href="./feedbacks.php">
                    <i class="fas fa-comments"></i> Feedbacks
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link admin-nav-link <?php if($current=='users.php') echo 'active'; ?>" href="./users.php">
                    <i class="fas fa-users"></i> Users
                </a>
            </li>            <li class="nav-item">
                <div class="mt-3 mb-2 px-3">
                    <div class="p-2 text-center bg-dark rounded-3">
                        <div class="mb-1 text-white">ADMIN CONTROLS</div>
                        <hr class="my-2 border-light opacity-25">
                        <form action="../../controller/user_logout_process.php" method="post">
                            <input type="hidden" name="logout" value="true">
                            <button type="submit" class="admin-logout">
                                <i class="fas fa-sign-out-alt"></i> LOG OUT
                            </button>
                        </form>
                    </div>
                </div>
            </li>
        </ul>
        
        <!-- Admin Footer -->
        <div class="admin-footer mt-auto">
            <p class="mb-0">GroceryGo Admin Panel &copy; <?php echo date('Y'); ?></p>
        </div>
    </div>
</nav>

<!-- For mobile: Toggle button for sidebar -->
<button class="admin-toggle-btn d-md-none d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu">
    <i class="fas fa-bars"></i>
</button>

<script>    document.addEventListener('DOMContentLoaded', function() {
        // For mobile: toggle sidebar
        const toggleBtn = document.querySelector('.admin-toggle-btn');
        const sidebar = document.querySelector('.admin-sidebar');
        
        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener('click', function() {
                sidebar.classList.toggle('show');
            });
            
            // Close sidebar when clicking outside
            document.addEventListener('click', function(e) {
                if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
                    sidebar.classList.remove('show');
                }
            });
        }
        
        // Ensure logout button is visible
        const logoutBtn = document.querySelector('.admin-logout');
        if (logoutBtn) {
            // Add a subtle animation to draw attention to the logout button
            setTimeout(() => {
                logoutBtn.style.transform = 'scale(1.05)';
                setTimeout(() => {
                    logoutBtn.style.transform = 'scale(1)';
                }, 200);
            }, 1000);
            
            // Add scroll into view if needed
            window.addEventListener('resize', checkLogoutVisibility);
            checkLogoutVisibility();
        }
        
        function checkLogoutVisibility() {
            const logoutBtn = document.querySelector('.admin-logout');
            if (!logoutBtn) return;
            
            const rect = logoutBtn.getBoundingClientRect();
            const isVisible = rect.top >= 0 && 
                            rect.bottom <= window.innerHeight;
                            
            if (!isVisible) {
                logoutBtn.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
</script>