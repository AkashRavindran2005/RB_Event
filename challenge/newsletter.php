<?php
include 'includes/config.php';
include 'includes/header.php';
logActivity('page_view', 'newsletter');

$preview = "";
$subscribed = false;

function renderTemplate($template)
{
    $variables = [
        '{{company}}' => 'CyberTech Solutions',
        '{{year}}' => date('Y'),
        '{{date}}' => date('F j, Y'),
        '{{email}}' => 'newsletter@cybertech.com'
    ];

    $output = str_replace(array_keys($variables), array_values($variables), $template);

    if (preg_match_all('/\$\{(.+?)\}/', $output, $matches)) {
        foreach ($matches[1] as $index => $expression) {
            try {
                $result = @eval ("return $expression;");
                $output = str_replace($matches[0][$index], $result, $output);
            } catch (Exception $e) {
                $output = str_replace($matches[0][$index], '[Error]', $output);
            }
        }
    }

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

// Admin template preview mode
$showEditor = isset($_GET['mode']) && $_GET['mode'] === 'preview';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['template'])) {
        $template = $_POST['template'];
        logActivity('template_render', 'Template preview: ' . substr($template, 0, 100));
        $preview = renderTemplate($template);
    } else if (isset($_POST['email'])) {
        $subscribed = true;
    }
}

$defaultTemplate = "Welcome to {{company}}!

Dear subscriber,

Thank you for signing up for our newsletter. You'll receive updates about the latest security trends and company news.

Best regards,
The {{company}} Team

© {{year}} - All rights reserved
Contact: {{email}}";
?>

<?php if ($showEditor): ?>
    <!-- Admin Template Preview Mode -->
    <div class="section-padding">
        <div class="container-custom">
            <h1 class="display-text mb-3">Email Template Manager</h1>
            <p class="text-secondary mb-5">Create and preview newsletter templates.</p>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="bento-card p-5">
                        <h3 class="text-white mb-4"><i class="fas fa-edit me-2"></i>Template Editor</h3>

                        <form method="POST">
                            <div class="mb-4">
                                <label class="form-label text-secondary">Template Content</label>
                                <textarea name="template" class="form-control" rows="12" style="font-family: monospace;"
                                    placeholder="Enter your template..."><?php echo isset($_POST['template']) ? htmlspecialchars($_POST['template']) : $defaultTemplate; ?></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-eye me-2"></i>Preview Template
                            </button>
                        </form>

                        <div class="mt-4 p-3 rounded" style="background: #0d1117; border: 1px solid #30363d;">
                            <h6 class="text-success mb-2">Variables:</h6>
                            <code class="text-info">{{company}}</code> - Company name<br>
                            <code class="text-info">{{year}}</code> - Current year<br>
                            <code class="text-info">{{date}}</code> - Today's date<br>
                            <code class="text-info">{{email}}</code> - Contact email
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
        </div>
    </div>

<?php else: ?>
    <!-- Public Newsletter Signup -->
    <div class="section-padding">
        <div class="container-custom" style="max-width: 600px;">
            <div class="text-center mb-5">
                <h1 class="display-text mb-3">Stay Updated</h1>
                <p class="text-secondary">Subscribe to our newsletter for the latest cybersecurity insights, industry
                    trends, and company updates.</p>
            </div>

            <div class="bento-card p-5">
                <?php if ($subscribed): ?>
                    <div class="text-center">
                        <i class="fas fa-check-circle text-success" style="font-size: 48px;"></i>
                        <h3 class="text-white mt-4">Thank You!</h3>
                        <p class="text-secondary">You've been successfully subscribed to our newsletter.</p>
                    </div>
                <?php else: ?>
                    <form method="POST">
                        <div class="mb-4">
                            <label class="form-label text-secondary">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="Enter your email address"
                                required>
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="consent" required>
                                <label class="form-check-label text-secondary small" for="consent">
                                    I agree to receive marketing communications from CyberTech Solutions.
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-envelope me-2"></i>Subscribe
                        </button>
                    </form>
                <?php endif; ?>
            </div>

            <div class="text-center mt-4">
                <p class="text-secondary small">We respect your privacy. Unsubscribe at any time.</p>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>