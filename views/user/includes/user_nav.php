<?php
$current = basename($_SERVER['PHP_SELF']);
?>

<style>
    :root {
        --primary: #2F855A; /* Tailwind green-700 */
        --secondary: #38A169; /* Tailwind green-600 */
        --light: #F0FFF4; /* Tailwind green-50 */
        --accent: #F6E05E; /* Tailwind yellow-300 */
        --dark: #1A202C; /* Tailwind gray-800 */
    }
    
    .sidebar {
        background-color: var(--dark);
        color: white;
        padding: 2rem 1.5rem;
        min-height: 100vh;
    }
    
    .sidebar .navbar-brand {
        font-family: 'Fredoka One', cursive;
        font-size: 1.8rem;
        color: white;
        display: block;
        margin-bottom: 2rem;
        text-decoration: none;
    }
    
    .user-info {
        text-align: center;
        padding: 1.5rem 0;
        margin-bottom: 1.5rem;
        border-bottom: 1px solid rgba(255,255,255,0.2);
    }
    
    .user-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background-color: var(--light);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        font-size: 2rem;
        color: var(--primary);
    }
    
    .user-name {
        font-weight: 600;
        font-size: 1.1rem;
        margin-bottom: 0.25rem;
    }
    
    .user-email {
        font-size: 0.9rem;
        color: rgba(255,255,255,0.7);
        word-break: break-all;
    }
    
    .nav-pills .nav-link {
        color: white;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        margin-bottom: 0.5rem;
        transition: all 0.3s;
        font-weight: 500;
    }
    
    .nav-pills .nav-link:hover {
        background-color: rgba(255,255,255,0.1);
    }
    
    .nav-pills .nav-link.active {
        background-color: var(--primary);
        color: white;
        font-weight: 600;
    }
    
    .nav-icon {
        width: 20px;
        margin-right: 10px;
        text-align: center;
    }
    
    .logout-btn {
        background: none;
        border: none;
        width: 100%;
        text-align: left;
        color: #ff6b6b;
        font-weight: 500;
        margin-top: 1rem;
    }
    
    .logout-btn:hover {
        background-color: rgba(255, 107, 107, 0.1);
        color: #ff6b6b;
    }
</style>

<div class="col-md-3 col-lg-3 sidebar">
    <div class="brand">
        <a class="navbar-brand" href="../../index.php">GroceryGo.lk <i class="fa-solid fa-basket-shopping ms-1"></i></a>
    </div>
    <div class="user-info">
        <div class="user-avatar">
            <i class="fas fa-user"></i>
        </div>
        <div class="user-name"><?php echo $_SESSION['name']; ?></div>
        <div class="user-email"><?php echo $_SESSION['email']; ?></div>
    </div>
    
    <div class="mt-4">
        <ul class="nav nav-pills flex-column">
            <li class="nav-item">
                <a class="nav-link <?php if($current=='profile.php') echo 'active'; ?>" href="./profile.php">
                    <i class="fas fa-user nav-icon"></i> My Profile
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php if($current=='orders.php') echo 'active'; ?>" href="./orders.php">
                    <i class="fas fa-box nav-icon"></i> My Orders
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php if($current=='wishlist.php') echo 'active'; ?>" href="./wishlist.php">
                    <i class="fas fa-heart nav-icon"></i> Wishlist
                </a>
            </li>
            <form action="../../controller/user_logout_process.php" method="post">
                <input type="hidden" name="logout" value="true">
                <li class="nav-item">
                    <button type="submit" class="nav-link logout-btn">
                        <i class="fas fa-sign-out-alt nav-icon"></i> Logout
                    </button>
                </li>
            </form>
        </ul>
    </div>
</div>