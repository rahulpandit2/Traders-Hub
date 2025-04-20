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

// Pagination settings
$per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

// Sorting parameters
$valid_columns = ['file_name', 'start_date', 'upload_time', 'file_type', 'id'];
$sort_column = isset($_GET['sort']) && in_array($_GET['sort'], $valid_columns) ? $_GET['sort'] : 'upload_time';
$sort_order = isset($_GET['order']) && strtolower($_GET['order']) === 'asc' ? 'ASC' : 'DESC';

// Handle file editing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_file'])) {
    $file_id = $_POST['file_id'];
    $new_name = $_POST['new_name'];
    $new_start_date = $_POST['new_start_date'];
    
    try {
        $stmt = $pdo->prepare("UPDATE files SET file_name = ?, start_date = ? WHERE id = ?");
        $stmt->execute([$new_name, $new_start_date, $file_id]);
        
        // Log the edit
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action_type, description) VALUES (?, 'file_update', ?)");
        $stmt->execute([$_SESSION['admin_id'], "Updated file details for ID: " . $file_id]);
        
        $message = 'File details updated successfully';
    } catch (PDOException $e) {
        $error = 'Error updating file: ' . $e->getMessage();
    }
}

// Handle file deletion (admin only)
if (isset($_POST['delete_file']) && $user['user_type'] === 'admin') {
    $fileId = $_POST['file_id'];
    try {
        // Get file info first
        $stmt = $pdo->prepare("SELECT file_path, file_name FROM files WHERE id = ?");
        $stmt->execute([$fileId]);
        $file = $stmt->fetch();
        
        if ($file) {
            // Delete from database
            $stmt = $pdo->prepare("DELETE FROM files WHERE id = ?");
            $stmt->execute([$fileId]);
            
            // Delete physical file
            $filePath = '../' . $file['file_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            // Log the deletion
            $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action_type, description) VALUES (?, 'file_delete', ?)");
            $stmt->execute([$_SESSION['admin_id'], "Deleted file: " . $file['file_name']]);
            
            $message = 'File deleted successfully';
        }
    } catch (PDOException $e) {
        $error = 'Error deleting file: ' . $e->getMessage();
    }
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $startDate = $_POST['start_date'] ?? '';
    $customFileName = $_POST['file_name'] ?? '';
    
    if (empty($startDate)) {
        $error = 'Start date is required';
    } elseif (!isset($_FILES['file'])) {
        $error = 'Please select a file';
    } else {
        $file = $_FILES['file'];
        $fileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // Validate file type
        $allowedTypes = ['pdf', 'xlsx', 'xls'];
        if (!in_array($fileType, $allowedTypes)) {
            $error = 'Only PDF and Excel files are allowed';
        } else {
            // Create upload directory if it doesn't exist
            $uploadDir = '../uploads/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            // Generate unique filename
            $fileName = time() . '_' . ($customFileName ?: $file['name']);
            $filePath = $uploadDir . $fileName;
            
            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO files (file_name, original_name, start_date, file_type, file_size, file_path) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $fileName,
                        $file['name'],
                        $startDate,
                        $fileType,
                        $file['size'],
                        'uploads/' . $fileName
                    ]);

                    // Log the upload
                    $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action_type, description) VALUES (?, 'file_upload', ?)");
                    $stmt->execute([$_SESSION['admin_id'], "Uploaded file: " . $fileName]);
                    
                    $message = 'File uploaded successfully';
                } catch (PDOException $e) {
                    $error = 'Database error: ' . $e->getMessage();
                    unlink($filePath); // Remove uploaded file if database insert fails
                }
            } else {
                $error = 'Error uploading file';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Traders Hub</title>
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
                        <a class="nav-link active" href="index.php">Files</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contacts.php">Contacts</a>
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
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($user['user_type'] === 'admin'): ?>
        <div class="alert alert-info mb-4">
            <strong>Admin Access:</strong> You have full permissions to upload and delete files.
        </div>
        <?php else: ?>
        <div class="alert alert-info mb-4">
            <strong>Subadmin Access:</strong> You can upload/edit files but cannot delete them.
        </div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Upload New File</h5>
                <form id="uploadForm" class="row g-3">
                    <div class="col-md-6">
                        <label for="file" class="form-label">File</label>
                        <input type="file" class="form-control" id="file" name="file" accept=".pdf,.xlsx,.xls" required>
                        <div class="form-text">Allowed file types: PDF, Excel (xlsx, xls)</div>
                    </div>
                    <div class="col-md-6">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" required>
                    </div>
                    <div class="col-12">
                        <label for="file_name" class="form-label">Custom File Name (Optional)</label>
                        <input type="text" class="form-control" id="file_name" name="file_name" placeholder="Leave blank to use original filename">
                    </div>
                    <div class="col-12" id="progressDiv" style="display: none;">
                        <div class="progress mb-3">
                            <div id="uploadProgress" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                        </div>
                        <button type="button" id="cancelUpload" class="btn btn-danger mb-3">Cancel Upload</button>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Upload File</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Manage Files</h5>
                <div class="mb-3">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <label class="me-2">Show entries:</label>
                            <select class="form-select form-select-sm d-inline-block w-auto" onchange="window.location.href='?per_page=' + this.value + '&sort=<?php echo $sort_column; ?>&order=<?php echo $sort_order; ?>'">
                                <?php foreach ([10, 20, 50, 100] as $size): ?>
                                    <option value="<?php echo $size; ?>" <?php echo $per_page == $size ? 'selected' : ''; ?>><?php echo $size; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <?php
                                $columns = [
                                    'file_name' => 'File Name',
                                    'start_date' => 'Start Date',
                                    'upload_time' => 'Upload Date'
                                ];
                                foreach ($columns as $col => $label): 
                                    $order = ($sort_column === $col && $sort_order === 'ASC') ? 'desc' : 'asc';
                                    $arrow = ($sort_column === $col) ? ($sort_order === 'ASC' ? '↑' : '↓') : '';
                                ?>
                                <th>
                                    <a href="?per_page=<?php echo $per_page; ?>&sort=<?php echo $col; ?>&order=<?php echo $order; ?>" class="text-dark text-decoration-none">
                                        <?php echo $label; ?> <?php echo $arrow; ?>
                                    </a>
                                </th>
                                <?php endforeach; ?>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                // Get total count of files
                                $count_stmt = $pdo->query("SELECT COUNT(*) FROM files");
                                $total_files = $count_stmt->fetchColumn();
                                $total_pages = ceil($total_files / $per_page);

                                // Fetch files with pagination and sorting
                                $stmt = $pdo->prepare("SELECT * FROM files ORDER BY $sort_column $sort_order LIMIT :limit OFFSET :offset");
                                $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
                                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                                $stmt->execute();
                                while ($file = $stmt->fetch()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($file['original_name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($file['start_date']) . "</td>";
                                    echo "<td>" . htmlspecialchars($file['upload_time']) . "</td>";
                                    echo "<td>";
                                    echo "<button type='button' class='btn btn-primary btn-sm me-2' data-bs-toggle='modal' data-bs-target='#editModal" . $file['id'] . "'>Edit</button>";
                                    if ($user['user_type'] === 'admin') {
                                        echo "<form method='POST' style='display:inline;' onsubmit='return confirm(\"Are you sure you want to delete this file?\");'>";
                                        echo "<input type='hidden' name='file_id' value='" . $file['id'] . "'>";
                                        echo "<button type='submit' name='delete_file' class='btn btn-danger btn-sm'>Delete</button>";
                                        echo "</form>";
                                    }
                                    echo "<div class='modal fade' id='editModal" . $file['id'] . "' tabindex='-1'>";
                                    echo "<div class='modal-dialog'><div class='modal-content'>";
                                    echo "<div class='modal-header'><h5 class='modal-title'>Edit File Details</h5>";
                                    echo "<button type='button' class='btn-close' data-bs-dismiss='modal'></button></div>";
                                    echo "<form method='post'><div class='modal-body'>";
                                    echo "<input type='hidden' name='file_id' value='" . $file['id'] . "'>";
                                    echo "<div class='mb-3'><label class='form-label'>File Name</label>";
                                    echo "<input type='text' class='form-control' name='new_name' value='" . htmlspecialchars($file['file_name']) . "' required></div>";
                                    echo "<div class='mb-3'><label class='form-label'>Start Date</label>";
                                    echo "<input type='date' class='form-control' name='new_start_date' value='" . $file['start_date'] . "' required></div>";
                                    echo "</div><div class='modal-footer'>";
                                    echo "<button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>";
                                    echo "<button type='submit' name='edit_file' class='btn btn-primary'>Save changes</button>";
                                    echo "</div></form></div></div></div>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } catch (PDOException $e) {
                                echo "<tr><td colspan='4' class='text-danger'>Error loading files</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&per_page=<?php echo $per_page; ?>&sort=<?php echo $sort_column; ?>&order=<?php echo $sort_order; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/upload.js"></script>
</body>
</html>