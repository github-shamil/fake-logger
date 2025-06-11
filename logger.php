<?php
date_default_timezone_set("Asia/Kolkata");

// Get visitor IP address
$ip = $_SERVER['REMOTE_ADDR'];

// Get geolocation data
$geo = json_decode(file_get_contents("http://ip-api.com/json/$ip?fields=status,country,regionName,city,lat,lon,query"), true);

if ($geo && $geo['status'] === 'success') {
    $country = $geo['country'];
    $region = $geo['regionName'];
    $city = $geo['city'];
    $lat = $geo['lat'];
    $lon = $geo['lon'];
} else {
    $country = $region = $city = $lat = $lon = "Unknown";
}

// Time
$time = date("Y-m-d H:i:s");

// Save to SQLite
$db = new SQLite3('log.db');
$db->exec("CREATE TABLE IF NOT EXISTS visitors (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ip TEXT,
    country TEXT,
    region TEXT,
    city TEXT,
    lat TEXT,
    lon TEXT,
    time TEXT
)");

$stmt = $db->prepare("INSERT INTO visitors (ip, country, region, city, lat, lon, time) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bindValue(1, $ip);
$stmt->bindValue(2, $country);
$stmt->bindValue(3, $region);
$stmt->bindValue(4, $city);
$stmt->bindValue(5, $lat);
$stmt->bindValue(6, $lon);
$stmt->bindValue(7, $time);
$stmt->execute();

// Also write to log.txt
$log = "IP: $ip | Country: $country | Region: $region | City: $city | Lat: $lat | Lon: $lon | Time: $time\n";
file_put_contents("log.txt", $log, FILE_APPEND);

echo "📍 Visitor tracked successfully.";
?>
