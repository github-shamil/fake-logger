<?php
// ====== CONFIG ======
$password = "5121"; // 🔐 Set your password here

// ====== AUTH CHECK ======
if (!isset($_GET['pass']) || $_GET['pass'] !== $password) {
  die("Unauthorized access. Add ?pass=5121 to the URL.");
}

// ====== DATABASE ======
$db = new SQLite3('log.db');
$results = $db->query('SELECT * FROM visitors ORDER BY timestamp DESC');

// ====== FILTER LOGIC ======
$filter = strtolower($_GET['filter'] ?? '');
function matchFilter($row, $filter) {
  return !$filter || strpos(strtolower(json_encode($row)), $filter) !== false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Visitor Logs</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body {
      font-family: system-ui, sans-serif;
      background: #f2f2f2;
      margin: 0;
      padding: 20px;
    }
    h2 {
      margin-bottom: 10px;
    }
    input[type="text"] {
      padding: 8px;
      width: 100%;
      max-width: 300px;
      margin-bottom: 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      background: white;
      font-size: 14px;
    }
    th, td {
      padding: 8px;
      border: 1px solid #ddd;
      word-break: break-word;
    }
    th {
      background: #007bff;
      color: white;
      position: sticky;
      top: 0;
      z-index: 2;
    }
    tr:hover {
      background: #f9f9f9;
    }
    .highlight {
      background: #fff9c4;
    }
    @media (max-width: 600px) {
      table, thead, tbody, th, td, tr {
        display: block;
      }
      thead tr {
        display: none;
      }
      tr {
        margin-bottom: 10px;
        border: 1px solid #ccc;
        background: white;
        padding: 10px;
      }
      td {
        display: flex;
        justify-content: space-between;
        padding: 8px 10px;
        border: none;
        border-bottom: 1px solid #eee;
      }
      td::before {
        content: attr(data-label);
        font-weight: bold;
        color: #555;
      }
    }
  </style>
</head>
<body>
  <h2>📍 Visitor Tracker - Admin Logs</h2>
  <form method="get">
    <input type="hidden" name="pass" value="<?= htmlspecialchars($password) ?>">
    <input type="text" name="filter" placeholder="🔍 Search IP, country, city..." value="<?= htmlspecialchars($filter) ?>" oninput="this.form.submit()">
  </form>

  <table>
    <thead>
      <tr>
        <th>IP</th>
        <th>Country</th>
        <th>City</th>
        <th>Latitude</th>
        <th>Longitude</th>
        <th>Time</th>
        <th>Map</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
        if (!matchFilter($row, $filter)) continue;
        ?>
        <tr class="<?= $filter && stripos(json_encode($row), $filter) !== false ? 'highlight' : '' ?>">
          <td data-label="IP"><?= htmlspecialchars($row['ip']) ?></td>
          <td data-label="Country"><?= htmlspecialchars($row['country']) ?></td>
          <td data-label="City"><?= htmlspecialchars($row['city']) ?></td>
          <td data-label="Lat"><?= htmlspecialchars($row['lat']) ?></td>
          <td data-label="Lon"><?= htmlspecialchars($row['lon']) ?></td>
          <td data-label="Time"><?= htmlspecialchars($row['timestamp']) ?></td>
          <td data-label="Map">
            <a href="https://www.google.com/maps?q=<?= $row['lat'] ?>,<?= $row['lon'] ?>" target="_blank">🌍 View</a>
          </td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
</body>
</html>
