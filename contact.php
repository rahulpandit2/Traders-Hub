<?php
require_once 'db_config.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $messageText = trim($_POST['message'] ?? '');
    $consent = isset($_POST['consent']) ? 1 : 0;

    if (empty($name) || empty($email) || empty($subject) || empty($messageText)) {
        $error = 'All fields are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO contacts (name, email, subject, message, email_consent, status) VALUES (?, ?, ?, ?, ?, 'pending')");
            $stmt->execute([$name, $email, $subject, $messageText, $consent]);
            $message = 'Your message has been sent successfully!';
        } catch (PDOException $e) {
            $error = 'An error occurred while sending your message. Please try again later.';
        }
    }
}
?>
<?php
$page_title = 'Contact Us - TradersHub Automated Trading';
require_once 'partials/header.php';
?>
<style>
    .contact-card {
        transition: all 0.3s ease;
        opacity: 0;
        transform: translateY(20px);
    }

    .contact-card.animated {
        opacity: 1;
        transform: translateY(0);
    }

    .contact-option {
        border-left: 4px solid #28a745;
        padding: 15px;
        margin-bottom: 15px;
        transition: all 0.3s ease;
    }

    .contact-option:hover {
        background-color: #f8f9fa;
        transform: translateX(5px);
    }

    .github-link {
        background-color: #f6f8fa;
        border: 1px solid #e1e4e8;
        padding: 15px;
        border-radius: 6px;
    }

    .form-control:focus {
        border-color: #28a745;
        box-shadow: 0 0 0 0.25rem rgba(40, 167, 69, 0.25);
    }
</style>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card shadow-sm contact-card" id="contact-card">
                <div class="card-body">
                    <h1 class="card-title text-center mb-4">Contact Us</h1>
                    <p class="text-center mb-4">Choose your preferred contact method below</p>

                    <?php if ($message): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <div class="contact-option">
                                <h5><i class="bi bi-envelope-fill text-primary"></i> Email Us Directly</h5>
                                <p>Send your message to: <a href="mailto:rp1618938+tradershub@gmail.com">rp1618938+tradershub@gmail.com</a></p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="contact-option">
                                <h5><i class="bi bi-chat-left-text-fill text-success"></i> Use This Form</h5>
                                <p>Fill out the form below and we'll get back to you</p>
                            </div>
                        </div>
                    </div>

                    <form method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required>
                            <div class="invalid-feedback">Please provide your name</div>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <div class="invalid-feedback">Please provide a valid email</div>
                        </div>
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="subject" name="subject" required>
                            <div class="invalid-feedback">Please provide a subject</div>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                            <div class="invalid-feedback">Please write your message</div>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="consent" name="consent">
                            <label class="form-check-label" for="consent">I give consent to receive emails from Traders Hub</label>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-success btn-lg px-4">
                                <i class="bi bi-send-fill"></i> Send Message
                            </button>
                        </div>
                    </form>

                    <div class="mt-5 p-4 border rounded shadow-sm bg-light">
                        <h4><i class="bi bi-cash-coin text-warning"></i> Interested in Becoming an Investor?</h4>
                        <p>At TradersHub, we are continuously expanding and welcoming strategic partners and investors to join our journey toward transforming automated trading. Whether you're an individual, an angel investor, or an institution, we offer transparent collaboration opportunities backed by real-time reporting and profit-sharing models.</p>
                        <p><strong>How to Get Started:</strong></p>
                        <ul>
                            <li>Submit your interest through this contact form or email us directly.</li>
                            <li>Our investment relations team will reach out to schedule a confidential discussion.</li>
                            <li>Receive a detailed investor kit and explore partnership terms tailored for mutual growth.</li>
                        </ul>
                        <p>For a deeper understanding of our mission, history, and vision, please visit our
                            <a href="about.php" class="text-decoration-underline fw-bold">About Us</a> page.
                        </p>
                    </div>


                    <div class="github-link mt-5">
                        <h4><i class="bi bi-github"></i> Feedback & Bug Reports</h4>
                        <p>Found a bug or have suggestions? Contribute on GitHub:</p>
                        <a href="https://github.com/rahulpandit2" target="_blank" class="btn btn-dark">
                            <i class="bi bi-github"></i> Visit GitHub Repository
                        </a>
                        <p class="mt-2 small">For technical issues or feature requests, please open an issue on GitHub.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Animation for contact card
    document.addEventListener('DOMContentLoaded', function() {
        const contactCard = document.getElementById('contact-card');
        setTimeout(() => {
            contactCard.classList.add('animated');
        }, 100);

        // Form validation
        (function() {
            'use strict';
            var forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms)
                .forEach(function(form) {
                    form.addEventListener('submit', function(event) {
                        if (!form.checkValidity()) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
        })();
    });
</script>

<?php require_once 'partials/footer.php'; ?>