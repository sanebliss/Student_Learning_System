<?php
/**
 * Login Page
 * Single login for all roles with role-based redirection
 */

require_once 'config/config.php';
require_once 'config/db.php';
require_once 'includes/auth.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    switch ($_SESSION['role']) {
        case ROLE_ADMIN:
            header('Location: admin/dashboard.php');
            exit();
        case ROLE_TEACHER:
            header('Location: teacher/dashboard.php');
            exit();
        case ROLE_STUDENT:
            header('Location: student/dashboard.php');
            exit();
        case ROLE_PARENT:
            header('Location: parent/dashboard.php');
            exit();
    }
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email and password are required.';
    } else {
        // Get user from database
        $stmt = $mysqli->prepare("SELECT id, email, password, full_name, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
        
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                
                // Determine redirect URL based on role
                $redirect_url = '';
                switch ($user['role']) {
                    case ROLE_ADMIN:
                        $redirect_url = SITE_URL . 'admin/dashboard.php';
                        break;
                    case ROLE_TEACHER:
                        $redirect_url = SITE_URL . 'teacher/dashboard.php';
                        break;
                    case ROLE_STUDENT:
                        $redirect_url = SITE_URL . 'student/dashboard.php';
                        break;
                    case ROLE_PARENT:
                        $redirect_url = SITE_URL . 'parent/dashboard.php';
                        break;
                }
                
                // Redirect with absolute URL
                header('Location: ' . $redirect_url);
                exit();
            } else {
                $error = 'Invalid email or password.';
            }
        } else {
            $error = 'Invalid email or password.';
        }
        $stmt->close();
    }
}

$page_title = 'Login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            max-width: 400px;
            width: 100%;
            padding: 40px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 10px;
        }
        .login-header p {
            color: #666;
            font-size: 14px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 10px;
            font-weight: 600;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }
        .alert {
            margin-bottom: 20px;
        }
        .demo-info {
            background: #f0f0f0;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin-top: 20px;
            font-size: 13px;
            border-radius: 4px;
        }
        .demo-info strong {
            display: block;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1><?php echo APP_NAME; ?></h1>
            <p>Sign In to Your Account</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success" role="alert">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input 
                    type="email" 
                    class="form-control" 
                    id="email" 
                    name="email" 
                    required
                    autofocus
                >
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input 
                    type="password" 
                    class="form-control" 
                    id="password" 
                    name="password" 
                    required
                >
            </div>

            <button type="submit" class="btn btn-login btn-primary w-100">Login</button>
        </form>

        <div class="demo-info">
            <strong>Demo Credentials:</strong>
            Admin: admin@example.com / password<br>
            Teacher: teacher@example.com / password<br>
            Student: student@example.com / password<br>
            Parent: parent@example.com / password
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
