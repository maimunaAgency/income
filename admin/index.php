<?php
session_start();

// Check if admin is already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

// Database connection
$host = 'localhost';
$db_name = 'qydipzkd_Income';
$username = 'qydipzkd_Income';
$password = 'income314@';

$login_error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $admin_username = $_POST['username'] ?? '';
        $admin_password = $_POST['password'] ?? '';
        
        if (!$admin_username || !$admin_password) {
            $login_error = 'Please provide both username and password';
        } else {
            // Check admin credentials
            $stmt = $conn->prepare("SELECT id, username, password, role, full_name FROM admin_users WHERE username = :username AND status = 'active'");
            $stmt->bindParam(':username', $admin_username);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $admin = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Verify password
                if (password_verify($admin_password, $admin['password'])) {
                    // Password is correct, set session
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_username'] = $admin['username'];
                    $_SESSION['admin_role'] = $admin['role'];
                    $_SESSION['admin_name'] = $admin['full_name'];
                    
                    // Update last login time
                    $stmt = $conn->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = :id");
                    $stmt->bindParam(':id', $admin['id']);
                    $stmt->execute();
                    
                    // Log activity
                    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
                    $activity_description = "Admin login: {$admin['username']}";
                    
                    $stmt = $conn->prepare("INSERT INTO activity_logs (admin_id, activity_type, description, ip_address) VALUES (:admin_id, 'admin_login', :description, :ip)");
                    $stmt->bindParam(':admin_id', $admin['id']);
                    $stmt->bindParam(':description', $activity_description);
                    $stmt->bindParam(':ip', $ip);
                    $stmt->execute();
                    
                    // Redirect to dashboard
                    header('Location: dashboard.php');
                    exit;
                } else {
                    $login_error = 'Invalid password';
                }
            } else {
                $login_error = 'Admin not found or account is inactive';
            }
        }
        
    } catch(PDOException $e) {
        $login_error = 'Database error: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MZ Income - Admin Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #FFDE03;
            --dark: #121212;
            --dark-light: #1e1e1e;
            --text-light: #f5f5f5;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: var(--dark);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .login-container {
            background-color: var(--dark-light);
            border-radius: 10px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            padding: 30px;
            border: 1px solid #333;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h1 {
            color: var(--primary);
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .login-header p {
            color: var(--text-light);
            font-size: 14px;
            opacity: 0.7;
        }
        
        .login-form .input-group {
            margin-bottom: 20px;
        }
        
        .login-form label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-light);
            font-weight: 500;
        }
        
        .login-form input {
            width: 100%;
            padding: 12px 15px;
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid #333;
            border-radius: 5px;
            color: var(--text-light);
            font-size: 14px;
        }
        
        .login-form input:focus {
            outline: none;
            border-color: var(--primary);
            background-color: rgba(255, 255, 255, 0.15);
        }
        
        .login-form button {
            width: 100%;
            background-color: var(--primary);
            color: var(--dark);
            border: none;
            border-radius: 5px;
            padding: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .login-form button:hover {
            background-color: #FFE838;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 222, 3, 0.3);
        }
        
        .error-message {
            background-color: rgba(244, 67, 54, 0.2);
            color: #ff6b6b;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .back-to-site {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: var(--primary);
            text-decoration: none;
            font-size: 14px;
        }
        
        .back-to-site:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>MZ Income Admin</h1>
            <p>Enter your credentials to access the dashboard</p>
        </div>
        
        <?php if ($login_error): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($login_error); ?>
            </div>
        <?php endif; ?>
        
        <form class="login-form" method="post" action="">
            <div class="input-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit">Login</button>
        </form>
        
        <a href="../index.php" class="back-to-site">Back to Main Site</a>
    </div>
</body>
</html>