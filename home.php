<?php session_start(); // Start session to access flash messages ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Feedback Analytics</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">FeedbackAI</div>
            <nav class="nav-menu">
                <a href="home.php" class="nav-link">Home</a>
                <a href="index.php" class="nav-link">Submit</a>
                <a href="view.php" class="nav-link">Analytics</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="hero-section">
            <h1>Customer Feedback Analytics</h1>
            <p>Harness the power of AI to understand customer sentiment and extract valuable insights from your feedback.</p>
            
            <div class="cta-buttons">
                <a href="index.php" class="btn btn-primary">Submit Feedback</a>
                <a href="view.php" class="btn btn-secondary">View Analytics</a>
            </div>
        </div>
        
        <div class="features-section">
            <h2>Key Features</h2>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3>Sentiment Analysis</h3>
                    <p>Automatically classify feedback as positive, negative, or neutral using Google's Gemini AI.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">🔑</div>
                    <h3>Keyword Extraction</h3>
                    <p>Identify the most important topics and issues mentioned in customer feedback.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">📈</div>
                    <h3>Trend Visualization</h3>
                    <p>Track sentiment trends over time to measure customer satisfaction.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">🔍</div>
                    <h3>Detailed Insights</h3>
                    <p>Get a comprehensive view of what your customers are saying about your products or services.</p>
                </div>
            </div>
        </div>
        
        <div class="how-it-works">
            <h2>How It Works</h2>
            <div class="steps">
                <div class="step">
                    <div class="step-number">1</div>
                    <h3>Submit Feedback</h3>
                    <p>Enter customer feedback from various sources like surveys, emails, or support tickets.</p>
                </div>
                
                <div class="step">
                    <div class="step-number">2</div>
                    <h3>AI Analysis</h3>
                    <p>Our system processes the feedback using Google's Gemini AI for sentiment and keyword extraction.</p>
                </div>
                
                <div class="step">
                    <div class="step-number">3</div>
                    <h3>View Results</h3>
                    <p>Access comprehensive analytics and insights through the dashboard.</p>
                </div>
            </div>
        </div>
    </div>
    
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>FeedbackAI</h3>
                    <p>Harness the power of AI to understand customer sentiment and extract valuable insights.</p>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="home.php">Home</a></li>
                        <li><a href="index.php">Submit Feedback</a></li>
                        <li><a href="view.php">View Analytics</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-copyright">
                &copy; <?php echo date('Y'); ?> FeedbackAI. All rights reserved.
            </div>
        </div>
    </footer>
</body>
</html>