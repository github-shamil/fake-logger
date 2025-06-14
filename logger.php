<?php
// === CONFIG ===
$DB_FILE = 'log.db';
$TXT_FILE = 'log.txt';
$TELEGRAM_BOT_TOKEN = '7943375930:AAEiifo4A9NiuxY13o73qjCJVUiHXEu2ta8'; // Optional
$TELEGRAM_CHAT_ID = '6602027873';   // Optional

// === IP Detection ===
function get_client_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    return $_SERVER['REMOTE_ADDR'];
}

$ip = get_client_ip();

// === Get Location from IP ===
$api = "http://ip-api.com/json/{$ip}?fields=status,message,country,regionName,city,district,lat,lon,query";
$response = @file_get_contents($api);
$data = json_decode($response, true);

if ($data['status'] !== 'success') die("Failed to get location");

// === Extract Info ===
$country = $data['country'] ?? 'N/A';
$region = $data['regionName'] ?? 'N/A';
$city = $data['city'] ?? 'N/A';
$town = $data['district'] ?? 'N/A'; // District/Town/Village
$lat = $data['lat'] ?? '';
$lon = $data['lon'] ?? '';
$timestamp = date("Y-m-d H:i:s");

// === Log to SQLite ===
$db = new SQLite3($DB_FILE);
$db->exec("CREATE TABLE IF NOT EXISTS visitors (
    id INTEGER PRIMARY KEY,
    ip TEXT, country TEXT, region TEXT,
    city TEXT, town TEXT,
    lat TEXT, lon TEXT, timestamp TEXT
)");
$stmt = $db->prepare("INSERT INTO visitors (ip, country, region, city, town, lat, lon, timestamp)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bindValue(1, $ip);
$stmt->bindValue(2, $country);
$stmt->bindValue(3, $region);
$stmt->bindValue(4, $city);
$stmt->bindValue(5, $town);
$stmt->bindValue(6, $lat);
$stmt->bindValue(7, $lon);
$stmt->bindValue(8, $timestamp);
$stmt->execute();

// === Log to TXT ===
$log = "$timestamp | IP: $ip | $country, $region, $city, $town | $lat,$lon\n";
file_put_contents($TXT_FILE, $log, FILE_APPEND);

// === Optional: Telegram ===
if (!empty($TELEGRAM_BOT_TOKEN) && !empty($TELEGRAM_CHAT_ID)) {
    $msg = "🕵️ Visitor Logged:\nIP: $ip\n$country, $region, $city, $town\nLat/Lon: $lat,$lon\n$timestamp";
    file_get_contents("https://api.telegram.org/bot$TELEGRAM_BOT_TOKEN/sendMessage?chat_id=$TELEGRAM_CHAT_ID&text=" . urlencode($msg));
}

echo "Logged Successfully.";
?>
