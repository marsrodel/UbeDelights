<?php require_once __DIR__ . '/../server/customer_auth.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ube Delights - Activity Logs</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard.css?v=4.0">
    <link rel="stylesheet" href="../css/customer_logs.css?v=1.0">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <a onclick="getIndex()" class="logo-link">
                    <img src="../images/logo.png" alt="Ube Delights" class="logo-image">
                    <h2>Ube Delights</h2>
                </a>
            </div>
            <div class="nav-menu">
                <a onclick="getIndex()" class="nav-link">Dashboard</a>
                <a onclick="getShop()" class="nav-link">Shop</a>
                <a onclick="getCart()" class="nav-link cart-link">Cart <span class="cart-badge" id="cartBadge" style="display:none;">0</span></a>
                <a onclick="getOrders()" class="nav-link">My Orders</a>
                <a onclick="getActivityLogs()" class="nav-link active">Activity Logs</a>
                <a onclick="getProfile()" class="nav-link">Profile</a>
                <a onclick="getLogout()" class="nav-link">Log Out</a>
            </div>
        </div>
    </nav>

    <section class="hero-section hero-small">
        <div class="hero-content">
            <h1>Activity <span>Logs</span></h1>
            <p>View your account activity history.</p>
        </div>
    </section>

    <main class="main-content">
        <div class="logs-card">
            <div class="filters-grid">
                <div class="filter-field">
                    <label for="logsSearch">Search</label>
                    <input type="text" id="logsSearch" placeholder="Search username, name, action...">
                </div>
                <div class="filter-field">
                    <label for="logsFromDate">From Date</label>
                    <input type="date" id="logsFromDate">
                </div>
                <div class="filter-field">
                    <label for="logsToDate">To Date</label>
                    <input type="date" id="logsToDate">
                </div>
                <div class="filter-buttons">
                    <button type="button" id="btnApplyLogsFilter" class="btn-primary"><i class="fa-solid fa-filter"></i> Apply</button>
                    <button type="button" id="btnClearLogsFilter" class="btn-outline"><i class="fa-solid fa-xmark"></i> Clear</button>
                </div>
            </div>

            <table class="data-table" id="logsTable">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Action</th>
                        <th>Browser / OS</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>IP Address</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody id="logsTableBody"></tbody>
            </table>

            <div id="emptyLogs" class="empty-state" style="display:none;">
                <i class="fa-solid fa-clock-rotate-left"></i>
                <h3>No activity logs</h3>
                <p>No activity records match your current filters.</p>
            </div>

            <div class="pagination-bar" id="logsPaginationContainer"></div>
        </div>
    </main>

    <footer class="footer">
        <p>&copy; 2026 Ube Delights. All rights reserved.</p>
    </footer>

    <script src="../javascript/routing.js"></script>
    <script src="../javascript/dashboard.js"></script>
    <script src="../javascript/disable_back.js"></script>
    <script src="../javascript/index.js"></script>
    <script src="../javascript/inspect.js"></script>
    <script src="../javascript/customer_logs.js?v=1.0"></script>
</body>
</html>
