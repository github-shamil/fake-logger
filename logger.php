<?php

$DB_FILE = 'log.db';
$TXT_FILE = 'log.txt';
$TELEGRAM_BOT_TOKEN = '7943375930:AAEiifo4A9NiuxY13o73qjCJVUiHXEu2ta8'; // Optional
$TELEGRAM_CHAT_ID = '6602027873';   // Optional
// === Get Visitor IP ===
function get_client_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
}

// === Collect IP and Geo Info ===
$ip = get_client_ip();
$geo = json_decode(file_get_contents("http://ip-api.com/json/{$ip}?fields=status,message,country,regionName,city,district,lat,lon,query"), true);

if ($geo['status'] !== 'success') {
    http_response_code(500);
    echo "Failed to get geolocation";
    exit;
}

// === Extract Info ===
$country = $geo['country'] ?? '';
$region  = $geo['regionName'] ?? '';
$city    = $geo['city'] ?? '';
$town    = $geo['district'] ?? ''; // Narath, etc.
$lat     = $geo['lat'] ?? '';
$lon     = $geo['lon'] ?? '';
$ip      = $geo['query'] ?? $ip;
$time    = date("Y-m-d H:i:s");

// === Save to log.txt ===
$log_line = "$time | $ip | $country | $region | $city | $town | $lat,$lon\n";
file_put_contents("log.txt", $log_line, FILE_APPEND);

// === Save to SQLite ===
try {
    $db = new SQLite3("log.db");
    $db->exec("CREATE TABLE IF NOT EXISTS visitors (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        ip TEXT,
        country TEXT,
        region TEXT,
        city TEXT,
        town TEXT,
        lat TEXT,
        lon TEXT,
        timestamp TEXT
    )");

    $stmt = $db->prepare("INSERT INTO visitors (ip, country, region, city, town, lat, lon, timestamp)
                          VALUES (:ip, :country, :region, :city, :town, :lat, :lon, :timestamp)");
    $stmt->bindValue(':ip', $ip);
    $stmt->bindValue(':country', $country);
    $stmt->bindValue(':region', $region);
    $stmt->bindValue(':city', $city);
    $stmt->bindValue(':town', $town);
    $stmt->bindValue(':lat', $lat);
    $stmt->bindValue(':lon', $lon);
    $stmt->bindValue(':timestamp', $time);
    $stmt->execute();

// === Optional: Telegram ===
if (!empty($TELEGRAM_BOT_TOKEN) && !empty($TELEGRAM_CHAT_ID)) {
    $msg = "🕵️ Visitor Logged:\nIP: $ip\n$country, $region, $city, $town\nLat/Lon: $lat,$lon\n$timestamp";
    file_get_contents("https://api.telegram.org/bot$TELEGRAM_BOT_TOKEN/sendMessage?chat_id=$TELEGRAM_CHAT_ID&text=" . urlencode($msg));
}


} catch (Exception $e) {
    http_response_code(500);
    echo "Database error";
    exit;
}

// === Optional Output ===
echo "Logged successfully";
?>
