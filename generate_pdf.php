<?php
/**
 * Medilo Medical Appointment and Doctor Management System
 * Documentation PDF Exporter & Generator
 */

require_once __DIR__ . '/config/config.php';

$doc_content = file_get_contents(__DIR__ . '/DOCUMENTATION.md');

// Convert basic Markdown headers and bold text to HTML formatting
$html_content = htmlspecialchars($doc_content);
$html_content = preg_replace('/^# (.*$)/m', '<h1 class="text-primary border-bottom pb-2 mt-4">$1</h1>', $html_content);
$html_content = preg_replace('/^## (.*$)/m', '<h2 class="text-secondary border-bottom pb-1 mt-3">$1</h2>', $html_content);
$html_content = preg_replace('/^### (.*$)/m', '<h3 class="text-dark mt-3">$1</h3>', $html_content);
$html_content = preg_replace('/^\*\* (.*$)/m', '<strong>$1</strong>', $html_content);
$html_content = nl2br($html_content);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medilo System - Project Documentation PDF Report</title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/bootstrap.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333; line-height: 1.6; }
        .cert-box { border: 3px double #00A3C8; background: #fdfdfd; padding: 30px; text-align: center; border-radius: 10px; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; margin: 0; }
        }
    </style>
</head>
<body class="bg-light p-4">

<div class="container bg-white shadow-lg p-5 rounded-3">
    <div class="d-flex justify-content-between align-items-center mb-4 no-print border-bottom pb-3">
        <h4 class="text-primary mb-0"><i class="fa-solid fa-file-pdf me-2"></i> Project Documentation Report</h4>
        <div>
            <button onclick="window.print();" class="btn btn-primary fw-bold py-2 px-4">
                <i class="fa-solid fa-print me-1"></i> Print / Save as PDF
            </button>
            <a href="<?php echo APP_URL; ?>/index.php" class="btn btn-outline-secondary py-2 px-3">Back to Website</a>
        </div>
    </div>

    <div class="documentation-body">
        <?php echo $html_content; ?>
    </div>
</div>

</body>
</html>
