<?php
include 'includes/config.php';
include 'includes/header.php';
logActivity('page_view', 'newsletter');

$preview = "";
$error = "";

// Server-Side Template Injection (SSTI) Vulnerability
// Using a custom "template engine" that's vulnerable to code injection

function renderTemplate($template)
{
    // "Simple" template engine - processes {{variable}} syntax
    // VULNERABLE: Also allows PHP code execution through eval

    // Replace predefined variables
    $variables = [
        '{{company}}' => 'CyberTech Solutions',
        '{{year}}' => date('Y'),
        '{{date}}' => date('F j, Y'),
        '{{email}}' => 'newsletter@cybertech.com'
    ];

    $output = str_replace(array_keys($variables), array_values($variables), $template);

    // VULNERABILITY: Process "dynamic" expressions with ${...}
    // This allows arbitrary PHP code execution!
    if (preg_match_all('/\$\{(.+?)\}/', $output, $matches)) {
        foreach ($matches[1] as $index => $expression) {
            try {
                // Dangerous: evaluates user input as PHP code
                $result = @eval ("return $expression;");
                $output = str_replace($matches[0][$index], $result, $output);
            } catch (Exception $e) {
                $output = str_replace($matches[0][$index], '[Error]', $output);
            }
        }
    }

    // Also vulnerable: Process {{= expression}} syntax  
    if (preg_match_all('/\{\{=\s*(.+?)\s*\}\}/', $output, $matches)) {
        foreach ($matches[1] as $index => $expression) {
            try {
                $result = @eval ("return $expression;");
                $output = str_replace($matches[0][$index], $result, $output);
            } catch (Exception $e) {
                $output = str_replace($matches[0][$index], '[Error]', $output);
            }
        }
    }

    return $output;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['template'])) {
    $template = $_POST['template'];
    logActivity('template_render', 'SSTI attempt: ' . substr($template, 0, 100));
    $preview = renderTemplate($template);
}

// Default template for the form
$defaultTemplate = "Welcome to {{company}}!

Dear subscriber,

Thank you for signing up for our newsletter. You'll receive updates about the latest security trends and company news.

Best regards,
The {{company}} Team

© {{year}} - All rights reserved
Contact: {{email}}";
?>

<div class="section-padding">
    <div class="container-custom">
        <h1 class="display-text mb-3">Newsletter Template Designer</h1>
        <p class="text-secondary mb-5">Create and preview custom newsletter templates for our subscribers.</p>

        <div class="row g-4">
            <div class="col-md-6">
                <div class="bento-card p-5">
                    <h3 class="text-white mb-4"><i class="fas fa-edit me-2"></i>Template Editor</h3>

                    <form method="POST">
                        <div class="mb-4">
                            <label class="form-label text-secondary">Template Content</label>
                            <textarea name="template" class="form-control" rows="12"
                                style="background: #1d1d1f; border-color: #424245; font-family: monospace;"
                                placeholder="Enter your template..."><?php echo isset($_POST['template']) ? htmlspecialchars($_POST['template']) : $defaultTemplate; ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-eye me-2"></i>Preview Template
                        </button>
                    </form>

                    <div class="mt-4 p-3 rounded" style="background: #0d1117; border: 1px solid #30363d;">
                        <h6 class="text-success mb-2">Available Variables:</h6>
                        <code class="text-info">{{company}}</code> - Company name<br>
                        <code class="text-info">{{year}}</code> - Current year<br>
                        <code class="text-info">{{date}}</code> - Today's date<br>
                        <code class="text-info">{{email}}</code> - Contact email<br>
                        <hr class="border-secondary">
                        <small class="text-muted">
                            <i class="fas fa-flask me-1"></i>Advanced: Use <code
                                class="text-warning">${expression}</code> for dynamic values
                        </small>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="bento-card p-5">
                    <h3 class="text-white mb-4"><i class="fas fa-desktop me-2"></i>Preview</h3>

                    <div class="p-4 rounded" style="background: #fafafa; color: #333; min-height: 300px;">
                        <?php if ($preview): ?>
                            <pre
                                style="white-space: pre-wrap; margin: 0; font-family: inherit; color: #333;"><?php echo $preview; ?></pre>
                        <?php else: ?>
                            <p class="text-muted text-center mb-0">
                                <i class="fas fa-arrow-left me-2"></i>Create a template and click "Preview" to see it here
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hint Section -->
        <div class="bento-card p-4 mt-5">
            <p class="text-secondary mb-0">
                <i class="fas fa-lightbulb me-2 text-warning"></i>
                <strong>Developer Note:</strong> Our template engine supports basic variable substitution.
                We also added an experimental feature for dynamic expressions -
                just wrap any calculation in <code class="text-warning">${...}</code> syntax!
            </p>
        </div>
    </div>
</div>

<!--
    CTF Challenge: Server-Side Template Injection (SSTI)
    
    This custom "template engine" is vulnerable to SSTI.
    The ${...} syntax allows arbitrary PHP code execution.
    
    Attack progression:
    
    1. Confirm code execution: ${7*7} → should output 49
    
    2. Explore server: ${phpinfo()}, ${getcwd()}, ${scandir('.')}
    
    3. Read system files: ${file_get_contents('/etc/passwd')}
    
    4. Execute commands: ${shell_exec('ls -la includes/')}
    
    5. Find and extract the crown jewel...
    
    The real flag requires server exploration.
    A true Red Team operator doesn't need the path handed to them.
-->

<?php include 'includes/footer.php'; ?>