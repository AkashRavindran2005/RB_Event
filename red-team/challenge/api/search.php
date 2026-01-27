<?php
include '../includes/config.php';

header('Content-Type: application/json');

// Create data directory and flag file if they don't exist
$data_dir = '/tmp/ctf_data';
if (!is_dir($data_dir)) {
    mkdir($data_dir, 0755, true);
    file_put_contents($data_dir . '/flag.txt', 'FLAG{c0mm4nd_1nj3ct10n_r00t}');
    file_put_contents($data_dir . '/services.txt', "red-teaming\nblue-teaming\npenetration-testing\nsecurity-audit\nincident-response");
}

if (isset($_GET['q'])) {
    $search = $_GET['q'];

    logActivity('api_search', "Query: $search");

    // VULNERABLE TO COMMAND INJECTION!
    // Try: api/search.php?q=test'; cat /tmp/ctf_data/flag.txt; echo '
    // Or: api/search.php?q=test $(cat /tmp/ctf_data/flag.txt)
    // Or: api/search.php?q=test | cat /tmp/ctf_data/flag.txt
    $output = shell_exec("grep -ri '$search' $data_dir 2>&1");

    echo json_encode([
        'status' => 'success',
        'query' => $search,
        'results' => $output,
        'timestamp' => date('Y-m-d H:i:s'),
        'hint' => 'Try searching for services like "red-teaming" or "security"'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'No search query provided',
        'usage' => 'api/search.php?q=<search_term>',
        'example' => 'api/search.php?q=security'
    ]);
}
?>