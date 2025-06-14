<?php
$password = "yourpassword"; // Change to your secure password

if (!isset($_GET['pass']) || $_GET['pass'] !== $password) {
  http_response_code(403);
  die("Access Denied");
}

$db = new SQLite3('log.db');

// Handle Delete
if (isset($_GET['delete']) && $_GET['pass'] === $password) {
  $db->exec("DELETE FROM visitors");
  header("Location: admin.php?pass=$password");
  exit;
}

// Handle Export
if (isset($_GET['export']) && $_GET['pass'] === $password) {
  header('Content-Type: text/csv');
  header('Content-Disposition: attachment; filename="visitor_logs.csv"');
  $res = $db->query('SELECT * FROM visitors ORDER BY timestamp DESC');
  echo "IP,Country,City,Town,Latitude,Longitude,Timestamp\n";
  while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    echo "{$row['ip']},{$row['country']},{$row['city']},{$row['town']},{$row['lat']},{$row['lon']},{$row['timestamp']}\n";
  }
  exit;
}

$results = $db->query('SELECT * FROM visitors ORDER BY timestamp DESC');
?>

<!DOCTYPE html>
<html>
<head>
  <title>Visitor Logs</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f4f4f4;
      color: #222;
      padding: 20px;
      transition: all 0.3s;
    }
    h2 {
      margin-bottom: 15px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      background: white;
      box-shadow: 0 0 5px rgba(0,0,0,0.1);
    }
    th, td {
      padding: 10px;
      border: 1px solid #ccc;
      font-size: 14px;
      text-align: center;
    }
    th {
      background: #007BFF;
      color: white;
    }
    .actions {
      margin-bottom: 15px;
    }
    .btn {
      padding: 8px 15px;
      margin-right: 10px;
      background: #007BFF;
      color: white;
      border: none;
      cursor: pointer;
      border-radius: 5px;
      font-size: 14px;
    }
    .btn:hover {
      background: #0056b3;
    }
    .dark-mode {
      background: #121212;
      color: #eee;
    }
    .dark-mode table {
      background: #1e1e1e;
    }
    .dark-mode th {
      background: #333;
    }
    .map-popup {
      display: none;
      position: fixed;
      top: 10%;
      left: 50%;
      transform: translateX(-50%);
      width: 90%;
      height: 400px;
      z-index: 9999;
      border: 3px solid #007BFF;
      background: white;
    }
    .close-map {
      position: absolute;
      top: -25px;
      right: 0;
      background: red;
      color: white;
      padding: 5px 10px;
      cursor: pointer;
      font-weight: bold;
    }
  </style>
</head>
<body>
  <h2>🌍 Visitor Tracker - Admin Panel</h2>

  <div class="actions">
    <button class="btn" onclick="location.href='admin.php?pass=<?= $password ?>&export=true'">📤 Export CSV</button>
    <button class="btn" onclick="if(confirm('Are you sure you want to delete all logs?'))location.href='admin.php?pass=<?= $password ?>&delete=true'">🗑️ Delete Logs</button>
    <button class="btn" onclick="toggleDark()">🌓 Toggle Dark Mode</button>
  </div>

  <table>
    <tr>
      <th>IP</th>
      <th>Country</th>
      <th>City</th>
      <th>Town</th>
      <th>Latitude</th>
      <th>Longitude</th>
      <th>Timestamp</th>
      <th>Map</th>
    </tr>
    <?php while ($row = $results->fetchArray()) { ?>
    <tr>
      <td><?= htmlspecialchars($row['ip']) ?></td>
      <td><?= htmlspecialchars($row['country']) ?></td>
      <td><?= htmlspecialchars($row['city']) ?></td>
      <td><?= htmlspecialchars($row['town']) ?></td>
      <td><?= $row['lat'] ?></td>
      <td><?= $row['lon'] ?></td>
      <td><?= $row['timestamp'] ?></td>
      <td><a href="#" onclick="showMap(<?= $row['lat'] ?>, <?= $row['lon'] ?>)">📍 View</a></td>
    </tr>
    <?php } ?>
  </table>

  <div class="map-popup" id="mapPopup">
    <div class="close-map" onclick="document.getElementById('mapPopup').style.display='none'">✖</div>
    <iframe id="mapFrame" width="100%" height="100%" frameborder="0" style="border:0" allowfullscreen></iframe>
  </div>

  <script>
    function toggleDark() {
      document.body.classList.toggle("dark-mode");
    }

    function showMap(lat, lon) {
      const frame = document.getElementById('mapFrame');
      frame.src = `https://maps.google.com/maps?q=${lat},${lon}&z=15&output=embed`;
      document.getElementById('mapPopup').style.display = 'block';
    }
  </script>
</body>
</html>
