<?php
/**
 * Admin - Edit User
 */

require_once '../config/config.php';
require_once '../config/db.php';
require_once '../includes/auth.php';

requireRole(ROLE_ADMIN);

$user_id = intval($_GET['id'] ?? 0);
$user = null;
$error = '';
$success = '';

// Get user
if ($user_id > 0) {
    $stmt = $mysqli->prepare("SELECT id, full_name, email, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        $error = 'User not found.';
    }
    $stmt->close();
}

if (!$user) {
    http_response_code(404);
    die('User not found.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $role = $_POST['role'] ?? '';

    if (empty($full_name) || empty($role)) {
        $error = 'All fields are required.';
    } elseif (!in_array($role, [ROLE_ADMIN, ROLE_TEACHER, ROLE_STUDENT, ROLE_PARENT])) {
        $error = 'Invalid role selected.';
    } else {
        // Update user
        $update_stmt = $mysqli->prepare("UPDATE users SET full_name = ?, role = ? WHERE id = ?");
        $update_stmt->bind_param("ssi", $full_name, $role, $user_id);

        if ($update_stmt->execute()) {
            $user['full_name'] = $full_name;
            $user['role'] = $role;
            $success = 'User updated successfully!';
        } else {
            $error = 'Error updating user. Please try again.';
        }
        $update_stmt->close();
    }
}

$page_title = 'Edit User';
include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h2 class="mb-3">Edit User</h2>

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
                                value="<?php echo htmlspecialchars($user['full_name']); ?>"
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
                                value="<?php echo htmlspecialchars($user['email']); ?>"
                                disabled
                            >
                            <small class="text-muted">Email cannot be changed</small>
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-control" id="role" name="role" required>
                                <option value="<?php echo ROLE_ADMIN; ?>" <?php echo $user['role'] === ROLE_ADMIN ? 'selected' : ''; ?>>Admin</option>
                                <option value="<?php echo ROLE_TEACHER; ?>" <?php echo $user['role'] === ROLE_TEACHER ? 'selected' : ''; ?>>Teacher</option>
                                <option value="<?php echo ROLE_STUDENT; ?>" <?php echo $user['role'] === ROLE_STUDENT ? 'selected' : ''; ?>>Student</option>
                                <option value="<?php echo ROLE_PARENT; ?>" <?php echo $user['role'] === ROLE_PARENT ? 'selected' : ''; ?>>Parent</option>
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Update User</button>
                            <a href="users_list.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
