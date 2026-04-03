<?php
/**
 * Admin Dashboard
 * Overview of system statistics and management options
 */

require_once '../config/config.php';
require_once '../config/db.php';
require_once '../includes/auth.php';

requireRole(ROLE_ADMIN);

// Get statistics
$stats = [];

// Total users
$result = $mysqli->query("SELECT COUNT(*) as count FROM users");
$stats['total_users'] = $result->fetch_assoc()['count'];

// Users by role
$result = $mysqli->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
while ($row = $result->fetch_assoc()) {
    $stats[$row['role'] . '_count'] = $row['count'];
}

// Total classes
$result = $mysqli->query("SELECT COUNT(*) as count FROM classes");
$stats['total_classes'] = $result->fetch_assoc()['count'];

// Total students
$result = $mysqli->query("SELECT COUNT(*) as count FROM students");
$stats['total_students'] = $result->fetch_assoc()['count'];

// Total activities
$result = $mysqli->query("SELECT COUNT(*) as count FROM activities");
$stats['total_activities'] = $result->fetch_assoc()['count'];

// Total announcements
$result = $mysqli->query("SELECT COUNT(*) as count FROM announcements");
$stats['total_announcements'] = $result->fetch_assoc()['count'];

$page_title = 'Admin Dashboard';
include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Admin Dashboard</h2>
            <p class="text-muted">Welcome to the administration panel</p>
        </div>
    </div>

    <!-- Statistics Row -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Total Users</h5>
                    <h2 class="card-text"><?php echo $stats['total_users'] ?? 0; ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Total Classes</h5>
                    <h2 class="card-text"><?php echo $stats['total_classes'] ?? 0; ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">Total Students</h5>
                    <h2 class="card-text"><?php echo $stats['total_students'] ?? 0; ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">Total Activities</h5>
                    <h2 class="card-text"><?php echo $stats['total_activities'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- User Breakdown -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Users by Role</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2"><strong>Admins:</strong> <?php echo $stats['admin_count'] ?? 0; ?></li>
                        <li class="mb-2"><strong>Teachers:</strong> <?php echo $stats['teacher_count'] ?? 0; ?></li>
                        <li class="mb-2"><strong>Students:</strong> <?php echo $stats['student_count'] ?? 0; ?></li>
                        <li><strong>Parents:</strong> <?php echo $stats['parent_count'] ?? 0; ?></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Other Statistics</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2"><strong>Announcements:</strong> <?php echo $stats['total_announcements'] ?? 0; ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <a href="users_list.php" class="btn btn-primary me-2">Manage Users</a>
                    <a href="classes_list.php" class="btn btn-success me-2">Manage Classes</a>
                    <a href="students_list.php" class="btn btn-info me-2">Manage Students</a>
                    <a href="../announcements_list.php" class="btn btn-warning">View Announcements</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
