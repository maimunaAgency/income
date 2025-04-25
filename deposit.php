<?php
// deposit.php
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

$message = '';
$message_type = '';
$step = 1; // Default to step 1 (amount and method selection)

// Define the base URL to ensure consistent paths
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
$current_url = $base_url . $_SERVER['PHP_SELF'];

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username_db, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get user's balance
    $stmt = $conn->prepare("SELECT balance FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
    $balance = $user_data['balance'] ?? 0;
    
    // Get payment methods for deposit
    $stmt = $conn->prepare("SELECT * FROM payment_methods WHERE type IN ('deposit', 'both') AND status = 'active'");
    $stmt->execute();
    $payment_methods = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get minimum deposit amount from settings
    $stmt = $conn->prepare("SELECT setting_value FROM site_settings WHERE setting_key = 'deposit_min_amount'");
    $stmt->execute();
    $min_deposit = $stmt->fetch(PDO::FETCH_ASSOC)['setting_value'] ?? 100;
    
    // Process step 1 form submission (amount and method selection)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['step']) && $_POST['step'] == 1) {
        $amount = isset($_POST['amount']) ? (float)$_POST['amount'] : 0;
        $payment_method_id = isset($_POST['payment_method']) ? (int)$_POST['payment_method'] : 0;
        
        // Validate input
        if ($amount < $min_deposit) {
            $message = "ন্যূনতম ডিপোজিট পরিমাণ ৳ {$min_deposit}";
            $message_type = 'error';
        } elseif (empty($payment_method_id)) {
            $message = "অনুগ্রহ করে একটি পেমেন্ট মেথড নির্বাচন করুন";
            $message_type = 'error';
        } else {
            // Move to step 2
            $step = 2;
            
            // Store in session
            $_SESSION['deposit_amount'] = $amount;
            $_SESSION['deposit_method_id'] = $payment_method_id;
            
            // Get payment method details
            $stmt = $conn->prepare("SELECT name FROM payment_methods WHERE id = :id");
            $stmt->bindParam(':id', $payment_method_id);
            $stmt->execute();
            $payment_method = $stmt->fetch(PDO::FETCH_ASSOC);
            $_SESSION['deposit_method_name'] = $payment_method['name'];
        }
    }
    
    // Process step 2 form submission (transaction details)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['step']) && $_POST['step'] == 2) {
        $amount = $_SESSION['deposit_amount'] ?? 0;
        $payment_method_id = $_SESSION['deposit_method_id'] ?? 0;
        $payment_method_name = $_SESSION['deposit_method_name'] ?? '';
        
        $transaction_id = isset($_POST['transaction_id']) ? trim($_POST['transaction_id']) : '';
        $sender_number = isset($_POST['sender_number']) ? trim($_POST['sender_number']) : '';
        
        // Handle file upload
        $screenshot = null;
        if (isset($_FILES['payment_screenshot']) && $_FILES['payment_screenshot']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png'];
            $filename = $_FILES['payment_screenshot']['name'];
            $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($file_ext, $allowed)) {
                $new_filename = 'payment_' . time() . '_' . rand(1000, 9999) . '.' . $file_ext;
                $upload_dir = 'uploads/payments/';
                
                // Create directory if it doesn't exist
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $upload_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['payment_screenshot']['tmp_name'], $upload_path)) {
                    $screenshot = $upload_path;
                }
            }
        }
        
        // Validate input
        $error = false;
        
        if (empty($transaction_id)) {
            $message = "ট্রানজেকশন আইডি প্রয়োজন";
            $message_type = 'error';
            $error = true;
        } elseif (empty($sender_number)) {
            $message = "প্রেরকের নম্বর প্রয়োজন";
            $message_type = 'error';
            $error = true;
        } elseif ($payment_method_id == 1 && (!preg_match('/^CD[A-Z0-9]{8}$/', $transaction_id))) {
            // Only validate bKash transaction ID format (but don't give any hint)
            $message = "অবৈধ ট্রানজেকশন আইডি";
            $message_type = 'error';
            $error = true;
        }
        
        if (!$error) {
            // Generate unique reference ID
            $reference_id = 'DEP' . time() . rand(1000, 9999);
            
            // Calculate fee if any
            $fee = 0; // No fee for deposits typically
            
            // Begin transaction to ensure data consistency
            $conn->beginTransaction();
            
            try {
                // Insert deposit request into transactions table
                $stmt = $conn->prepare("INSERT INTO transactions (user_id, type, amount, fee, status, payment_method, transaction_details, reference_id) 
                                      VALUES (:user_id, 'deposit', :amount, :fee, 'pending', :payment_method, :details, :reference_id)");
                $stmt->bindParam(':user_id', $user_id);
                $stmt->bindParam(':amount', $amount);
                $stmt->bindParam(':fee', $fee);
                $stmt->bindParam(':payment_method', $payment_method_name);
                
                $details = json_encode([
                    'transaction_id' => $transaction_id,
                    'sender_number' => $sender_number,
                    'payment_method_id' => $payment_method_id
                ]);
                
                $stmt->bindParam(':details', $details);
                $stmt->bindParam(':reference_id', $reference_id);
                $stmt->execute();
                
                $transaction_id_db = $conn->lastInsertId();
                
                // Save screenshot if uploaded
                if ($screenshot) {
                    // Save screenshot info to deposit_proofs table
                    $stmt = $conn->prepare("INSERT INTO deposit_proofs (transaction_id, file_path, original_filename, file_size, mime_type) 
                                          VALUES (:transaction_id, :file_path, :original_filename, :file_size, :mime_type)");
                    $stmt->bindParam(':transaction_id', $transaction_id_db);
                    $stmt->bindParam(':file_path', $screenshot);
                    
                    $original_filename = $_FILES['payment_screenshot']['name'];
                    $file_size = $_FILES['payment_screenshot']['size'];
                    $mime_type = $_FILES['payment_screenshot']['type'];
                    
                    $stmt->bindParam(':original_filename', $original_filename);
                    $stmt->bindParam(':file_size', $file_size);
                    $stmt->bindParam(':mime_type', $mime_type);
                    $stmt->execute();
                }
                
                // Create notification for user
                $notification_title = "ডিপোজিট অনুরোধ গৃহীত হয়েছে";
                $notification_message = "আপনার ৳ " . number_format($amount, 2) . " ডিপোজিট অনুরোধ গৃহীত হয়েছে। রেফারেন্স: {$reference_id}";
                
                $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, type, related_id) 
                                       VALUES (:user_id, :title, :message, 'deposit', :transaction_id)");
                $stmt->bindParam(':user_id', $user_id);
                $stmt->bindParam(':title', $notification_title);
                $stmt->bindParam(':message', $notification_message);
                $stmt->bindParam(':transaction_id', $transaction_id_db);
                $stmt->execute();
                
                // Create notification for admin
                $stmt = $conn->prepare("SELECT id FROM admin_users WHERE role = 'super_admin' LIMIT 1");
                $stmt->execute();
                $admin_id = $stmt->fetch(PDO::FETCH_ASSOC)['id'] ?? null;
                
                if ($admin_id) {
                    $admin_notification_title = "New Deposit Request";
                    $admin_notification_message = "User {$username} has requested a deposit of ৳ " . number_format($amount, 2) . ". Reference: {$reference_id}";
                    
                    $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, type, related_id) 
                                           VALUES (:admin_id, :title, :message, 'admin_deposit', :transaction_id)");
                    $stmt->bindParam(':admin_id', $admin_id);
                    $stmt->bindParam(':title', $admin_notification_title);
                    $stmt->bindParam(':message', $admin_notification_message);
                    $stmt->bindParam(':transaction_id', $transaction_id_db);
                    $stmt->execute();
                }
                
                // Log activity
                $ip = $_SERVER['REMOTE_ADDR'] ?? '';
                $activity_description = "Deposit request of ৳ " . number_format($amount, 2) . " via " . $payment_method_name;
                
                $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, activity_type, description, ip_address) 
                                       VALUES (:user_id, 'deposit_request', :description, :ip)");
                $stmt->bindParam(':user_id', $user_id);
                $stmt->bindParam(':description', $activity_description);
                $stmt->bindParam(':ip', $ip);
                $stmt->execute();
                
                $conn->commit();
                
                // Clear session deposit variables
                unset($_SESSION['deposit_amount']);
                unset($_SESSION['deposit_method_id']);
                unset($_SESSION['deposit_method_name']);
                
                $message = "আপনার ডিপোজিট অনুরোধ সফলভাবে জমা দেওয়া হয়েছে। রেফারেন্স: {$reference_id}";
                $message_type = 'success';
                $step = 3; // Success step
                
            } catch (Exception $e) {
                $conn->rollBack();
                $message = "একটি ত্রুটি ঘটেছে: " . $e->getMessage();
                $message_type = 'error';
                $step = 2; // Stay on step 2 if there was an error
            }
        } else {
            $step = 2; // Stay on step 2 if there was an error
        }
    }
    
    // Get recent deposit transactions
    $stmt = $conn->prepare("SELECT * FROM transactions WHERE user_id = :user_id AND type = 'deposit' ORDER BY id DESC LIMIT 5");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $recent_deposits = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $message = "ডাটাবেস ত্রুটি: " . $e->getMessage();
    $message_type = 'error';
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MZ Income - ডিপোজিট</title>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
            font-family: 'Hind Siliguri', sans-serif;
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

        .back-btn {
            background: none;
            border: none;
            color: var(--text-light);
            font-size: 18px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* Main Container */
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Page Title */
        .page-title {
            margin-bottom: 20px;
            border-bottom: 1px solid #333;
            padding-bottom: 15px;
        }

        .page-title h1 {
            font-size: 24px;
            color: var(--primary);
            margin-bottom: 5px;
        }

        .page-title p {
            color: var(--text-muted);
            font-size: 14px;
        }

        /* Balance Card */
        .balance-card {
            background-color: var(--dark-light);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border: 1px solid #333;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .balance-info {
            display: flex;
            align-items: center;
        }

        .balance-icon {
            width: 40px;
            height: 40px;
            background-color: rgba(255, 222, 3, 0.1);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-right: 15px;
            color: var(--primary);
        }

        .balance-details h3 {
            font-size: 14px;
            color: var(--text-muted);
            margin-bottom: 5px;
        }

        .balance-amount {
            font-size: 20px;
            font-weight: 600;
            color: var(--text-light);
        }

        /* Form Card */
        .form-card {
            background-color: var(--dark-light);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border: 1px solid #333;
            margin-bottom: 30px;
        }

        .form-title {
            font-size: 18px;
            color: var(--primary);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #333;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-light);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            background-color: var(--dark-medium);
            border: 1px solid #333;
            border-radius: 5px;
            color: var(--text-light);
            font-size: 14px;
            transition: all 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(255, 222, 3, 0.2);
        }

        .form-hint {
            display: block;
            margin-top: 5px;
            font-size: 12px;
            color: var(--text-muted);
        }

        /* Payment Methods */
        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .payment-method {
            background-color: var(--dark-medium);
            border: 1px solid #333;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            height: 120px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .payment-method:hover, .payment-method.active {
            border-color: var(--primary);
            background-color: rgba(255, 222, 3, 0.1);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .payment-method-logo {
            width: 70px;
            height: 50px;
            margin-bottom: 10px;
            object-fit: contain;
        }

        .payment-method-name {
            font-size: 16px;
            font-weight: 500;
            color: var(--text-light);
            margin-top: 8px;
        }

        /* Payment Info Card */
        .payment-info-card {
            background-color: var(--dark-medium);
            border-radius: 10px;
            border: 1px solid #333;
            padding: 20px;
            margin-bottom: 20px;
        }

        .payment-method-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #333;
        }

        .payment-logo {
            width: 70px;
            height: 50px;
            background-color: #fff;
            border-radius: 6px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-right: 15px;
            padding: 5px;
        }

        .payment-logo img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .payment-method-details h3 {
            font-size: 18px;
            color: var(--primary);
            margin-bottom: 5px;
        }

        .payment-amount {
            font-size: 22px;
            font-weight: 600;
        }

        .payment-instruction {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #333;
        }

        .payment-instruction p {
            margin-bottom: 10px;
            line-height: 1.5;
        }

        .payment-number-container {
            display: flex;
            align-items: center;
            background-color: rgba(255, 222, 3, 0.1);
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .payment-number {
            flex: 1;
            font-size: 18px;
            font-weight: 600;
            font-family: monospace;
            color: var(--primary);
        }

        .copy-btn {
            background-color: var(--primary);
            color: var(--dark);
            border: none;
            border-radius: 5px;
            padding: 8px 15px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .copy-btn:hover {
            background-color: #FFE838;
        }

        .btn-primary {
            background-color: var(--primary);
            color: var(--dark);
            border: none;
            border-radius: 5px;
            padding: 12px 20px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
        }

        .btn-primary:hover {
            background-color: #FFE838;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 222, 3, 0.3);
        }

        /* Steps */
        .steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .step {
            flex: 1;
            text-align: center;
            position: relative;
        }

        .step:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 15px;
            right: -50%;
            width: 100%;
            height: 2px;
            background-color: #333;
            z-index: 1;
        }

        .step.active:not(:last-child)::after {
            background-color: var(--primary);
        }

        .step-number {
            width: 30px;
            height: 30px;
            background-color: #333;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 10px;
            position: relative;
            z-index: 2;
        }

        .step.active .step-number {
            background-color: var(--primary);
            color: var(--dark);
            font-weight: 600;
        }

        .step-title {
            font-size: 14px;
            color: var(--text-muted);
        }

        .step.active .step-title {
            color: var(--primary);
            font-weight: 500;
        }

        /* Message alert */
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: rgba(76, 175, 80, 0.1);
            border: 1px solid rgba(76, 175, 80, 0.3);
            color: var(--success);
        }

        .alert-error {
            background-color: rgba(244, 67, 54, 0.1);
            border: 1px solid rgba(244, 67, 54, 0.3);
            color: var(--danger);
        }

        /* Recent Transactions */
        .transactions-card {
            background-color: var(--dark-light);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border: 1px solid #333;
        }

        .transaction-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #333;
        }

        .transaction-item:last-child {
            border-bottom: none;
        }

        .transaction-icon {
            width: 40px;
            height: 40px;
            background-color: rgba(76, 175, 80, 0.1);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-right: 15px;
            color: var(--success);
        }

        .transaction-info {
            flex: 1;
        }

        .transaction-title {
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 3px;
        }

        .transaction-date {
            font-size: 12px;
            color: var(--text-muted);
        }

        .transaction-amount {
            font-size: 16px;
            font-weight: 600;
        }

        .transaction-status {
            font-size: 12px;
            padding: 3px 8px;
            border-radius: 10px;
            margin-top: 5px;
            display: inline-block;
        }

        .status-pending {
            background-color: rgba(255, 152, 0, 0.1);
            color: var(--warning);
        }

        .status-completed {
            background-color: rgba(76, 175, 80, 0.1);
            color: var(--success);
        }

        .status-rejected {
            background-color: rgba(244, 67, 54, 0.1);
            color: var(--danger);
        }

        /* Success animation */
        .success-animation {
            text-align: center;
            padding: 30px 0;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background-color: rgba(76, 175, 80, 0.1);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 20px;
            color: var(--success);
            font-size: 40px;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(76, 175, 80, 0.4);
            }
            70% {
                box-shadow: 0 0 0 15px rgba(76, 175, 80, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(76, 175, 80, 0);
            }
        }

        .file-input-container {
            position: relative;
            margin-bottom: 20px;
        }

        .file-input-label {
            display: block;
            background-color: var(--dark-medium);
            border: 1px solid #333;
            border-radius: 5px;
            padding: 12px 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .file-input-label:hover {
            background-color: rgba(255, 222, 3, 0.1);
            border-color: var(--primary);
        }

        .file-input-label i {
            margin-right: 10px;
        }

        .file-input {
            position: absolute;
            top: 0;
            left: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-name {
            margin-top: 8px;
            font-size: 14px;
            color: var(--text-muted);
        }

        @media (max-width: 768px) {
            .payment-methods {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            }
            
            .steps {
                flex-direction: column;
                gap: 20px;
            }
            
            .step:not(:last-child)::after {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <button class="back-btn" onclick="window.location.href='dashboard.php'">
            <i class="fas fa-arrow-left"></i> <span>ব্যাক</span>
        </button>
        <div class="logo">
            <h1>MZ Income</h1>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container">
        <div class="page-title">
            <h1>ডিপোজিট</h1>
            <p>আপনার অ্যাকাউন্টে টাকা জমা করুন</p>
        </div>

        <!-- Steps -->
        <div class="steps">
            <div class="step <?php echo ($step >= 1) ? 'active' : ''; ?>">
                <div class="step-number">1</div>
                <div class="step-title">পরিমাণ নির্বাচন</div>
            </div>
            <div class="step <?php echo ($step >= 2) ? 'active' : ''; ?>">
                <div class="step-number">2</div>
                <div class="step-title">পেমেন্ট</div>
            </div>
            <div class="step <?php echo ($step >= 3) ? 'active' : ''; ?>">
                <div class="step-number">3</div>
                <div class="step-title">নিশ্চিতকরণ</div>
            </div>
        </div>

        <!-- Current Balance -->
        <div class="balance-card">
            <div class="balance-info">
                <div class="balance-icon">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="balance-details">
                    <h3>বর্তমান ব্যালেন্স</h3>
                    <div class="balance-amount">৳ <?php echo number_format($balance, 2); ?></div>
                </div>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if ($step == 1): ?>
            <!-- Step 1: Amount and Method Selection -->
            <div class="form-card">
                <h2 class="form-title">পরিমাণ এবং পেমেন্ট মেথড নির্বাচন করুন</h2>
                
                <form method="post" action="<?php echo $current_url; ?>">
                    <input type="hidden" name="step" value="1">
                    
                    <div class="form-group">
                        <label for="amount">পরিমাণ (৳)</label>
                        <input type="number" id="amount" name="amount" class="form-control" min="<?php echo $min_deposit; ?>" required>
                        <span class="form-hint">ন্যূনতম ডিপোজিট ৳ <?php echo $min_deposit; ?></span>
                    </div>

                    <div class="form-group">
                        <label>পেমেন্ট মেথড</label>
                        <div class="payment-methods">
                            <?php foreach ($payment_methods as $method): ?>
                                <div class="payment-method" data-id="<?php echo $method['id']; ?>">
                                    <?php 
                                    $logo_file = '';
                                    switch (strtolower($method['name'])) {
                                        case 'bkash':
                                            $logo_file = 'bkash_logo.png';
                                            break;
                                        case 'nagad':
                                            $logo_file = 'nagad_logo.png';
                                            break;
                                        case 'rocket':
                                            $logo_file = 'rocket_logo.png';
                                            break;
                                        default:
                                            $logo_file = 'payment_logo.png';
                                    }
                                    ?>
                                    <img src="assets/images/<?php echo $logo_file; ?>" alt="<?php echo htmlspecialchars($method['name']); ?>" class="payment-method-logo">
                                    <div class="payment-method-name"><?php echo htmlspecialchars($method['name']); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" id="payment_method" name="payment_method" required>
                    </div>

                    <button type="submit" class="btn-primary">পরবর্তী ধাপ</button>
                </form>
            </div>
        <?php elseif ($step == 2): ?>
            <!-- Step 2: Payment Information -->
            <div class="form-card">
                <?php
                $amount = $_SESSION['deposit_amount'] ?? 0;
                $payment_method_id = $_SESSION['deposit_method_id'] ?? 0;
                $payment_method_name = $_SESSION['deposit_method_name'] ?? '';
                
                // Get payment account for the selected method
                $stmt = $conn->prepare("SELECT account_number FROM payment_accounts 
                                       WHERE payment_method_id = :method_id AND is_active = 1 
                                       ORDER BY id DESC LIMIT 1");
                $stmt->bindParam(':method_id', $payment_method_id);
                $stmt->execute();
                $account = $stmt->fetch(PDO::FETCH_ASSOC);
                $payment_number = $account['account_number'] ?? '01XXXXXXXXX';
                
                $logo_file = '';
                switch (strtolower($payment_method_name)) {
                    case 'bkash':
                        $logo_file = 'bkash_logo.png';
                        break;
                    case 'nagad':
                        $logo_file = 'nagad_logo.png';
                        break;
                    case 'rocket':
                        $logo_file = 'rocket_logo.png';
                        break;
                    default:
                        $logo_file = 'payment_logo.png';
                }
                ?>
                
                <div class="payment-info-card">
                    <div class="payment-method-header">
                        <div class="payment-logo">
                            <img src="assets/images/<?php echo $logo_file; ?>" alt="<?php echo htmlspecialchars($payment_method_name); ?>">
                        </div>
                        <div class="payment-method-details">
                            <h3><?php echo htmlspecialchars($payment_method_name); ?></h3>
                            <div class="payment-amount">৳ <?php echo number_format($amount, 2); ?></div>
                        </div>
                    </div>
                    
                    <div class="payment-instruction">
                        <p><strong>অনুগ্রহ করে নিম্নলিখিত নম্বরে টাকা পাঠান:</strong></p>
                        
                        <div class="payment-number-container">
                            <div class="payment-number"><?php echo $payment_number; ?></div>
                            <button class="copy-btn" onclick="copyToClipboard('<?php echo $payment_number; ?>')">
                                <i class="fas fa-copy"></i> কপি
                            </button>
                        </div>
                        
                        <p>১। <?php echo htmlspecialchars($payment_method_name); ?> অ্যাপ ওপেন করুন</p>
                        <p>২। "Send Money" অপশন সিলেক্ট করুন</p>
                        <p>৩। উপরের নম্বরে টাকা পাঠান</p>
                        <p>৪। ট্রানজেকশন আইডি এবং সেন্ডার নম্বর সংরক্ষণ করুন</p>
                        <p>৫। নিচের ফর্মে তথ্য সাবমিট করুন</p>
                    </div>
                </div>
                
                <h2 class="form-title">পেমেন্ট কনফার্মেশন</h2>
                
                <form method="post" action="<?php echo $current_url; ?>" enctype="multipart/form-data">
                    <input type="hidden" name="step" value="2">
                    
                    <div class="form-group">
                        <label for="transaction_id">ট্রানজেকশন আইডি</label>
                        <input type="text" id="transaction_id" name="transaction_id" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="sender_number">সেন্ডার নম্বর</label>
                        <input type="text" id="sender_number" name="sender_number" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>পেমেন্ট স্ক্রিনশট</label>
                        <div class="file-input-container">
                            <label class="file-input-label">
                                <i class="fas fa-upload"></i> ফাইল আপলোড করুন
                                <input type="file" name="payment_screenshot" class="file-input" accept="image/*" onchange="updateFileName(this)">
                            </label>
                            <div class="file-name" id="file-name">কোন ফাইল নির্বাচিত হয়নি</div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-primary">পেমেন্ট সম্পন্ন করুন</button>
                </form>
            </div>
        <?php elseif ($step == 3): ?>
            <!-- Step 3: Success -->
            <div class="form-card">
                <div class="success-animation">
                    <div class="success-icon">
                        <i class="fas fa-check"></i>
                    </div>
                    <h2>ডিপোজিট অনুরোধ সম্পন্ন হয়েছে!</h2>
                    <p style="margin-top: 15px; margin-bottom: 20px;">আপনার ডিপোজিট অনুরোধ সফলভাবে জমা হয়েছে। পেমেন্ট যাচাই হলে আপনার ব্যালেন্সে টাকা যোগ করা হবে।</p>
                    <a href="dashboard.php" class="btn-primary" style="display: inline-block; width: auto; padding-left: 30px; padding-right: 30px;">ড্যাশবোর্ডে ফিরে যান</a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Recent Deposits -->
        <div class="transactions-card">
            <h2 class="form-title">সাম্প্রতিক ডিপোজিট</h2>
            
            <?php if (!empty($recent_deposits)): ?>
                <?php foreach ($recent_deposits as $deposit): ?>
                    <div class="transaction-item">
                        <div class="transaction-icon">
                            <i class="fas fa-arrow-down"></i>
                        </div>
                        <div class="transaction-info">
                            <div class="transaction-title">ডিপোজিট</div>
                            <div class="transaction-date">
                                <?php 
                                $deposit_time = strtotime($deposit['created_at']);
                                echo date('d M Y, h:i A', $deposit_time);
                                ?>
                            </div>
                            <div class="transaction-status status-<?php echo $deposit['status']; ?>">
                                <?php 
                                $status = '';
                                switch ($deposit['status']) {
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
                        <div class="transaction-amount">
                            ৳ <?php echo number_format($deposit['amount'], 2); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 20px;">
                    <p>কোন ডিপোজিট ইতিহাস নেই</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Payment method selection
        const paymentMethods = document.querySelectorAll('.payment-method');
        const paymentMethodInput = document.getElementById('payment_method');
        
        paymentMethods.forEach(method => {
            method.addEventListener('click', function() {
                // Remove active class from all methods
                paymentMethods.forEach(m => m.classList.remove('active'));
                
                // Add active class to selected method
                this.classList.add('active');
                
                // Set payment method id
                const methodId = this.getAttribute('data-id');
                paymentMethodInput.value = methodId;
            });
        });
        
        // Copy to clipboard
        function copyToClipboard(text) {
            const el = document.createElement('textarea');
            el.value = text;
            document.body.appendChild(el);
            el.select();
            document.execCommand('copy');
            document.body.removeChild(el);
            
            // Show copied notification
            const copyBtn = document.querySelector('.copy-btn');
            const originalText = copyBtn.innerHTML;
            copyBtn.innerHTML = '<i class="fas fa-check"></i> কপি হয়েছে';
            
            setTimeout(() => {
                copyBtn.innerHTML = originalText;
            }, 2000);
        }
        
        // Update file name on select
        function updateFileName(input) {
            const fileName = input.files[0] ? input.files[0].name : 'কোন ফাইল নির্বাচিত হয়নি';
            document.getElementById('file-name').textContent = fileName;
        }
    </script>
</body>
</html>