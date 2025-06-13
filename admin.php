<?php
// admin.php: Password-protected log viewer

$password = "8590";
if (!isset($_GET['pass']) || $_GET['pass'] !== $password) {
  die("❌ Access denied. Append ?pass=8590 to URL.");
}

echo "<h2>🛡️ Visitor Logs</h2>";

// Show raw log.txt
echo "<h3>📄 Raw log.txt</h3><pre>";
echo htmlentities(file_get_contents("log.txt") ?: "No logs.");
echo "</pre>";

// Show from SQLite
echo "<h3>📊 SQLite log.db</h3>";
$db = new SQLite3("log.db");
$res = $db->query("SELECT * FROM visitors ORDER BY id DESC");

echo "<table border=1 cellpadding=6><tr>
<th>ID</th><th>IP</th><th>City</th><th>Country</th>
<th>Lat</th><th>Lon</th><th>Time</th><th>Map</th>
</tr>";

while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
  echo "<tr>
  <td>{$row['id']}</td>
  <td>{$row['ip']}</td>
  <td>{$row['city']}</td>
  <td>{$row['country']}</td>
  <td>{$row['lat']}</td>
  <td>{$row['lon']}</td>
  <td>{$row['timestamp']}</td>
  <td><a href='{$row['gmap']}' target='_blank'>📍Map</a></td>
  </tr>";
}
echo "</table>";
?>
