<?php
$db = new SQLite3('log.db');
$results = $db->query('SELECT * FROM visitors ORDER BY timestamp DESC');
?>
<!DOCTYPE html>
<html>
<head>
  <title>Visitor Logs</title>
  <style>
    body { font-family: Arial; background: #f5f5f5; padding: 20px; }
    table { width: 100%; border-collapse: collapse; background: white; }
    th, td { padding: 10px; border: 1px solid #ccc; font-size: 14px; }
    th { background: #007BFF; color: white; }
  </style>
</head>
<body>
  <h2>Visitor Tracker - Log Viewer</h2>
  <table>
    <tr>
      <th>IP</th>
      <th>Country</th>
      <th>City</th>
      <th>Latitude</th>
      <th>Longitude</th>
      <th>Timestamp</th>
      <th>Map</th>
    </tr>
    <?php while ($row = $results->fetchArray()) { ?>
    <tr>
      <td><?= $row['ip'] ?></td>
      <td><?= $row['country'] ?></td>
      <td><?= $row['city'] ?></td>
      <td><?= $row['lat'] ?></td>
      <td><?= $row['lon'] ?></td>
      <td><?= $row['timestamp'] ?></td>
      <td><a target="_blank" href="https://www.google.com/maps?q=<?= $row['lat'] ?>,<?= $row['lon'] ?>">View</a></td>
    </tr>
    <?php } ?>
  </table>
</body>
</html>
