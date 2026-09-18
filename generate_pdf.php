<?php
/**
 * Medilo Medical Appointment and Doctor Management System
 * Documentation PDF Exporter & Generator
 */

require_once __DIR__ . '/config/config.php';

$doc_content = file_get_contents(__DIR__ . '/DOCUMENTATION.md');

// Convert Markdown to clean formatted HTML
$html_content = htmlspecialchars($doc_content);

// Headers
$html_content = preg_replace('/^# (.*$)/m', '<h1 class="text-primary border-bottom border-2 pb-2 mt-4 mb-3 fw-bold">$1</h1>', $html_content);
$html_content = preg_replace('/^## (.*$)/m', '<h2 class="text-secondary border-bottom pb-1 mt-4 mb-2 fw-semibold">$1</h2>', $html_content);
$html_content = preg_replace('/^### (.*$)/m', '<h3 class="text-primary mt-3 mb-2 fw-semibold fs-4">$1</h3>', $html_content);
$html_content = preg_replace('/^#### (.*$)/m', '<h4 class="text-dark mt-3 mb-2 fw-bold fs-5">$1</h4>', $html_content);

// Bold and Inline Code
$html_content = preg_replace('/\*\*(.*?)\*\*/', '<strong class="text-dark">$1</strong>', $html_content);
$html_content = preg_replace('/`(.*?)`/', '<code class="bg-light px-2 py-1 rounded text-danger border">$1</code>', $html_content);

// Horizontal Rules
$html_content = preg_replace('/^---$/m', '<hr class="my-4 text-muted">', $html_content);

// Bullet lists
$html_content = preg_replace('/^- (.*$)/m', '<li class="mb-1">$1</li>', $html_content);
$html_content = preg_replace('/(<li>.*<\/li>)/s', '<ul class="ps-3 mb-3">$1</ul>', $html_content);

$html_content = nl2br($html_content);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medilo System - Project Documentation PDF Report</title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/fontawesome.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333; line-height: 1.7; font-size: 15px; }
        .doc-header { border-left: 5px solid #2a96e6; padding-left: 15px; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; margin: 0; background: #fff !important; }
            .container { box-shadow: none !important; max-width: 100% !important; padding: 0 !important; }
        }
    </style>
</head>
<body class="bg-light p-4">

<div class="container bg-white shadow-sm p-4 p-md-5 rounded-3">
    <div class="d-flex justify-content-between align-items-center mb-4 no-print border-bottom pb-3">
        <div class="doc-header">
            <h4 class="text-primary mb-0 fw-bold"><i class="fa-solid fa-file-pdf me-2"></i> Medilo System Documentation</h4>
            <span class="text-muted small">Official Architecture & Functional Guide</span>
        </div>
        <div>
            <button onclick="window.print();" class="btn btn-primary fw-bold py-2 px-4 shadow-sm">
                <i class="fa-solid fa-print me-1"></i> Print / Save as PDF
            </button>
            <a href="<?php echo APP_URL; ?>/index.php" class="btn btn-outline-secondary py-2 px-3 ms-2">
                <i class="fa-solid fa-house me-1"></i> Back to Home
            </a>
        </div>
    </div>

    <div class="documentation-body">
        <?php echo $html_content; ?>
    </div>

    <div class="border-top pt-3 mt-5 text-center text-muted small">
        <p class="mb-0">Medilo Healthcare & Medical Appointment System &copy; <?php echo date('Y'); ?>. All Rights Reserved.</p>
    </div>
</div>

</body>
</html>
