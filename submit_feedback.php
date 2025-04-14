<?php
session_start(); // Start session for flash messages
require_once 'includes/db_connect.php';
require_once 'includes/gemini_analyzer.php'; // Include the analyzer function

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Get and Validate Input ---
    $feedbackText = trim($_POST['feedback_text'] ?? '');
    $source = trim($_POST['source'] ?? ''); // Optional source field

    if (empty($feedbackText)) {
        $_SESSION['message'] = 'Feedback text cannot be empty.';
        $_SESSION['message_type'] = 'error';
        header('Location: index.php');
        exit;
    }

    // Basic sanitization (consider more robust sanitization/validation if needed)
    $feedbackText = htmlspecialchars($feedbackText, ENT_QUOTES, 'UTF-8');
    $source = !empty($source) ? htmlspecialchars($source, ENT_QUOTES, 'UTF-8') : null;

    $feedbackId = null; // To store the ID of the inserted record

    // --- 1. Initial Database Insert (Mark as Processing) ---
    try {
        $sql = "INSERT INTO feedback_entries (feedback_text, source, analysis_status) VALUES (?, ?, 'Processing')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$feedbackText, $source]);
        $feedbackId = $pdo->lastInsertId(); // Get the ID of this new entry
    } catch (PDOException $e) {
        error_log("DB Insert Error: " . $e->getMessage());
        $_SESSION['message'] = 'Error saving feedback initially. Please try again.';
        $_SESSION['message_type'] = 'error';
        header('Location: index.php');
        exit;
    }

    // --- 2. Call Gemini API Analyzer ---
    if ($feedbackId) {
        $analysisResult = analyze_feedback_with_gemini($feedbackText); // Pass the raw text

        // --- 3. Update Database with Analysis Results ---
        try {
            $updateSql = "";
            $params = [];

            if ($analysisResult['status'] === 'success') {
                $updateSql = "UPDATE feedback_entries SET
                                sentiment_label = ?,
                                extracted_info = ?,
                                analysis_status = 'Completed',
                                processing_timestamp = NOW(),
                                error_message = NULL,
                                api_model_used = 'gemini-pro' /* Or derive dynamically if needed */
                              WHERE id = ?";
                $params = [
                    $analysisResult['data']['sentiment'],
                    $analysisResult['data']['keywords_json'], // Store the JSON string
                    $feedbackId
                ];
                $_SESSION['message'] = 'Feedback submitted and analyzed successfully!';
                $_SESSION['message_type'] = 'success';

            } else { // Analysis failed
                $updateSql = "UPDATE feedback_entries SET
                                analysis_status = 'API_Error',
                                processing_timestamp = NOW(),
                                error_message = ?
                              WHERE id = ?";
                $params = [
                    $analysisResult['message'], // Store the error message
                    $feedbackId
                ];
                $_SESSION['message'] = 'Feedback submitted, but analysis failed: ' . htmlspecialchars($analysisResult['message']);
                $_SESSION['message_type'] = 'error';
            }

            $stmt = $pdo->prepare($updateSql);
            $stmt->execute($params);

        } catch (PDOException $e) {
            error_log("DB Update Error after analysis: " . $e->getMessage());
            // Update status to DB_Error if possible, otherwise just set session message
             try {
                $errorStmt = $pdo->prepare("UPDATE feedback_entries SET analysis_status = 'DB_Error', error_message = ? WHERE id = ?");
                $errorStmt->execute(['Database update failed after analysis.', $feedbackId]);
             } catch (PDOException $innerE) {
                 error_log("DB Update Error (nested): " . $innerE->getMessage());
             }
             $_SESSION['message'] = 'Error updating feedback after analysis. Please check logs.';
             $_SESSION['message_type'] = 'error';
        }
    }

    // --- 4. Redirect ---
    // Redirect back to the form page (or could redirect to view.php)
    header('Location: index.php');
    exit;

} else {
    // Not a POST request, redirect to form
    $_SESSION['message'] = 'Invalid request method.';
    $_SESSION['message_type'] = 'error';
    header('Location: index.php');
    exit;
}
?>