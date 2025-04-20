<?php
session_start();
require_once '../db_config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Get current user info
$stmt = $pdo->prepare("SELECT user_type FROM admin_users WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$user = $stmt->fetch();

// Only admin can view logs
if ($user['user_type'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : (isset($_COOKIE['logs_per_page']) ? (int)$_COOKIE['logs_per_page'] : 20);
$offset = ($page - 1) * $per_page;

// Get total count
$stmt = $pdo->query("SELECT COUNT(*) FROM activity_logs");
$total_logs = $stmt->fetchColumn();
$total_pages = ceil($total_logs / $per_page);

// Get logs with user info
$stmt = $pdo->prepare("
    SELECT l.*, u.username 
    FROM activity_logs l 
    JOIN admin_users u ON l.user_id = u.id 
    ORDER BY l.created_at DESC 
    LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$logs = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Logs - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="js/cookie-settings.js"></script>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Admin Panel</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Files</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contacts.php">Contacts</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="logs.php">Logs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Activity Logs</h5>
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Show per page</label>
                        <select name="per_page" class="form-select">
                            <option value="10" <?php echo $per_page === 10 ? 'selected' : ''; ?>>10</option>
                            <option value="20" <?php echo $per_page === 20 ? 'selected' : ''; ?>>20</option>
                            <option value="50" <?php echo $per_page === 50 ? 'selected' : ''; ?>>50</option>
                            <option value="100" <?php echo $per_page === 100 ? 'selected' : ''; ?>>100</option>
                        </select>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary" onclick="saveLogSettings()">Apply</button>
                    </div>
                </form>
                <script>
                    function saveLogSettings() {
                        const perPage = document.querySelector('select[name="per_page"]').value;
                        CookieSettings.setCookie('logs_per_page', perPage);
                    }

                    // Set initial values from cookies
                    window.onload = function() {
                        const perPage = CookieSettings.getCookie('logs_per_page');
                        if (perPage) document.querySelector('select[name="per_page"]').value = perPage;
                    };
                </script>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date/Time</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(date('Y-m-d H:i:s', strtotime($log['created_at']))); ?></td>
                                <td><?php echo htmlspecialchars($log['username']); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo match($log['action_type']) {
                                            'file_upload' => 'success',
                                            'file_delete' => 'danger',
                                            'contact_status' => 'info',
                                            'user_update' => 'warning',
                                            default => 'secondary'
                                        };
                                    ?>">
                                        <?php echo htmlspecialchars(str_replace('_', ' ', $log['action_type'])); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($log['description']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <a href="index.php" class="btn btn-primary">Back to Dashboard</a>
                    <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination mb-0">
                            <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo ($page - 1); ?>&per_page=<?php echo $per_page; ?>">&laquo;</a>
                            </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $page === $i ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&per_page=<?php echo $per_page; ?>"><?php echo $i; ?></a>
                            </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo ($page + 1); ?>&per_page=<?php echo $per_page; ?>">&raquo;</a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>