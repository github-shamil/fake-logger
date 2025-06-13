<?php
$db = new SQLite3('log.db');

// Headers
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="visitor_logs.csv"');

// Output stream
$output = fopen('php://output', 'w');

// CSV Header
fputcsv($output, ['IP', 'Latitude', 'Longitude', 'City', 'Country', 'Timestamp', 'Google Maps Link']);

// Query
$results = $db->query('SELECT * FROM logs ORDER BY id DESC');

while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
    $mapLink = "https://www.google.com/maps?q={$row['latitude']},{$row['longitude']}";
    fputcsv($output, [
        $row['ip'], $row['latitude'], $row['longitude'],
        $row['city'], $row['country'], $row['timestamp'], $mapLink
    ]);
}

fclose($output);
exit;
?>
