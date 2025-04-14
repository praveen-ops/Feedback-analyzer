<?php
/**
 * Database connection script
 * 
 * @var string $DB_HOST Database host from config.php
 * @var string $DB_NAME Database name from config.php
 * @var string $DB_USER Database username from config.php
 * @var string $DB_PASS Database password from config.php
 * @var string $DB_CHARSET Database charset from config.php
 */

// Load config
if (file_exists(__DIR__ . '/../config.php')) {
    require_once __DIR__ . '/../config.php';
} elseif (file_exists(__DIR__ . '/../../config.php')) { // If included from deeper file
     require_once __DIR__ . '/../../config.php';
} else {
     die('ERROR: Configuration file not found.');
}

$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Use native prepared statements
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (\PDOException $e) {
    // In production, log the error instead of showing details to the user
    error_log("Database Connection Error: " . $e->getMessage());
    // Display a generic error message to the user
    die("Database connection failed. Please try again later or contact support.");
}

// $pdo variable is now available for use in scripts that include this file.
?>