<?php
require_once 'db_config.php';
?>
<?php
$page_title = 'About Us - TradersHub Automated Trading';
require_once 'partials/header.php';
?>
    <style>
        .profit-highlight {
            background-color: #f8f9fa;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin: 20px 0;
        }
        .investment-card {
            border: 1px solid #28a745;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .investment-card:hover {
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }
    </style>
</head>
<body class="bg-light">

    <div class="container py-5">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h1 class="card-title text-center mb-4">About TradersHub</h1>
                        <div class="text-center mb-4">
                            <a href="https://www.youtube.com/@tradershub-2" target="_blank" class="btn btn-danger mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-youtube" viewBox="0 0 16 16">
                                    <path d="M8.051 1.999h.089c.822.003 4.987.033 6.11.335a2.01 2.01 0 0 1 1.415 1.42c.101.38.172.883.22 1.402l.01.104.022.26.008.104c.065.914.073 1.77.074 1.957v.075c-.001.194-.01 1.108-.082 2.06l-.008.105-.009.104c-.05.572-.124 1.14-.235 1.558a2.007 2.007 0 0 1-1.415 1.42c-1.16.312-5.569.334-6.18.335h-.142c-.309 0-1.587-.006-2.927-.052l-.17-.006-.087-.004-.171-.007-.171-.007c-1.11-.049-2.167-.128-2.654-.26a2.007 2.007 0 0 1-1.415-1.419c-.111-.417-.185-.986-.235-1.558L.09 9.82l-.008-.104A31.4 31.4 0 0 1 0 7.68v-.123c.002-.215.01-.958.064-1.778l.007-.103.003-.052.008-.104.022-.26.01-.104c.048-.519.119-1.023.22-1.402a2.007 2.007 0 0 1 1.415-1.42c.487-.13 1.544-.21 2.654-.26l.17-.007.172-.006.086-.003.171-.007A99.788 99.788 0 0 1 7.858 2h.193zM6.4 5.209v4.818l4.157-2.408L6.4 5.209z"/>
                                </svg>
                                Watch Our Live Trading on YouTube
                            </a>
                        </div>
                        
                        <div class="profit-highlight text-center">
                            <h3 class="text-success">$1,287,450+ Total Profits Generated</h3>
                            <p class="mb-0">See our verified trading results and join our successful investors</p>
                        </div>
                        
                        <p class="lead text-center mb-4">Your Gateway to Automated Trading Excellence</p>
                        
                        <div class="mb-4">
                            <h5 class="fw-bold">Who We Are</h5>
                            <p>TradersHub is a leading platform dedicated to automated trading strategies and market analysis. We showcase real, verifiable profits from our trading systems - all documented through our live YouTube trading sessions. Our transparent approach lets you see exactly how we generate consistent returns.</p>
                        </div>
                        
                        <div class="mb-4">
                            <h5 class="fw-bold">Our Mission</h5>
                            <p>Our mission is to democratize automated trading by providing transparent, reliable, and actionable trading insights. We believe in empowering traders with the knowledge and tools they need to succeed in today's dynamic markets.</p>
                        </div>
                        
                        <div class="mb-4">
                            <h5 class="fw-bold">Investment Opportunities</h5>
                            <div class="card investment-card mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">💰 Join Our Investment Program</h5>
                                    <p class="card-text">We accept investors with a minimum of <strong>$1,000</strong> for secure, managed returns. All trades are executed live on YouTube as proof of our strategy's effectiveness.</p>
                                    <a href="contact.php" class="btn btn-success">Contact Us to Invest</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <h5 class="fw-bold">Start Trading Yourself</h5>
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">🚀 Trade with Unlimited Margin</h5>
                                    <p class="card-text">We recommend <strong>Exness</strong> - the best broker in the market with 1:Unlimited margin and lightning-fast execution.</p>
                                    <a href="https://one.exnesstrack.org/a/26npe4gzna" target="_blank" class="btn btn-primary">Join Exness via Our Affiliate Link</a>
                                    <p class="text-muted mt-2 mb-0"><small>Supports our channel when you use our link</small></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <h5 class="fw-bold">What We Offer</h5>
                            <ul class="list-unstyled">
                                <li class="mb-2">✓ Verified profit reports with transparent performance analytics</li>
                                <li class="mb-2">✓ Live trading sessions on YouTube as proof of concept</li>
                                <li class="mb-2">✓ Managed investment opportunities (minimum $1,000)</li>
                                <li class="mb-2">✓ The best broker recommendation (Exness with unlimited margin)</li>
                                <li class="mb-2">✓ Educational content for automated trading</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">© <?php echo date('Y'); ?> TradersHub Automated Trading. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>