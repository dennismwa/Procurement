<?php
/**
 * Tender Details Page
 * Procurement Management System
 */

require_once 'config/database.php';

// Get tender ID from URL
$tender_id = $_GET['id'] ?? null;

if (!$tender_id || !is_numeric($tender_id)) {
    header('Location: index.php');
    exit();
}

try {
    $db = getDB();
    
    // Get tender details
    $stmt = $db->prepare("SELECT * FROM tenders WHERE id = ? AND status = 'open'");
    $stmt->execute([$tender_id]);
    $tender = $stmt->fetch();
    
    if (!$tender) {
        header('Location: index.php');
        exit();
    }
    
    // Get tender documents
    $stmt = $db->prepare("SELECT * FROM files WHERE related_type = 'tender' AND related_id = ? ORDER BY uploaded_at DESC");
    $stmt->execute([$tender_id]);
    $documents = $stmt->fetchAll();
    
    // Calculate days remaining
    $deadline = new DateTime($tender['deadline']);
    $today = new DateTime();
    $days_remaining = $today->diff($deadline)->days;
    $is_expired = $today > $deadline;
    
} catch(PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($tender['title']); ?> - Procurement System</title>
    <link rel="stylesheet" href="style.css">
    <meta name="description" content="<?php echo htmlspecialchars(substr($tender['description'], 0, 160)); ?>">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <h2><a href="index.php">Procurement System</a></h2>
            </div>
            <ul class="nav-menu">
                <li><a href="index.php" class="nav-link">Home</a></li>
                <li><a href="index.php#tenders" class="nav-link">Tenders</a></li>
                <li><a href="all-tenders.php" class="nav-link">All Tenders</a></li>
                <li><a href="index.php#contact" class="nav-link">Contact</a></li>
                <li><a href="admin/admin.php" class="nav-link admin-link">Admin</a></li>
            </ul>
            <div class="hamburger">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
        </div>
    </nav>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <div class="container">
            <nav class="breadcrumb-nav">
                <a href="index.php">Home</a>
                <span class="breadcrumb-separator">></span>
                <a href="index.php#tenders">Tenders</a>
                <span class="breadcrumb-separator">></span>
                <span class="breadcrumb-current"><?php echo htmlspecialchars($tender['title']); ?></span>
            </nav>
        </div>
    </div>

    <!-- Tender Details -->
    <section class="tender-details-section">
        <div class="container">
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="tender-details-container">
                <!-- Tender Header -->
                <div class="tender-header-section">
                    <div class="tender-title-block">
                        <h1><?php echo htmlspecialchars($tender['title']); ?></h1>
                        <div class="tender-meta">
                            <span class="tender-id">Tender ID: #<?php echo $tender['id']; ?></span>
                            <span class="tender-status status-<?php echo $tender['status']; ?>">
                                <?php echo ucfirst($tender['status']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="tender-deadline-block">
                        <?php if (!$is_expired): ?>
                            <div class="deadline-counter">
                                <div class="deadline-number"><?php echo $days_remaining; ?></div>
                                <div class="deadline-text">Days Remaining</div>
                            </div>
                        <?php else: ?>
                            <div class="deadline-expired">
                                <div class="deadline-text">EXPIRED</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tender Info Grid -->
                <div class="tender-info-grid">
                    <div class="tender-main-info">
                        <div class="info-section">
                            <h2>Tender Description</h2>
                            <div class="description-content">
                                <?php echo nl2br(htmlspecialchars($tender['description'])); ?>
                            </div>
                        </div>

                        <?php if (!empty($documents)): ?>
                            <div class="info-section">
                                <h2>Tender Documents</h2>
                                <div class="documents-list">
                                    <?php foreach ($documents as $document): ?>
                                        <div class="document-item">
                                            <div class="document-icon">
                                                <?php
                                                $extension = strtolower(pathinfo($document['original_name'], PATHINFO_EXTENSION));
                                                switch ($extension) {
                                                    case 'pdf':
                                                        echo '📄';
                                                        break;
                                                    case 'doc':
                                                    case 'docx':
                                                        echo '📝';
                                                        break;
                                                    case 'xls':
                                                    case 'xlsx':
                                                        echo '📊';
                                                        break;
                                                    default:
                                                        echo '📎';
                                                }
                                                ?>
                                            </div>
                                            <div class="document-info">
                                                <h4><?php echo htmlspecialchars($document['original_name']); ?></h4>
                                                <div class="document-meta">
                                                    <span>Size: <?php echo number_format($document['file_size'] / 1024, 1); ?> KB</span>
                                                    <span>Uploaded: <?php echo date('M j, Y', strtotime($document['uploaded_at'])); ?></span>
                                                </div>
                                            </div>
                                            <a href="<?php echo htmlspecialchars($document['file_path']); ?>" 
                                               class="btn btn-outline btn-sm" download>
                                                Download
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="info-section">
                            <h2>Important Information</h2>
                            <div class="important-info">
                                <div class="info-alert">
                                    <div class="alert-icon">⚠️</div>
                                    <div class="alert-content">
                                        <h4>Submission Guidelines</h4>
                                        <ul>
                                            <li>All bids must be submitted before the deadline</li>
                                            <li>Late submissions will not be considered</li>
                                            <li>Ensure all required documents are included</li>
                                            <li>Contact us if you have any questions</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tender-sidebar">
                        <div class="sidebar-section">
                            <h3>Tender Summary</h3>
                            <div class="summary-list">
                                <div class="summary-item">
                                    <span class="summary-label">Budget</span>
                                    <span class="summary-value">$<?php echo number_format($tender['budget'], 2); ?></span>
                                </div>
                                <div class="summary-item">
                                    <span class="summary-label">Deadline</span>
                                    <span class="summary-value"><?php echo date('F j, Y', strtotime($tender['deadline'])); ?></span>
</div>
<div class="summary-item">
<span class="summary-label">Status</span>
<span class="summary-value status-<?php echo $tender['status']; ?>">
<?php echo ucfirst($tender['status']); ?>
</span>
</div>
<div class="summary-item">
<span class="summary-label">Posted</span>
<span class="summary-value"><?php echo date('M j, Y', strtotime($tender['created_at'])); ?></span>
</div>
</div>
</div>
<div class="sidebar-section">
                        <h3>Actions</h3>
                        <div class="action-buttons">
                            <?php if (!$is_expired): ?>
                                <button class="btn btn-primary btn-full" onclick="showInterestModal()">
                                    Express Interest
                                </button>
                                <button class="btn btn-secondary btn-full" onclick="printTender()">
                                    Print Details
                                </button>
                            <?php else: ?>
                                <div class="expired-notice">
                                    <p>This tender has expired and is no longer accepting bids.</p>
                                </div>
                            <?php endif; ?>
                            <button class="btn btn-outline btn-full" onclick="shareTender()">
                                Share Tender
                            </button>
                        </div>
                    </div>

                    <div class="sidebar-section">
                        <h3>Need Help?</h3>
                        <div class="help-info">
                            <p>Have questions about this tender?</p>
                            <div class="contact-options">
                                <a href="tel:+254700123456" class="contact-option">
                                    <span class="contact-icon">📞</span>
                                    Call Us
                                </a>
                                <a href="mailto:info@procurement.gov.ke" class="contact-option">
                                    <span class="contact-icon">✉️</span>
                                    Email Us
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Related Tenders -->
<section class="related-tenders">
    <div class="container">
        <h2>Other Open Tenders</h2>
        <div class="related-tender-grid">
            <?php
            try {
                $stmt = $db->prepare("SELECT * FROM tenders WHERE status = 'open' AND id != ? AND deadline >= CURDATE() ORDER BY deadline ASC LIMIT 3");
                $stmt->execute([$tender_id]);
                $related_tenders = $stmt->fetchAll();
                
                if (!empty($related_tenders)):
                    foreach ($related_tenders as $related):
            ?>
                <div class="related-tender-card">
                    <h4><?php echo htmlspecialchars($related['title']); ?></h4>
                    <p class="related-budget">$<?php echo number_format($related['budget'], 2); ?></p>
                    <p class="related-deadline">Deadline: <?php echo date('M j, Y', strtotime($related['deadline'])); ?></p>
                    <a href="tender-details.php?id=<?php echo $related['id']; ?>" class="btn btn-outline btn-sm">
                        View Details
                    </a>
                </div>
            <?php
                    endforeach;
                else:
            ?>
                <p class="no-related">No other open tenders available at the moment.</p>
            <?php
                endif;
            } catch(PDOException $e) {
                echo '<p class="error">Unable to load related tenders.</p>';
            }
            ?>
        </div>
    </div>
</section>

<!-- Interest Modal -->
<div id="interestModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Express Interest</h3>
            <span class="modal-close">&times;</span>
        </div>
        <div class="modal-body">
            <p>To express interest in this tender, please contact us directly with the following information:</p>
            <div class="contact-details">
                <div class="contact-item">
                    <strong>Email:</strong> info@procurement.gov.ke
                </div>
                <div class="contact-item">
                    <strong>Phone:</strong> +254 700 123 456
                </div>
                <div class="contact-item">
                    <strong>Reference:</strong> Tender #<?php echo $tender['id']; ?>
                </div>
            </div>
            <p><strong>Include in your inquiry:</strong></p>
            <ul>
                <li>Your company name and registration details</li>
                <li>Brief description of your capabilities</li>
                <li>Any questions about the tender requirements</li>
            </ul>
        </div>
        <div class="modal-footer">
            <button class="btn btn-primary" onclick="window.open('mailto:info@procurement.gov.ke?subject=Interest in Tender #<?php echo $tender['id']; ?>')">
                Send Email
            </button>
            <button class="btn btn-secondary" onclick="closeModal()">Close</button>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <h3>Procurement System</h3>
                <p>Promoting transparency and efficiency in procurement processes.</p>
            </div>
            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul class="footer-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="all-tenders.php">All Tenders</a></li>
                    <li><a href="index.php#about">About</a></li>
                    <li><a href="index.php#contact">Contact</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Contact</h4>
                <p>📞 +254 700 123 456</p>
                <p>✉️ info@procurement.gov.ke</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Procurement Management System. All rights reserved.</p>
        </div>
    </div>
</footer>

<script>
    // Mobile navigation
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');
    
    hamburger.addEventListener('click', () => {
        hamburger.classList.toggle('active');
        navMenu.classList.toggle('active');
    });

    // Modal functions
    function showInterestModal() {
        document.getElementById('interestModal').style.display = 'block';
    }

    function closeModal() {
        document.getElementById('interestModal').style.display = 'none';
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('interestModal');
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    }

    // Close modal with escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeModal();
        }
    });

    // Close modal button
    document.querySelector('.modal-close').onclick = closeModal;

    // Print function
    function printTender() {
        window.print();
    }

    // Share function
    function shareTender() {
        if (navigator.share) {
            navigator.share({
                title: '<?php echo addslashes($tender['title']); ?>',
                text: 'Check out this procurement tender',
                url: window.location.href
            });
        } else {
            // Fallback - copy to clipboard
            navigator.clipboard.writeText(window.location.href).then(() => {
                alert('Tender link copied to clipboard!');
            });
        }
    }

    // Deadline countdown (if tender is still active)
    <?php if (!$is_expired): ?>
    function updateCountdown() {
        const deadline = new Date('<?php echo $tender['deadline']; ?>T23:59:59');
        const now = new Date();
        const timeLeft = deadline - now;

        if (timeLeft > 0) {
            const days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
            const hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));

            const countdownElement = document.querySelector('.deadline-number');
            if (countdownElement) {
                if (days > 0) {
                    countdownElement.textContent = days;
                    document.querySelector('.deadline-text').textContent = days === 1 ? 'Day Remaining' : 'Days Remaining';
                } else if (hours > 0) {
                    countdownElement.textContent = hours;
                    document.querySelector('.deadline-text').textContent = hours === 1 ? 'Hour Remaining' : 'Hours Remaining';
                } else {
                    countdownElement.textContent = minutes;
                    document.querySelector('.deadline-text').textContent = minutes === 1 ? 'Minute Remaining' : 'Minutes Remaining';
                }
            }
        } else {
            location.reload(); // Refresh page when deadline passes
        }
    }

    // Update countdown every minute
    setInterval(updateCountdown, 60000);
    updateCountdown(); // Initial call
    <?php endif; ?>

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
</script>
</body>
</html>