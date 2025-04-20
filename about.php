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
        .section-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            transition: all 0.3s ease;
            margin-bottom: 20px;
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }
        .section-card.visible {
            opacity: 1;
            transform: translateY(0);
        }
        .investment-tier {
            border-left: 4px solid #007bff;
            padding-left: 15px;
            margin-bottom: 10px;
        }
        .github-feedback {
            background-color: #f6f8fa;
            border: 1px solid #e1e4e8;
        }
    </style>
</head>
<body class="bg-light">

    <div class="container py-5">
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <!-- Main Title Card -->
                <div class="section-card card shadow-sm" id="main-title">
                    <div class="card-body text-center">
                        <h1 class="card-title mb-4">About TradersHub</h1>
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
                    </div>
                </div>
                
                <!-- Who We Are Card -->
                <div class="section-card card shadow-sm" id="who-we-are">
                    <div class="card-body">
                        <h2 class="card-title fw-bold mb-4">Who We Are</h2>
                        <p>TradersHub is a leading platform dedicated to automated trading strategies and market analysis. We showcase real, verifiable profits from our trading systems - all documented through our live YouTube trading sessions. Our transparent approach lets you see exactly how we generate consistent returns.</p>
                    </div>
                </div>
                
                <!-- Mission Card -->
                <div class="section-card card shadow-sm" id="mission">
                    <div class="card-body">
                        <h2 class="card-title fw-bold mb-4">Our Mission</h2>
                        <p>Our mission is to democratize automated trading by providing transparent, reliable, and actionable trading insights. We believe in empowering traders with the knowledge and tools they need to succeed in today's dynamic markets.</p>
                    </div>
                </div>
                
                <!-- Investment Program Card -->
                <div class="section-card card shadow-sm" id="investment-program">
                    <div class="card-body">
                        <h2 class="card-title fw-bold mb-4">💰 Investment Program</h2>
                        <p>We accept investors with a minimum of <strong>$1,000</strong> for secure, managed returns. All trades are executed live on YouTube as proof of our strategy's effectiveness.</p>
                        <div class="mt-4">
                            <h5 class="fw-bold">Investment Tiers & Commission Structure:</h5>
                            <div class="investment-tier">
                                <h6>$1,000 - $2,999</h6>
                                <p>50% profit commission</p>
                            </div>
                            <div class="investment-tier">
                                <h6>$3,000 - $9,999</h6>
                                <p>45% profit commission</p>
                            </div>
                            <div class="investment-tier">
                                <h6>$10,000+</h6>
                                <p>40% profit commission</p>
                            </div>
                        </div>
                        <a href="contact.php" class="btn btn-success mt-3">Contact Us to Invest</a>
                    </div>
                </div>
                
                <!-- Trading Platform Card -->
                <div class="section-card card shadow-sm" id="trading-platform">
                    <div class="card-body">
                        <h2 class="card-title fw-bold mb-4">🚀 Trading Platform</h2>
                        <p>We recommend <strong>Exness</strong> - the best broker in the market with 1:Unlimited margin and lightning-fast execution.</p>
                        <a href="https://one.exnesstrack.org/a/26npe4gzna" target="_blank" class="btn btn-primary">Join Exness via Our Affiliate Link</a>
                        <p class="text-muted mt-2 mb-0"><small>Supports our channel when you use our link</small></p>
                    </div>
                </div>
                
                <!-- Policies Card -->
                <div class="section-card card shadow-sm" id="policies">
                    <div class="card-body">
                        <h2 class="card-title fw-bold mb-4">📜 Our Policies</h2>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">• All deposits are locked for a minimum of 3 months</li>
                            <li class="list-group-item">• Withdrawals can only be made after the 3-month period</li>
                            <li class="list-group-item">• Commission is taken from net profits only</li>
                            <li class="list-group-item">• Investors understand and accept market risks</li>
                            <li class="list-group-item">• TradersHub is not liable for any trading losses</li>
                            <li class="list-group-item">• All investments are subject to our terms and conditions</li>
                        </ul>
                    </div>
                </div>
                
                <!-- What We Offer Card -->
                <div class="section-card card shadow-sm" id="offerings">
                    <div class="card-body">
                        <h2 class="card-title fw-bold mb-4">What We Offer</h2>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title">📊 Verified Profit Reports</h5>
                                        <p class="card-text">Transparent performance analytics with real trading data.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title">🎥 Live Trading Sessions</h5>
                                        <p class="card-text">YouTube broadcasts as proof of our trading concepts.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title">💼 Managed Investments</h5>
                                        <p class="card-text">Professional trading with tiered investment options.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title">📚 Educational Content</h5>
                                        <p class="card-text">Resources to improve your automated trading skills.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- GitHub Feedback Card -->
                <div class="section-card card shadow-sm github-feedback" id="feedback">
                    <div class="card-body text-center">
                        <h2 class="card-title fw-bold mb-4">💡 Feedback & Bug Reports</h2>
                        <p>We welcome your feedback and bug reports to improve our platform.</p>
                        <a href="https://github.com/rahulpandit2" target="_blank" class="btn btn-dark">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-github" viewBox="0 0 16 16">
                                <path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.012 8.012 0 0 0 16 8c0-4.42-3.58-8-8-8z"/>
                            </svg>
                            Visit Our GitHub
                        </a>
                        <p class="mt-2 mb-0">Report issues or suggest improvements directly to our developer.</p>
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
    <script>
        // Scroll animation for section cards
        document.addEventListener('DOMContentLoaded', function() {
            const sectionCards = document.querySelectorAll('.section-card');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, {
                threshold: 0.1
            });
            
            sectionCards.forEach(card => {
                observer.observe(card);
            });
            
            // Animate main title first
            if (sectionCards.length > 0) {
                sectionCards[0].classList.add('visible');
            }
        });
    </script>
</body>
</html>