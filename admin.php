<?php
session_start();

// Password protection
$correct_password = '8590';
if (isset($_POST['password'])) {
    if ($_POST['password'] === $correct_password) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        $error = "Incorrect password!";
    }
}

if (!isset($_SESSION['admin_logged_in'])):
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <style>
        body {
            background: #121212;
            color: white;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-box {
            background: #1e1e1e;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px #000;
        }
        input[type="password"] {
            padding: 10px;
            width: 100%;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            margin-top: 10px;
        }
        button {
            margin-top: 15px;
            padding: 10px 20px;
            font-size: 16px;
            background: #03DAC6;
            color: black;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .error {
            color: red;
            margin-top: 10px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Admin Login</h2>
        <form method="post">
            <input type="password" name="password" placeholder="Enter Password" required />
            <button type="submit">Login</button>
            <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>
        </form>
    </div>
</body>
</html>
<?php
exit;
endif;

// Show logs
$db = new SQLite3('log.db');
$results = $db->query("SELECT * FROM visitors ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body {
            background-color: #121212;
            color: #fff;
            font-family: 'Segoe UI', sans-serif;
            padding: 20px;
        }
        h1 {
            color: #03DAC6;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #1e1e1e;
        }
        th, td {
            border: 1px solid #333;
            padding: 10px;
            text-align: left;
        }
        th {
            background: #272727;
        }
        tr:hover {
            background: #2c2c2c;
        }
        .logout {
            margin-top: 15px;
            display: inline-block;
            padding: 10px 20px;
            background: crimson;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .csv-btn {
            margin-top: 10px;
            padding: 10px 15px;
            background: #03DAC6;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>🛡️ Visitor Logs Dashboard</h1>

    <form method="post" action="export_csv.php">
        <button type="submit" class="csv-btn">📥 Export CSV</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Timestamp</th>
                <th>IP</th>
                <th>Country</th>
                <th>City</th>
                <th>Lat</th>
                <th>Lon</th>
                <th>Map Link</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $results->fetchArray()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['timestamp']) ?></td>
                    <td><?= htmlspecialchars($row['ip']) ?></td>
                    <td><?= htmlspecialchars($row['country']) ?></td>
                    <td><?= htmlspecialchars($row['city']) ?></td>
                    <td><?= htmlspecialchars($row['latitude']) ?></td>
                    <td><?= htmlspecialchars($row['longitude']) ?></td>
                    <td><a href="<?= htmlspecialchars($row['maps_link']) ?>" target="_blank">🌍 View</a></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <a href="logout.php" class="logout">🚪 Logout</a>
</body>
</html>
