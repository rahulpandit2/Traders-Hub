<?php
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Admin Panel - TradersHub'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        html,
        body {
            height: 100%;
            margin: 0;
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .main-content {
            flex: 1 0 auto;
        }

        footer {
            flex-shrink: 0;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark" style="margin-bottom: 20px;">
        <div class="container">
            <a class="navbar-brand" href="index.php">TradersHub Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page === 'index.php' ? 'active' : ''; ?>" href="index.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page === 'contacts.php' ? 'active' : ''; ?>" href="contacts.php">Contacts</a>
                    </li>
                    <?php if ($user['user_type'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page === 'logs.php' ? 'active' : ''; ?>" href="logs.php">Logs</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page === 'savedData.php' ? 'active' : ''; ?>" href="savedData.php">Saved Data</a>
                        </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <?php echo htmlspecialchars($_SESSION['admin_username']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>