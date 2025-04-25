<?php
// signup.php
header('Content-Type: application/json');

// Database connection
$host = 'localhost';
$db_name = 'qydipzkd_Income';
$username = 'qydipzkd_Income';
$password = 'income314@';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get form data
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $mobile = $_POST['mobile'] ?? '';
    $referral = $_POST['referral'] ?? '';
    
    if (!$username || !$password || !$mobile) {
        echo json_encode([
            'success' => false,
            'message' => 'Please fill all required fields'
        ]);
        exit;
    }
    
    // Check if username already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Username already exists'
        ]);
        exit;
    }
    
    // Check if mobile already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE mobile = :mobile");
    $stmt->bindParam(':mobile', $mobile);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Mobile number already registered'
        ]);
        exit;
    }
    
    // Validate referral code if provided
    $referred_by = null;
    if ($referral) {
        $stmt = $conn->prepare("SELECT username FROM users WHERE referral_code = :referral");
        $stmt->bindParam(':referral', $referral);
        $stmt->execute();
        
        if ($stmt->rowCount() == 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid referral code'
            ]);
            exit;
        } else {
            $referrer = $stmt->fetch(PDO::FETCH_ASSOC);
            $referred_by = $referrer['username'];
        }
    }
    
    // Generate unique 6-digit account number
    $account_number = '';
    $is_unique = false;
    
    while (!$is_unique) {
        $account_number = sprintf("%06d", mt_rand(100000, 999999));
        
        $stmt = $conn->prepare("SELECT id FROM users WHERE account_number = :account_number");
        $stmt->bindParam(':account_number', $account_number);
        $stmt->execute();
        
        if ($stmt->rowCount() == 0) {
            $is_unique = true;
        }
    }
    
    // Generate unique referral code
    $referral_code = '';
    $is_unique_referral = false;
    
    while (!$is_unique_referral) {
        // Generate a random alphanumeric code
        $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $referral_code = '';
        for ($i = 0; $i < 6; $i++) {
            $referral_code .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        
        $stmt = $conn->prepare("SELECT id FROM users WHERE referral_code = :referral_code");
        $stmt->bindParam(':referral_code', $referral_code);
        $stmt->execute();
        
        if ($stmt->rowCount() == 0) {
            $is_unique_referral = true;
        }
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Begin transaction to ensure data consistency
    $conn->beginTransaction();
    
    try {
        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (username, password, mobile, account_number, referral_code, referred_by, balance) VALUES (:username, :password, :mobile, :account_number, :referral_code, :referred_by, 0.00)");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':mobile', $mobile);
        $stmt->bindParam(':account_number', $account_number);
        $stmt->bindParam(':referral_code', $referral_code);
        $stmt->bindParam(':referred_by', $referred_by);
        $stmt->execute();
        
        $user_id = $conn->lastInsertId();
        
        // Create welcome notification
        $notification_title = 'Welcome to MZ Income!';
        $notification_message = 'Thank you for joining MZ Income. Start exploring our platform to earn money.';
        
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (:user_id, :title, :message, 'welcome')");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':title', $notification_title);
        $stmt->bindParam(':message', $notification_message);
        $stmt->execute();
        
        // Process referral if any
        if ($referred_by) {
            // Get referrer details
            $stmt = $conn->prepare("SELECT id, username FROM users WHERE username = :username");
            $stmt->bindParam(':username', $referred_by);
            $stmt->execute();
            $referrer = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Get referral bonus percentage from settings
            $stmt = $conn->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'referral_bonus_percentage'");
            $stmt->execute();
            $bonus_percentage = $stmt->fetch(PDO::FETCH_ASSOC)['setting_value'] ?? 5;
            
            // We'll create a pending referral record but won't process the bonus yet
            // The bonus will be processed when the referred user makes their first deposit
            $stmt = $conn->prepare("INSERT INTO referral_earnings (referrer_id, referred_id, amount, status) VALUES (:referrer_id, :referred_id, 0, 'pending')");
            $stmt->bindParam(':referrer_id', $referrer['id']);
            $stmt->bindParam(':referred_id', $user_id);
            $stmt->execute();
            
            // Create notification for referrer
            $notification_title = 'New Referral Joined!';
            $notification_message = "User {$username} has joined using your referral code. You'll receive a bonus when they make their first deposit.";
            
            $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (:user_id, :title, :message, 'referral')");
            $stmt->bindParam(':user_id', $referrer['id']);
            $stmt->bindParam(':title', $notification_title);
            $stmt->bindParam(':message', $notification_message);
            $stmt->execute();
        }
        
        // Log activity
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $activity_description = "New user registered with username: {$username}";
        
        $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, activity_type, description, ip_address) VALUES (:user_id, 'registration', :description, :ip)");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':description', $activity_description);
        $stmt->bindParam(':ip', $ip);
        $stmt->execute();
        
        $conn->commit();
        
        // Start session and log user in
        session_start();
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
        $_SESSION['account_number'] = $account_number;
        
        echo json_encode([
            'success' => true,
            'message' => 'Registration successful',
            'account_number' => $account_number,
            'referral_code' => $referral_code
        ]);
        
    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>