<?php
session_start();
include '../config/db.php';
include '../config/helpers.php';

// Initialize search parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$price_range = isset($_GET['price']) ? $_GET['price'] : '';

// Build the base query
$sql = 'SELECT p.id, p.name, c.category_name, p.price, p.qty, p.image, p.created_at,
        CASE WHEN w.wishlist_id IS NOT NULL THEN 1 ELSE 0 END as in_wishlist
        FROM products p 
        LEFT JOIN category c ON p.category_id = c.id 
        LEFT JOIN wishlist w ON w.product_id = p.id AND w.user_id = ? 
        WHERE 1=1';

$params = [$_SESSION['user_id'] ?? 0];

// Add search condition if search term is provided
if (!empty($search)) {
    $sql .= ' AND (p.name LIKE ? OR p.description LIKE ?)';
    $searchTerm = "%{$search}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

// Add category filter
if (!empty($category)) {
    $sql .= ' AND p.category_id = ?';
    $params[] = $category;
}

// Add price range filter
if (!empty($price_range)) {
    $price_parts = explode('-', $price_range);
    if (count($price_parts) == 2) {
        $sql .= ' AND p.price BETWEEN ? AND ?';
        $params[] = $price_parts[0];
        $params[] = $price_parts[1];
    }
}

$sql .= ' ORDER BY p.created_at DESC';

// Prepare and execute the query
$stmt = $conn->prepare($sql);
$products = [];
if ($stmt && $stmt->execute($params)) {
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Return products as HTML
foreach ($products as $product): ?>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3 d-flex">
        <div class="card product-card border-0">
            <div class="position-relative">
                <a href="./details.php?id=<?php echo $product['id']; ?>">
                    <img src="../uploads/products/<?php echo htmlspecialchars($product['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                </a>                <?php 
                    $categoryName = $product['category_name'];
                    list($bgColor, $textColor) = generateCategoryColor($categoryName);
                ?>
                <span class="badge badge-discount rounded-pill px-2 py-1" 
                      style="background-color: <?php echo $bgColor; ?>; color: <?php echo $textColor; ?>;">
                    <?php echo htmlspecialchars($categoryName); ?>
                </span><?php if (isset($_SESSION['user_id'])): ?>
                    <button type="button" class="btn-wishlist" data-product-id="<?php echo $product['id']; ?>" aria-label="<?php echo $product['in_wishlist'] ? 'Remove from Wishlist' : 'Add to Wishlist'; ?>">
                        <i class="<?php echo $product['in_wishlist'] ? 'fas' : 'far'; ?> fa-heart"></i>
                    </button>
                <?php else: ?>
                    <a href="signin.php" class="btn-wishlist" aria-label="Add to Wishlist">
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
                        <a href="signin.php" class="btn btn-sm btn-primary rounded-circle" title="Add to Cart">
                            <i class="fas fa-plus"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
