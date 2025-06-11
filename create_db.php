<?php
$db = new SQLite3("log.db");
$db->exec("CREATE TABLE IF NOT EXISTS logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ip TEXT,
    country TEXT,
    region TEXT,
    city TEXT,
    zip TEXT,
    lat TEXT,
    lon TEXT,
    isp TEXT,
    time TEXT
)");
echo "✅ Database created";
?>
