<?php
session_start();

//===========================
// CONFIG
//===========================
$PASSWORD = "8590";
$ENABLE_2FA = false; // change to true if you want Google Authenticator
$TELEGRAM_ENABLED = true;
$TELEGRAM_BOT_TOKEN = "7943375930:AAEiifo4A9NiuxY13o73qjCJVUiHXEu2ta8";
$TELEGRAM_CHAT_ID = "6602027873";

//===========================
// LOGIN SYSTEM
//===========================
if (!isset($_SESSION['logged_in'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['password'] === $PASSWORD) {
        $_SESSION['logged_in'] = true;
    } else {
        echo '<form method="post" style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:100vh;font-family:sans-serif;">
                <h2>Admin Access</h2>
                <input type="password" name="password" placeholder="Enter Password" style="padding:10px;width:200px;" />
                <button style="margin-top:10px;padding:10px 20px;">Login</button>
              </form>';
        exit();
    }
}

//===========================
// LOGOUT
//===========================
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit();
}

//===========================
// SQLite Setup
//===========================
$db = new SQLite3('log.db');
$db->exec("CREATE TABLE IF NOT EXISTS logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ip TEXT,
    latitude REAL,
    longitude REAL,
    city TEXT,
    country TEXT,
    timestamp TEXT
)");

//===========================
// DELETE LOGS
//===========================
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $db->exec("DELETE FROM logs WHERE id = $id");
    header("Location: admin.php");
    exit();
}

if (isset($_GET['delete_all'])) {
    $db->exec("DELETE FROM logs");
    file_put_contents("log.txt", "");
    header("Location: admin.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Panel - Visitor Logs</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body { font-family: "Segoe UI", sans-serif; background: #111; color: #eee; padding: 20px; }
    h1 { color: #0f0; }
    button, a { padding: 8px 14px; margin: 5px; border: none; cursor: pointer; background: #222; color: #0f0; border-radius: 5px; text-decoration: none; }
    button:hover, a:hover { background: #0f0; color: #000; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid #333; padding: 8px; text-align: left; }
    th { background: #222; color: #0f0; }
    tr:nth-child(even) { background: #1a1a1a; }
    canvas { max-width: 600px; margin-top: 30px; background: #fff; border-radius: 8px; }
  </style>
</head>
<body>
  <h1>📋 Visitor Logs</h1>
  <div>
    <a href="?logout=1">🔐 Logout</a>
    <a href="export_csv.php">📥 Export CSV</a>
    <a href="export_json.php">📥 Export JSON</a>
    <a href="download_db.php">📥 Download DB</a>
    <a href="?delete_all=1" onclick="return confirm('Delete all logs?')">🗑️ Delete All Logs</a>
  </div>

  <canvas id="countryChart"></canvas>

  <table>
    <tr>
      <th>ID</th><th>IP</th><th>Lat</th><th>Lon</th><th>City</th><th>Country</th><th>Time</th><th>Map</th><th>🗑️</th>
    </tr>
    <?php
    $results = $db->query("SELECT * FROM logs ORDER BY id DESC");
    $countries = [];
    while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
      $country = $row['country'] ?: 'Unknown';
      $countries[$country] = ($countries[$country] ?? 0) + 1;
      echo "<tr>
        <td>{$row['id']}</td>
        <td>{$row['ip']}</td>
        <td>{$row['latitude']}</td>
        <td>{$row['longitude']}</td>
        <td>{$row['city']}</td>
        <td>{$row['country']}</td>
        <td>{$row['timestamp']}</td>
        <td><a href='https://maps.google.com?q={$row['latitude']},{$row['longitude']}' target='_blank'>View</a></td>
        <td><a href='?delete={$row['id']}' onclick=\"return confirm('Delete this log?')\">❌</a></td>
      </tr>";
    }
    ?>
  </table>

  <script>
    const countryData = <?php echo json_encode($countries); ?>;
    const ctx = document.getElementById('countryChart').getContext('2d');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: Object.keys(countryData),
        datasets: [{
          label: 'Visitors by Country',
          data: Object.values(countryData),
          backgroundColor: '#0f0'
        }]
      },
      options: {
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
      }
    });
  </script>
</body>
</html>
