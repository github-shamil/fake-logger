<?php
// logger.php: Save real IP and geolocation to log.txt and log.db

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) die("No data received");

// Prepare data
$ip        = $data['ip']        ?? 'Unknown';
$city      = $data['city']      ?? 'Unknown';
$country   = $data['country']   ?? 'Unknown';
$lat       = $data['lat']       ?? 'Unknown';
$lon       = $data['lon']       ?? 'Unknown';
$timestamp = $data['timestamp'] ?? date("Y-m-d H:i:s");
$gmapLink  = $data['gmapLink']  ?? '';

// Format log entry
$entry = "$timestamp | $ip | $city, $country | $lat,$lon | $gmapLink\n";

// 1️⃣ Save to log.txt
file_put_contents("log.txt", $entry, FILE_APPEND);

// 2️⃣ Save to SQLite (log.db)
$db = new SQLite3("log.db");
$db->exec("CREATE TABLE IF NOT EXISTS visitors (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  ip TEXT, city TEXT, country TEXT,
  lat TEXT, lon TEXT,
  timestamp TEXT,
  gmap TEXT
)");
$stmt = $db->prepare("INSERT INTO visitors (ip, city, country, lat, lon, timestamp, gmap) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bindValue(1, $ip);
$stmt->bindValue(2, $city);
$stmt->bindValue(3, $country);
$stmt->bindValue(4, $lat);
$stmt->bindValue(5, $lon);
$stmt->bindValue(6, $timestamp);
$stmt->bindValue(7, $gmapLink);
$stmt->execute();

echo "Logged";
?>
