<?php
$filename = 'log.db';
if (file_exists($filename)) {
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
    header('Content-Length: ' . filesize($filename));
    readfile($filename);
    exit;
} else {
    echo "Database not found.";
}
?>
