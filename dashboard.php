<?php
// dashboard.php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'User';
$account_number = $_SESSION['account_number'] ?? '000000';

// Database connection
$host = 'localhost';
$db_name = 'qydipzkd_Income';
$username_db = 'qydipzkd_Income';
$password = 'income314@';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username_db, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get user's balance
    $stmt = $conn->prepare("SELECT balance, referral_code FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $balance = $user_data['balance'] ?? 0;
    $referral_code = $user_data['referral_code'] ?? '';
    
    // Get unread notification count
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = :user_id AND is_read = 0");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $notification_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get recent activities
    $stmt = $conn->prepare("SELECT * FROM transactions WHERE user_id = :user_id ORDER BY id DESC LIMIT 4");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $recent_activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get recent notifications
    $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = :user_id ORDER BY id DESC LIMIT 3");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $recent_notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    // Handle database error
    $error_message = "Database error: " . $e->getMessage();
    // You might want to log this error or display it to the user
    $balance = 0;
    $notification_count = 0;
    $recent_activities = [];
    $recent_notifications = [];
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MZ Income - ড্যাশবোর্ড</title>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Same CSS as before */
         
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Hind Siliguri', sans-serif;
        }

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

        body {
            background-color: var(--dark);
            color: var(--text-light);
            min-height: 100vh;
        }

        /* Header */
        .header {
            background-color: var(--dark-light);
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .logo {
            display: flex;
            align-items: center;
        }

        .logo h1 {
            color: var(--primary);
            font-size: 24px;
            font-weight: 700;
        }

        .header-icons {
            display: flex;
            gap: 20px;
        }

        .icon-btn {
            background: none;
            border: none;
            color: var(--text-light);
            font-size: 20px;
            cursor: pointer;
            position: relative;
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: var(--primary);
            color: var(--dark);
            font-size: 10px;
            font-weight: 700;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Main Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* User Info & Wallet */
        .user-wallet-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
        }

        .user-card {
            flex: 1;
            min-width: 280px;
            background-color: var(--dark-light);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border: 1px solid #333;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-avatar {
            width: 60px;
            height: 60px;
            background-color: var(--primary);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 24px;
            font-weight: 700;
            color: var(--dark);
        }

        .user-details h2 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .user-details p {
            font-size: 14px;
            color: var(--text-muted);
        }

        .wallet-card {
            flex: 1;
            min-width: 280px;
            background-color: var(--dark-light);
            background-image: linear-gradient(45deg, rgba(255, 222, 3, 0.1), transparent);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border: 1px solid #333;
            position: relative;
            overflow: hidden;
        }

        .wallet-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 222, 3, 0.1) 0%, transparent 70%);
            z-index: 0;
        }

        .wallet-info {
            position: relative;
            z-index: 1;
        }

        .wallet-label {
            font-size: 14px;
            color: var(--text-muted);
            margin-bottom: 5px;
        }

        .wallet-balance {
            font-size: 32px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 10px;
        }

        .wallet-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .wallet-btn {
            background-color: rgba(255, 222, 3, 0.15);
            color: var(--primary);
            border: 1px solid var(--primary);
            border-radius: 5px;
            padding: 8px 15px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
            flex: 1;
            text-align: center;
            text-decoration: none;
        }

        .wallet-btn:hover {
            background-color: var(--primary);
            color: var(--dark);
        }

        /* Action Boxes */
        .action-boxes {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .action-box {
            background-color: var(--dark-light);
            border: 1px solid #333;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
            text-decoration: none;
            color: var(--text-light);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            aspect-ratio: 1 / 1;
        }

        .action-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            border-color: var(--primary);
        }

        .action-box i {
            font-size: 28px;
            color: var(--primary);
            margin-bottom: 10px;
        }

        .action-box h3 {
            font-size: 16px;
            font-weight: 500;
        }

        /* Recent Activity */
        .section-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--primary);
            position: relative;
            display: inline-block;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 40px;
            height: 3px;
            background-color: var(--primary);
            border-radius: 5px;
        }

        .activity-list {
            background-color: var(--dark-light);
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #333;
        }

        .activity-item {
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #333;
            transition: background-color 0.2s;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-item:hover {
            background-color: var(--dark-medium);
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 18px;
        }

        .icon-deposit {
            background-color: rgba(76, 175, 80, 0.1);
            color: var(--success);
        }

        .icon-withdraw {
            background-color: rgba(244, 67, 54, 0.1);
            color: var(--danger);
        }

        .icon-transfer {
            background-color: rgba(33, 150, 243, 0.1);
            color: var(--info);
        }

        .icon-earn {
            background-color: rgba(255, 152, 0, 0.1);
            color: var(--warning);
        }

        .activity-details {
            flex: 1;
            margin-left: 15px;
        }

        .activity-title {
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 3px;
        }

        .activity-date {
            font-size: 12px;
            color: var(--text-muted);
        }

        .activity-amount {
            font-size: 16px;
            font-weight: 600;
            text-align: right;
        }

        .status-pill {
            font-size: 12px;
            padding: 3px 10px;
            border-radius: 15px;
            font-weight: 500;
            margin-top: 3px;
            display: inline-block;
        }

        .status-success {
            background-color: rgba(76, 175, 80, 0.1);
            color: var(--success);
        }

        .status-pending {
            background-color: rgba(255, 152, 0, 0.1);
            color: var(--warning);
        }

        /* Footer */
        .footer {
            background-color: var(--dark-light);
            padding: 20px;
            text-align: center;
            margin-top: 40px;
            border-top: 1px solid #333;
            color: var(--text-muted);
            font-size: 14px;
        }

        /* Notification Dropdown */
        .notification-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            width: 300px;
            background-color: var(--dark-light);
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
            border: 1px solid #333;
            z-index: 1000;
            display: none;
            max-height: 400px;
            overflow-y: auto;
        }

        .notification-header {
            padding: 15px;
            border-bottom: 1px solid #333;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notification-header h3 {
            font-size: 16px;
            font-weight: 600;
            color: var(--primary);
        }

        .notification-list {
            padding: 0;
        }

        .notification-item {
            padding: 15px;
            border-bottom: 1px solid #333;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            transition: background-color 0.2s;
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .notification-item:hover {
            background-color: var(--dark-medium);
        }

        .notification-icon {
            font-size: 18px;
            color: var(--primary);
        }

        .notification-content {
            flex: 1;
        }

        .notification-title {
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 3px;
        }

        .notification-time {
            font-size: 12px;
            color: var(--text-muted);
        }

        .notification-footer {
            padding: 10px;
            text-align: center;
            border-top: 1px solid #333;
        }

        .notification-footer a {
            color: var(--primary);
            text-decoration: none;
            font-size: 14px;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .user-wallet-container {
                flex-direction: column;
            }

            .action-boxes {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            }

            .notification-dropdown {
                width: 290px;
                right: -10px;
            }
        }

        /* View All Button */
        .view-all-btn {
            display: block;
            text-align: center;
            margin-top: 20px;
            padding: 10px;
            border-radius: 5px;
            background-color: var(--dark-medium);
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            border: 1px solid #333;
            transition: all 0.2s;
        }

        .view-all-btn:hover {
            background-color: rgba(255, 222, 3, 0.1);
            border-color: var(--primary);
        }

        /* ... */
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="logo">
            <h1>MZ Income</h1>
        </div>
        <div class="header-icons">
            <button class="icon-btn" id="notification-btn">
                <i class="fas fa-bell"></i>
                <?php if ($notification_count > 0): ?>
                <span class="notification-badge"><?php echo $notification_count; ?></span>
                <?php endif; ?>
            </button>
            
            <!-- Notification Dropdown -->
            <div class="notification-dropdown" id="notification-dropdown">
                <div class="notification-header">
                    <h3>নোটিফিকেশন</h3>
                    <span><?php echo $notification_count; ?> নতুন</span>
                </div>
                <div class="notification-list">
                    <?php if (!empty($recent_notifications)): ?>
                        <?php foreach ($recent_notifications as $notification): ?>
                            <div class="notification-item">
                                <div class="notification-icon">
                                    <?php 
                                    $icon = 'fa-bell';
                                    switch ($notification['type']) {
                                        case 'welcome':
                                            $icon = 'fa-smile';
                                            break;
                                        case 'deposit':
                                            $icon = 'fa-arrow-down';
                                            break;
                                        case 'withdraw':
                                            $icon = 'fa-arrow-up';
                                            break;
                                        case 'transfer':
                                            $icon = 'fa-exchange-alt';
                                            break;
                                        case 'referral':
                                            $icon = 'fa-users';
                                            break;
                                    }
                                    ?>
                                    <i class="fas <?php echo $icon; ?>"></i>
                                </div>
                                <div class="notification-content">
                                    <div class="notification-title"><?php echo htmlspecialchars($notification['title']); ?></div>
                                    <div class="notification-time">
                                        <?php 
                                        $notification_time = strtotime($notification['created_at']);
                                        $now = time();
                                        $diff = $now - $notification_time;
                                        
                                        if ($diff < 60) {
                                            echo 'এই মাত্র';
                                        } elseif ($diff < 3600) {
                                            echo floor($diff / 60) . ' মিনিট আগে';
                                        } elseif ($diff < 86400) {
                                            echo floor($diff / 3600) . ' ঘণ্টা আগে';
                                        } else {
                                            echo date('d M Y', $notification_time);
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="notification-item">
                            <div class="notification-content">
                                <div class="notification-title">কোন নোটিফিকেশন নেই</div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="notification-footer">
                    <a href="notifications.php">সব নোটিফিকেশন দেখুন</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container">
        <!-- User Info & Wallet -->
        <div class="user-wallet-container">
            <div class="user-card">
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($username, 0, 1)); ?>
                    </div>
                    <div class="user-details">
                        <h2><?php echo htmlspecialchars($username); ?></h2>
                        <p>অ্যাকাউন্ট #<?php echo htmlspecialchars($account_number); ?></p>
                        <p style="margin-top: 5px; font-size: 12px;">রেফারাল কোড: <strong><?php echo htmlspecialchars($referral_code); ?></strong></p>
                    </div>
                </div>
            </div>

            <div class="wallet-card">
                <div class="wallet-info">
                    <div class="wallet-label">ব্যালেন্স</div>
                    <div class="wallet-balance">৳ <?php echo number_format($balance, 2); ?></div>
                    
                    <div class="wallet-buttons">
                        <a href="deposit.php" class="wallet-btn">
                            <i class="fas fa-plus"></i> ডিপোজিট
                        </a>
                        <a href="withdraw.php" class="wallet-btn">
                            <i class="fas fa-minus"></i> উইথড্র
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Boxes -->
        <div class="action-boxes">
            <a href="deposit.php" class="action-box">
                <i class="fas fa-wallet"></i>
                <h3>ডিপোজিট</h3>
            </a>
            <a href="withdraw.php" class="action-box">
                <i class="fas fa-money-bill-wave"></i>
                <h3>উইথড্র</h3>
            </a>
            <a href="transfer.php" class="action-box">
                <i class="fas fa-exchange-alt"></i>
                <h3>ট্রান্সফার</h3>
            </a>
            <a href="earn.php" class="action-box">
                <i class="fas fa-coins"></i>
                <h3>আয় করুন</h3>
            </a>
            <a href="membership.php" class="action-box">
                <i class="fas fa-crown"></i>
                <h3>মেম্বারশিপ</h3>
            </a>
            <a href="affiliate.php" class="action-box">
                <i class="fas fa-users"></i>
                <h3>অ্যাফিলিয়েট</h3>
            </a>
            <a href="settings.php" class="action-box">
                <i class="fas fa-cog"></i>
                <h3>সেটিংস</h3>
            </a>
            <a href="logout.php" class="action-box">
                <i class="fas fa-sign-out-alt"></i>
                <h3>লগ আউট</h3>
            </a>
        </div>

        <!-- Recent Activity -->
        <h2 class="section-title">সাম্প্রতিক কার্যকলাপ</h2>
        <div class="activity-list">
            <?php if (!empty($recent_activities)): ?>
                <?php foreach ($recent_activities as $activity): ?>
                    <div class="activity-item">
                        <div class="activity-icon icon-<?php echo $activity['type']; ?>">
                            <?php 
                            $icon = 'fa-question';
                            $title = '';
                            switch ($activity['type']) {
                                case 'deposit':
                                    $icon = 'fa-arrow-down';
                                    $title = 'ডিপোজিট';
                                    break;
                                case 'withdraw':
                                    $icon = 'fa-arrow-up';
                                    $title = 'উইথড্র';
                                    break;
                                case 'transfer':
                                    $icon = 'fa-exchange-alt';
                                    $title = 'ট্রান্সফার';
                                    break;
                                case 'bonus':
                                    $icon = 'fa-gift';
                                    $title = 'বোনাস';
                                    break;
                                case 'earning':
                                    $icon = 'fa-coins';
                                    $title = 'আয়';
                                    break;
                            }
                            ?>
                            <i class="fas <?php echo $icon; ?>"></i>
                        </div>
                        <div class="activity-details">
                            <div class="activity-title"><?php echo $title; ?></div>
                            <div class="activity-date">
                                <?php 
                                $activity_time = strtotime($activity['created_at']);
                                $now = time();
                                $diff = $now - $activity_time;
                                
                                if ($diff < 60) {
                                    echo 'এই মাত্র';
                                } elseif ($diff < 3600) {
                                    echo floor($diff / 60) . ' মিনিট আগে';
                                } elseif ($diff < 86400) {
                                    echo floor($diff / 3600) . ' ঘণ্টা আগে';
                                } elseif ($diff < 172800) {
                                    echo 'গতকাল';
                                } else {
                                    echo date('d M Y', $activity_time);
                                }
                                ?>
                            </div>
                        </div>
                        <div class="activity-amount">
                            ৳ <?php echo number_format($activity['amount'], 2); ?>
                            <div class="status-pill status-<?php echo $activity['status']; ?>">
                                <?php 
                                $status = '';
                                switch ($activity['status']) {
                                    case 'completed':
                                        $status = 'সম্পন্ন';
                                        break;
                                    case 'pending':
                                        $status = 'বাকি';
                                        break;
                                    case 'rejected':
                                        $status = 'বাতিল';
                                        break;
                                }
                                echo $status;
                                ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="activity-item" style="justify-content: center; padding: 30px 20px;">
                    <div style="text-align: center; width: 100%;">
                        <i class="fas fa-info-circle" style="font-size: 24px; color: #aaa; margin-bottom: 10px;"></i>
                        <p>কোন কার্যকলাপ নেই</p>
                        <p style="font-size: 12px; color: #aaa; margin-top: 5px;">আপনার প্রথম ডিপোজিট করে শুরু করুন!</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <a href="activities.php" class="view-all-btn">সমস্ত কার্যকলাপ দেখুন</a>
    </div>

    <!-- Footer -->
    <footer class="footer">
        &copy; <?php echo date('Y'); ?> MZ Income. সর্বস্বত্ব সংরক্ষিত।
    </footer>
<script>
        // Toggle notification dropdown
        const notificationBtn = document.getElementById('notification-btn');
        const notificationDropdown = document.getElementById('notification-dropdown');
        
        notificationBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            notificationDropdown.style.display = notificationDropdown.style.display === 'block' ? 'none' : 'block';
            
            // Mark notifications as read when opened
            if (notificationDropdown.style.display === 'block' && <?php echo $notification_count; ?> > 0) {
                fetch('mark_notifications_read.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const badge = document.querySelector('.notification-badge');
                        if (badge) {
                            badge.style.display = 'none';
                        }
                    }
                })
                .catch(error => {
                    console.error('Error marking notifications as read:', error);
                });
            }
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!notificationDropdown.contains(e.target) && e.target !== notificationBtn) {
                notificationDropdown.style.display = 'none';
            }
        });
        
        // Action boxes click handler
        document.querySelectorAll('.action-box').forEach(box => {
            box.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                window.location.href = href;
            });
        });
    </script>
</body>
</html>