<?php
session_start();
$PASSWORD = "8590";

// Login system
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

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Panel - Tracker Logs</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body { font-family: "Segoe UI", sans-serif; background: #111; color: #eee; margin: 0; padding: 20px; }
    h1 { color: #0f0; }
    button, a { padding: 8px 14px; margin: 5px; border: none; cursor: pointer; background: #222; color: #0f0; border-radius: 5px; }
    button:hover, a:hover { background: #0f0; color: #000; }
    pre { background: #222; padding: 10px; border-radius: 5px; white-space: pre-wrap; max-height: 400px; overflow-y: auto; }
    canvas { max-width: 500px; margin-top: 20px; background: #fff; border-radius: 10px; }
    .topbar { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; margin-bottom: 20px; }
    .dark-toggle { float: right; cursor: pointer; }
  </style>
</head>
<body>
  <div class="topbar">
    <h1>📋 Visitor Logs</h1>
    <a href="?logout=1">Logout</a>
    <a href="export_csv.php">📥 Export CSV</a>
    <a href="export_json.php">📥 Export JSON</a>
    <a href="download_db.php">📥 Download DB</a>
    <button onclick="toggleDarkMode()">🌓 Toggle Mode</button>
  </div>

  <canvas id="countryChart"></canvas>

  <h2>Latest Visitors</h2>
  <pre id="logContent">Loading...</pre>

  <script>
    function fetchLog() {
      fetch('log.txt')
        .then(res => res.text())
        .then(data => {
          document.getElementById('logContent').innerText = data || "No logs yet.";
          renderChart(data);
        });
    }

    function renderChart(log) {
      const countries = {};
      log.split('\n').forEach(line => {
        const match = line.match(/Country:\s(\w+)/);
        if (match) {
          const country = match[1];
          countries[country] = (countries[country] || 0) + 1;
        }
      });

      const ctx = document.getElementById('countryChart').getContext('2d');
      if (window.chart) window.chart.destroy();
      window.chart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: Object.keys(countries),
          datasets: [{
            label: '# of Visits',
            data: Object.values(countries),
            backgroundColor: '#0f0'
          }]
        },
        options: {
          plugins: { legend: { display: false }},
          scales: { y: { beginAtZero: true } }
        }
      });
    }

    function toggleDarkMode() {
      document.body.style.background = document.body.style.background === "white" ? "#111" : "white";
      document.body.style.color = document.body.style.color === "black" ? "#eee" : "black";
    }

    fetchLog();
    setInterval(fetchLog, 30000); // refresh every 30s
  </script>
</body>
</html>
