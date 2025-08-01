<?php
/**
 * Database Backup Script
 * Creates a SQL backup of the procurement database
 * 
 * SECURITY: Only run this script from admin panel or with proper authentication
 * Access: yoursite.com/backup-database.php?key=your_secret_key
 */

// Simple security check (change this key)
$backup_key = 'procurement_backup_2024';
if (!isset($_GET['key']) || $_GET['key'] !== $backup_key) {
    die('Unauthorized access');
}

require_once 'config/database.php';

// Database credentials
$host = DB_HOST;
$username = DB_USERNAME;
$password = DB_PASSWORD;
$database = DB_NAME;

// Backup settings
$backup_dir = 'backups/';
$backup_filename = 'procurement_backup_' . date('Y-m-d_H-i-s') . '.sql';

// Create backup directory if it doesn't exist
if (!is_dir($backup_dir)) {
    mkdir($backup_dir, 0755, true);
}

// Create .htaccess to protect backup directory
$htaccess_content = "Order deny,allow\nDeny from all";
file_put_contents($backup_dir . '.htaccess', $htaccess_content);

try {
    $db = getDB();
    
    // Get all tables
    $tables = [];
    $result = $db->query("SHOW TABLES");
    while ($row = $result->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }
    
    $backup_content = "-- Procurement System Database Backup\n";
    $backup_content .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n";
    $backup_content .= "-- Database: {$database}\n\n";
    
    $backup_content .= "SET FOREIGN_KEY_CHECKS=0;\n";
    $backup_content .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
    $backup_content .= "SET AUTOCOMMIT = 0;\n";
    $backup_content .= "START TRANSACTION;\n\n";
    
    foreach ($tables as $table) {
        // Get CREATE TABLE statement
        $create_table = $db->query("SHOW CREATE TABLE `{$table}`")->fetch();
        $backup_content .= "-- Table structure for table `{$table}`\n";
        $backup_content .= "DROP TABLE IF EXISTS `{$table}`;\n";
        $backup_content .= $create_table[1] . ";\n\n";
        
        // Get table data
        $rows = $db->query("SELECT * FROM `{$table}`");
        $num_rows = $rows->rowCount();
        
        if ($num_rows > 0) {
            $backup_content .= "-- Dumping data for table `{$table}`\n";
            
            while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
                $backup_content .= "INSERT INTO `{$table}` (";
                $backup_content .= implode(', ', array_map(function($col) { return "`{$col}`"; }, array_keys($row)));
                $backup_content .= ") VALUES (";
                
                $values = [];
                foreach ($row as $value) {
                    if ($value === null) {
                        $values[] = 'NULL';
                    } else {
                        $values[] = "'" . addslashes($value) . "'";
                    }
                }
                $backup_content .= implode(', ', $values);
                $backup_content .= ");\n";
            }
            $backup_content .= "\n";
        }
    }
    
    $backup_content .= "SET FOREIGN_KEY_CHECKS=1;\n";
    $backup_content .= "COMMIT;\n";
    
    // Save backup file
    $backup_path = $backup_dir . $backup_filename;
    file_put_contents($backup_path, $backup_content);
    
    // Compress backup (if zip extension is available)
    if (extension_loaded('zip')) {
        $zip = new ZipArchive();
        $zip_filename = str_replace('.sql', '.zip', $backup_filename);
        $zip_path = $backup_dir . $zip_filename;
        
        if ($zip->open($zip_path, ZipArchive::CREATE) === TRUE) {
            $zip->addFile($backup_path, $backup_filename);
            $zip->close();
            
            // Remove uncompressed file
            unlink($backup_path);
            $backup_path = $zip_path;
            $backup_filename = $zip_filename;
        }
    }
    
    // Output success message
    $file_size = round(filesize($backup_path) / 1024, 2);
    
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Database Backup Complete</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
            .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; }
            .info { background: #d1ecf1; border: 1px solid #b8daff; color: #0c5460; padding: 15px; border-radius: 5px; margin-top: 20px; }
            .download { background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 15px; }
        </style>
    </head>
    <body>
        <h1>Database Backup Complete</h1>
        <div class='success'>
            <strong>Success!</strong> Database backup created successfully.
        </div>
        <div class='info'>
            <strong>Backup Details:</strong><br>
            File: {$backup_filename}<br>
            Size: {$file_size} KB<br>
            Tables backed up: " . count($tables) . "<br>
            Created: " . date('Y-m-d H:i:s') . "
        </div>
        <a href='{$backup_path}' class='download'>Download Backup</a>
        <p><small>Keep your backup files secure and delete old backups regularly.</small></p>
    </body>
    </html>";
    
} catch(Exception $e) {
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Backup Error</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
            .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; }
        </style>
    </head>
    <body>
        <h1>Backup Error</h1>
        <div class='error'>
            <strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "
        </div>
    </body>
    </html>";
}
?>