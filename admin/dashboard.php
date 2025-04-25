<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$admin_username = $_SESSION['admin_username'] ?? 'Admin';
$admin_role = $_SESSION['admin_role'] ?? 'admin';
$admin_name = $_SESSION['admin_name'] ?? 'Administrator';

// Database connection
$host = 'localhost';
$db_name = 'qydipzkd_Income';
$username = 'qydipzkd_Income';
$password = 'income314@';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get summary data
    // Total users
    $stmt = $conn->query("SELECT COUNT(*) as total FROM users");
    $total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Active users (users who logged in today)
    $stmt = $conn->query("SELECT COUNT(*) as total FROM activity_logs WHERE activity_type = 'login' AND DATE(created_at) = CURDATE()");
    $active_users = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total deposits
    $stmt = $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE type = 'deposit' AND status = 'completed'");
    $total_deposits = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total withdrawals
    $stmt = $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE type = 'withdraw' AND status = 'completed'");
    $total_withdrawals = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Pending withdrawal requests
    $stmt = $conn->query("SELECT COUNT(*) as total FROM transactions WHERE type = 'withdraw' AND status = 'pending'");
    $pending_withdrawals = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Latest users
    $stmt = $conn->query("SELECT id, username, account_number, mobile, balance, created_at FROM users ORDER BY id DESC LIMIT 5");
    $latest_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Recent transactions
    $stmt = $conn->query("SELECT t.id, t.amount, t.type, t.status, t.created_at, u.username 
                          FROM transactions t 
                          JOIN users u ON t.user_id = u.id 
                          ORDER BY t.id DESC LIMIT 10");
    $recent_transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MZ Income - Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #FFDE03;
            --dark: #121212;
            --dark-light: #1e1e1e;
            --dark-medium: #2a2a2a;
            --text-light: #f5f5f5;
            --text-muted: #aaa;
            --success: #4CAF50;
            --warning: #FF9800;
            --danger: #F44336;
            --info: #2196F3;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: var(--dark);
            color: var(--text-light);
            min-height: 100vh;
            display: flex;
        }
        
        /* Sidebar */
        .sidebar {
            width: 250px;
            background-color: var(--dark-light);
            border-right: 1px solid #333;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            overflow-y: auto;
            z-index: 100;
        }
        
        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid #333;
        }
        
        .sidebar-header h1 {
            color: var(--primary);
            font-size: 22px;
            font-weight: 700;
        }
        
        .sidebar-header p {
            color: var(--text-muted);
            font-size: 12px;
            margin-top: 5px;
        }
        
        .admin-info {
            padding: 15px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid #333;
        }
        
        .admin-avatar {
            width: 40px;
            height: 40px;
            background-color: var(--primary);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 18px;
            font-weight: 700;
            color: var(--dark);
        }
        
        .admin-details {
            flex: 1;
        }
        
        .admin-name {
            font-size: 14px;
            font-weight: 500;
        }
        
        .admin-role {
            font-size: 12px;
            color: var(--primary);
        }
        
        .sidebar-menu {
            padding: 15px 0;
        }
        
        .menu-item {
            display: flex;
            align-items: center;
            padding: 10px 20px;
            color: var(--text-light);
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .menu-item:hover, .menu-item.active {
            background-color: var(--dark-medium);
            color: var(--primary);
        }
        
        .menu-item i {
            width: 25px;
            margin-right: 10px;
        }
        
        .menu-category {
            font-size: 12px;
            color: var(--text-muted);
            padding: 15px 20px 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #333;
        }
        
        .page-title h1 {
            font-size: 24px;
            font-weight: 600;
            color: var(--primary);
        }
        
        .page-title p {
            font-size: 14px;
            color: var(--text-muted);
            margin-top: 5px;
        }
        
        .header-actions a {
            background-color: var(--primary);
            color: var(--dark);
            padding: 8px 15px;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .header-actions a:hover {
            background-color: #FFE838;
            transform: translateY(-2px);
        }
        
        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: var(--dark-light);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border: 1px solid #333;
            display: flex;
            align-items: center;
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 22px;
            margin-right: 15px;
        }
        
        .icon-users {
            background-color: rgba(33, 150, 243, 0.1);
            color: var(--info);
        }
        
        .icon-deposits {
            background-color: rgba(76, 175, 80, 0.1);
            color: var(--success);
        }
        
        .icon-withdrawals {
            background-color: rgba(244, 67, 54, 0.1);
            color: var(--danger);
        }
        
        .icon-pending {
            background-color: rgba(255, 152, 0, 0.1);
            color: var(--warning);
        }
        
        .stat-info {
            flex: 1;
        }
        
        .stat-value {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 12px;
            color: var(--text-muted);
            text-transform: uppercase;
        }
        
        /* Data Tables */
        .data-card {
            background-color: var(--dark-light);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border: 1px solid #333;
            margin-bottom: 30px;
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 15px;
            border-bottom: 1px solid #333;
            margin-bottom: 15px;
        }
        
        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--primary);
        }
        
        .view-all {
            font-size: 14px;
            color: var(--primary);
            text-decoration: none;
        }
        
        .view-all:hover {
            text-decoration: underline;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th, table td {
            padding: 10px 15px;
            text-align: left;
            border-bottom: 1px solid #333;
        }
        
        table th {
            color: var(--text-muted);
            font-weight: 500;
            font-size: 14px;
        }
        
        table tr:last-child td {
            border-bottom: none;
        }
        
        table tr:hover {
            background-color: var(--dark-medium);
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-completed {
            background-color: rgba(76, 175, 80, 0.1);
            color: var(--success);
        }
        
        .status-pending {
            background-color: rgba(255, 152, 0, 0.1);
            color: var(--warning);
        }
        
        .status-rejected {
            background-color: rgba(244, 67, 54, 0.1);
            color: var(--danger);
        }
        
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        
        .btn-action {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .btn-view {
            background-color: rgba(33, 150, 243, 0.1);
            color: var(--info);
        }
        
        .btn-edit {
            background-color: rgba(255, 152, 0, 0.1);
            color: var(--warning);
        }
        
        .btn-delete {
            background-color: rgba(244, 67, 54, 0.1);
            color: var(--danger);
        }
        
        .btn-action:hover {
            transform: translateY(-2px);
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                width: 70px;
                overflow: visible;
            }
            
            .sidebar-header h1, .admin-details, .menu-item span, .menu-category {
                display: none;
            }
            
            .sidebar-header {
                padding: 15px 0;
            }
            
            .admin-info {
                justify-content: center;
                padding: 15px 0;
            }
            
            .menu-item {
                justify-content: center;
                padding: 15px 0;
            }
            
            .menu-item i {
                margin-right: 0;
                font-size: 18px;
            }
            
            .main-content {
                margin-left: 70px;
            }
        }
        
        @media (max-width: 768px) {
            .stats-container {
                grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            }
        }
        
        @media (max-width: 576px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .header-actions {
                width: 100%;
                display: flex;
                justify-content: flex-end;
            }
            
            .stats-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h1>MZ Income</h1>
            <p>Admin Panel</p>
        </div>
        
        <div class="admin-info">
            <div class="admin-avatar">
                <?php echo strtoupper(substr($admin_username, 0, 1)); ?>
            </div>
            <div class="admin-details">
                <div class="admin-name"><?php echo htmlspecialchars($admin_name); ?></div>
                <div class="admin-role"><?php echo ucfirst(htmlspecialchars($admin_role)); ?></div>
            </div>
        </div>
        
        <div class="sidebar-menu">
            <a href="dashboard.php" class="menu-item active">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            
            <div class="menu-category">User Management</div>
            <a href="users/index.php" class="menu-item">
                <i class="fas fa-users"></i>
                <span>All Users</span>
            </a>
            <a href="users/add.php" class="menu-item">
                <i class="fas fa-user-plus"></i>
                <span>Add User</span>
            </a>
            
            <div class="menu-category">Transactions</div>
            <a href="transactions/deposits.php" class="menu-item">
                <i class="fas fa-money-bill-wave"></i>
                <span>Deposits</span>
            </a>
            <a href="transactions/withdrawals.php" class="menu-item">
                <i class="fas fa-hand-holding-usd"></i>
                <span>Withdrawals</span>
            </a>
            <a href="transactions/transfers.php" class="menu-item">
                <i class="fas fa-exchange-alt"></i>
                <span>Transfers</span>
            </a>
            
            <div class="menu-category">Earning</div>
            <a href="earning/tasks.php" class="menu-item">
                <i class="fas fa-tasks"></i>
                <span>Earning Tasks</span>
            </a>
            
            <div class="menu-category">Membership</div>
            <a href="membership/plans.php" class="menu-item">
                <i class="fas fa-crown"></i>
                <span>Plans</span>
            </a>
            <a href="membership/members.php" class="menu-item">
                <i class="fas fa-user-tag"></i>
                <span>Members</span>
            </a>
            
            <div class="menu-category">Settings</div>
            <a href="settings/general.php" class="menu-item">
                <i class="fas fa-cog"></i>
                <span>General Settings</span>
            </a>
            <a href="settings/payment.php" class="menu-item">
                <i class="fas fa-credit-card"></i>
                <span>Payment Methods</span>
            </a>
            
            <div class="menu-category">System</div>
            <a href="admins/index.php" class="menu-item">
                <i class="fas fa-user-shield"></i>
                <span>Admin Users</span>
            </a>
            <a href="logs.php" class="menu-item">
                <i class="fas fa-history"></i>
                <span>Activity Logs</span>
            </a>
            <a href="logout.php" class="menu-item">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <div class="page-title">
                <h1>Dashboard</h1>
                <p>Welcome back, <?php echo htmlspecialchars($admin_name); ?>!</p>
            </div>
            
            <div class="header-actions">
                <a href="../index.php" target="_blank">
                    <i class="fas fa-external-link-alt"></i> View Site
                </a>
            </div>
        </div>
        
        <!-- Stats -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon icon-users">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?php echo number_format($total_users); ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon icon-users">
                    <i class="fas fa-user-clock"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?php echo number_format($active_users); ?></div>
                    <div class="stat-label">Active Today</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon icon-deposits">
                    <i class="fas fa-arrow-down"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value">৳ <?php echo number_format($total_deposits, 2); ?></div>
                    <div class="stat-label">Total Deposits</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon icon-withdrawals">
                    <i class="fas fa-arrow-up"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value">৳ <?php echo number_format($total_withdrawals, 2); ?></div>
                    <div class="stat-label">Total Withdrawals</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon icon-pending">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value"><?php echo number_format($pending_withdrawals); ?></div>
                    <div class="stat-label">Pending Withdrawals</div>
                </div>
            </div>
        </div>
        
        <!-- Latest Users -->
        <div class="data-card">
            <div class="card-header">
                <h3 class="card-title">Latest Users</h3>
                <a href="users/index.php" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
            </div>
            
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Account #</th>
                            <th>Mobile</th>
                            <th>Balance</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($latest_users)): ?>
                            <?php foreach ($latest_users as $user): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['account_number']); ?></td>
                                    <td><?php echo htmlspecialchars($user['mobile']); ?></td>
                                    <td>৳ <?php echo number_format($user['balance'], 2); ?></td>
                                    <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="users/view.php?id=<?php echo $user['id']; ?>" class="btn-action btn-view">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="users/edit.php?id=<?php echo $user['id']; ?>" class="btn-action btn-edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">No users found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Recent Transactions -->
        <div class="data-card">
            <div class="card-header">
                <h3 class="card-title">Recent Transactions</h3>
                <a href="transactions/index.php" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
            </div>
            
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recent_transactions)): ?>
                            <?php foreach ($recent_transactions as $transaction): ?>
                                <tr>
                                    <td><?php echo $transaction['id']; ?></td>
                                    <td><?php echo htmlspecialchars($transaction['username']); ?></td>
                                    <td>
                                        <?php
                                        $type_icon = '';
                                        switch ($transaction['type']) {
                                            case 'deposit':
                                                $type_icon = '<i class="fas fa-arrow-down text-success"></i>';
                                                $type_text = 'Deposit';
                                                break;
                                            case 'withdraw':
                                                $type_icon = '<i class="fas fa-arrow-up text-danger"></i>';
                                                $type_text = 'Withdraw';
                                                break;
                                            case 'transfer':
                                                $type_icon = '<i class="fas fa-exchange-alt text-info"></i>';
                                                $type_text = 'Transfer';
                                                break;
                                            case 'bonus':
                                                $type_icon = '<i class="fas fa-gift text-warning"></i>';
                                                $type_text = 'Bonus';
                                                break;
                                            case 'earning':
                                                $type_icon = '<i class="fas fa-coins text-warning"></i>';
                                                $type_text = 'Earning';
                                                break;
                                        }
                                        echo $type_icon . ' ' . $type_text;
                                        ?>
                                    </td>
                                    <td>৳ <?php echo number_format($transaction['amount'], 2); ?></td>
                                    <td>
                                        <?php
                                        $status_class = '';
                                        switch ($transaction['status']) {
                                            case 'completed':
                                                $status_class = 'status-completed';
                                                break;
                                            case 'pending':
                                                $status_class = 'status-pending';
                                                break;
                                            case 'rejected':
                                                $status_class = 'status-rejected';
                                                break;
                                        }
                                        ?>
                                        <span class="status-badge <?php echo $status_class; ?>">
                                            <?php echo ucfirst($transaction['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d M Y H:i', strtotime($transaction['created_at'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="transactions/view.php?id=<?php echo $transaction['id']; ?>" class="btn-action btn-view">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($transaction['status'] == 'pending'): ?>
                                                <a href="transactions/process.php?id=<?php echo $transaction['id']; ?>" class="btn-action btn-edit">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">No transactions found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>