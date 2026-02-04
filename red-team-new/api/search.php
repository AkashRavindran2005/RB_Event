<?php
include '../includes/config.php';

header('Content-Type: application/json');

if (isset($_GET['q'])) {
    $search = $_GET['q'];
    
    logActivity('api_search', "Query: $search");
    
    // Intentionally vulnerable to command injection
    $output = shell_exec("grep -r '$search' /var/www/html/data/ 2>&1");
    
    // Flag hidden in environment variable
    // FLAG{c0mm4nd_1nj3ct10n_r00t}
    
    echo json_encode([
        'status' => 'success',
        'query' => $search,
        'results' => $output,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No search query provided']);
}

// Try: api/search.php?q=test; cat /etc/passwd
// Or: api/search.php?q=test && ls -la
?>
