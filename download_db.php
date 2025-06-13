<?php
$file = 'log.db';

if (file_exists($file)) {
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="log.db"');
    header('Content-Length: ' . filesize($file));
    readfile($file);
    exit;
} else {
    echo "Database file not found.";
}
?>
