<?php
/**
 * Admin - Create User
 */

require_once '../config/config.php';
require_once '../config/db.php';
require_once '../includes/auth.php';

requireRole(ROLE_ADMIN);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? '';

    // Validation
    if (empty($full_name) || empty($email) || empty($password) || empty($role)) {
        $error = 'All fields are required.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif (!in_array($role, [ROLE_ADMIN, ROLE_TEACHER, ROLE_STUDENT, ROLE_PARENT])) {
        $error = 'Invalid role selected.';
    } else {
        // Check if email already exists
        $check_stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();

        if ($check_stmt->get_result()->num_rows > 0) {
            $error = 'Email already exists.';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Insert user
            $insert_stmt = $mysqli->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)");
            $insert_stmt->bind_param("ssss", $full_name, $email, $hashed_password, $role);

            if ($insert_stmt->execute()) {
                $success = 'User created successfully!';
                // Clear form
                $_POST = [];
            } else {
                $error = 'Error creating user. Please try again.';
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
    }
}

$page_title = 'Create User';
include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2 class="mb-3">Create New User</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo htmlspecialchars($success); ?>
                    <a href="users_list.php" class="alert-link">View all users</a>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="full_name" 
                                name="full_name" 
                                value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input 
                                type="email" 
                                class="form-control" 
                                id="email" 
                                name="email" 
                                value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                required
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
                            <small class="text-muted">Minimum 6 characters</small>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input 
                                type="password" 
                                class="form-control" 
                                id="confirm_password" 
                                name="confirm_password" 
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-control" id="role" name="role" required>
                                <option value="">Select a role</option>
                                <option value="<?php echo ROLE_ADMIN; ?>">Admin</option>
                                <option value="<?php echo ROLE_TEACHER; ?>">Teacher</option>
                                <option value="<?php echo ROLE_STUDENT; ?>">Student</option>
                                <option value="<?php echo ROLE_PARENT; ?>">Parent</option>
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Create User</button>
                            <a href="users_list.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
