<?php
// 📍 Visitor Logger: Save to log.txt + SQLite + Telegram

function get_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    return $_SERVER['REMOTE_ADDR'];
}

$ip = get_ip();
$details = json_decode(file_get_contents("http://ip-api.com/json/{$ip}?fields=status,country,city,lat,lon,query"), true);

if ($details['status'] !== 'success') {
    http_response_code(500);
    exit("Geo lookup failed");
}

$country = $details['country'];
$city = $details['city'];
$lat = $details['lat'];
$lon = $details['lon'];
$timestamp = date("Y-m-d H:i:s");

// Save to log.txt
$logLine = "$ip | $country, $city | $lat, $lon | $timestamp\n";
file_put_contents("log.txt", $logLine, FILE_APPEND);

// Save to SQLite
$db = new SQLite3("log.db");
$db->exec("CREATE TABLE IF NOT EXISTS visitors (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ip TEXT, country TEXT, city TEXT,
    lat REAL, lon REAL, timestamp TEXT
)");
$stmt = $db->prepare("INSERT INTO visitors (ip, country, city, lat, lon, timestamp) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bindValue(1, $ip);
$stmt->bindValue(2, $country);
$stmt->bindValue(3, $city);
$stmt->bindValue(4, $lat);
$stmt->bindValue(5, $lon);
$stmt->bindValue(6, $timestamp);
$stmt->execute();

// ✅ Telegram notification (optional)
$token = "7943375930:AAEiifo4A9NiuxY13o73qjCJVUiHXEu2ta8"; // 🟡 Replace with your bot token
$chat_id = "6602027873"; // 🟡 Replace with your chat ID
$msg = "📥 New Visitor:\nIP: $ip\nCountry: $country\nCity: $city\nLat: $lat\nLon: $lon\nTime: $timestamp";
file_get_contents("https://api.telegram.org/bot$token/sendMessage?chat_id=$chat_id&text=" . urlencode($msg));

echo "Logged successfully";
?>
