<?php
/**
 * Announcements List
 * Visible to all roles
 */

require_once 'config/config.php';
require_once 'config/db.php';
require_once 'includes/auth.php';

requireLogin();

$page = intval($_GET['page'] ?? 1);
if ($page < 1) $page = 1;

$offset = ($page - 1) * RECORDS_PER_PAGE;

// Get total announcements
$count_result = $mysqli->query("SELECT COUNT(*) as total FROM announcements");
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / RECORDS_PER_PAGE);

// Get announcements
$stmt = $mysqli->prepare("
    SELECT a.id, a.title, a.message, a.posted_by, a.posted_at, u.full_name
    FROM announcements a
    JOIN users u ON a.posted_by = u.id
    ORDER BY a.posted_at DESC
    LIMIT ?, ?
");
$records_per_page = RECORDS_PER_PAGE;
$stmt->bind_param("ii", $offset, $records_per_page);
$stmt->execute();
$announcements = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$page_title = 'Announcements';
include 'includes/header.php';
include 'includes/nav.php';
?>

<div class="container-fluid mt-4">
    <div class="row mb-3">
        <div class="col-md-8">
            <h2>Announcements</h2>
        </div>
        <div class="col-md-4 text-end">
            <?php if (isAdmin() || isTeacher()): ?>
                <a href="announcement_create.php" class="btn btn-primary">Create Announcement</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Announcements -->
    <div class="row">
        <div class="col-md-8">
            <?php if (count($announcements) > 0): ?>
                <?php foreach ($announcements as $announcement): ?>
                    <div class="card mb-3">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5 class="mb-1"><?php echo htmlspecialchars($announcement['title']); ?></h5>
                                    <small class="text-muted">
                                        Posted by <?php echo htmlspecialchars($announcement['full_name']); ?> 
                                        on <?php echo date('M d, Y \a\t g:i A', strtotime($announcement['posted_at'])); ?>
                                    </small>
                                </div>
                                <?php if ((isAdmin() || isTeacher()) && (isAdmin() || $_SESSION['user_id'] == $announcement['posted_by'])): ?>
                                    <div>
                                        <a href="announcement_edit.php?id=<?php echo $announcement['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                        <a href="announcement_delete.php?id=<?php echo $announcement['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete?');">Delete</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <p class="card-text"><?php echo nl2br(htmlspecialchars($announcement['message'])); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-info" role="alert">
                    No announcements yet.
                </div>
            <?php endif; ?>
        </div>

        <div class="col-md-4">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-header">
                    <h5 class="mb-0">Announcements Info</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        All announcements are visible to everyone in the system. Teachers and admins can post announcements.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
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

<?php include 'includes/footer.php'; ?>
