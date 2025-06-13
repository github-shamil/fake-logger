<?php
header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename=visitor_logs.csv');

$db = new SQLite3('log.db');
$results = $db->query("SELECT * FROM visitors");

$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'Timestamp', 'IP', 'Country', 'City', 'Latitude', 'Longitude', 'Google Maps Link']);

while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
    fputcsv($output, $row);
}

fclose($output);
?>
