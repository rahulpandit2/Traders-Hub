<?php
session_start();
require_once '../db_config.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Get user type
$stmt = $pdo->prepare("SELECT user_type FROM admin_users WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$user = $stmt->fetch();

$message = '';
$error = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_status'])) {
    $contact_id = $_POST['contact_id'];
    $new_status = $_POST['status'] === 'pending' ? 'replied' : 'pending';
    
    try {
        $stmt = $pdo->prepare("UPDATE contacts SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $contact_id]);
        
        // Log the status change
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action_type, description) VALUES (?, 'contact_status', ?)");
        $stmt->execute([$_SESSION['admin_id'], "Updated contact status to: " . $new_status . " for contact ID: " . $contact_id]);
        
        $message = 'Status updated successfully';
    } catch (PDOException $e) {
        $error = 'Error updating status: ' . $e->getMessage();
    }
}

// Pagination settings
$per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

// Filtering and sorting parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
$sort_order = isset($_GET['order']) && strtolower($_GET['order']) === 'asc' ? 'ASC' : 'DESC';

// Get total count with filters
$count_query = "SELECT COUNT(*) FROM contacts WHERE 1=1";
$count_params = [];

// Add filters to count query
if ($status_filter !== '') {
    $count_query .= " AND status = ?";
    $count_params[] = $status_filter;
}
if ($date_filter !== '') {
    $count_query .= " AND DATE(created_at) = ?";
    $count_params[] = $date_filter;
}

// Execute count query
$stmt = $pdo->prepare($count_query);
$stmt->execute($count_params);
$total_contacts = $stmt->fetchColumn();
$total_pages = ceil($total_contacts / $per_page);

// Build the main query
$query = "SELECT * FROM contacts WHERE 1=1";
$params = [];

// Add filters
if ($status_filter !== '') {
    $query .= " AND status = ?";
    $params[] = $status_filter;
}
if ($date_filter !== '') {
    $query .= " AND DATE(created_at) = ?";
    $params[] = $date_filter;
}

// Add sorting
$valid_sort_columns = ['created_at', 'subject', 'email', 'name', 'status', 'id'];
$sort_by = in_array($sort_by, $valid_sort_columns) ? $sort_by : 'created_at';
$query .= " ORDER BY $sort_by $sort_order LIMIT :limit OFFSET :offset";

// Execute query
$stmt = $pdo->prepare($query);

// Bind all previous parameters
foreach ($params as $key => $value) {
    $stmt->bindValue($key + 1, $value);
}

// Bind pagination parameters
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$contacts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Requests - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
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
                        <a class="nav-link active" href="contacts.php">Contacts</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">Profile</a>
                    </li>
                    <?php if ($user['user_type'] === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="logs.php">Logs</a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3">Filter Contacts</h5>
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All</option>
                            <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="replied" <?php echo $status_filter === 'replied' ? 'selected' : ''; ?>>Replied</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" class="form-control" value="<?php echo $date_filter; ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Sort By</label>
                        <select name="sort" class="form-select">
                            <option value="created_at" <?php echo $sort_by === 'created_at' ? 'selected' : ''; ?>>Date Received</option>
                            <option value="subject" <?php echo $sort_by === 'subject' ? 'selected' : ''; ?>>Subject</option>
                            <option value="email" <?php echo $sort_by === 'email' ? 'selected' : ''; ?>>Email</option>
                            <option value="name" <?php echo $sort_by === 'name' ? 'selected' : ''; ?>>Name</option>
                            <option value="status" <?php echo $sort_by === 'status' ? 'selected' : ''; ?>>Status</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Order</label>
                        <select name="order" class="form-select">
                            <option value="desc" <?php echo $sort_order === 'DESC' ? 'selected' : ''; ?>>Descending</option>
                            <option value="asc" <?php echo $sort_order === 'ASC' ? 'selected' : ''; ?>>Ascending</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Show per page</label>
                        <select name="per_page" class="form-select">
                            <option value="10" <?php echo $per_page === 10 ? 'selected' : ''; ?>>10</option>
                            <option value="25" <?php echo $per_page === 25 ? 'selected' : ''; ?>>25</option>
                            <option value="50" <?php echo $per_page === 50 ? 'selected' : ''; ?>>50</option>
                            <option value="100" <?php echo $per_page === 100 ? 'selected' : ''; ?>>100</option>
                        </select>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">Apply</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="container">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Contact Requests</h5>
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contacts as $contact): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($contact['id']); ?></td>
                        <td><?php echo htmlspecialchars($contact['name']); ?></td>
                        <td><?php echo htmlspecialchars($contact['email']); ?></td>
                        <td><?php echo htmlspecialchars($contact['subject']); ?></td>
                        <td><?php echo htmlspecialchars($contact['message']); ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($contact['created_at'])); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $contact['status'] === 'replied' ? 'success' : 'warning'; ?>">
                                <?php echo ucfirst($contact['status'] ?? 'pending'); ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="contact_id" value="<?php echo $contact['id']; ?>">
                                <input type="hidden" name="status" value="<?php echo $contact['status'] ?? 'pending'; ?>">
                                <button type="submit" name="toggle_status" class="btn btn-sm btn-<?php echo $contact['status'] === 'replied' ? 'warning' : 'success'; ?>">
                                    Mark as <?php echo $contact['status'] === 'replied' ? 'Pending' : 'Replied'; ?>
                                </button>
                            </form>
                        </td>
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
                        <a class="page-link" href="?page=<?php echo ($page - 1); ?>&per_page=<?php echo $per_page; ?>&status=<?php echo $status_filter; ?>&date=<?php echo $date_filter; ?>&sort=<?php echo $sort_by; ?>&order=<?php echo $sort_order; ?>">&laquo;</a>
                    </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $page === $i ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&per_page=<?php echo $per_page; ?>&status=<?php echo $status_filter; ?>&date=<?php echo $date_filter; ?>&sort=<?php echo $sort_by; ?>&order=<?php echo $sort_order; ?>"><?php echo $i; ?></a>
                    </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo ($page + 1); ?>&per_page=<?php echo $per_page; ?>&status=<?php echo $status_filter; ?>&date=<?php echo $date_filter; ?>&sort=<?php echo $sort_by; ?>&order=<?php echo $sort_order; ?>">&raquo;</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>