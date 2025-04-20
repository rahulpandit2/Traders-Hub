<?php
require_once 'db_config.php';

// Initialize search parameters
$search = [];
$orderBy = isset($_GET['sort']) ? $_GET['sort'] : (isset($_COOKIE['user_sort']) ? $_COOKIE['user_sort'] : 'upload_time');
$order = isset($_GET['order']) ? $_GET['order'] : (isset($_COOKIE['user_order']) ? $_COOKIE['user_order'] : 'DESC');
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = isset($_COOKIE['user_per_page']) ? (int)$_COOKIE['user_per_page'] : 10;

// Build search conditions
if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
    $search[] = "start_date = :start_date";
}
if (isset($_GET['file_name']) && !empty($_GET['file_name'])) {
    $search[] = "(file_name LIKE :file_name OR original_name LIKE :file_name)";
}
if (isset($_GET['file_type']) && !empty($_GET['file_type'])) {
    $search[] = "file_type = :file_type";
}

// Build query
$sql = "SELECT * FROM files";
if (!empty($search)) {
    $sql .= " WHERE " . implode(" AND ", $search);
}
$sql .= " ORDER BY $orderBy $order";

// Count total records for pagination
$countSql = "SELECT COUNT(*) FROM files";
if (!empty($search)) {
    $countSql .= " WHERE " . implode(" AND ", $search);
}
$stmt = $pdo->prepare($countSql);

// Bind search parameters
if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
    $stmt->bindValue(':start_date', $_GET['start_date']);
}
if (isset($_GET['file_name']) && !empty($_GET['file_name'])) {
    $stmt->bindValue(':file_name', '%' . $_GET['file_name'] . '%');
}
if (isset($_GET['file_type']) && !empty($_GET['file_type'])) {
    $stmt->bindValue(':file_type', $_GET['file_type']);
}

$stmt->execute();
$totalRecords = $stmt->fetchColumn();
$totalPages = ceil($totalRecords / $perPage);

// Get paginated results
$sql .= " LIMIT :offset, :limit";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':offset', ($page - 1) * $perPage, PDO::PARAM_INT);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);

// Bind search parameters again for main query
if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
    $stmt->bindValue(':start_date', $_GET['start_date']);
}
if (isset($_GET['file_name']) && !empty($_GET['file_name'])) {
    $stmt->bindValue(':file_name', '%' . $_GET['file_name'] . '%');
}
if (isset($_GET['file_type']) && !empty($_GET['file_type'])) {
    $stmt->bindValue(':file_type', $_GET['file_type']);
}

$stmt->execute();
$files = $stmt->fetchAll(PDO::FETCH_ASSOC);

function formatFileSize($bytes)
{
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    }
    return $bytes . ' bytes';
}
?>

<?php
$page_title = 'TradersHub Automated Trading';
require_once 'partials/header.php';
?>
    <style>
    </style>
</head>

<body class="bg-light">

    <div class="container py-5">
        <h1 class="text-center mb-4">Automated Trading Performance Reports</h1>
        <div class="alert alert-info mb-4">
            <p class="mb-0 text-center">Welcome to TradersHub! Here you can find our latest automated trading performance reports. Visit our <a href="https://www.youtube.com/@tradershub-2" target="_blank" class="alert-link">YouTube channel</a> for detailed analysis and insights.</p>
        </div>

        <!-- Search Form -->
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-3">
                <input type="date" name="start_date" class="form-control" value="<?php echo $_GET['start_date'] ?? ''; ?>" placeholder="Start Date">
            </div>
            <div class="col-md-3">
                <input type="text" name="file_name" class="form-control" value="<?php echo $_GET['file_name'] ?? ''; ?>" placeholder="File Name">
            </div>
            <div class="col-md-3">
                <select name="file_type" class="form-control">
                    <option value="">All File Types</option>
                    <option value="pdf" <?php echo (isset($_GET['file_type']) && $_GET['file_type'] == 'pdf') ? 'selected' : ''; ?>>PDF</option>
                    <option value="xlsx" <?php echo (isset($_GET['file_type']) && $_GET['file_type'] == 'xlsx') ? 'selected' : ''; ?>>Excel (XLSX)</option>
                    <option value="xls" <?php echo (isset($_GET['file_type']) && $_GET['file_type'] == 'xls') ? 'selected' : ''; ?>>Excel (XLS)</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary">Search</button>
                <a href="index.php" class="btn btn-secondary">Reset</a>
            </div>
        </form>

        <!-- Files Table -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>
                            File Name
                            <a href="?sort=file_name&order=ASC<?php echo isset($_GET['file_type']) ? '&file_type=' . $_GET['file_type'] : ''; ?><?php echo isset($_GET['start_date']) ? '&start_date=' . $_GET['start_date'] : ''; ?>" class="text-decoration-none">↑</a>
                            <a href="?sort=file_name&order=DESC<?php echo isset($_GET['file_type']) ? '&file_type=' . $_GET['file_type'] : ''; ?><?php echo isset($_GET['start_date']) ? '&start_date=' . $_GET['start_date'] : ''; ?>" class="text-decoration-none">↓</a>
                        </th>
                        <th>
                            Start Date
                            <a href="?sort=start_date&order=ASC<?php echo isset($_GET['file_type']) ? '&file_type=' . $_GET['file_type'] : ''; ?><?php echo isset($_GET['file_name']) ? '&file_name=' . $_GET['file_name'] : ''; ?>" class="text-decoration-none">↑</a>
                            <a href="?sort=start_date&order=DESC<?php echo isset($_GET['file_type']) ? '&file_type=' . $_GET['file_type'] : ''; ?><?php echo isset($_GET['file_name']) ? '&file_name=' . $_GET['file_name'] : ''; ?>" class="text-decoration-none">↓</a>
                        </th>
                        <th>
                            Upload Time
                            <a href="?sort=upload_time&order=ASC<?php echo isset($_GET['file_type']) ? '&file_type=' . $_GET['file_type'] : ''; ?><?php echo isset($_GET['file_name']) ? '&file_name=' . $_GET['file_name'] : ''; ?>" class="text-decoration-none">↑</a>
                            <a href="?sort=upload_time&order=DESC<?php echo isset($_GET['file_type']) ? '&file_type=' . $_GET['file_type'] : ''; ?><?php echo isset($_GET['file_name']) ? '&file_name=' . $_GET['file_name'] : ''; ?>" class="text-decoration-none">↓</a>
                        </th>
                        <th>
                            Type
                            <a href="?sort=file_type&order=ASC<?php echo isset($_GET['start_date']) ? '&start_date=' . $_GET['start_date'] : ''; ?><?php echo isset($_GET['file_name']) ? '&file_name=' . $_GET['file_name'] : ''; ?>" class="text-decoration-none">↑</a>
                            <a href="?sort=file_type&order=DESC<?php echo isset($_GET['start_date']) ? '&start_date=' . $_GET['start_date'] : ''; ?><?php echo isset($_GET['file_name']) ? '&file_name=' . $_GET['file_name'] : ''; ?>" class="text-decoration-none">↓</a>
                        </th>
                        <th>
                            Size
                            <a href="?sort=file_size&order=ASC<?php echo isset($_GET['file_type']) ? '&file_type=' . $_GET['file_type'] : ''; ?><?php echo isset($_GET['file_name']) ? '&file_name=' . $_GET['file_name'] : ''; ?>" class="text-decoration-none">↑</a>
                            <a href="?sort=file_size&order=DESC<?php echo isset($_GET['file_type']) ? '&file_type=' . $_GET['file_type'] : ''; ?><?php echo isset($_GET['file_name']) ? '&file_name=' . $_GET['file_name'] : ''; ?>" class="text-decoration-none">↓</a>
                        </th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($files): ?>
                        <?php foreach ($files as $file): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($file['original_name'] ?: $file['file_name']); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($file['start_date'])); ?></td>
                                <td><?php echo date('Y-m-d H:i:s', strtotime($file['upload_time'])); ?></td>
                                <td><?php echo strtoupper($file['file_type']); ?></td>
                                <td><?php echo formatFileSize($file['file_size']); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="download.php?file_id=<?php echo $file['id']; ?>" class="btn btn-sm btn-primary">Download</a>
                                        <a href="download.php?file_id=<?php echo $file['id']; ?>&view=true" class="btn btn-sm btn-info" target="_blank">View</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No files found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo isset($_GET['sort']) ? '&sort=' . $_GET['sort'] : ''; ?><?php echo isset($_GET['order']) ? '&order=' . $_GET['order'] : ''; ?><?php echo isset($_GET['file_type']) ? '&file_type=' . $_GET['file_type'] : ''; ?><?php echo isset($_GET['start_date']) ? '&start_date=' . $_GET['start_date'] : ''; ?><?php echo isset($_GET['file_name']) ? '&file_name=' . $_GET['file_name'] : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>

    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">© <?php echo date('Y'); ?> TradersHub Automated Trading. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/cookies.js"></script>
</body>

</html>