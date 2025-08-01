<?php
/**
 * All Tenders Page
 * Procurement Management System
 */

require_once 'config/database.php';

// Pagination settings
$tenders_per_page = 12;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $tenders_per_page;

// Filter settings
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'deadline';

try {
    $db = getDB();
    
    // Build WHERE clause
    $where_conditions = [];
    $params = [];
    
    if ($status_filter !== 'all') {
        $where_conditions[] = "status = ?";
        $params[] = $status_filter;
    }
    
    if (!empty($search_query)) {
        $where_conditions[] = "(title LIKE ? OR description LIKE ?)";
        $params[] = '%' . $search_query . '%';
        $params[] = '%' . $search_query . '%';
    }
    
    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // Build ORDER BY clause
    $order_by = match($sort_by) {
        'title' => 'ORDER BY title ASC',
        'budget_high' => 'ORDER BY budget DESC',
        'budget_low' => 'ORDER BY budget ASC',
        'newest' => 'ORDER BY created_at DESC',
        'oldest' => 'ORDER BY created_at ASC',
        default => 'ORDER BY deadline ASC'
    };
    
    // Get total count for pagination
    $count_query = "SELECT COUNT(*) as total FROM tenders $where_clause";
    $stmt = $db->prepare($count_query);
    $stmt->execute($params);
    $total_tenders = $stmt->fetch()['total'];
    $total_pages = ceil($total_tenders / $tenders_per_page);
    
    // Get tenders for current page
    $tenders_query = "SELECT * FROM tenders $where_clause $order_by LIMIT $tenders_per_page OFFSET $offset";
    $stmt = $db->prepare($tenders_query);
    $stmt->execute($params);
    $tenders = $stmt->fetchAll();
    
    // Get status counts for filter tabs
    $status_counts = [];
    $status_query = "SELECT status, COUNT(*) as count FROM tenders GROUP BY status";
    $stmt = $db->query($status_query);
    while ($row = $stmt->fetch()) {
        $status_counts[$row['status']] = $row['count'];
    }
    
    $status_counts['all'] = array_sum($status_counts);
    
} catch(PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    $tenders = [];
    $total_pages = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Tenders - Procurement System</title>
    <link rel="stylesheet" href="style.css">
    <meta name="description" content="Browse all available procurement tenders and opportunities">
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
                <li><a href="index.php#tenders" class="nav-link">Featured Tenders</a></li>
                <li><a href="all-tenders.php" class="nav-link active">All Tenders</a></li>
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

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <div class="header-content">
                <h1>All Procurement Tenders</h1>
                <p>Browse through all available procurement opportunities</p>
                <div class="header-stats">
                    <span class="stat-item">
                        <strong><?php echo $total_tenders; ?></strong> Total Tenders
                    </span>
                    <span class="stat-item">
                        <strong><?php echo $status_counts['open'] ?? 0; ?></strong> Currently Open
                    </span>
                </div>
            </div>
        </div>
    </section>

    <!-- Filters and Search -->
    <section class="filters-section">
        <div class="container">
            <div class="filters-container">
                <!-- Search Form -->
                <form method="GET" class="search-form-inline">
                    <div class="search-input-group">
                        <input type="text" name="search" placeholder="Search tenders..." 
                               value="<?php echo htmlspecialchars($search_query); ?>">
                        <button type="submit" class="search-btn">Search</button>
                    </div>
                    
                    <!-- Preserve other parameters -->
                    <?php if ($status_filter !== 'all'): ?>
                        <input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>">
                    <?php endif; ?>
                    <?php if ($sort_by !== 'deadline'): ?>
                        <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort_by); ?>">
                    <?php endif; ?>
                </form>

                <!-- Sort Options -->
                <div class="sort-options">
                    <label for="sort-select">Sort by:</label>
                    <select id="sort-select" onchange="updateSort(this.value)">
                        <option value="deadline" <?php echo $sort_by === 'deadline' ? 'selected' : ''; ?>>Deadline (Earliest)</option>
                        <option value="newest" <?php echo $sort_by === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                        <option value="oldest" <?php echo $sort_by === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                        <option value="title" <?php echo $sort_by === 'title' ? 'selected' : ''; ?>>Title (A-Z)</option>
                        <option value="budget_high" <?php echo $sort_by === 'budget_high' ? 'selected' : ''; ?>>Budget (High to Low)</option>
                        <option value="budget_low" <?php echo $sort_by === 'budget_low' ? 'selected' : ''; ?>>Budget (Low to High)</option>
                    </select>
                </div>
            </div>

            <!-- Status Filter Tabs -->
            <div class="filter-tabs">
                <a href="?<?php echo http_build_query(array_merge($_GET, ['status' => 'all', 'page' => 1])); ?>" 
                   class="filter-tab <?php echo $status_filter === 'all' ? 'active' : ''; ?>">
                   All (<?php echo $status_counts['all'] ?? 0; ?>)
                </a>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['status' => 'open', 'page' => 1])); ?>" 
                   class="filter-tab <?php echo $status_filter === 'open' ? 'active' : ''; ?>">
                   Open (<?php echo $status_counts['open'] ?? 0; ?>)
                </a>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['status' => 'closed', 'page' => 1])); ?>" 
                   class="filter-tab <?php echo $status_filter === 'closed' ? 'active' : ''; ?>">
                   Closed (<?php echo $status_counts['closed'] ?? 0; ?>)
                </a>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['status' => 'awarded', 'page' => 1])); ?>" 
                   class="filter-tab <?php echo $status_filter === 'awarded' ? 'active' : ''; ?>">
                   Awarded (<?php echo $status_counts['awarded'] ?? 0; ?>)
                </a>
            </div>
        </div>
    </section>

    <!-- Tenders Grid -->
    <section class="tenders-listing">
        <div class="container">
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if (!empty($search_query)): ?>
                <div class="search-results-header">
                    <h3>Search Results for "<?php echo htmlspecialchars($search_query); ?>"</h3>
                    <p>Found <?php echo $total_tenders; ?> tender(s)</p>
                    <a href="all-tenders.php" class="clear-search">Clear Search</a>
                </div>
            <?php endif; ?>

            <?php if (!empty($tenders)): ?>
                <div class="tenders-grid">
                    <?php foreach ($tenders as $tender): ?>
                        <?php
                        $deadline = new DateTime($tender['deadline']);
                        $today = new DateTime();
                        $days_remaining = $today->diff($deadline)->days;
                        $is_expired = $today > $deadline;
                        ?>
                        <div class="tender-card <?php echo $tender['status']; ?>">
                            <div class="tender-card-header">
                                <h3><?php echo htmlspecialchars($tender['title']); ?></h3>
                                <span class="tender-status status-<?php echo $tender['status']; ?>">
                                    <?php echo ucfirst($tender['status']); ?>
                                </span>
                            </div>
                            
                            <div class="tender-card-body">
                                <p class="tender-description">
                                    <?php echo htmlspecialchars(substr($tender['description'], 0, 120)) . (strlen($tender['description']) > 120 ? '...' : ''); ?>
                                </p>
                                
                                <div class="tender-details-grid">
                                    <div class="detail-item">
                                        <span class="detail-label">Budget:</span>
                                        <span class="detail-value budget">$<?php echo number_format($tender['budget'], 2); ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Deadline:</span>
                                        <span class="detail-value deadline">
                                            <?php echo date('M j, Y', strtotime($tender['deadline'])); ?>
                                        </span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Posted:</span>
                                        <span class="detail-value">
                                            <?php echo date('M j, Y', strtotime($tender['created_at'])); ?>
                                        </span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">
                                            <?php echo $is_expired ? 'Expired:' : 'Time Left:'; ?>
                                        </span>
                                        <span class="detail-value time-left <?php echo $is_expired ? 'expired' : ($days_remaining <= 3 ? 'urgent' : ($days_remaining <= 7 ? 'warning' : 'normal')); ?>">
                                            <?php echo $is_expired ? 'Closed' : $days_remaining . ' days'; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="tender-card-footer">
                                <a href="tender-details.php?id=<?php echo $tender['id']; ?>" class="btn btn-primary">
                                    View Details
                                </a>
                                <span class="tender-id">ID: #<?php echo $tender['id']; ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination-container">
                        <nav class="pagination">
                            <?php if ($current_page > 1): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>" class="pagination-link first">
                                    First
                                </a>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $current_page - 1])); ?>" class="pagination-link prev">
                                    Previous
                                </a>
                            <?php endif; ?>
                            
                            <?php
                            $start_page = max(1, $current_page - 2);
                            $end_page = min($total_pages, $current_page + 2);
                            
                            for ($i = $start_page; $i <= $end_page; $i++):
                            ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                                   class="pagination-link <?php echo $i === $current_page ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($current_page < $total_pages): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $current_page + 1])); ?>" class="pagination-link next">
                                    Next
                                </a>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $total_pages])); ?>" class="pagination-link last">
                                    Last
                                </a>
                            <?php endif; ?>
                        </nav>
                        
                        <div class="pagination-info">
                            Showing <?php echo $offset + 1; ?> - <?php echo min($offset + $tenders_per_page, $total_tenders); ?> 
                            of <?php echo $total_tenders; ?> tenders
                        </div>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="no-tenders-found">
                    <div class="no-tenders-icon">🔍</div>
                    <h3>No Tenders Found</h3>
                    <?php if (!empty($search_query)): ?>
                        <p>No tenders match your search criteria. Try adjusting your search terms.</p>
                        <a href="all-tenders.php" class="btn btn-primary">View All Tenders</a>
                    <?php elseif ($status_filter !== 'all'): ?>
                        <p>No tenders found with status "<?php echo ucfirst($status_filter); ?>".</p>
                        <a href="all-tenders.php" class="btn btn-primary">View All Tenders</a>
                    <?php else: ?>
                        <p>There are currently no tenders available. Please check back later.</p>
                        <a href="index.php" class="btn btn-primary">Return to Home</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Procurement System</h3>
                    <p>Your trusted platform for transparent procurement processes.</p>
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
                    <h4>Contact Info</h4>
                    <p>📞 +254 700 123 456</p>
                    <p>✉️ info@procurement.gov.ke</p>
                    <p>📍 Nairobi, Kenya</p>
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

        // Sort functionality
        function updateSort(sortValue) {
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('sort', sortValue);
            urlParams.set('page', '1'); // Reset to first page
            window.location.search = urlParams.toString();
        }

        // Search form enhancement
        document.querySelector('.search-form-inline').addEventListener('submit', function(e) {
            const searchInput = this.querySelector('input[name="search"]');
            if (!searchInput.value.trim()) {
                e.preventDefault();
                // Remove search parameter and reload
                const urlParams = new URLSearchParams(window.location.search);
                urlParams.delete('search');
                urlParams.set('page', '1');
                window.location.search = urlParams.toString();
            }
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + K to focus search
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                document.querySelector('input[name="search"]').focus();
            }
        });

        // Infinite scroll (optional enhancement)
        let isLoading = false;
        const loadMoreTenders = () => {
            if (isLoading) return;
            
            const scrollPosition = window.innerHeight + window.scrollY;
            const documentHeight = document.body.offsetHeight;
            
            if (scrollPosition >= documentHeight - 1000) {
                // Load more logic would go here
                console.log('Near bottom of page - could load more tenders');
            }
        };

        // Uncomment to enable infinite scroll
        // window.addEventListener('scroll', loadMoreTenders);

        // Card hover effects
        document.querySelectorAll('.tender-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });

        // Update time left indicators with color coding
        document.querySelectorAll('.time-left').forEach(element => {
            const text = element.textContent.trim();
            if (text.includes('days')) {
                const days = parseInt(text);
                if (days <= 3) {
                    element.classList.add('urgent');
                } else if (days <= 7) {
                    element.classList.add('warning');
                } else {
                    element.classList.add('normal');
                }
            }
        });

        // Auto-refresh page every 10 minutes to update tender status
        setInterval(() => {
            console.log('Checking for updates...');
            // In production, you might want to use AJAX to update only changed data
        }, 600000);
    </script>
</body>
</html>