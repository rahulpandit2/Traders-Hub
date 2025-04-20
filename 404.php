<?php
header("HTTP/1.0 404 Not Found");
?>
<?php
$page_title = '404 Not Found - TradersHub';
require_once 'partials/header.php';
?>
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
    <style>
        .error-container {
            min-height: 80vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .lottie-container {
            max-width: 400px;
            margin: 0 auto;
        }
        .countdown-container {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin: 20px 0;
            text-align: center;
            border: 1px solid #dee2e6;
        }
        .countdown-number {
            font-size: 2rem;
            font-weight: bold;
            color: #dc3545;
            margin: 0 5px;
        }
        .countdown-text {
            font-size: 1.2rem;
            margin-bottom: 5px;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">TradersHub Automated Trading</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container error-container">
        <div class="lottie-container">
            <lottie-player
                src="https://assets2.lottiefiles.com/packages/lf20_kcsr6fcp.json"
                background="transparent"
                speed="1"
                loop
                autoplay
            ></lottie-player>
        </div>
        <h1 class="text-center mt-4">Oops! Page Not Found</h1>
        <p class="text-center text-muted mb-4">The page you're looking for doesn't exist or has been moved.</p>
        
        <div class="countdown-container">
            <p class="countdown-text">You will be automatically redirected in</p>
            <div>
                <span class="countdown-number" id="countdown">15</span>
                <span>seconds</span>
            </div>
        </div>
        
        <div class="text-center">
            <a href="index.php" class="btn btn-primary btn-lg">Return to Home Now</a>
        </div>
    </div>

    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">© <?php echo date('Y'); ?> TradersHub Automated Trading. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Countdown and redirect
        let seconds = 15;
        const countdownElement = document.getElementById('countdown');
        
        const countdown = setInterval(() => {
            seconds--;
            countdownElement.textContent = seconds;
            
            if (seconds <= 0) {
                clearInterval(countdown);
                window.location.href = 'index.php';
            }
        }, 1000);
    </script>
</body>
</html>