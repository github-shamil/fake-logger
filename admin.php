<?php
// === CONFIG ===
$DB_FILE = 'log.db';
$PASSWORD = '5121';
$PER_PAGE = 20;

// === AUTH CHECK ===
if (!isset($_GET['pass']) || $_GET['pass'] !== $PASSWORD) {
    die("<h2>Access Denied ❌</h2><p>Provide ?pass=yourpassword in the URL.</p>");
}

$db = new SQLite3($DB_FILE);
$db->exec("CREATE TABLE IF NOT EXISTS visitors (
    id INTEGER PRIMARY KEY,
    ip TEXT, country TEXT, region TEXT,
    city TEXT, town TEXT,
    lat TEXT, lon TEXT, timestamp TEXT
)");

// === FILTERING ===
$filter = [
  'ip' => $_GET['ip'] ?? '',
  'city' => $_GET['city'] ?? '',
  'town' => $_GET['town'] ?? '',
  'date' => $_GET['date'] ?? '',
];

$where = [];
foreach ($filter as $key => $val) {
  if ($val) $where[] = "$key LIKE '%" . SQLite3::escapeString($val) . "%'";
}
$whereClause = $where ? "WHERE " . implode(" AND ", $where) : "";

// === PAGINATION ===
$page = max(1, intval($_GET['page'] ?? 1));
$offset = ($page - 1) * $PER_PAGE;

$total = $db->querySingle("SELECT COUNT(*) FROM visitors $whereClause");
$results = $db->query("SELECT * FROM visitors $whereClause ORDER BY timestamp DESC LIMIT $PER_PAGE OFFSET $offset");

?>
<!DOCTYPE html>
<html>
<head>
  <title>Visitor Log Admin</title>
  <style>
    body { font-family: Arial; background: #f0f2f5; padding: 20px; }
    h2 { margin-bottom: 10px; }
    form input { padding: 6px; margin-right: 5px; }
    table { width: 100%; border-collapse: collapse; background: white; }
    th, td { padding: 8px 10px; border: 1px solid #ccc; font-size: 13px; }
    th { background: #333; color: white; }
    a.map { color: blue; text-decoration: underline; }
    .pagination a { padding: 4px 8px; background: #007BFF; color: white; margin: 2px; text-decoration: none; border-radius: 4px; }
    .pagination { margin-top: 10px; }
  </style>
</head>
<body>
  <h2>🧭 Visitor Logs</h2>

  <form method="GET">
    <input type="hidden" name="pass" value="<?= htmlspecialchars($PASSWORD) ?>">
    <input name="ip" placeholder="IP" value="<?= htmlspecialchars($filter['ip']) ?>">
    <input name="city" placeholder="City" value="<?= htmlspecialchars($filter['city']) ?>">
    <input name="town" placeholder="Town" value="<?= htmlspecialchars($filter['town']) ?>">
    <input name="date" placeholder="YYYY-MM-DD" value="<?= htmlspecialchars($filter['date']) ?>">
    <button type="submit">Search</button>
    <a href="?pass=<?= htmlspecialchars($PASSWORD) ?>">Reset</a>
  </form>

  <table>
    <tr>
      <th>IP</th>
      <th>Country</th>
      <th>Region</th>
      <th>City</th>
      <th>Town</th>
      <th>Lat</th>
      <th>Lon</th>
      <th>Time</th>
      <th>Map</th>
    </tr>
    <?php while ($row = $results->fetchArray()) { ?>
    <tr>
      <td><?= htmlspecialchars($row['ip']) ?></td>
      <td><?= htmlspecialchars($row['country']) ?></td>
      <td><?= htmlspecialchars($row['region']) ?></td>
      <td><?= htmlspecialchars($row['city']) ?></td>
      <td><?= htmlspecialchars($row['town']) ?></td>
      <td><?= htmlspecialchars($row['lat']) ?></td>
      <td><?= htmlspecialchars($row['lon']) ?></td>
      <td><?= htmlspecialchars($row['timestamp']) ?></td>
      <td><a class="map" target="_blank" href="https://www.google.com/maps?q=<?= $row['lat'] ?>,<?= $row['lon'] ?>">View</a></td>
    </tr>
    <?php } ?>
  </table>

  <div class="pagination">
    <?php for ($i = 1; $i <= ceil($total / $PER_PAGE); $i++): ?>
      <a href="?pass=<?= $PASSWORD ?>&page=<?= $i ?><?= $filter['ip'] ? "&ip=" . urlencode($filter['ip']) : "" ?><?= $filter['city'] ? "&city=" . urlencode($filter['city']) : "" ?><?= $filter['town'] ? "&town=" . urlencode($filter['town']) : "" ?><?= $filter['date'] ? "&date=" . urlencode($filter['date']) : "" ?>">
        <?= $i ?>
      </a>
    <?php endfor; ?>
  </div>
</body>
</html>
