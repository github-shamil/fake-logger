<?php
// === CONFIG ===
$correct_pass = "5121"; // change to your real password
$db_file = "log.db";
$per_page = 20;

// === Password Protection ===
if (!isset($_GET['pass']) || $_GET['pass'] !== $correct_pass) {
  http_response_code(403);
  echo "Access denied";
  exit;
}

// === Connect to SQLite ===
$db = new SQLite3($db_file);

// === Filters ===
$where = "WHERE 1=1";
$params = [];

if (!empty($_GET['ip'])) {
  $where .= " AND ip LIKE :ip";
  $params[':ip'] = "%" . $_GET['ip'] . "%";
}

if (!empty($_GET['city'])) {
  $where .= " AND city LIKE :city";
  $params[':city'] = "%" . $_GET['city'] . "%";
}

if (!empty($_GET['town'])) {
  $where .= " AND town LIKE :town";
  $params[':town'] = "%" . $_GET['town'] . "%";
}

if (!empty($_GET['date'])) {
  $where .= " AND DATE(timestamp) = :date";
  $params[':date'] = $_GET['date'];
}

// === Pagination ===
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $per_page;

// === Count Total Entries ===
$count_stmt = $db->prepare("SELECT COUNT(*) as total FROM visitors $where");
foreach ($params as $key => $val) {
  $count_stmt->bindValue($key, $val, SQLITE3_TEXT);
}
$total = $count_stmt->execute()->fetchArray(SQLITE3_ASSOC)['total'];
$pages = ceil($total / $per_page);

// === Fetch Logs ===
$stmt = $db->prepare("SELECT * FROM visitors $where ORDER BY timestamp DESC LIMIT $per_page OFFSET $offset");
foreach ($params as $key => $val) {
  $stmt->bindValue($key, $val, SQLITE3_TEXT);
}
$results = $stmt->execute();
?>
<!DOCTYPE html>
<html>
<head>
  <title>Visitor Log Viewer</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
    h2 { margin-top: 0; }
    table { width: 100%; border-collapse: collapse; background: white; margin-top: 20px; }
    th, td { padding: 10px; border: 1px solid #ccc; font-size: 14px; text-align: left; }
    th { background-color: #007BFF; color: white; }
    tr:nth-child(even) { background: #f9f9f9; }
    form input { padding: 5px 10px; margin-right: 10px; }
    .pagination { margin-top: 15px; }
    .pagination a { padding: 5px 10px; background: #007BFF; color: white; text-decoration: none; margin-right: 5px; border-radius: 3px; }
    .pagination span { padding: 5px 10px; }
  </style>
</head>
<body>
  <h2>Visitor Tracker - Log Viewer</h2>

  <form method="get">
    <input type="hidden" name="pass" value="<?= htmlspecialchars($correct_pass) ?>">
    <input type="text" name="ip" placeholder="IP" value="<?= htmlspecialchars($_GET['ip'] ?? '') ?>">
    <input type="text" name="city" placeholder="City" value="<?= htmlspecialchars($_GET['city'] ?? '') ?>">
    <input type="text" name="town" placeholder="Town" value="<?= htmlspecialchars($_GET['town'] ?? '') ?>">
    <input type="date" name="date" value="<?= htmlspecialchars($_GET['date'] ?? '') ?>">
    <input type="submit" value="Filter">
  </form>

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
    <?php while ($row = $results->fetchArray(SQLITE3_ASSOC)) { ?>
      <tr>
        <td><?= htmlspecialchars($row['ip']) ?></td>
        <td><?= htmlspecialchars($row['country']) ?></td>
        <td><?= htmlspecialchars($row['region']) ?></td>
        <td><?= htmlspecialchars($row['city']) ?></td>
        <td><?= htmlspecialchars($row['town'] ?? '') ?></td>
        <td><?= htmlspecialchars($row['lat']) ?></td>
        <td><?= htmlspecialchars($row['lon']) ?></td>
        <td><?= htmlspecialchars($row['timestamp']) ?></td>
        <td>
          <a href="https://www.google.com/maps?q=<?= $row['lat'] ?>,<?= $row['lon'] ?>" target="_blank">View</a>
        </td>
      </tr>
    <?php } ?>
  </table>

  <div class="pagination">
    <?php for ($i = 1; $i <= $pages; $i++) {
      $url = $_GET;
      $url['page'] = $i;
      $query = http_build_query($url);
      echo $i == $page
        ? "<span><b>$i</b></span>"
        : "<a href=\"?$query\">$i</a>";
    } ?>
  </div>
</body>
</html>
