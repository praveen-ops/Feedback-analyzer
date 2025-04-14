<?php
/**
 * Configuration File
 *
 * IMPORTANT:
 * 1. Fill in your actual Gemini API Key.
 * 2. Keep this file secure and DO NOT commit it to public repositories if it contains sensitive keys.
 * 3. Ensure your database credentials match your XAMPP MySQL setup (defaults are often 'root' with no password).
 */

// --- Gemini API Configuration ---
define('GEMINI_API_KEY', 'AIzaSyD7roQlayvnjQRp88Ej-BsQYGMnk_Ja9xw'); // <--- PUT YOUR KEY HERE
define('GEMINI_API_ENDPOINT', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent');

// --- Database Configuration ---
define('DB_HOST', 'localhost');          // Usually 'localhost' for XAMPP
define('DB_NAME', 'feedback');        // The database name you created
define('DB_USER', 'root');               // Default XAMPP username
define('DB_PASS', '');                   // Default XAMPP password (often empty)
define('DB_CHARSET', 'utf8mb4');

// --- Application Settings ---
define('APP_NAME', 'Customer Feedback Analyzer');

// Optional: Set default timezone (important for timestamps)
date_default_timezone_set('UTC'); // Or your preferred timezone, e.g., 'America/New_York'

?>