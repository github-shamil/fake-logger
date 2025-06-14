<?php
$db = new SQLite3("log.db");
$db->exec("CREATE TABLE IF NOT EXISTS visitors (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ip TEXT,
    country TEXT,
    city TEXT,
    latitude REAL,
    longitude REAL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
)");

$data = file_get_contents("php://input");
if ($data) {
    $json = json_decode($data, true);
    $ip = $_SERVER['REMOTE_ADDR'];
    $country = $json['country'] ?? 'Unknown';
    $city = $json['city'] ?? 'Unknown';
    $lat = $json['lat'] ?? 0;
    $lon = $json['lon'] ?? 0;

    $stmt = $db->prepare("INSERT INTO visitors (ip, country, city, latitude, longitude) VALUES (?, ?, ?, ?, ?)");
    $stmt->bindValue(1, $ip);
    $stmt->bindValue(2, $country);
    $stmt->bindValue(3, $city);
    $stmt->bindValue(4, $lat);
    $stmt->bindValue(5, $lon);
    $stmt->execute();
}

$results = $db->query("SELECT * FROM visitors ORDER BY timestamp DESC");
echo "<h2>Visitor Logs</h2><table border='1'><tr><th>IP</th><th>Country</th><th>City</th><th>Latitude</th><th>Longitude</th><th>Map</th><th>Time</th></tr>";
while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
    echo "<tr>
        <td>{$row['ip']}</td>
        <td>{$row['country']}</td>
        <td>{$row['city']}</td>
        <td>{$row['latitude']}</td>
        <td>{$row['longitude']}</td>
        <td><a href='https://maps.google.com/?q={$row['latitude']},{$row['longitude']}' target='_blank'>View</a></td>
        <td>{$row['timestamp']}</td>
    </tr>";
}
echo "</table>";
?>
