<?php
// Ensure config is loaded
if (!defined('DB_HOST')) {  // Check if config constants are already defined
    if (file_exists(__DIR__ . '/../config.php')) {
        require_once __DIR__ . '/../config.php';
    } elseif (file_exists(__DIR__ . '/../../config.php')) { // Try one level up if needed
        require_once __DIR__ . '/../../config.php';
    } else {
        die('ERROR: Configuration file not found in analyzer.');
    }
}

/**
 * Analyzes feedback text using the Google Gemini API.
 *
 * @param string $text The customer feedback text.
 * @return array Associative array with 'status' ('success' or 'error')
 *               and 'data' (containing 'sentiment', 'keywords') or 'message'.
 */
function analyze_feedback_with_gemini(string $text): array
{
    $apiKey = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : null;
    $apiUrl = defined('GEMINI_API_ENDPOINT') ? GEMINI_API_ENDPOINT : null;

    if (!$apiKey || $apiKey === 'YOUR_ACTUAL_GEMINI_API_KEY_HERE') {
        return ['status' => 'error', 'message' => 'API Key not configured.'];
    }
    if (!$apiUrl) {
        return ['status' => 'error', 'message' => 'API Endpoint not configured.'];
    }

    // --- Construct the Prompt ---
    $prompt = "Please analyze the sentiment of this customer feedback and extract 3-5 key topics.

Customer feedback: " . json_encode($text) . "

Respond ONLY with a valid JSON object with this exact structure:
{
  \"sentiment\": \"POSITIVE\", (must be exactly one of: POSITIVE, NEGATIVE, or NEUTRAL)
  \"keywords\": [\"keyword1\", \"keyword2\", \"keyword3\"] (an array of 3-5 most important topics)
}

Do not include any explanations, additional text, or markdown formatting - ONLY return the JSON object.";

    // --- Prepare API Request Data ---
    $data = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $prompt]
                ]
            ]
        ],
        'generationConfig' => [
            'temperature' => 0.2,  // Lower temperature for more predictable responses
            'topP' => 0.95,
            'topK' => 40
        ]
    ];

    $jsonData = json_encode($data);

    // --- Use cURL to make the API request ---
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $apiUrl . '?key=' . $apiKey);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    // Important for HTTPS verification
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Keep true in production
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);    // Keep true in production
    curl_setopt($ch, CURLOPT_TIMEOUT, 45); // Set timeout (e.g., 45 seconds)


    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    // --- Process the API Response ---
    if ($curlError) {
        error_log("cURL Error: " . $curlError);
        return ['status' => 'error', 'message' => "cURL Error: " . $curlError];
    }

    if ($httpCode !== 200) {
        // Try to decode error message from Gemini if available
        $errorDetails = json_decode($response, true);
        $errorMessage = $errorDetails['error']['message'] ?? 'API request failed';
        error_log("API Error (HTTP {$httpCode}): " . $errorMessage);
        error_log("Raw Response: " . $response);
        return ['status' => 'error', 'message' => "API Error (HTTP {$httpCode}): " . $errorMessage, 'raw_response' => $response];
    }

    $responseData = json_decode($response, true);
    
    // Debug: Log the full response for troubleshooting
    error_log("Gemini API Raw Response: " . $response);

    // --- Safely Extract Data from Gemini's Response Structure ---
    if (!isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
        $finishReason = $responseData['candidates'][0]['finishReason'] ?? 'UNKNOWN';
        error_log("API response structure unexpected. Finish reason: {$finishReason}");
        error_log("Full response: " . json_encode($responseData));
        
        if ($finishReason !== 'STOP') {
             return ['status' => 'error', 'message' => "API Error: Content generation stopped due to {$finishReason}.", 'raw_response' => $response];
        }
        return ['status' => 'error', 'message' => 'Could not parse API response structure.', 'raw_response' => $response];
    }

    $generatedText = $responseData['candidates'][0]['content']['parts'][0]['text'];
    
    // Log the text content for debugging
    error_log("Generated text from Gemini: " . $generatedText);

    // Try to clean the response text before JSON parsing
    $cleanedText = trim($generatedText);
    // Remove any markdown code fences if present
    $cleanedText = preg_replace('/^```json\s*|\s*```$/m', '', $cleanedText);
    // Remove any potential explanatory text before/after JSON
    if (strpos($cleanedText, '{') !== false) {
        $cleanedText = substr($cleanedText, strpos($cleanedText, '{'));
        if (strrpos($cleanedText, '}') !== false) {
            $cleanedText = substr($cleanedText, 0, strrpos($cleanedText, '}') + 1);
        }
    }

    // Attempt to decode the JSON *within* the text part
    $analysisResult = json_decode($cleanedText, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Failed to parse JSON. Error: " . json_last_error_msg());
        error_log("Cleaned text attempted to parse: " . $cleanedText);
        return [
            'status' => 'error', 
            'message' => 'API response content was not valid JSON: ' . json_last_error_msg(), 
            'raw_response' => $generatedText
        ];
    }

    // Extract sentiment and keywords safely
    $sentiment = $analysisResult['sentiment'] ?? 'Unknown';
    $keywords = $analysisResult['keywords'] ?? [];

    // Optional: Validate sentiment value
    $validSentiments = ['POSITIVE', 'NEGATIVE', 'NEUTRAL', 'Unknown'];
     if (!in_array(strtoupper($sentiment), $validSentiments)) {
        $sentiment = 'Unknown'; // Default if invalid value returned
     }

    return [
        'status' => 'success',
        'data' => [
            'sentiment' => strtoupper($sentiment),
            // Ensure keywords are returned as JSON string for DB storage
            'keywords_json' => json_encode($keywords) // Store as JSON string
        ]
    ];
}
?>