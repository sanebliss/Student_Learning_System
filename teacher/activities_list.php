<?php
/**
 * Teacher - Activities List
 */

require_once '../config/config.php';
require_once '../config/db.php';
require_once '../includes/auth.php';

requireRole(ROLE_TEACHER);

$teacher_id = $_SESSION['user_id'];
$page = intval($_GET['page'] ?? 1);
if ($page < 1) $page = 1;

$offset = ($page - 1) * RECORDS_PER_PAGE;

// Get total count
$count_result = $mysqli->query(
    "SELECT COUNT(*) as total FROM activities WHERE created_by = {$teacher_id}"
);
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / RECORDS_PER_PAGE);

// Get activities
$stmt = $mysqli->prepare("
    SELECT a.id, a.title, a.activity_type, a.created_at 
    FROM activities a 
    WHERE a.created_by = ? 
    ORDER BY a.created_at DESC 
    LIMIT ?, ?
");
$records_per_page = RECORDS_PER_PAGE;
$stmt->bind_param("iii", $teacher_id, $offset, $records_per_page);
$stmt->execute();
$activities = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$page_title = 'My Activities';
include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="container-fluid mt-4">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2>My Activities</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="activity_create.php" class="btn btn-primary">Create Activity</a>
        </div>
    </div>

    <!-- Activities Table -->
    <div class="card">
        <div class="card-body table-responsive">
            <?php if (count($activities) > 0): ?>
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($activities as $activity): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($activity['title']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $activity['activity_type'] === ACTIVITY_TYPE_QUIZ ? 'info' : 'secondary'; ?>">
                                        <?php echo ucfirst($activity['activity_type']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($activity['created_at'])); ?></td>
                                <td>
                                    <a href="activity_edit.php?id=<?php echo $activity['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <?php if ($activity['activity_type'] === ACTIVITY_TYPE_QUIZ): ?>
                                        <a href="quiz_questions.php?activity_id=<?php echo $activity['id']; ?>" class="btn btn-sm btn-success">Manage Questions</a>
                                    <?php endif; ?>
                                    <a href="activity_delete.php?id=<?php echo $activity['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted">No activities created yet. <a href="activity_create.php">Create one now</a>.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mt-3">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=1">First</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                    </li>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $total_pages; ?>">Last</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
