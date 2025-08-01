<?php
/**
 * Sitemap Generator for Procurement System
 * Run this script to generate sitemap.xml
 * Access: yourdomain.com/generate-sitemap.php
 */

require_once 'config/database.php';

// Set content type for XML
header('Content-Type: application/xml; charset=utf-8');

// Your domain (change this to your actual domain)
$domain = 'https://yourdomain.com';

// Start XML sitemap
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

// Static pages
$static_pages = [
    [
        'url' => '/',
        'changefreq' => 'daily',
        'priority' => '1.0',
        'lastmod' => date('Y-m-d')
    ],
    [
        'url' => '/tenders',
        'changefreq' => 'daily',
        'priority' => '0.9',
        'lastmod' => date('Y-m-d')
    ]
];

// Add static pages to sitemap
foreach ($static_pages as $page) {
    echo "  <url>\n";
    echo "    <loc>{$domain}{$page['url']}</loc>\n";
    echo "    <lastmod>{$page['lastmod']}</lastmod>\n";
    echo "    <changefreq>{$page['changefreq']}</changefreq>\n";
    echo "    <priority>{$page['priority']}</priority>\n";
    echo "  </url>\n";
}

// Add dynamic tender pages
try {
    $db = getDB();
    
    // Get all public tenders
    $stmt = $db->query("SELECT id, updated_at FROM tenders WHERE status = 'open' ORDER BY updated_at DESC");
    $tenders = $stmt->fetchAll();
    
    foreach ($tenders as $tender) {
        $lastmod = date('Y-m-d', strtotime($tender['updated_at']));
        echo "  <url>\n";
        echo "    <loc>{$domain}/tender/{$tender['id']}</loc>\n";
        echo "    <lastmod>{$lastmod}</lastmod>\n";
        echo "    <changefreq>weekly</changefreq>\n";
        echo "    <priority>0.8</priority>\n";
        echo "  </url>\n";
    }
    
} catch(PDOException $e) {
    // If database error, just continue with static pages
    echo "<!-- Database error: " . htmlspecialchars($e->getMessage()) . " -->\n";
}

// Close sitemap
echo '</urlset>' . "\n";

// Save to file (optional)
$sitemap_content = ob_get_contents();
if (!headers_sent()) {
    file_put_contents('sitemap.xml', $sitemap_content);
    echo "<!-- Sitemap saved to sitemap.xml -->\n";
}
?>