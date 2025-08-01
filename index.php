<?php
/**
 * Public Frontend - Home Page
 * Procurement Management System
 */

require_once 'config/database.php';

// Get available tenders
try {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM tenders WHERE status = 'open' AND deadline >= CURDATE() ORDER BY deadline ASC LIMIT 6");
    $featured_tenders = $stmt->fetchAll();
    
    // Get tender statistics
    $stmt = $db->query("SELECT COUNT(*) as total FROM tenders WHERE status = 'open'");
    $open_tenders_count = $stmt->fetch()['total'];
    
    $stmt = $db->query("SELECT SUM(budget) as total FROM tenders WHERE status = 'open'");
    $total_budget = $stmt->fetch()['total'] ?? 0;
    
} catch(PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    $featured_tenders = [];
    $open_tenders_count = 0;
    $total_budget = 0;
}

// Handle search
$search_results = [];
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = '%' . $_GET['search'] . '%';
    try {
        $stmt = $db->prepare("SELECT * FROM tenders WHERE status = 'open' AND (title LIKE ? OR description LIKE ?) AND deadline >= CURDATE() ORDER BY deadline ASC");
        $stmt->execute([$search_term, $search_term]);
        $search_results = $stmt->fetchAll();
    } catch(PDOException $e) {
        $search_error = "Search error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Procurement Management System</title>
    <link rel="stylesheet" href="style.css">
    <meta name="description" content="Find and participate in government and institutional procurement tenders">
    <meta name="keywords" content="procurement, tenders, bidding, government contracts">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <h2>Procurement System</h2>
            </div>
            <ul class="nav-menu">
                <li><a href="#home" class="nav-link">Home</a></li>
                <li><a href="#tenders" class="nav-link">Tenders</a></li>
                <li><a href="#about" class="nav-link">About</a></li>
                <li><a href="#contact" class="nav-link">Contact</a></li>
                <li><a href="admin/admin.php" class="nav-link admin-link">Admin</a></li>
            </ul>
            <div class="hamburger">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-content">
            <h1>Welcome to Our Procurement System</h1>
            <p>Discover transparent procurement opportunities and participate in fair bidding processes</p>
            <div class="hero-stats">
                <div class="stat-item">
                    <h3><?php echo $open_tenders_count; ?></h3>
                    <p>Open Tenders</p>
                </div>
                <div class="stat-item">
                    <h3>$<?php echo number_format($total_budget, 0); ?></h3>
                    <p>Total Value</p>
                </div>
                <div class="stat-item">
                    <h3>100%</h3>
                    <p>Transparent</p>
                </div>
            </div>
            <div class="hero-actions">
                <a href="#tenders" class="btn btn-primary">View Tenders</a>
                <a href="#about" class="btn btn-secondary">Learn More</a>
            </div>
        </div>
        <div class="hero-image">
            <div class="hero-graphic">
                <div class="graphic-circle"></div>
                <div class="graphic-dots"></div>
            </div>
        </div>
    </section>

    <!-- Search Section -->
    <section class="search-section">
        <div class="container">
            <h2>Find Tenders</h2>
            <form method="GET" class="search-form">
                <div class="search-input-group">
                    <input type="text" name="search" placeholder="Search tenders by title or description..." 
                           value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button type="submit" class="search-btn">Search</button>
                </div>
            </form>
            
            <?php if (isset($_GET['search'])): ?>
                <div class="search-results">
                    <h3>Search Results for "<?php echo htmlspecialchars($_GET['search']); ?>"</h3>
                    <?php if (!empty($search_results)): ?>
                        <div class="tender-grid">
                            <?php foreach ($search_results as $tender): ?>
                                <div class="tender-card">
                                    <div class="tender-header">
                                        <h4><?php echo htmlspecialchars($tender['title']); ?></h4>
                                        <span class="tender-budget">$<?php echo number_format($tender['budget'], 2); ?></span>
                                    </div>
                                    <p class="tender-description"><?php echo htmlspecialchars(substr($tender['description'], 0, 120)) . '...'; ?></p>
                                    <div class="tender-footer">
                                        <span class="tender-deadline">Deadline: <?php echo date('M j, Y', strtotime($tender['deadline'])); ?></span>
                                        <a href="tender-details.php?id=<?php echo $tender['id']; ?>" class="btn btn-sm">View Details</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="no-results">No tenders found matching your search criteria.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Available Tenders Section -->
    <section id="tenders" class="tenders-section">
        <div class="container">
            <div class="section-header">
                <h2>Available Tenders</h2>
                <p>Current procurement opportunities ready for bidding</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if (!empty($featured_tenders)): ?>
                <div class="tender-grid">
                    <?php foreach ($featured_tenders as $tender): ?>
                        <div class="tender-card">
                            <div class="tender-header">
                                <h3><?php echo htmlspecialchars($tender['title']); ?></h3>
                                <span class="tender-status status-<?php echo $tender['status']; ?>">
                                    <?php echo ucfirst($tender['status']); ?>
                                </span>
                            </div>
                            
                            <div class="tender-body">
                                <p class="tender-description">
                                    <?php echo htmlspecialchars(substr($tender['description'], 0, 150)) . (strlen($tender['description']) > 150 ? '...' : ''); ?>
                                </p>
                                
                                <div class="tender-details">
                                    <div class="detail-item">
                                        <strong>Budget:</strong>
                                        <span>$<?php echo number_format($tender['budget'], 2); ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <strong>Deadline:</strong>
                                        <span class="deadline"><?php echo date('M j, Y', strtotime($tender['deadline'])); ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <strong>Days Left:</strong>
                                        <span class="days-left">
                                            <?php
                                            $days_left = (strtotime($tender['deadline']) - time()) / (60 * 60 * 24);
                                            echo floor($days_left) . ' days';
                                            ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="tender-footer">
                                <a href="tender-details.php?id=<?php echo $tender['id']; ?>" class="btn btn-primary">
                                    View Details
                                </a>
                                <span class="tender-id">ID: #<?php echo $tender['id']; ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="section-footer">
                    <a href="all-tenders.php" class="btn btn-outline">View All Tenders</a>
                </div>
            <?php else: ?>
                <div class="no-tenders">
                    <div class="no-tenders-icon">📋</div>
                    <h3>No Active Tenders</h3>
                    <p>There are currently no open tenders available. Please check back later.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about-section">
        <div class="container">
            <div class="about-content">
                <div class="about-text">
                    <h2>About Our Procurement System</h2>
                    <p>Our procurement management system ensures transparency, fairness, and efficiency in all procurement processes. We provide a centralized platform where organizations can post tenders and suppliers can participate in competitive bidding.</p>
                    
                    <div class="features">
                        <div class="feature-item">
                            <div class="feature-icon">🔍</div>
                            <div class="feature-content">
                                <h4>Transparent Process</h4>
                                <p>All tender information is publicly available with clear criteria and deadlines.</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">⚡</div>
                            <div class="feature-content">
                                <h4>Easy Access</h4>
                                <p>Simple interface to browse, search, and access tender documents.</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">📊</div>
                            <div class="feature-content">
                                <h4>Real-time Updates</h4>
                                <p>Stay informed with the latest tender postings and deadline reminders.</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">🛡️</div>
                            <div class="feature-content">
                                <h4>Secure Platform</h4>
                                <p>Your data and documents are protected with enterprise-level security.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="about-stats">
                    <div class="stats-card">
                        <h3>Our Impact</h3>
                        <div class="stats-list">
                            <div class="stat-row">
                                <span class="stat-label">Total Tenders Posted</span>
                                <span class="stat-value"><?php echo $open_tenders_count + 50; ?>+</span>
                            </div>
                            <div class="stat-row">
                                <span class="stat-label">Success Rate</span>
                                <span class="stat-value">98%</span>
                            </div>
                            <div class="stat-row">
                                <span class="stat-label">Average Response Time</span>
                                <span class="stat-value">< 24hrs</span>
                            </div>
                            <div class="stat-row">
                                <span class="stat-label">User Satisfaction</span>
                                <span class="stat-value">4.9/5</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact-section">
        <div class="container">
            <div class="section-header">
                <h2>Get In Touch</h2>
                <p>Have questions about our procurement process? We're here to help.</p>
            </div>
            
            <div class="contact-content">
                <div class="contact-info">
                    <div class="contact-item">
                        <div class="contact-icon">📍</div>
                        <div class="contact-details">
                            <h4>Address</h4>
                            <p>123 Procurement Street<br>Business District<br>Nairobi, Kenya</p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">📞</div>
                        <div class="contact-details">
                            <h4>Phone</h4>
                            <p>+254 700 123 456<br>+254 711 987 654</p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">✉️</div>
                        <div class="contact-details">
                            <h4>Email</h4>
                            <p>info@procurement.gov.ke<br>support@procurement.gov.ke</p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">🕒</div>
                        <div class="contact-details">
                            <h4>Business Hours</h4>
                            <p>Monday - Friday: 8:00 AM - 5:00 PM<br>Saturday: 9:00 AM - 1:00 PM</p>
                        </div>
                    </div>
                </div>
                
                <div class="contact-form">
                    <h3>Send us a Message</h3>
                    <form id="contactForm">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Full Name</label>
                                <input type="text" id="name" name="name" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <input type="text" id="subject" name="subject" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" rows="5" required></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Procurement System</h3>
                    <p>Promoting transparency and efficiency in public and private procurement processes.</p>
                    <div class="social-links">
                        <a href="#" class="social-link">📘</a>
                        <a href="#" class="social-link">🐦</a>
                        <a href="#" class="social-link">💼</a>
                        <a href="#" class="social-link">📧</a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="#home">Home</a></li>
                        <li><a href="#tenders">Current Tenders</a></li>
                        <li><a href="all-tenders.php">All Tenders</a></li>
                        <li><a href="#about">About Us</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Resources</h4>
                    <ul class="footer-links">
                        <li><a href="#">Bidding Guidelines</a></li>
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Terms & Conditions</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Contact Info</h4>
                    <div class="footer-contact">
                        <p>📞 +254 700 123 456</p>
                        <p>✉️ info@procurement.gov.ke</p>
                        <p>📍 Nairobi, Kenya</p>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Procurement Management System. All rights reserved.</p>
                <p>Built with transparency and efficiency in mind.</p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile navigation toggle
        const hamburger = document.querySelector('.hamburger');
        const navMenu = document.querySelector('.nav-menu');
        
        hamburger.addEventListener('click', () => {
            hamburger.classList.toggle('active');
            navMenu.classList.toggle('active');
        });
        
        // Close mobile menu when clicking on a link
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                hamburger.classList.remove('active');
                navMenu.classList.remove('active');
            });
        });
        
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // Contact form handling
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Simple form validation
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const subject = document.getElementById('subject').value.trim();
            const message = document.getElementById('message').value.trim();
            
            if (!name || !email || !subject || !message) {
                alert('Please fill in all fields');
                return;
            }
            
            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                alert('Please enter a valid email address');
                return;
            }
            
            // Simulate form submission
            alert('Thank you for your message! We will get back to you soon.');
            this.reset();
        });
        
        // Add animation to cards on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);
        
        // Observe tender cards and feature items
        document.querySelectorAll('.tender-card, .feature-item').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(card);
        });
        
        // Update days left with color coding
        document.querySelectorAll('.days-left').forEach(element => {
            const daysText = element.textContent;
            const days = parseInt(daysText);
            
            if (days <= 3) {
                element.style.color = '#dc3545';
                element.style.fontWeight = 'bold';
            } else if (days <= 7) {
                element.style.color = '#fd7e14';
                element.style.fontWeight = 'bold';
            } else {
                element.style.color = '#28a745';
            }
        });
        
        // Auto-refresh tender data every 5 minutes
        setInterval(() => {
            // In a real application, you might want to fetch updated data via AJAX
            console.log('Checking for tender updates...');
        }, 300000);
    </script>
</body>
</html>