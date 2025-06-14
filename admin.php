<?php
$correct_pass = "5121";
if (!isset($_GET['pass']) || $_GET['pass'] !== $correct_pass) {
    http_response_code(403);
    echo "Access denied";
    exit;
}

$db = new SQLite3('log.db');
$db->exec("CREATE TABLE IF NOT EXISTS visitors (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ip TEXT,
    country TEXT,
    region TEXT,
    city TEXT,
    town TEXT,
    lat TEXT,
    lon TEXT,
    timestamp TEXT
)");

$results = $db->query('SELECT * FROM visitors ORDER BY timestamp DESC');
?>
<!DOCTYPE html>
<html>
<head>
  <title>Visitor Logs</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f9f9f9;
      padding: 20px;
    }
    h2 {
      color: #333;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      background: white;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    th, td {
      padding: 12px;
      border: 1px solid #ccc;
      text-align: left;
      font-size: 14px;
    }
    th {
      background: #007BFF;
      color: white;
    }
    iframe {
      border: none;
      width: 200px;
      height: 150px;
    }
  </style>
</head>
<body>
  <h2>Visitor Tracker - Log Viewer</h2>
  <p>Accessed with correct password ✔</p>
  <table>
    <tr>
      <th>IP</th>
      <th>Country</th>
      <th>Region</th>
      <th>City</th>
      <th>Town</th>
      <th>Latitude</th>
      <th>Longitude</th>
      <th>Time</th>
      <th>Map</th>
    </tr>
    <?php while ($row = $results->fetchArray()) {
      $ip = $row['ip'];
      $country = $row['country'];
      $region = $row['region'];
      $city = $row['city'];
      $town = $row['town'] ?: '-';
      $lat = $row['lat'];
      $lon = $row['lon'];
      $time = $row['timestamp'];
      $mapURL = "https://maps.google.com/maps?q={$lat},{$lon}&z=15&output=embed";
    ?>
    <tr>
      <td><?= htmlspecialchars($ip) ?></td>
      <td><?= htmlspecialchars($country) ?></td>
      <td><?= htmlspecialchars($region) ?></td>
      <td><?= htmlspecialchars($city) ?></td>
      <td><?= htmlspecialchars($town) ?></td>
      <td><?= htmlspecialchars($lat) ?></td>
      <td><?= htmlspecialchars($lon) ?></td>
      <td><?= htmlspecialchars($time) ?></td>
      <td><iframe src="<?= $mapURL ?>"></iframe></td>
    </tr>
    <?php } ?>
  </table>
</body>
</html>
