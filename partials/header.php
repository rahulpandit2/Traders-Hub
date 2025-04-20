<?php
$current_page = basename($_SERVER['PHP_SELF']);
$canonical_url = "https://tradershub.com" . $_SERVER['REQUEST_URI'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="TradersHub - Automated Trading Platform with verified profits. Join our investment program or learn algorithmic trading strategies with live YouTube sessions.">
    <meta name="keywords" content="automated trading, algorithmic trading, investment program, forex trading, stock market, cryptocurrency trading, trading strategies">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= $canonical_url ?>">
    <meta property="og:title" content="<?php echo isset($page_title) ? $page_title : 'TradersHub Automated Trading'; ?>">
    <meta property="og:description" content="Professional automated trading platform with verified results. Join our investor program or learn algorithmic trading strategies.">
    <meta property="og:image" content="https://tradershub.com/images/og-image.jpg">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?= $canonical_url ?>">
    <meta property="twitter:title" content="<?php echo isset($page_title) ? $page_title : 'TradersHub Automated Trading'; ?>">
    <meta property="twitter:description" content="Professional automated trading platform with verified results. Join our investor program or learn algorithmic trading strategies.">
    <meta property="twitter:image" content="https://tradershub.com/images/og-image.jpg">

    <!-- Canonical -->
    <link rel="canonical" href="<?= $canonical_url ?>">

    <!-- Preconnect -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://www.youtube.com">
    
    <title><?php echo isset($page_title) ? "$page_title | TradersHub Automated Trading" : 'TradersHub - Automated Trading Platform'; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/favicon.png">
    
    <!-- Schema Markup -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "TradersHub Automated Trading",
        "url": "https://tradershub.com",
        "logo": "https://tradershub.com/images/logo.png",
        "sameAs": [
            "https://www.youtube.com/@tradershub-2",
            "https://github.com/rahulpandit2"
        ],
        "description": "Professional automated trading platform offering verified trading strategies and investment opportunities."
    }
    </script>

    <!-- Assets -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        html, body {
            height: 100%;
            margin: 0;
            scroll-behavior: smooth;
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