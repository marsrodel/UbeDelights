<?php
$products = [
    ['id' => 1, 'name' => 'Ube Cheesecake', 'price' => '₱850', 'priceNum' => 850, 'type' => 'cakes', 'status' => 'Best Seller',
     'description' => 'Creamy cheesecake with authentic ube swirl on a graham crust',
     'image' => '../../images/items/cheesecake.jpg'],
    ['id' => 2, 'name' => 'Classic Ube Cake', 'price' => '₱950', 'priceNum' => 950, 'type' => 'cakes', 'status' => 'Premium',
     'description' => 'Soft ube macapuno sponge layered with rich ube buttercream frosting',
     'image' => '../../images/items/classic.jpg'],
    ['id' => 3, 'name' => 'Ube Pandesal', 'price' => '₱120', 'priceNum' => 120, 'type' => 'pastries', 'status' => 'Premium',
     'description' => 'Warm, pillowy pandesal filled with premium ube halaya, baked fresh daily',
     'image' => '../../images/items/pandesal.jpg'],
    ['id' => 4, 'name' => 'Ube Roll', 'price' => '₱450', 'priceNum' => 450, 'type' => 'rolls', 'status' => 'Popular',
     'description' => 'Fluffy ube sponge roll wrapped around smooth ube buttercream filling',
     'image' => '../../images/items/uberoll.jpg'],
    ['id' => 4, 'name' => 'Ube Halo-Halo', 'price' => '₱180', 'priceNum' => 180, 'type' => 'beverages', 'status' => 'Popular',
     'description' => 'Classic Filipino shaved ice dessert topped with creamy ube halaya',
     'image' => '../../images/items/halohalo.jpg'],
    ['id' => 5, 'name' => 'Ube Crinkles', 'price' => '₱280', 'priceNum' => 280, 'type' => 'pastries', 'status' => 'New',
     'description' => 'Chewy sugar-dusted crinkle cookies bursting with ube flavor',
     'image' => '../../images/items/crinkles.jpg'],
    ['id' => 6, 'name' => 'Ube Latte', 'price' => '₱150', 'priceNum' => 150, 'type' => 'beverages', 'status' => 'New',
     'description' => 'Espresso blended with steamed milk and house-made ube syrup',
     'image' => '../../images/items/latte.jpg'],
    ['id' => 7, 'name' => 'Ube Macapuno', 'price' => '₱150', 'priceNum' => 150, 'type' => 'pastries', 'status' => 'Not Available',
     'description' => 'Sweet ube and macapuno preserves in a soft, buttery pastry shell',
     'image' => '../../images/items/macapuno.jpg'],
];

function statusClass($status) {
    if (!$status) return '';
    $map = [
        'Premium'       => 'badge-premium',
        'Best Seller'   => 'badge-best-seller',
        'Popular'       => 'badge-popular',
        'New'           => 'badge-new',
        'Not Available' => 'badge-unavailable',
    ];
    return isset($map[$status]) ? $map[$status] : 'badge-unavailable';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ube Delights - Admin Products</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../css/admin.css?v=1.4">
</head>
<body class="admin-body">
    <aside class="admin-sidebar">
        <div class="sidebar-logo">
            <img src="../../images/logo.png" alt="Ube Delights Logo">
            <div>
                <h2>Ube Delights</h2>
                <span class="sidebar-tag">Admin Panel</span>
            </div>
        </div>

        <div class="sidebar-profile">
            <div class="admin-chip">
                <div class="admin-avatar">AU</div>
                <div class="admin-chip-info">
                    <strong>Admin User</strong>
                    <small>Administrator</small>
                </div>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a onclick="getAdminDashboard()" class="sidebar-link"><i class="fa-solid fa-gauge-high"></i><span>Dashboard</span></a>
            <a onclick="getAdminProducts()" class="sidebar-link active"><i class="fa-solid fa-box"></i><span>Products</span></a>
            <a onclick="getAdminOrders()" class="sidebar-link"><i class="fa-solid fa-bag-shopping"></i><span>Orders</span></a>
            <a onclick="getAdminUserManagement()" class="sidebar-link"><i class="fa-solid fa-users-cog"></i><span>User Management</span></a>
            <a onclick="getAdminPendingApprovals()" class="sidebar-link"><i class="fa-solid fa-user-clock"></i><span>Pending Approvals</span></a>
            <a onclick="getAdminSystemLogs()" class="sidebar-link"><i class="fa-solid fa-list-alt"></i><span>System Logs</span></a>
        </nav>

        <div class="sidebar-footer">
            <a onclick="getAdminLogout()" class="sidebar-logout"><i class="fa-solid fa-right-from-bracket"></i><span>Log Out</span></a>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <h1>Products</h1>
            <div class="topbar-right">
                <span class="topbar-date"><i class="fa-solid fa-calendar-days"></i><?php echo date('F j, Y'); ?></span>
            </div>
        </header>

        <main class="admin-content">
            <div class="products-toolbar">
                <div class="order-filters">
                    <button class="filter-btn active" data-category="all">All Products</button>
                    <button class="filter-btn" data-category="cakes">Cakes</button>
                    <button class="filter-btn" data-category="rolls">Rolls</button>
                    <button class="filter-btn" data-category="pastries">Pastries</button>
                    <button class="filter-btn" data-category="beverages">Beverages</button>
                </div>
                <button class="btn-primary" id="btnAddProduct" style="padding:10px 12px; font-size:0.95rem; border-radius:10px;" title="Add Product"><i class="fa-solid fa-plus"></i></button>
            </div>

            <div class="products-grid" id="productsGrid">
                <?php foreach ($products as $product): ?>
                <?php $unavailable = ($product['status'] === 'Not Available'); ?>
                <?php $badgeClass = statusClass($product['status']); ?>
                <?php $badgeHtml = $badgeClass ? '<span class="product-status-badge ' . $badgeClass . '">' . htmlspecialchars($product['status']) . '</span>' : ''; ?>
                <div class="admin-product-card<?php echo $unavailable ? ' unavailable' : ''; ?>"
                     data-id="<?php echo $product['id']; ?>"
                     data-category="<?php echo $product['type']; ?>"
                     data-name="<?php echo htmlspecialchars($product['name'], ENT_QUOTES); ?>"
                     data-desc="<?php echo htmlspecialchars($product['description'], ENT_QUOTES); ?>"
                     data-type="<?php echo $product['type']; ?>"
                     data-price="<?php echo $product['priceNum']; ?>"
                     data-status="<?php echo htmlspecialchars($product['status'], ENT_QUOTES); ?>"
                     data-image="<?php echo $product['image']; ?>">
                    <div class="product-photo">
                        <img src="<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <?php echo $badgeHtml; ?>
                    </div>
                    <div class="product-card-body">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="product-desc"><?php echo htmlspecialchars($product['description']); ?></p>
                        <div class="product-meta">
                            <span class="product-type"><?php echo htmlspecialchars($product['type']); ?></span>
                            <span class="product-price"><?php echo $product['price']; ?></span>
                        </div>
                    </div>
                    <div class="product-card-actions">
                        <button class="btn-outline btn-edit" data-id="<?php echo $product['id']; ?>"><i class="fa-solid fa-pen-to-square"></i> Edit</button>
                        <button class="btn-outline btn-delete" data-id="<?php echo $product['id']; ?>"><i class="fa-solid fa-trash-can"></i> Delete</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="empty-state" id="productsEmpty" style="display:none;">
                <div class="empty-icon">📦</div>
                <h3>No products found</h3>
                <p>No products match this category.</p>
            </div>

            <!-- Add/Edit Product Modal -->
            <div class="modal-overlay" id="productModal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
                <div class="modal">
                    <div class="modal-header">
                        <h2 id="modalTitle">Add Product</h2>
                        <button class="modal-close" id="modalClose" aria-label="Close modal"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                    <form class="modal-form" id="productForm">
                        <input type="hidden" name="productId" id="productId">
                        <div class="form-group">
                            <label for="productName">Product Name <span class="required">*</span></label>
                            <input type="text" id="productName" name="productName" required placeholder="e.g., Ube Cheesecake">
                        </div>
                        <div class="form-group">
                            <label for="productDesc">Description <span class="required">*</span></label>
                            <textarea id="productDesc" name="productDesc" required rows="3" placeholder="Describe the product..."></textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="productType">Product Type <span class="required">*</span></label>
                                <select id="productType" name="productType" required>
                                    <option value="" disabled selected>Select type</option>
                                    <option value="cakes">Cakes</option>
                                    <option value="rolls">Rolls</option>
                                    <option value="pastries">Pastries</option>
                                    <option value="beverages">Beverages</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="productStatus">Status</label>
                                <select id="productStatus" name="productStatus">
                                    <option value="" selected>Select status (optional)</option>
                                    <option value="Premium">Premium</option>
                                    <option value="Best Seller">Best Seller</option>
                                    <option value="Popular">Popular</option>
                                    <option value="New">New</option>
                                    <option value="Not Available">Not Available</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="productPrice">Price (₱) <span class="required">*</span></label>
                            <input type="number" id="productPrice" name="productPrice" required min="1" step="1" placeholder="850">
                        </div>
                        <div class="form-group">
                            <label for="productImage">Product Image <span class="required">*</span></label>
                            <input type="file" id="productImage" name="productImage" accept="image/*">
                            <div class="image-preview-wrapper">
                                <img id="imagePreview" src="" alt="Image preview" style="display:none;">
                                <span class="preview-placeholder">No image selected</span>
                            </div>
                        </div>
                    </form>
                    <div class="modal-footer">
                        <button type="button" class="btn-outline" id="modalCancel">Cancel</button>
                        <button type="button" class="btn-primary" id="modalSubmit">Add Product</button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div class="toast" id="toast"></div>

    <script src="../../javascript/admin-routing.js"></script>
    <script src="../../javascript/admin.js"></script>
    <script src="../../javascript/inspect.js"></script>
</body>
</html>