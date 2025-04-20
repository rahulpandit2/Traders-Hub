<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'TradersHub Automated Trading'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        html, body {
            height: 100%;
            margin: 0;
        }
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .container.py-5 {
            flex: 1 0 auto;
        }
        footer {
            flex-shrink: 0;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark" style="margin-bottom: 20px;">
        <div class="container">
            <a class="navbar-brand" href="/Traders Hub/index.php">TradersHub Automated Trading</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page === 'index.php' ? 'active' : ''; ?>" href="/Traders Hub/index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page === 'about.php' ? 'active' : ''; ?>" href="/Traders Hub/about.php">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page === 'contact.php' ? 'active' : ''; ?>" href="/Traders Hub/contact.php">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin/login.php">Admin Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>