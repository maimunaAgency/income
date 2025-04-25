<?php
// login.php
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
    $user_input = $_POST['user_input'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!$user_input || !$password) {
        echo json_encode([
            'success' => false,
            'message' => 'Please provide both username/account number and password'
        ]);
        exit;
    }
    
    // Check if input is account number (6 digits) or username
    $is_account_number = (strlen($user_input) === 6 && is_numeric($user_input));
    
    if ($is_account_number) {
        // Login with account number
        $stmt = $conn->prepare("SELECT id, username, password, account_number FROM users WHERE account_number = :account_number");
        $stmt->bindParam(':account_number', $user_input);
    } else {
        // Login with username
        $stmt = $conn->prepare("SELECT id, username, password, account_number FROM users WHERE username = :username");
        $stmt->bindParam(':username', $user_input);
    }
    
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Password is correct, start a session
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['account_number'] = $user['account_number'];
            
            echo json_encode([
                'success' => true,
                'message' => 'Login successful'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid password'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => $is_account_number ? 'Account number not found' : 'Username not found'
        ]);
    }
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>