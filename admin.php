<?php
session_start();
$correct_password = "8590";

// Handle login
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["password"])) {
    if ($_POST["password"] === $correct_password) {
        $_SESSION["logged_in"] = true;
    } else {
        $error = "Incorrect password.";
    }
}

// Redirect if not logged in
if (!isset($_SESSION["logged_in"])) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Admin Login</title>
        <style>
            body {
                background: #121212;
                color: #fff;
                display: flex;
                height: 100vh;
                justify-content: center;
                align-items: center;
                font-family: Arial, sans-serif;
            }
            form {
                background: #1e1e1e;
                padding: 30px;
                border-radius: 10px;
                box-shadow: 0 0 15px rgba(0,0,0,0.4);
            }
            input[type="password"] {
                padding: 10px;
                font-size: 16px;
                width: 100%;
                border: none;
                margin-bottom: 10px;
                border-radius: 5px;
            }
            button {
                padding: 10px;
                width: 100%;
                background: #00bcd4;
                color: white;
                font-size: 16px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
            }
            .error { color: #ff4444; margin-bottom: 10px; }
        </style>
    </head>
    <body>
        <form method="post">
            <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>
            <input type="password" name="password" placeholder="Enter password..." required>
            <button type="submit">Login</button>
        </form>
    </body>
    </html>
    <?php
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Visitor Logs</title>
    <style>
        body {
            background: #f4f4f4;
            font-family: Roboto, sans-serif;
            margin: 0;
            padding: 20px;
        }
        h1 {
            text-align: center;
        }
        .log-entry {
            background: white;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .log-entry p {
            margin: 5px 0;
        }
        .map-link {
            color: #0077cc;
            text-decoration: none;
        }
        .map-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>📍 Visitor Logs</h1>

    <?php
    // Load from log.txt
    if (file_exists("log.txt")) {
        echo "<h2>Log from log.txt</h2>";
        $lines = file("log.txt", FILE_IGNORE_NEW_LINES);
        foreach ($lines as $line) {
            echo "<div class='log-entry'><p>$line</p></div>";
        }
    } else {
        echo "<p>No log.txt file found.</p>";
    }

    // Load from SQLite
    if (file_exists("log.db")) {
        try {
            $db = new SQLite3("log.db");
            $results = $db->query("SELECT * FROM visitors ORDER BY timestamp DESC");

            echo "<h2>Log from log.db</h2>";
            while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
                echo "<div class='log-entry'>";
                echo "<p><strong>IP:</strong> " . $row["ip"] . "</p>";
                echo "<p><strong>Country:</strong> " . $row["country"] . "</p>";
                echo "<p><strong>City:</strong> " . $row["city"] . "</p>";
                echo "<p><strong>Latitude:</strong> " . $row["latitude"] . "</p>";
                echo "<p><strong>Longitude:</strong> " . $row["longitude"] . "</p>";
                echo "<p><strong>Time:</strong> " . $row["timestamp"] . "</p>";
                echo "<p><a class='map-link' target='_blank' href='https://www.google.com/maps?q={$row["latitude"]},{$row["longitude"]}'>🌍 View on Google Maps</a></p>";
                echo "</div>";
            }
        } catch (Exception $e) {
            echo "<p>Error reading SQLite DB: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p>No log.db file found.</p>";
    }
    ?>
</body>
</html>

