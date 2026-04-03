<?php
/**
 * User Profile Page
 * Allows logged-in users to view and edit their profile
 */

require_once 'config/config.php';
require_once 'config/db.php';
require_once 'includes/auth.php';

requireLogin();

$user = getCurrentUser();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');

    if (empty($full_name)) {
        $error = 'Full name is required.';
    } else {
        // Update user profile
        $stmt = $mysqli->prepare("UPDATE users SET full_name = ? WHERE id = ?");
        $stmt->bind_param("si", $full_name, $_SESSION['user_id']);

        if ($stmt->execute()) {
            $_SESSION['full_name'] = $full_name;
            $user['full_name'] = $full_name;
            $success = 'Profile updated successfully!';
        } else {
            $error = 'Error updating profile. Please try again.';
        }
        $stmt->close();
    }
}

$page_title = 'My Profile';
include 'includes/header.php';
include 'includes/nav.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">My Profile</h4>
                </div>
                <div class="card-body">
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

                    <div class="mb-3">
                        <label class="form-label fw-bold">Email:</label>
                        <p class="form-control-plaintext"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Role:</label>
                        <p class="form-control-plaintext">
                            <span class="badge bg-info"><?php echo ucfirst($user['role']); ?></span>
                        </p>
                    </div>

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

                        <button type="submit" class="btn btn-primary">Update Profile</button>
                        <a href="javascript:history.back()" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
