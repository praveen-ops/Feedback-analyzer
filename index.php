<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Feedback | FeedbackAI</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <!-- Add modern icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">FeedbackAI</div>
            <nav class="nav-menu">
                <a href="home.php" class="nav-link">Home</a>
                <a href="index.php" class="nav-link active">Submit</a>
                <a href="view.php" class="nav-link">Analytics</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="page-header">
            <h1>Submit Customer Feedback</h1>
            <p class="subtitle">Our AI will analyze your feedback for sentiment and extract key insights.</p>
        </div>
        
        <?php if (isset($_SESSION['message'])): ?>
            <?php $message_type = $_SESSION['message_type'] ?? 'info'; ?>
            <div class="alert alert-<?php echo htmlspecialchars($message_type); ?> fade-in">
                <div class="alert-icon">
                    <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'info-circle'; ?>"></i>
                </div>
                <div class="alert-content">
                    <?php echo htmlspecialchars($_SESSION['message']); ?>
                </div>
                <button class="alert-close" onclick="this.parentElement.style.display='none';">×</button>
            </div>
            <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
        <?php endif; ?>

        <div class="card form-card">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-comment-dots"></i></div>
                <h2>Feedback Form</h2>
            </div>
            
            <div class="card-body">
                <form action="submit_feedback.php" method="POST" id="feedback-form">
                    <div class="form-group">
                        <label for="feedback_text">Feedback Text</label>
                        <div class="textarea-container">
                            <textarea 
                                id="feedback_text" 
                                name="feedback_text" 
                                rows="8" 
                                required 
                                placeholder="Enter customer feedback here..."
                                maxlength="5000"
                            ></textarea>
                            <div class="char-counter">
                                <span id="char-count">0</span>/5000
                            </div>
                        </div>
                        <div class="form-hint">
                            <i class="fas fa-lightbulb"></i> 
                            <span>For best results, include specific details about products, services, or interactions.</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="source">Source (Optional)</label>
                        <div class="input-with-icon">
                            <i class="fas fa-tag"></i>
                            <input 
                                type="text" 
                                id="source" 
                                name="source" 
                                placeholder="e.g., Web Form, Email, Survey, Support Chat"
                            >
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="clearForm()">
                            <i class="fas fa-undo"></i> Clear
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Submit &amp; Analyze
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="card-footer">
                <div class="ai-badge">
                    <i class="fas fa-robot"></i> Powered by Google Gemini AI
                </div>
            </div>
        </div>
        
        <div class="info-section">
            <div class="info-card">
                <div class="info-icon"><i class="fas fa-shield-alt"></i></div>
                <div class="info-content">
                    <h3>Data Privacy</h3>
                    <p>Your feedback data is securely processed and stored.</p>
                </div>
            </div>
            
            <div class="info-card">
                <div class="info-icon"><i class="fas fa-bolt"></i></div>
                <div class="info-content">
                    <h3>Real-time Analysis</h3>
                    <p>Results are processed immediately using AI technology.</p>
                </div>
            </div>
            
            <div class="info-card">
                <div class="info-icon"><i class="fas fa-chart-line"></i></div>
                <div class="info-content">
                    <h3>View Analytics</h3>
                    <p>Check insights in the <a href="view.php">Analytics Dashboard</a>.</p>
                </div>
            </div>
        </div>
    </div>
    
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>FeedbackAI</h3>
                    <p>Advanced customer feedback analysis system powered by Google Gemini AI.</p>
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

    <script>
        // Character counter for textarea
        document.getElementById('feedback_text').addEventListener('input', function() {
            const charCount = this.value.length;
            document.getElementById('char-count').textContent = charCount;
            
            // Visual feedback when approaching limit
            const counter = document.querySelector('.char-counter');
            if (charCount > 4500) {
                counter.classList.add('near-limit');
            } else {
                counter.classList.remove('near-limit');
            }
        });
        
        // Clear form function
        function clearForm() {
            document.getElementById('feedback-form').reset();
            document.getElementById('char-count').textContent = '0';
            document.querySelector('.char-counter').classList.remove('near-limit');
        }
        
        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => alert.style.display = 'none', 500);
            });
        }, 5000);
    </script>
</body>
</html>