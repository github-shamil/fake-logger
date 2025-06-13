<?php
$db = new SQLite3('log.db');
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="visitor_logs.csv"');
$output = fopen('php://output', 'w');
fputcsv($output, ['IP', 'Latitude', 'Longitude', 'City', 'Country', 'Timestamp', 'Google Maps Link']);
$results = $db->query('SELECT * FROM logs ORDER BY id DESC');
while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
  $link = "https://www.google.com/maps?q={$row['latitude']},{$row['longitude']}";
  fputcsv($output, [$row['ip'], $row['latitude'], $row['longitude'], $row['city'], $row['country'], $row['timestamp'], $link]);
}
fclose($output);
exit;
?>
