<?php
/**
 * Create Announcement
 * Only Admin and Teacher can create
 */

require_once 'config/config.php';
require_once 'config/db.php';
require_once 'includes/auth.php';

requireRoles([ROLE_ADMIN, ROLE_TEACHER]);

$error = '';
$success = '';

$user_id = $_SESSION['user_id'];

// Get classes if teacher
$classes = [];
if (isTeacher()) {
    $classes_result = $mysqli->query(
        "SELECT id, class_name FROM classes WHERE teacher_id = {$user_id} ORDER BY class_name"
    );
    $classes = $classes_result->fetch_all(MYSQLI_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $class_id = intval($_POST['class_id'] ?? 0);

    if (empty($title) || empty($message)) {
        $error = 'Title and message are required.';
    } else {
        $class_id_insert = $class_id > 0 ? $class_id : null;

        $stmt = $mysqli->prepare("
            INSERT INTO announcements (title, message, class_id, posted_by) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("ssii", $title, $message, $class_id_insert, $user_id);

        if ($stmt->execute()) {
            $success = 'Announcement posted successfully!';
            $_POST = [];
        } else {
            $error = 'Error creating announcement. Please try again.';
        }
        $stmt->close();
    }
}

$page_title = 'Create Announcement';
include 'includes/header.php';
include 'includes/nav.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h2 class="mb-3">Create Announcement</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo htmlspecialchars($success); ?>
                    <a href="announcements_list.php" class="alert-link">View all announcements</a>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="title" 
                                name="title" 
                                value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea 
                                class="form-control" 
                                id="message" 
                                name="message" 
                                rows="6"
                                required
                            ><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                        </div>

                        <?php if (isTeacher()): ?>
                            <div class="mb-3">
                                <label for="class_id" class="form-label">Broadcast to Class (Optional)</label>
                                <select class="form-control" id="class_id" name="class_id">
                                    <option value="">All Users</option>
                                    <?php foreach ($classes as $class): ?>
                                        <option value="<?php echo $class['id']; ?>" <?php echo (intval($_POST['class_id'] ?? 0) === $class['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($class['class_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Post Announcement</button>
                            <a href="announcements_list.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
