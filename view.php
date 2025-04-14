<?php
session_start();
require_once 'includes/db_connect.php'; // Ensure DB connection is available

$feedbackEntries = [];
$errorMessage = '';

// Calculate sentiment statistics
$stats = [
    'total' => 0,
    'positive' => 0,
    'neutral' => 0,
    'negative' => 0
];

try {
    // Fetch all feedback entries, newest first
    $stmt = $pdo->query("SELECT * FROM feedback_entries ORDER BY submission_timestamp DESC");
    $feedbackEntries = $stmt->fetchAll();
    
    // Calculate statistics
    $stats['total'] = count($feedbackEntries);
    foreach ($feedbackEntries as $entry) {
        if (!empty($entry['sentiment_label'])) {
            $sentiment = strtolower($entry['sentiment_label']);
            if (isset($stats[$sentiment])) {
                $stats[$sentiment]++;
            }
        }
    }
} catch (PDOException $e) {
    error_log("DB Select Error: " . $e->getMessage());
    $errorMessage = "Error retrieving feedback entries. Please try again later.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Analytics | FeedbackAI</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">FeedbackAI</div>
            <nav class="nav-menu">
                <a href="home.php" class="nav-link">Home</a>
                <a href="index.php" class="nav-link">Submit</a>
                <a href="view.php" class="nav-link active">Analytics</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="page-header">
            <h1>Feedback Analytics</h1>
            <p class="subtitle">View and analyze all customer feedback and AI-generated insights.</p>
        </div>
        
        <?php if ($errorMessage): ?>
            <div class="alert alert-error fade-in">
                <div class="alert-icon"><i class="fas fa-exclamation-circle"></i></div>
                <div class="alert-content"><?php echo htmlspecialchars($errorMessage); ?></div>
                <button class="alert-close" onclick="this.parentElement.style.display='none';">×</button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($feedbackEntries)): ?>
            <!-- Analytics Dashboard -->
            <div class="analytics-dashboard">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-comments"></i></div>
                    <div class="stat-content">
                        <h3><?php echo $stats['total']; ?></h3>
                        <p>Total Feedback</p>
                    </div>
                </div>
                
                <div class="stat-card positive">
                    <div class="stat-icon"><i class="fas fa-smile"></i></div>
                    <div class="stat-content">
                        <h3><?php echo $stats['positive']; ?></h3>
                        <p>Positive</p>
                    </div>
                </div>
                
                <div class="stat-card neutral">
                    <div class="stat-icon"><i class="fas fa-meh"></i></div>
                    <div class="stat-content">
                        <h3><?php echo $stats['neutral']; ?></h3>
                        <p>Neutral</p>
                    </div>
                </div>
                
                <div class="stat-card negative">
                    <div class="stat-icon"><i class="fas fa-frown"></i></div>
                    <div class="stat-content">
                        <h3><?php echo $stats['negative']; ?></h3>
                        <p>Negative</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Search and Filter Options -->
        <div class="filter-section">
            <div class="search-container">
                <input type="text" id="feedbackSearch" placeholder="Search feedback..." class="search-input">
                <button class="search-btn"><i class="fas fa-search"></i></button>
            </div>
            
            <div class="filter-controls">
                <select id="sentimentFilter" class="filter-select">
                    <option value="">All Sentiments</option>
                    <option value="positive">Positive</option>
                    <option value="neutral">Neutral</option>
                    <option value="negative">Negative</option>
                </select>
                
                <select id="statusFilter" class="filter-select">
                    <option value="">All Statuses</option>
                    <option value="completed">Completed</option>
                    <option value="processing">Processing</option>
                    <option value="api_error">API Error</option>
                    <option value="db_error">DB Error</option>
                </select>
            </div>
        </div>

        <div class="card data-card">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-table"></i></div>
                <h2>Feedback Data</h2>
            </div>
            
            <div class="card-body">
                <?php if (empty($feedbackEntries) && empty($errorMessage)): ?>
                    <div class="empty-state">
                        <div class="empty-icon"><i class="fas fa-inbox"></i></div>
                        <h3>No feedback yet</h3>
                        <p>Submit your first feedback entry to see it here.</p>
                        <a href="index.php" class="btn btn-primary">Submit Feedback</a>
                    </div>
                <?php elseif (!empty($feedbackEntries)): ?>
                    <div class="table-responsive">
                        <table id="feedbackTable">
                            <thead>
                                <tr>
                                    <th class="sortable" data-sort="id">ID <i class="fas fa-sort"></i></th>
                                    <th class="sortable" data-sort="date">Date <i class="fas fa-sort"></i></th>
                                    <th>Source</th>
                                    <th>Feedback</th>
                                    <th>Status</th>
                                    <th class="sortable" data-sort="sentiment">Sentiment <i class="fas fa-sort"></i></th>
                                    <th>Keywords</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($feedbackEntries as $entry): ?>
                                    <tr data-sentiment="<?php echo strtolower($entry['sentiment_label'] ?? ''); ?>" data-status="<?php echo strtolower($entry['analysis_status']); ?>">
                                        <td><?php echo htmlspecialchars($entry['id']); ?></td>
                                        <td data-date="<?php echo strtotime($entry['submission_timestamp']); ?>">
                                            <?php echo htmlspecialchars(date('M d, Y', strtotime($entry['submission_timestamp']))); ?>
                                            <span class="time-small"><?php echo htmlspecialchars(date('H:i', strtotime($entry['submission_timestamp']))); ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($entry['source'] ?? 'N/A'); ?></td>
                                        <td class="feedback-text">
                                            <div class="text-truncate">
                                                <?php echo nl2br(htmlspecialchars($entry['feedback_text'])); ?>
                                            </div>
                                            <button class="btn-text expand-btn">Read more</button>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo strtolower($entry['analysis_status']); ?>">
                                                <?php 
                                                    $statusIcon = 'circle-notch';
                                                    if ($entry['analysis_status'] === 'Completed') $statusIcon = 'check-circle';
                                                    if ($entry['analysis_status'] === 'API_Error') $statusIcon = 'exclamation-triangle';
                                                    if ($entry['analysis_status'] === 'DB_Error') $statusIcon = 'database';
                                                ?>
                                                <i class="fas fa-<?php echo $statusIcon; ?>"></i>
                                                <?php echo htmlspecialchars($entry['analysis_status']); ?>
                                            </span>
                                            <?php if (!empty($entry['error_message'])): ?>
                                                <div class="error-tooltip">
                                                    <i class="fas fa-info-circle"></i>
                                                    <span class="tooltiptext"><?php echo htmlspecialchars($entry['error_message']); ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($entry['sentiment_label'])): ?>
                                                <span class="sentiment-badge sentiment-<?php echo strtolower($entry['sentiment_label']); ?>">
                                                    <?php 
                                                        $sentimentIcon = 'meh';
                                                        if (strtolower($entry['sentiment_label']) === 'positive') $sentimentIcon = 'smile';
                                                        if (strtolower($entry['sentiment_label']) === 'negative') $sentimentIcon = 'frown';
                                                    ?>
                                                    <i class="fas fa-<?php echo $sentimentIcon; ?>"></i>
                                                    <?php echo htmlspecialchars($entry['sentiment_label']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="sentiment-badge sentiment-unknown">
                                                    <i class="fas fa-question-circle"></i>
                                                    Unknown
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="keywords-cell">
                                            <?php
                                            $info = $entry['extracted_info'];
                                            $decoded_keywords = json_decode($info ?? '', true);
                                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded_keywords)) {
                                                $keywords = [];
                                                foreach ($decoded_keywords as $keyword) {
                                                    $keywords[] = '<span class="keyword">' . htmlspecialchars($keyword) . '</span>';
                                                }
                                                echo implode(' ', $keywords);
                                            } elseif (!empty($info)) {
                                                echo htmlspecialchars($info);
                                            } else {
                                                echo '<span class="no-data">-</span>';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($feedbackEntries)): ?>
                <div class="card-footer">
                    <div class="pagination">
                        <button class="btn-page" id="prevPage" disabled><i class="fas fa-chevron-left"></i></button>
                        <span id="pageInfo">Page 1 of 1</span>
                        <button class="btn-page" id="nextPage" disabled><i class="fas fa-chevron-right"></i></button>
                    </div>
                    <div class="items-per-page">
                        <label for="rowsPerPage">Rows per page:</label>
                        <select id="rowsPerPage">
                            <option value="10">10</option>
                            <option value="20">20</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>
            <?php endif; ?>
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
        // Table functionality for filtering, sorting, and pagination
        document.addEventListener('DOMContentLoaded', function() {
            // Expand/collapse feedback text
            document.querySelectorAll('.expand-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const textCell = this.parentElement;
                    textCell.classList.toggle('expanded');
                    this.textContent = textCell.classList.contains('expanded') ? 'Show less' : 'Read more';
                });
            });
            
            // Table filtering
            const feedbackTable = document.getElementById('feedbackTable');
            if (feedbackTable) {
                const rows = Array.from(feedbackTable.querySelectorAll('tbody tr'));
                
                // Search functionality
                document.getElementById('feedbackSearch').addEventListener('input', filterTable);
                
                // Filter by sentiment and status
                document.getElementById('sentimentFilter').addEventListener('change', filterTable);
                document.getElementById('statusFilter').addEventListener('change', filterTable);
                
                function filterTable() {
                    const searchTerm = document.getElementById('feedbackSearch').value.toLowerCase();
                    const sentimentFilter = document.getElementById('sentimentFilter').value.toLowerCase();
                    const statusFilter = document.getElementById('statusFilter').value.toLowerCase();
                    
                    rows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        const sentiment = row.getAttribute('data-sentiment');
                        const status = row.getAttribute('data-status');
                        
                        const matchesSearch = !searchTerm || text.includes(searchTerm);
                        const matchesSentiment = !sentimentFilter || sentiment === sentimentFilter;
                        const matchesStatus = !statusFilter || status === statusFilter;
                        
                        row.style.display = matchesSearch && matchesSentiment && matchesStatus ? '' : 'none';
                    });
                    
                    updatePagination();
                }
                
                // Sorting functionality
                document.querySelectorAll('.sortable').forEach(header => {
                    header.addEventListener('click', function() {
                        const sortBy = this.getAttribute('data-sort');
                        const isAscending = this.classList.contains('sort-asc');
                        
                        // Reset all sort indicators
                        document.querySelectorAll('.sortable').forEach(h => {
                            h.classList.remove('sort-asc', 'sort-desc');
                            h.querySelector('i').className = 'fas fa-sort';
                        });
                        
                        // Set new sort direction
                        if (isAscending) {
                            this.classList.add('sort-desc');
                            this.querySelector('i').className = 'fas fa-sort-down';
                        } else {
                            this.classList.add('sort-asc');
                            this.querySelector('i').className = 'fas fa-sort-up';
                        }
                        
                        // Sort the rows
                        const tbody = feedbackTable.querySelector('tbody');
                        const sortedRows = rows.slice().sort((a, b) => {
                            let valueA, valueB;
                            
                            if (sortBy === 'id') {
                                valueA = parseInt(a.cells[0].textContent);
                                valueB = parseInt(b.cells[0].textContent);
                            } else if (sortBy === 'date') {
                                valueA = parseInt(a.cells[1].getAttribute('data-date'));
                                valueB = parseInt(b.cells[1].getAttribute('data-date'));
                            } else if (sortBy === 'sentiment') {
                                valueA = a.getAttribute('data-sentiment');
                                valueB = b.getAttribute('data-sentiment');
                            }
                            
                            if (valueA < valueB) return isAscending ? -1 : 1;
                            if (valueA > valueB) return isAscending ? 1 : -1;
                            return 0;
                        });
                        
                        // Reorder the table
                        sortedRows.forEach(row => tbody.appendChild(row));
                        
                        updatePagination();
                    });
                });
                
                // Pagination functionality
                let currentPage = 1;
                let rowsPerPage = 10;
                
                document.getElementById('rowsPerPage').addEventListener('change', function() {
                    rowsPerPage = parseInt(this.value);
                    currentPage = 1;
                    updatePagination();
                });
                
                document.getElementById('prevPage').addEventListener('click', function() {
                    if (currentPage > 1) {
                        currentPage--;
                        updatePagination();
                    }
                });
                
                document.getElementById('nextPage').addEventListener('click', function() {
                    const visibleRows = rows.filter(row => row.style.display !== 'none');
                    const totalPages = Math.ceil(visibleRows.length / rowsPerPage);
                    
                    if (currentPage < totalPages) {
                        currentPage++;
                        updatePagination();
                    }
                });
                
                function updatePagination() {
                    const visibleRows = rows.filter(row => row.style.display !== 'none');
                    const totalPages = Math.ceil(visibleRows.length / rowsPerPage);
                    
                    // Update page navigation
                    document.getElementById('pageInfo').textContent = `Page ${currentPage} of ${totalPages || 1}`;
                    document.getElementById('prevPage').disabled = currentPage <= 1;
                    document.getElementById('nextPage').disabled = currentPage >= totalPages;
                    
                    // Show/hide rows for current page
                    visibleRows.forEach((row, index) => {
                        const start = (currentPage - 1) * rowsPerPage;
                        const end = start + rowsPerPage;
                        row.classList.toggle('pagination-hidden', index < start || index >= end);
                    });
                }
                
                // Initialize pagination
                updatePagination();
            }
        });
    </script>
</body>
</html>